<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PayementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('allusers', [UserController::class, 'allusers']);
Route::get('alladminusers', [UserController::class, 'alladminusers']);
Route::get('getUser', [AuthController::class, 'getUser']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('getCurrentUser', [AuthController::class, 'getCurrentUser']);
});
//edit step1
Route::get('edit-user/{id}', [UserController::class, 'edit']);
//edit step2
Route::post('update-user/{id}', [UserController::class, 'update']);
//delete 
Route::delete('user/{id}', [UserController::class, 'destroy']);
Route::put('users/password', [AuthController::class, 'updatePassword']);
//SEND EMAIL sendEmail
Route::post('sendemail', [UserController::class, 'sendemail']);
//SEND EMAIL sendEmail
Route::post('sendemailJob', [JobController::class, 'sendemailJob']);








//add category
Route::post('store-category', [CategoryController::class, 'store']);
//all categories
Route::get('allcategories', [CategoryController::class, 'allcategories']);
//edit step1
Route::get('edit-category/{id}', [CategoryController::class, 'edit']);
//edit step2
Route::post('update-category/{id}', [CategoryController::class, 'update']);
//delete 
Route::delete('category/{id}', [CategoryController::class, 'destroy']);

//add service
Route::post('store-service', [ServiceController::class, 'store']);
//show service
Route::get('view-service', [ServiceController::class, 'index']);
//edit step1
Route::get('edit-service/{id}', [ServiceController::class, 'edit']);
//edit step2
Route::post('update-service/{id}', [ServiceController::class, 'update']);
//all services by user
Route::middleware('auth:sanctum')->group(function () {
    Route::get('allservicesbyuser', [ServiceController::class, 'allservicesbyuser']);
});
//all services
Route::get('allservices', [ServiceController::class, 'allservices']);
//nb services
Route::get('nbservices', [ServiceController::class, 'nbservices']);
//nb services
Route::get('nb_serv_by_category', [ServiceController::class, 'nb_serv_by_category']);
//category services
Route::get('catserv/{id}', [ServiceController::class, 'catserv']);
//delete 
Route::delete('service/{id}', [ServiceController::class, 'destroy']);


//add room
Route::post('store-room', [RoomController::class, 'store']);
//all rooms by user
Route::middleware('auth:sanctum')->group(function () {
    Route::get('allroomsbyuser', [RoomController::class, 'allroomsbyuser']);
});
//all rooms
Route::get('allrooms', [RoomController::class, 'allrooms']);
//edit step1
Route::get('edit-room/{id}', [RoomController::class, 'edit']);
//edit step2
Route::post('update-room/{id}', [RoomController::class, 'update']);
//rooms by services
Route::get('roomserv/{id}', [RoomController::class, 'roomserv']);
//rooms by services
Route::get('getRoomById/{id}', [RoomController::class, 'getRoomById']);
//delete 
Route::delete('room/{id}', [RoomController::class, 'destroy']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('reservations', [ReservationController::class, 'reservations']);
});
//reservation by id
Route::get('getReservationsByRoomIdForP/{id}', [ReservationController::class, 'getReservationsByRoomIdForP']);
//reservation by id
Route::get('getReservationsByRoomId/{id}', [ReservationController::class, 'getReservationsByRoomId']);
//reservation by id
Route::get('countReservationsByRoomId/{id}', [ReservationController::class, 'countReservationsByRoomId']);
//add reservation
Route::post('store-reservation', [ReservationController::class, 'store']);
//edit step1
Route::get('edit-reservation/{id}', [ReservationController::class, 'edit']);
//edit step2
Route::post('update-reservation/{id}', [ReservationController::class, 'update']);
//delete 
Route::delete('reservations/{id}', [ReservationController::class, 'destroy']);
//edit step1
Route::middleware('auth:sanctum')->group(function () {
    Route::get('getUserByReservation', [ReservationController::class, 'getUserByReservation']);
});
//delete 
Route::post('ResetReservation/{id}', [ReservationController::class, 'ResetReservation']);
//delete 
Route::post('ResetReservationAdmin/{id}', [ReservationController::class, 'ResetReservationAdmin']);
//Update reservation date and notification
Route::post('UpdateReservation/{id}', [ReservationController::class, 'UpdateReservation']);
//Get reservationbynewst date resr
// Route::middleware('auth:sanctum')->group(function () {
    Route::get('getReservationsByRoomIdWithDateNewest/{room_id}/{reservation_id}', [ReservationController::class, 'getReservationsByRoomIdWithDateNewest']);
