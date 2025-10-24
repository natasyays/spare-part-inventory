<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\SparePartUsageController;  
use App\Http\Controllers\Api\SparePartController;
use App\Http\Controllers\Api\MachineController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/spare-parts', [SparePartController::class, 'index']);
Route::post('/spare-parts/usage/record', [SparePartUsageController::class, 'store']);
Route::get('/machines', [MachineController::class, 'index']);

Route::get('/spare-parts/categories', [SparePartController::class, 'getCategories']);
Route::get('/spare-parts/updates', [SparePartController::class, 'getUpdates']);


