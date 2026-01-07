<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UpdatePasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
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
        $email = request('email');
        return redirect("http://172.31.226.7:5173/reset-password?token=$token&email=$email");
    })->name('password.reset');
});

Route::get('/auth/redirect/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/callback/google', [GoogleAuthController::class, 'handleGoogleCallback']);

Route::group(["prefix" => "admin", "middleware" => ["auth:sanctum", "role:admin"]], function (): void {

    Route::get("/users", [UserManagementController::class, "index"])->name("users.index");
    Route::get("/users/{user}", [UserManagementController::class, "show"])->name("users.show");
    Route::post("/users", [UserManagementController::class, "store"])->name("users.store");
    Route::put("/users/{user}", [UserManagementController::class, "update"])->name("users.update");
    Route::delete("/users/{user}", [UserManagementController::class, "destroy"])->name("users.destroy");
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
    Route::get('/activities/{activity}/photo', [ActivityController::class, 'photo'])
        ->name('activities.photo');
    Route::post('/activities/add', [ActivityController::class, 'store']);
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->controller(NotificationController::class)->group(function () {
    Route::get('/notifications', 'getUserNotifications');
    Route::middleware('role:admin')->post('/notifications/send', 'send');
});
