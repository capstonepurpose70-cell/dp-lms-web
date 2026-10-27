<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Public: Login ─────────────────────────────────────────────────────
// Throttled per IP — the mobile login must not be open to brute force
Route::post('/login',    [App\Http\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:20,1');
// 410 Gone — replaced by /activate (students) and /parent/activate (parents)
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);

// ── Public: Student activation (LRN + name + email code) ──
Route::post('/activate',        [App\Http\Controllers\Api\StudentActivationController::class, 'start'])->middleware('throttle:20,1');
Route::post('/activate/verify', [App\Http\Controllers\Api\StudentActivationController::class, 'verify'])->middleware('throttle:20,1');
Route::post('/activate/resend', [App\Http\Controllers\Api\StudentActivationController::class, 'resend'])->middleware('throttle:5,10');
Route::post('/forgot-password',       [App\Http\Controllers\Api\AuthController::class, 'forgotPassword'])->middleware('throttle:10,1');
Route::post('/forgot-password/reset', [App\Http\Controllers\Api\AuthController::class, 'resetPassword'])->middleware('throttle:20,1');

// ── Public: Parent activation / recovery (code from the child's account) ──
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/parent/activate', [App\Http\Controllers\Api\ParentLinkController::class, 'activate']);
    Route::post('/parent/recover',  [App\Http\Controllers\Api\ParentLinkController::class, 'recover']);
});

// ── Protected (Sanctum token)
// approved: rejected / banned while signed in to the app → 403 + token deleted
Route::middleware(['auth:sanctum', 'approved'])->group(function () {

    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/user',    fn(Request $r) => $r->user());

    // ── In-app notifications (every role) — e.g. "Juan arrived at school" ──
    Route::get('/notifications',                [App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read',     [App\Http\Controllers\NotificationController::class, 'read']);
    Route::post('/notifications/read-all',      [App\Http\Controllers\NotificationController::class, 'readAll']);

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

        // Parent link — one-time code (the app renders the QR)
        Route::post('/parent-link', [App\Http\Controllers\Api\ParentLinkController::class, 'generate'])
            ->middleware('throttle:10,10');

        // Class attendance — enter the code shown by the teacher
        Route::get('/class-attendance',            [App\Http\Controllers\Api\Student\ClassAttendanceController::class, 'index']);
        Route::post('/class-attendance/{session}', [App\Http\Controllers\Api\Student\ClassAttendanceController::class, 'mark'])
            ->whereNumber('session')->middleware('throttle:30,1');

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
        Route::get('/attendance',    [App\Http\Controllers\Api\Teacher\DashboardController::class, 'attendance']); // gate scans (legacy)
        Route::get('/students',      [App\Http\Controllers\Api\Teacher\DashboardController::class, 'students']);

        // Class attendance (teacher opens a window, students enter the code)
        Route::get('/class-attendance',                    [App\Http\Controllers\Api\Teacher\ClassAttendanceController::class, 'index']);
        Route::post('/class-attendance',                   [App\Http\Controllers\Api\Teacher\ClassAttendanceController::class, 'store']);
        Route::get('/class-attendance/{session}',          [App\Http\Controllers\Api\Teacher\ClassAttendanceController::class, 'show'])->whereNumber('session');
        Route::post('/class-attendance/{session}/code',    [App\Http\Controllers\Api\Teacher\ClassAttendanceController::class, 'code'])->whereNumber('session');
        Route::post('/class-attendance/{session}/close',   [App\Http\Controllers\Api\Teacher\ClassAttendanceController::class, 'close'])->whereNumber('session');
        Route::post('/class-attendance/{session}/mark',    [App\Http\Controllers\Api\Teacher\ClassAttendanceController::class, 'mark'])->whereNumber('session');

        // Quizzes / assignments (file-based)
        Route::get('/assignments',             [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'index']);
        Route::post('/assignments',            [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'store']);
        Route::get('/assignments/{id}',        [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'show']);
        Route::delete('/assignments/{id}',     [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'destroy']);
        Route::post('/submissions/{id}/grade', [App\Http\Controllers\Api\Teacher\AssignmentController::class, 'grade']);
        Route::delete('/materials/{id}',     [App\Http\Controllers\Api\Teacher\DashboardController::class, 'deleteMaterial']);
        Route::delete('/announcements/{id}', [App\Http\Controllers\Api\Teacher\DashboardController::class, 'deleteAnnouncement']);
    });

    // ── PARENT ───────────────────────────────────────────────────────
    Route::middleware('role:parent')->prefix('parent')->group(function () {
        Route::get('/dashboard',     [App\Http\Controllers\Api\ParentPortal\DashboardController::class, 'index']);
        Route::get('/child-records', [App\Http\Controllers\Api\ParentPortal\DashboardController::class, 'childRecords']);
        Route::get('/attendance',    [App\Http\Controllers\Api\ParentPortal\DashboardController::class, 'attendance']);
        Route::get('/class-attendance', [App\Http\Controllers\Api\ParentPortal\DashboardController::class, 'classAttendance']);
    });

    // Messaging (shared: student <-> teacher)
    Route::prefix('messages')->group(function () {
        Route::get('/',               [App\Http\Controllers\Api\MessageController::class, 'conversations']);
        Route::get('/contacts',       [App\Http\Controllers\Api\MessageController::class, 'contacts']);
        Route::get('/thread/{user}',  [App\Http\Controllers\Api\MessageController::class, 'thread']);
        Route::post('/thread/{user}', [App\Http\Controllers\Api\MessageController::class, 'send']);
        // Edit / unsend / typing for the app's chat
        Route::post('/typing/{user}',    [App\Http\Controllers\Api\MessageController::class, 'typing'])->middleware('throttle:60,1');
        Route::patch('/{message}',       [App\Http\Controllers\Api\MessageController::class, 'update'])->whereNumber('message');
        Route::delete('/{message}',      [App\Http\Controllers\Api\MessageController::class, 'destroy'])->whereNumber('message');
    });

});