// });
//pass to next  
Route::post('EndToNextClientReservation/{id}', [ReservationController::class, 'EndToNextClientReservation']);
//set  
Route::post('ReservationInProgress/{id}', [ReservationController::class, 'ReservationInProgress']);
//increm 
Route::post('SetTiming/{id}', [ReservationController::class, 'SetTiming']);
//decrement 
Route::post('SetTimingDecrement/{id}', [ReservationController::class, 'SetTimingDecrement']);





//Payement store 
Route::post('payer', [PayementController::class, 'payer']);
//Get all payement by auth user
Route::middleware('auth:sanctum')->group(function () {
    Route::get('allPayements', [PayementController::class, 'allPayements']);
});
//all PayementController for superadmin notification
Route::get('AllOfPayements', [PayementController::class, 'AllOfPayements']);
//Delete Payement
Route::delete('deleteOldPayments', [PayementController::class, 'deleteOldPayments']);
//-1 Payement
Route::post('minesPayement/{id}', [PayementController::class, 'minesPayement']);
//+1 Payement
Route::post('plusPayement/{id}', [PayementController::class, 'plusPayement']);



//all jobs bu owner
Route::middleware('auth:sanctum')->group(function () {
    Route::get('alljobsbyowner', [JobController::class, 'alljobsbyowner']);
});
//all jobs bu auth
Route::middleware('auth:sanctum')->group(function () {
    Route::get('alljobsbyauth', [JobController::class, 'alljobsbyauth']);
});
//all jobs
Route::get('alljobs', [JobController::class, 'alljobs']);
//Find Job
Route::post('store-job', [JobController::class, 'store']);
//all jobs by id
Route::get('jobbyid/{id}', [JobController::class, 'jobbyid']);
//delete 
Route::post('ResetJob/{id}', [JobController::class, 'ResetJob']);
/*Route::middleware('auth:sanctum')->group(function () {
    Route::get('jobbyid/{id}', [JobController::class, 'jobbyid']);
    });*/
//all jobs by user sender
Route::middleware('auth:sanctum')->group(function () {
    Route::get('alljobsbyusersender', [JobController::class, 'alljobsbyusersender']);
});
//all jobs by user reciver
Route::middleware('auth:sanctum')->group(function () {
    Route::get('alljobsbyuserreciver', [JobController::class, 'alljobsbyuserreciver']);
});
//edit step1
Route::get('edit-job/{id}', [JobController::class, 'edit']);
//edit step2
Route::post('update-job/{id}', [JobController::class, 'update']);








Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('changepassword', [AuthController::class, 'changepassword']);
});


Route::post('storepassword', [AuthController::class, 'storepassword']);

Route::get('createpassword/{token}', [AuthController::class, 'createpassword']);

// Notification
Route::get('smsnotification', [NotificationController::class, 'smsnotification']);

// Notification
Route::post('smsnotificationuser', [UserController::class, 'smsnotificationuser']);
// Notification
Route::post('sms', [UserController::class, 'sms']);


//Statistique 
Route::get('StaticCategorie', [CategoryController::class, 'StaticCategorie']);
//Statistique 
Route::get('StaticServices', [ServiceController::class, 'StaticServices']);
//Statistique 
Route::get('StaticRoom', [RoomController::class, 'StaticRoom']);
//Statistique 
Route::get('StaticReservations', [ReservationController::class, 'StaticReservations']);
//Statistique 
Route::get('StaticPayement', [PayementController::class, 'StaticPayement']);
//Statistique 
Route::get('StaticUsers', [UserController::class, 'StaticUsers']);
//Statistique 
Route::get('StaticJobs', [JobController::class, 'StaticJobs']);