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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['cors', 'ip.screen', 'log.route']], function () {

    Route::group(['middleware' => ['token.verify']], function () {
        Route::get('v1/dev/{one_email?}/{select?}/{date?}', 'App\Http\Controllers\BookingController@dev');

        Route::post('v1/checkAvailableRoom', 'App\Http\Controllers\BookingController@checkAvailableRoom');
        Route::post('v1/booking', 'App\Http\Controllers\BookingController@booking');
        Route::get('v1/availableGuest/{booking_number?}/{select?}', 'App\Http\Controllers\BookingController@availableGuest');
        Route::post('v1/guestManager/{select?}', 'App\Http\Controllers\BookingController@guestManager');
        Route::post('v1/unlock/{user_token?}', 'App\Http\Controllers\BookingController@unlock');
        Route::get('v1/ejectBooking/{one_email?}/{booking_number?}', 'App\Http\Controllers\BookingController@ejectBooking');

        Route::get('v1/bookingTable/{one_email?}/{select?}/{date?}', 'App\Http\Controllers\BookingController@bookingTable');
        Route::get('v1/userTable/{select?}/{one_email?}', 'App\Http\Controllers\BookingController@userTable');
        Route::get('v1/roomTable', 'App\Http\Controllers\BookingController@roomTable');
        Route::get('v1/getProfile/{user_token?}', 'App\Http\Controllers\BookingController@getProfile');
        Route::get('v1/nowMeetingTable/{one_email?}', 'App\Http\Controllers\BookingController@nowMeetingTable');
        Route::get('v1/availableStat/{day?}', 'App\Http\Controllers\BookingController@availableStat');
    });

    Route::get('v1/test', 'App\Http\Controllers\BookingController@test');

});

