<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Public: Login & Register ───────────────────────────────────────────
Route::post('/login',    [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/forgot-password', [App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);

// ── Protected (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/user',    fn(Request $r) => $r->user());

    // ── FCM push tokens (kahit anong naka-login na user) ──────────────
    Route::post('/fcm-token',   [App\Http\Controllers\Api\FcmTokenController::class, 'store']);
    Route::delete('/fcm-token', [App\Http\Controllers\Api\FcmTokenController::class, 'destroy']);

    // ── STUDENT ──────────────────────────────────────────────────────
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/dashboard',     [App\Http\Controllers\Api\Student\DashboardController::class, 'index']);
        Route::get('/subjects',      [App\Http\Controllers\Api\Student\DashboardController::class, 'subjects']);
        Route::get('/modules',       [App\Http\Controllers\Api\Student\DashboardController::class, 'modules']);

        // ── Learning material interactions (view / like / comment / detail) ──
        // These map to MaterialInteractionController (methods already existed;
        // only the routes were missing, which caused 404 on like & comment).
        Route::get('/materials/{id}',           [App\Http\Controllers\Api\MaterialInteractionController::class, 'studentDetail']);
        Route::post('/materials/{id}/view',     [App\Http\Controllers\Api\MaterialInteractionController::class, 'studentView']);
        Route::post('/materials/{id}/like',     [App\Http\Controllers\Api\MaterialInteractionController::class, 'studentToggleLike']);
        Route::post('/materials/{id}/comments', [App\Http\Controllers\Api\MaterialInteractionController::class, 'studentAddComment']);
        Route::get('/grades',        [App\Http\Controllers\Api\Student\DashboardController::class, 'grades']);
        Route::get('/announcements', [App\Http\Controllers\Api\Student\DashboardController::class, 'announcements']);
        Route::get('/assignments',              [App\Http\Controllers\Api\Student\AssignmentController::class, 'index']);
        Route::get('/assignments/{id}',         [App\Http\Controllers\Api\Student\AssignmentController::class, 'show']);
        Route::post('/assignments/{id}/submit', [App\Http\Controllers\Api\Student\AssignmentController::class, 'submit']);
        Route::post('/enroll',       [App\Http\Controllers\Api\Student\DashboardController::class, 'enroll']);
        Route::get('/school-years',  [App\Http\Controllers\Api\Student\DashboardController::class, 'schoolYears']);

        // Face registration (mobile camera)
        Route::get('/face',  [App\Http\Controllers\Api\Student\FaceRegistrationController::class, 'show']);
        Route::post('/face', [App\Http\Controllers\Api\Student\FaceRegistrationController::class, 'store']);
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

        // Quizzes / assignments (file-based)
        Route::get('/assignments',             [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'index']);
        Route::post('/assignments',            [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'store']);
        Route::get('/assignments/{id}',        [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'show']);
        Route::delete('/assignments/{id}',     [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'destroy']);
        Route::post('/submissions/{id}/grade', [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'grade']);
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

    // Messaging (shared: student <-> teacher)
    Route::prefix('messages')->group(function () {
        Route::get('/',               [App\Http\Controllers\Api\MessageController::class, 'conversations']);
        Route::get('/contacts',       [App\Http\Controllers\Api\MessageController::class, 'contacts']);
        Route::get('/thread/{user}',  [App\Http\Controllers\Api\MessageController::class, 'thread']);
        Route::post('/thread/{user}', [App\Http\Controllers\Api\MessageController::class, 'send']);
    });

});