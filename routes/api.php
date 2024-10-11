<?php

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\AdvertisementController;

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





Route::get('/userAgent', function (Request $request) {
    return request()->header('User-Agent');
});

Route::get('/weather', [WeatherController::class, 'show']);

Route::get('/social-links', [SocialLinkController::class, 'index']);
Route::get('/social-links/{platform}', [SocialLinkController::class, 'showByPlatform']);

Route::get('/pages/slug/{slug}', [PageController::class, 'showBySlug']);

Route::get('advertisements', [AdvertisementController::class, 'index']);

Route::get('/visitors', [VisitorController::class, 'index']);
Route::get('/visitors/reports', [VisitorController::class, 'generateReports']);




Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/products', [ProductController::class, 'getAllProducts']);
Route::post('/buy-product', [ProductController::class, 'buyProduct'])->middleware('auth:sanctum');



Route::post('/products/approve/{id}', [ProductController::class, 'approvePayment']); // To approve payment
Route::post('/products/reject/{id}', [ProductController::class, 'rejectPayment']); // To reject payment
Route::get('/products/pending-payments', [ProductController::class, 'listPendingPayments']); // List pending payments
Route::get('/products/approved-payments', [ProductController::class, 'listApprovedPayments']); // List approved payments





// Routes for Commission rates
Route::post('/commissions/set-rate', [CommissionController::class, 'setCommissionRate']);
Route::get('/commissions', [CommissionController::class, 'getCommissionRates']);

// Route for Distributing Commission (called when a purchase is made)
Route::post('/commissions/distribute/{user}', [CommissionTransactionController::class, 'distributeCommission']);


Route::post('/products/create', [ProductController::class, 'createProduct']);
