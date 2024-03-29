<?php

use Illuminate\Support\Facades\Route;

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
    return response()->json([
        'message' => 'Welcome to business directory API',
        'Server name' => gethostname(),
        'Server Ip' => $_SERVER['SERVER_ADDR'] ?? "Localhost",
    ]);
});
