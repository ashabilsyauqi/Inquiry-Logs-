<?php

use Illuminate\Support\Facades\Route;
use App\Models\Lead;

Route::get('/', function () {
    $leads = Lead::all();
    return view('dashboard', compact('leads'));
});
