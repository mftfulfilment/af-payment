<?php

use App\Http\Controllers\AfricasTalkingGateway;
use App\Http\Controllers\MpesaController;
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
    return view('welcome');
});

Route::get('sms', [AfricasTalkingGateway::class, 'sendMessage']);

Route::any('stk_push/{phone}', [MpesaController::class, 'stk_push']);
