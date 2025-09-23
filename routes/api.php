<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverHomeController;
use App\Http\Controllers\HomeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

 // throttle.auth used for multiple attempts

Route::middleware('auth:sanctum', 'throttle.auth')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('api.login');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
// Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
// Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
// Route::post('change-password', [AuthController::class, 'changePassword']);
// Route::post('login-via-OTP', [AuthController::class, 'loginWithOtp']);
// Route::post('login-via-google', [AuthController::class, 'googleLogin']);
// Route::post('login-via-facebook', [AuthController::class, 'facebookLogin']);
// Route::post('login-via-apple', [AuthController::class, 'appleLogin']);
Route::post('logout', [AuthController::class, 'logout']);
// Route::post('store-location', [AuthController::class, 'storeLocation']);
// Route::post('select-language', [AuthController::class, 'selectLanguage'])->name('select-language');
Route::get('get-profile', [AuthController::class, 'getProfile']);
Route::post('update-profile', [AuthController::class, 'updateProfile']);


Route::post('/driver/add-vehicle', [DriverHomeController::class, 'addVehicle']);
Route::get('/driver/get-vehicles', [DriverHomeController::class, 'getVehicles']);
Route::post('/driver/edit-vehicle/{id}', [DriverHomeController::class, 'editVehicle']);
Route::post('/driver/create-ride', [DriverHomeController::class, 'createRide']);
Route::post('/driver/edit-ride/{ride_id}', [DriverHomeController::class, 'editRide']);

 Route::get('/search-rides', [DriverHomeController::class, 'searchRides']);






Route::get('get-city', [HomeController::class, 'getCity']);
Route::get('/get-car-brands', [HomeController::class, 'getAllBrands'])->name('car-brands');
Route::get('/get-car-models/{brand}', [HomeController::class, 'getModelsByBrand'])->name('car-models');
Route::get('/get-car-colors/{model}', [HomeController::class, 'getColorsByModel'])->name('car-colors');
Route::get('/get-services', [HomeController::class, 'getAllServices']);
