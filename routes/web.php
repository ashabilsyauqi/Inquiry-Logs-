<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use Carbon\Carbon;

Route::get('/', function (Request $request) {
    $filter = $request->query('filter', 'all');
    $query = Lead::query();

    if ($filter === 'daily') {
        $query->whereDate('created_at', Carbon::today());
    } elseif ($filter === 'monthly') {
        $query->whereMonth('created_at', Carbon::now()->month)
              ->whereYear('created_at', Carbon::now()->year);
    } elseif ($filter === 'yearly') {
        $query->whereYear('created_at', Carbon::now()->year);
    }

    // Get the filtered leads
    $leads = $query->latest()->get();

    // Calculate metrics
    $totalLeads = $leads->count();
    $totalFollowUp = $leads->where('stage', 'Follow Up')->count();
    $totalPayment = $leads->where('stage', 'Payment')->count();
    $totalClosed = $leads->where('stage', 'Closed')->count();

    return view('dashboard', compact(
        'leads', 'filter', 'totalLeads', 'totalFollowUp', 'totalPayment', 'totalClosed'
    ));
});
