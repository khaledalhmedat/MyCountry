<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ComplaintController;




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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegisterController::class, 'register']);

Route::post('/login', [LoginController::class, 'login']);

Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('citizen/complaints')->group(function () {
        Route::post('/submit', [ComplaintController::class, 'submitComplaint']);
        Route::get('/', [ComplaintController::class, 'getUserComplaints']);
    });
});




use App\Http\Controllers\AuthController as EmployeeAuthController;
use App\Http\Controllers\EmployeeController as AdminEmployeeController;

Route::prefix('employee')->group(function () {
    Route::post('/login', [EmployeeAuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [EmployeeAuthController::class, 'logout']);
        Route::get('/me', [EmployeeAuthController::class, 'me']);
    });
});

Route::prefix('admin')->group(function () {
    Route::prefix('employees')->group(function () {
        Route::post('/', [AdminEmployeeController::class, 'createEmployee']);
        Route::get('/', [AdminEmployeeController::class, 'getAllEmployees']);
        Route::get('/type/{typeId}', [AdminEmployeeController::class, 'getEmployeesByType']);
        Route::get('/{id}', [AdminEmployeeController::class, 'getEmployee']);
        Route::put('/{id}', [AdminEmployeeController::class, 'updateEmployee']);
        Route::delete('/{id}', [AdminEmployeeController::class, 'deleteEmployee']);
    });
});