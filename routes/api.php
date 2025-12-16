<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverHomeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PassengerRequestController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RatingController;

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
Route::post('update-language', [AuthController::class, 'updateLanguage'])->name('update-language');
Route::get('get-language', [AuthController::class, 'getLanguage'])->name('get-language');
Route::post('delete-account', [AuthController::class, 'deleteAccount'])->name('delete-account');

Route::post('/driver/add-vehicle', [DriverHomeController::class, 'addVehicle']);
Route::get('/driver/get-vehicles', [DriverHomeController::class, 'getVehicles']);
Route::post('/driver/edit-vehicle', [DriverHomeController::class, 'editVehicle']);
Route::post('/driver/create-ride', [DriverHomeController::class, 'createRide']);
Route::post('/driver/edit-ride', [DriverHomeController::class, 'editRide']);
Route::post('/driver/delete-ride', [DriverHomeController::class, 'deleteRide']);
Route::post('/driver/cancel-ride', [DriverHomeController::class, 'cancelRide']);
Route::get('/get-all-rides-createdByDriver', [DriverHomeController::class, 'getAllRidesCreatedByDriver']);
Route::get('/driver-details', [DriverHomeController::class, 'driverDetails']);

 Route::get('/search-rides', [DriverHomeController::class, 'searchRides']);
 Route::get('/search-parcel-rides', [DriverHomeController::class, 'searchParcelRides']);


Route::post('/book-ride', [BookingController::class, 'bookRideOrParcel'])->name('book.ride');
Route::get('/get-drivers-booking', [BookingController::class, 'getDriverBookings'])->name('get-drivers-booking');
Route::get('/get-passengers-booking-requests', [BookingController::class, 'getPassengerBookingRequests'])->name('get-passengers-booking-requests');

Route::post('/confirm-booking', [BookingController::class, 'confirmBooking'])->name('confirm-booking');
Route::post('/upadte-booking-active-status', [BookingController::class, 'updateBookingActiveStatus'])->name('upadte-booking-active-status');
Route::post('/upadte-booking-complete-status', [BookingController::class, 'updateBookingCompleteStatus'])->name('upadte-booking-complete-status');

Route::post('/update-booking-active-complete-status', [BookingController::class, 'updateBookingActiveCompleteStatus']);


// Route::post('store-passenger-request', [PassengerRequestController::class, 'createRequest']);
Route::get('get-current-passenger-requests', [PassengerRequestController::class, 'listCurrentPassengerRequests']);
Route::post('/store-ride-request', [PassengerRequestController::class, 'createRideRequest']);

Route::post('/store-parcel-request', [PassengerRequestController::class, 'createParcelRequest']);
Route::get('all-ride-requests', [PassengerRequestController::class, 'getAllRideRequests']);
Route::get('all-parcel-requests', [PassengerRequestController::class, 'getAllParcelRequests']);  
Route::get('get-interested-drivers-list/{request_id}', [PassengerRequestController::class, 'getInterestedDrivers']);


// Driver make interest a request
Route::post('/driver/interest-request', [PassengerRequestController::class, 'updateRequestInterestStatus']);

// Passenger confirms a request
Route::post('request/respond-driver', [PassengerRequestController::class, 'confirmDriverByPassenger']);


Route::get('get-city', [HomeController::class, 'getCity']);
Route::get('/get-car-brands', [HomeController::class, 'getAllBrands'])->name('car-brands');
Route::get('/get-car-models/{brand}', [HomeController::class, 'getModelsByBrand'])->name('car-models');
Route::get('/get-car-colors/{model}', [HomeController::class, 'getColorsByModel'])->name('car-colors');
Route::get('/get-services', [HomeController::class, 'getAllServices']);


Route::post('/store-enquiry', [HomeController::class, 'storeEnquiry']);
Route::post('/get-enquiry-answer', [HomeController::class, 'getEnquiryAnswer']);


Route::get('privacy-policy', [ContentController::class, 'privacyPolicy']);
Route::get('terms-conditions', [ContentController::class, 'termsConditions']);

// conversation api

Route::post('chat/start', [ChatController::class, 'start']); // Start or get conversation
Route::get('chat/conversations', [ChatController::class, 'allConversation']); // List conversations with last message, timestamp, unread count
Route::post('chat/messages', [ChatController::class, 'allMessages']); // Get messages in a conversation
Route::post('chat/send', [ChatController::class, 'send']); // Send a message
Route::post('chat/mark-read', [ChatController::class, 'markRead']); // Mark messages as read



Route::get('get-driver-confirmedPendinCancelled-rides', [BookingController::class, 'getDriverConfirmedPendingancelledRides']);
Route::get('get-passenger-confirmedPendingCancelled-rides', [BookingController::class, 'getPassengerConfirmedPendingCanclledRides']);

Route::get('get-confirmation-status', [BookingController::class, 'getConfirmationStatus']);

Route::get('/get-send-response', [BookingController::class, 'getSendResponse'])->name('get-send-responses');
Route::get('/get-recived-response', [BookingController::class, 'getReceivedResponse'])->name('get-recived-responses');

Route::post('/rate', [RatingController::class, 'store']);
Route::get('/ratings', [RatingController::class, 'list']);

Route::post('/store-report', [HomeController::class, 'storeReport']);

Route::post('block-user', [HomeController::class, 'blockUser']);
Route::post('unblock-user', [HomeController::class, 'unblockUser']);
Route::get('blocked-users', [HomeController::class, 'getBlockedUsers']);
Route::get('get-user-notifications', [HomeController::class, 'getAllNotifications']);