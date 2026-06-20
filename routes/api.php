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
        Route::get('/grades',        [App\Http\Controllers\Api\Student\DashboardController::class, 'grades']);
        Route::get('/announcements', [App\Http\Controllers\Api\Student\DashboardController::class, 'announcements']);
        Route::get('/assignments',              [App\Http\Controllers\Api\Student\AssignmentController::class, 'index']);
        Route::get('/assignments/{id}',         [App\Http\Controllers\Api\Student\AssignmentController::class, 'show']);
        Route::post('/assignments/{id}/submit', [App\Http\Controllers\Api\Student\AssignmentController::class, 'submit']);
        Route::post('/enroll',       [App\Http\Controllers\Api\Student\DashboardController::class, 'enroll']);

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

// ── TEMPORARY FCM DEBUG (alisin pagkatapos mag-test) ──────────────────────
Route::get('/fcm-debug/{secret}', function ($secret) {
    if ($secret !== 'dplms123') abort(403);

    // 1) Credentials (env base64 -> local file)
    $b64 = env('FIREBASE_CREDENTIALS_BASE64');
    $creds = null;
    $source = null;
    if (!empty($b64)) {
        $creds = json_decode(base64_decode($b64), true);
        $source = 'env';
    }
    if (!$creds && file_exists(storage_path('app/firebase/firebase-service-account.json'))) {
        $creds = json_decode(file_get_contents(storage_path('app/firebase/firebase-service-account.json')), true);
        $source = 'file';
    }
    if (!$creds) {
        return response()->json([
            'step'  => 'credentials',
            'error' => 'NO CREDENTIALS — env var FIREBASE_CREDENTIALS_BASE64 hindi naset sa Railway?',
        ]);
    }

    // 2) Tokens in DB
    $tokens = \App\Models\FcmToken::pluck('token')->all();
    if (empty($tokens)) {
        return response()->json([
            'step'         => 'tokens',
            'creds_source' => $source,
            'error'        => 'WALANG token sa fcm_tokens table (hindi naka-register ang student device)',
        ]);
    }

    // 3) Access token (OAuth2)
    try {
        $sa = new \Google\Auth\Credentials\ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $creds
        );
        $access = $sa->fetchAuthToken()['access_token'] ?? null;
    } catch (\Throwable $e) {
        return response()->json([
            'step'         => 'access_token',
            'creds_source' => $source,
            'error'        => $e->getMessage(),
        ]);
    }

    if (!$access) {
        return response()->json([
            'step'  => 'access_token',
            'error' => 'walang access_token na nakuha',
        ]);
    }

    // 4) Send to the first token, return the RAW FCM response
    $res = \Illuminate\Support\Facades\Http::withToken($access)->post(
        'https://fcm.googleapis.com/v1/projects/dp-lms/messages:send',
        [
            'message' => [
                'token' => $tokens[0],
                'notification' => [
                    'title' => 'Debug test',
                    'body'  => 'Kung nakita mo to, OK ang FCM send!',
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'dp_lms_channel',
                        'sound'      => 'default',
                    ],
                ],
            ],
        ]
    );

    return response()->json([
        'creds_source' => $source,
        'token_count'  => count($tokens),
        'fcm_status'   => $res->status(),
        'fcm_response' => $res->json(),
    ]);
});

// ── TEMPORARY ANNOUNCEMENT-TARGET DEBUG (alisin pagkatapos) ────────────────
// Open: /api/ann-debug/dplms123?teacher=TEACHER_EMAIL&student=STUDENT_EMAIL
Route::get('/ann-debug/{secret}', function (\Illuminate\Http\Request $request, $secret) {
    if ($secret !== 'dplms123') abort(403);

    $teacher = \App\Models\User::where('email', $request->query('teacher'))->first();
    if (!$teacher) {
        return response()->json(['error' => 'teacher email not found — gamitin ?teacher=email&student=email']);
    }

    // Sections this teacher handles (BOTH sources, same as dashboard).
    $sectionIds = \App\Models\TeacherSubject::where('user_id', $teacher->id)->pluck('section_id')
        ->merge(\App\Models\TeacherAssignment::where('user_id', $teacher->id)->pluck('section_id'))
        ->filter()->unique()->values();

    $studentIds = \App\Models\User::where('role', 'student')->where('status', 'approved')
        ->whereIn('section_id', $sectionIds)->pluck('id');

    $student = \App\Models\User::where('email', $request->query('student'))->first();

    // Try an actual push to the targeted students (so makikita mo kung dumating).
    app(\App\Services\PushNotificationService::class)->sendToUsers(
        $studentIds->all(),
        'Ann-debug test',
        'Kung nakita mo to, tama ang announcement targeting!',
        ['type' => 'announcement']
    );

    return response()->json([
        'teacher'                => ['id' => $teacher->id, 'email' => $teacher->email],
        'teacher_section_ids'    => $sectionIds,
        'targeted_student_count' => $studentIds->count(),
        'targeted_with_tokens'   => \App\Models\FcmToken::whereIn('user_id', $studentIds)->count(),
        'test_student'           => $student ? [
            'id'                  => $student->id,
            'status'              => $student->status,
            'section_id'          => $student->section_id,
            'in_teacher_sections' => $sectionIds->contains($student->section_id),
            'has_token'           => \App\Models\FcmToken::where('user_id', $student->id)->exists(),
        ] : 'student email not found (idagdag ?student=email)',
    ]);
});