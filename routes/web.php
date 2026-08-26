<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'name' => 'Novel Platform API',
        'version' => '1.0.0',
        'docs' => route('health'),
        'status' => 'running',
        'time' => now()->toDateTimeString(),
    ]);
});

// 健康检查
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toDateTimeString(),
        'env' => config('app.env'),
    ]);
})->name('health');
