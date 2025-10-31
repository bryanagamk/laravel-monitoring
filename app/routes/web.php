<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetricsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Prometheus Metrics Endpoint
Route::get('/metrics', 'MetricsController');

// Load Testing Endpoints
Route::prefix('test')->group(function () {
    Route::get('/simple', 'LoadTestController@simple');
    Route::get('/cpu', 'LoadTestController@cpuIntensive');
    Route::get('/memory', 'LoadTestController@memoryIntensive');
    Route::get('/database', 'LoadTestController@database');
    Route::get('/cache', 'LoadTestController@cache');
    Route::get('/mixed', 'LoadTestController@mixed');
    Route::get('/slow', 'LoadTestController@slow');
});

// Products API Endpoints
Route::prefix('api/products')->group(function () {
    Route::get('/', 'ProductController@index');
    Route::get('/categories', 'ProductController@categories');
    Route::get('/stats', 'ProductController@stats');
    Route::get('/{id}', 'ProductController@show');
    Route::post('/sync', 'ProductController@sync');
    Route::post('/sync/{apiId}', 'ProductController@syncOne');
});
