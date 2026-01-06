<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UpdatePasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::middleware('auth:sanctum')->post('/logout', 'logout');
    Route::post('/register', 'register')->name('register');
    Route::post('/forgot-password',  'sendResetLinkEmail');
    Route::post('/reset-password',  'reset');
    Route::get('/reset-password/{token}', function ($token) {
        return redirect("https://frontend.example.com/reset-password?token=$token");
    })->name('password.reset');
});

Route::get('/auth/redirect/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/callback/google', [GoogleAuthController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->controller(UserController::class)->group(function () {
    Route::get('/users', 'index')->middleware('role:admin')->name('users.index');
    Route::get('/users/{user}', 'show')->name('users.show');
    Route::put('/users/{user}', 'update')->name('users.update');
    Route::delete('/users/{user}', 'destroy')->name('users.destroy');
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::get("/user", fn(Request $request): JsonResponse => $request->user())->name("user.profile");
    Route::get("/profile", [UserProfileController::class, "show"]);
    Route::put("/profile", [UserProfileController::class, "update"]);
    Route::put("/auth/change-password", [UpdatePasswordController::class, "updatePassword"]);
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/stats', [ActivityController::class, 'stats']);
    Route::get('/activities/{activity}', [ActivityController::class, 'show']);
    Route::post('/activities/start', [ActivityController::class, 'start']);
    Route::post('/activities/finish/{activity}', [ActivityController::class, 'finish']);
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->controller(NotificationController::class)->group(function () {
    Route::get('/notifications', 'getUserNotifications');
    Route::middleware('role:admin')->post('/notifications/send', 'send');
});
