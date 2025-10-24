<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SparePartController;
use App\Http\Controllers\Api\SparePartUsageController;


Route::get('/inventory', function () {
    return view('spare-parts');
});

Route::get('/', function () {
    return view('spare-parts');
});

// Route::get('/usage', function () {
//     return view('spare-part-usage');
// });