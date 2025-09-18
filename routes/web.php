<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CarController;


Route::fallback(function () {
    return response()->view('404', [], 404);
});
Route::get('/', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('forgot-password');
Route::post('/forgot-password-link', [AdminAuthController::class, 'forgotPasswordLink'])->name('forgot-password-link');
Route::post('/create-new-password', [AdminAuthController::class, 'createNewPassword'])->name('create-new-password');
Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/update-password', [AdminAuthController::class, 'updatePassword'])->name('update-password');
Route::get('/global-search', [GlobalSearchController::class, 'search'])->name('global-search');
Route::get('/privacy-policy', [ContactUsController::class, 'privacyPolicy'])->name('privacy-policy');

Route::get('/home', [DashboardController::class, 'appHome'])->name('app-home');
Route::get('/privacy-policy', [DashboardController::class, 'appPrivacyPolicy'])->name('app-privacy-policy');
Route::get('/term-conditions', [DashboardController::class, 'appTermCondition'])->name('app-term-services');






// Routes with the same prefix for both Admin and Investor
Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
    // Admin Dashboard
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'role:1']], function () {
        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        // Route::get('all-users', [UserController::class, 'usersList'])->name('all-users');
        Route::delete('delete-user', [UserController::class, 'deleteUser'])->name('deleteUser');

        Route::get('all-cities', [CityController::class, 'cityList'])->name('all-cities');
        Route::get('cities', [CityController::class, 'addCity'])->name('cities');
        Route::post('store-city', [CityController::class, 'storeCity'])->name('store-city');
        Route::get('{id}/edit-city', [CityController::class, 'editCity'])->name('edit-city');
        Route::put('upadte-city/{id}', [CityController::class, 'updateCity'])->name('update-city');


        Route::get('all-cars', [CarController::class, 'carList'])->name('all-cars');
         Route::get('car', [CarController::class, 'addCar'])->name('car');
         Route::post('store-car', [CarController::class, 'storeCar'])->name('store-car');
         Route::get('{id}/edit-car', [CarController::class, 'editCar'])->name('edit-car');
        Route::put('upadte-car/{id}', [CarController::class, 'updateCar'])->name('update-car');

    });

    

});



