<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\ProductController;

use App\Http\Controllers\CategoryController; 

use App\Http\Controllers\UserController;

use App\Http\Controllers\PaymentController;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\OAuthController;

use App\Http\Controllers\OrderController;


//Routing login dan register untuk user
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


//Routing login untuk admin
Route::post('/loginAdmin', [AuthController::class, 'loginAdmin']);


//routing untuk oauth2 google
Route::group(['middleware' => ['web']], function () {
    Route::get('/oauth/register', [OAuthController::class, 'redirect']);
    Route::get('/auth/google/callback', [OAuthController::class, 'callback']);
});


//Otorisasi hanya untuk role admin
Route::middleware(['admin-auth'])->group(function() {

    //Routing product
    Route::post('/products', [ProductController::class, 'store']); //tambah produk
    Route::delete('/products/{id}', [ProductController::class, 'delete']); //delete produk
    Route::put('/products/{id}', [ProductController::class, 'update']); //update produk

    //Routing category
    Route::post('/category', [CategoryController::class, 'create']); //tambah kategori
    Route::put('/category/{id}', [CategoryController::class, 'update']); //update kategori
    Route::delete('/category/{id}', [CategoryController::class, 'delete']); //delete kategori

    //Routing order
    Route::get('/order', [OrderController::class, 'index']); //menampilkan semua order yang ada
    Route::put('/order/status/{id}', [OrderController::class, 'updateStatus']); //update status order
    Route::delete('/order/{id}', [OrderController::class, 'delete']); // delete order
});


//Otorisasi untuk role admin dan user
Route::middleware(['user-auth'])->group(function() {

    //Routing product
    Route::get('/products', [ProductController::class, 'index']); //menampilkan semua produk
    Route::get('/products/{id}', [ProductController::class, 'ShowById']); //menampilka produk berdasarkan id

    //Routing category
    Route::get('/category', [CategoryController::class, 'showAll']); //menampilkan semua kategori

    //Routing kelola user
    Route::put('/user/{id}', [UserController::class, 'updateUser']); //update user
    Route::put('/user/password/{id}', [UserController::class, 'updatePassword']); //update password user
    Route::get('/user', [UserController::class, 'getUser']); //mengambil informasi user

    //Routing order
    Route::post('/order', [OrderController::class, 'create']); //membuat order
    Route::get('/order/{id}', [OrderController::class, 'showOrderByIdUser']); //menampilkan order dari user tertentu
});

Route::post('/create-transaction', [PaymentController::class, 'createTransaction']); //pemabayaran menggunakan midtrans