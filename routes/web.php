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
    return view('welcome');
});

use App\Http\Controllers\OAuthController; 

use App\Http\Controllers\PaymentController;

//routing untuk callback setelah redirect
Route::get('/auth/google/callback', [OAuthController::class, 'callback']);

//Routing pembayaran menggunakan midtrans
Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook']);
Route::post('/create-transaction', [PaymentController::class, 'createTransaction']);
Route::post('/midtrans-notification', [PaymentController::class, 'handleWebhook']);

