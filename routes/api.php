<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComponentsControllers;
use App\Http\Controllers\Api\ComponentSettingsController;
use App\Http\Controllers\Api\CustomComponentFieldsController;
use App\Http\Controllers\Api\CustomizeComponentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeleteWebsiteController;
use App\Http\Controllers\Api\FeedBackController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\GoogleSocialiteController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\OtpVerificationControlller;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\WebsiteSettingsController;
use App\Http\Controllers\Api\WordpressComponentController;
use App\Http\Controllers\Api\PreBookingController;
use Google\Service\Monitoring\Custom;

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

Route::post('/register',[RegistrationController::class,'store']);
Route::post('/login',[AuthController::class,'Login'])->name('login');
//Login By Google
// Route::get('auth/google', [GoogleSocialiteController::class, 'redirectToGoogle']);
// Route::get('/auth/google/callback', [GoogleSocialiteController::class, 'handleCallback']);
Route::get('/auth/google/register', [GoogleSocialiteController::class, 'handleGoogleLogin']);

Route::get('email/verify/{id}',[VerificationController::class,'verify'])->name('verification.verify');
Route::post('/forgot-password',[ForgotPasswordController::class,'forgotPassword']);
Route::post('/reset-password',[ResetPasswordController::class,'resetPassword']);


//Authenticated Group Routes
Route::middleware('auth:api')->group(function () {
    //Unverified Routes

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/dashboard',[DashboardController::class,'index'])->middleware('verifiedEmail');
    Route::post('/feedback',[FeedBackController::class,'feedback']);
    Route::post('/update-left-fields',[GoogleSocialiteController::class,'updateLeftFields']);
    Route::middleware('throttle:3,20')->get('email/resend',[VerificationController::class,'resend'])->name('verification.resend');
    //Delete Website On User Request
    Route::post('/get-otp',[OtpVerificationControlller::class,'generateOtp']);
    Route::post('/verify-otp',[OtpVerificationControlller::class,'verifyOtp']);
    Route::post('/delete-website',[DeleteWebsiteController::class,'deleteWebsite']);
    Route::post('/logout',[AuthController::class,'logout']);

    //End Unverified Routes

    //Email Verified Routes
    Route::middleware('verified')->group(function () {
    Route::post('/agency-website-details',[ComponentsControllers::class,'agencyWebsiteDetails']);
    // Route::get('/agency-website-info/{agency_id}',[DashboardController::class,'getAgencyWebsiteInfo']);
    Route::get('/get-website-categories',[DashboardController::class,'getWebsiteCategories']);

    Route::post('store-components', [ComponentsControllers::class, 'sendComponentToWordpress' ]);
    Route::post('components/regenerate', [ComponentsControllers::class, 'regenerateComponents' ]);
    Route::get('/get-active-components',[ComponentsControllers::class,'getActiveWordpressComponents']);
    Route::get('/fetch-active-components-detail',[ComponentsControllers::class,'fetchActiveComponentsDetail']);
    Route::get('/get-components-global-colors',[ComponentsControllers::class,'getWordpressGlobalColors']);
    Route::post('/update-global-colors',[ComponentsControllers::class,'updateWordpressGlobalColors']);
    Route::post('/add-global-colors',[ComponentsControllers::class,'addWordpressGlobalColors']);

    Route::get('/fetch-components',[CustomizeComponentController::class,'fetchComponent']);
    Route::get('/color-combinations',[CustomizeComponentController::class,'getColorCombination']);
    Route::post('/update-color-combination',[CustomizeComponentController::class,'updateColorCombination']);
    Route::get('/get-fonts',[CustomizeComponentController::class,'getFont']);
    Route::post('/change-component',[CustomizeComponentController::class,'updateComponent']);
    Route::post('/change-font-family',[CustomizeComponentController::class,'updateFont']);
    Route::post('update-component-position',[CustomizeComponentController::class,'updateComponentPosition']);

    Route::get('settings',[WebsiteSettingsController::class,'settings']);
    Route::post('/update-settings',[WebsiteSettingsController::class,'updateSettings']);

    Route::get('get-component-form-fields',[CustomComponentFieldsController::class,'getFormFields']);
    Route::post('update-component-form-fields',[CustomComponentFieldsController::class,'updateComponentFormFields']);
    Route::get('/get-social-links',[CustomComponentFieldsController::class,'getSocialLinks']);
    Route::post('/update-social-links',[CustomComponentFieldsController::class,'updateSocialLinks']);

    Route::post('upload-images',[ImageController::class,'uploadImages']);
    Route::get('uploaded-images',[ImageController::class,'getComponentImages']);
    Route::delete('delete-uploaded-images',[ImageController::class,'deleteUploadedImages']);
    });
    //End of Verified Routes
});
//End of Authenticated Group Routes

Route::post('pre-booking', [PreBookingController::class,'saveEmailForPreBooking']);

