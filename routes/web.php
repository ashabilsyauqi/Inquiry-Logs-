<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Setting;
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
    $totalInquiries = $leads->where('stage', 'Inquiries')->count();
    $totalFollowUp = $leads->where('stage', 'Follow Up')->count();
    $totalPayment = $leads->where('stage', 'Payment')->count();
    $totalClosed = $leads->where('stage', 'Closed')->count();

    return view('dashboard', compact(
        'leads', 'filter', 'totalLeads', 'totalInquiries', 'totalFollowUp', 'totalPayment', 'totalClosed'
    ));
});

Route::post('/leads/{id}/update', function (Request $request, $id) {
    $lead = Lead::findOrFail($id);
    
    // Update fields if they are provided
    if ($request->has('name')) {
        $lead->name = $request->input('name');
    }
    if ($request->has('notes')) {
        $lead->notes = $request->input('notes');
    }
    if ($request->has('priority')) {
        $lead->priority = (int) $request->input('priority');
    }
    
    $lead->save();
    return redirect()->back();
});
