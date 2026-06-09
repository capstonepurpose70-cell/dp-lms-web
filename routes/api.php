<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Public: Login & Register ───────────────────────────────────────────
Route::post('/login',    [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/forgot-password', [App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [App\Http\Controllers\Api\AuthController::class, 'resetPassword']);

// ── Protected (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/user',    fn(Request $r) => $r->user());

    // ── STUDENT ──────────────────────────────────────────────────────
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/dashboard',     [App\Http\Controllers\Api\Student\DashboardController::class, 'index']);
        Route::get('/subjects',      [App\Http\Controllers\Api\Student\DashboardController::class, 'subjects']);
        Route::get('/modules',       [App\Http\Controllers\Api\Student\DashboardController::class, 'modules']);
        Route::get('/grades',        [App\Http\Controllers\Api\Student\DashboardController::class, 'grades']);
        Route::get('/announcements', [App\Http\Controllers\Api\Student\DashboardController::class, 'announcements']);
        Route::get('/assignments',   [App\Http\Controllers\Api\Student\DashboardController::class, 'assignments']);
        Route::post('/enroll',       [App\Http\Controllers\Api\Student\DashboardController::class, 'enroll']);
    });

    // ── TEACHER ──────────────────────────────────────────────────────
    Route::middleware('role:teacher')->prefix('teacher')->group(function () {
        Route::get('/dashboard',     [App\Http\Controllers\Api\Teacher\DashboardController::class, 'index']);
        Route::get('/materials',     [App\Http\Controllers\Api\Teacher\DashboardController::class, 'materials']);
        Route::post('/materials',    [App\Http\Controllers\Api\Teacher\DashboardController::class, 'storeMaterial']);
        Route::get('/gradebook',     [App\Http\Controllers\Api\Teacher\DashboardController::class, 'gradebook']);
        Route::post('/grades',       [App\Http\Controllers\Api\Teacher\DashboardController::class, 'saveGrade']);
        Route::get('/announcements', [App\Http\Controllers\Api\Teacher\DashboardController::class, 'announcements']);
        Route::post('/announcements',[App\Http\Controllers\Api\Teacher\DashboardController::class, 'storeAnnouncement']);
        Route::get('/attendance',    [App\Http\Controllers\Api\Teacher\DashboardController::class, 'attendance']);
        Route::get('/students',      [App\Http\Controllers\Api\Teacher\DashboardController::class, 'students']);
        Route::delete('/materials/{id}',     [App\Http\Controllers\Api\Teacher\DashboardController::class, 'deleteMaterial']);
Route::delete('/announcements/{id}', [App\Http\Controllers\Api\Teacher\DashboardController::class, 'deleteAnnouncement']);
    });

    // ── FACULTY ──────────────────────────────────────────────────────
    Route::middleware('role:faculty')->prefix('faculty')->group(function () {
        Route::get('/dashboard',               [App\Http\Controllers\Api\Faculty\DashboardController::class, 'index']);
        Route::get('/enrollments',             [App\Http\Controllers\Api\Faculty\DashboardController::class, 'enrollments']);
        Route::post('/enrollments/{id}/approve',[App\Http\Controllers\Api\Faculty\DashboardController::class, 'approve']);
        Route::post('/enrollments/{id}/reject', [App\Http\Controllers\Api\Faculty\DashboardController::class, 'reject']);
    });

    // ── PARENT ───────────────────────────────────────────────────────
    Route::middleware('role:parent')->prefix('parent')->group(function () {
        Route::get('/dashboard',     [App\Http\Controllers\Api\ParentPortal\DashboardController::class, 'index']);
        Route::get('/child-records', [App\Http\Controllers\Api\ParentPortal\DashboardController::class, 'childRecords']);
    });
});