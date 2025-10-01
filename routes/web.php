<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EnquiryController;


Route::fallback(function () {
    return response()->view('404', [], 404);
});
Route::get('/', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::get('/login', function () {
    return redirect('/');
})->name('login.redirect');
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
        Route::get('all-drivers', [UserController::class, 'driversList'])->name('all-drivers');
        Route::delete('delete-user', [UserController::class, 'deleteUser'])->name('deleteUser');
        Route::post('verify-user', [UserController::class, 'verifyUser'])->name('verifyUser');
        Route::post('reject-user', [UserController::class, 'rejectUser'])->name('rejectUser');

        Route::post('verify-user', [UserController::class, 'verifyUser']);
        Route::post('reject-user', [UserController::class, 'rejectUser']);


        Route::get('all-cities', [CityController::class, 'cityList'])->name('all-cities');
        Route::get('cities', [CityController::class, 'addCity'])->name('cities');
        Route::post('store-city', [CityController::class, 'storeCity'])->name('store-city');
        Route::get('{id}/edit-city', [CityController::class, 'editCity'])->name('edit-city');
        Route::put('upadte-city/{id}', [CityController::class, 'updateCity'])->name('update-city');
        Route::delete('delete-city', [CityController::class, 'deleteCity'])->name('deleteCity');


        Route::get('all-cars', [CarController::class, 'carList'])->name('all-cars');
         Route::get('car', [CarController::class, 'addCar'])->name('car');
         Route::post('store-car', [CarController::class, 'storeCar'])->name('store-car');
         Route::get('{id}/edit-car', [CarController::class, 'editCar'])->name('edit-car');
        Route::put('upadte-car/{id}', [CarController::class, 'updateCar'])->name('update-car');
        Route::delete('delete-car', [CarController::class, 'deleteCar'])->name('deleteCar');


       Route::get('all-services', [CarController::class, 'servicesList'])->name('all-services');
       Route::get('service', [CarController::class, 'addService'])->name('service');
        Route::post('store-service', [CarController::class, 'storeService'])->name('store-service');
        Route::get('{id}/edit-service', [CarController::class, 'editService'])->name('edit-service');
        Route::put('upadte-service/{id}', [CarController::class, 'updateService'])->name('update-service');
       Route::delete('delete-service', [CarController::class, 'deleteService'])->name('deleteService');  

        Route::get('all-queries', [EnquiryController::class, 'allQueries'])->name('all-query');
        Route::delete('delete-query', [EnquiryController::class, 'deleteQuery'])->name('deleteQuery');
        Route::post('answer-query', [EnquiryController::class, 'answerQuery'])->name('answer-query');

        Route::get('edit-privacy-policy', [EnquiryController::class, 'editPrivacyPolicy'])->name('privacy-policy.edit');
        Route::post('update-privacy-policy', [EnquiryController::class, 'updatePrvacyPolicy'])->name('privacy-policy.update');
        Route::get('edit-terms-conditions', [EnquiryController::class, 'editTermsConditions'])->name('terms-comditions-edit');
        Route::post('update-terms-conditions', [EnquiryController::class, 'updateTermsConditions'])->name('update-terms-comditions');

    });

    

});



