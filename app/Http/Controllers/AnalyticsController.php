<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function getChartData(Request $request)
    {
        $period = $request->query('period', 'daily');
        $accountId = $request->query('account_id', 'all');

        $user = Auth::user();
        if ($user && !$user->isCeo() && $user->wa_account_id) {
            $accountId = $user->wa_account_id;
        }

        $query = Lead::query();
        if ($accountId !== 'all') {
            $query->where('wa_account_id', $accountId);
        }

        $labels = [];
        $data = [];

        if ($period === 'daily') {
            // Last 14 Days
            for ($i = 13; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('d M');
                $count = (clone $query)->whereDate('created_at', $date)->count();
                $data[] = $count;
            }
        } elseif ($period === 'weekly') {
            // Last 8 Weeks
            for ($i = 7; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                $labels[] = $startOfWeek->format('d M');
                $count = (clone $query)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
                $data[] = $count;
            }
        } elseif ($period === 'monthly') {
            // Last 12 Months
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M Y');
                $count = (clone $query)->whereMonth('created_at', $date->month)
                                      ->whereYear('created_at', $date->year)->count();
                $data[] = $count;
            }
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'period' => $period
        ]);
    }
}
