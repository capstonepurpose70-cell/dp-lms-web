<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\StudentActivationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ParentActivationController;
use App\Http\Controllers\Student\ParentLinkController as StudentParentLinkController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\FaceVerificationController;
use App\Http\Controllers\Admin\LrnController;
use App\Http\Controllers\Teacher\EnrollmentController as TeacherEnrollmentController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\FaceRegistrationController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\GradebookController;
use App\Http\Controllers\Teacher\MaterialController;
use App\Http\Controllers\Teacher\AnnouncementController;
use App\Http\Controllers\ParentPortal\ParentDashboardController;
use App\Http\Controllers\Teacher\TeacherStudentController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Teacher\TeacherMessageController;

// ═════════════════════════════════════════════════════════════════════════════
// ROOT — redirect based on role
// ═════════════════════════════════════════════════════════════════════════════
Route::get('/', function () {
    if (auth()->check()) {
        return match(auth()->user()->role) {
            'student' => redirect()->route('student.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent'  => redirect()->route('parent.dashboard'),
            default   => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// ═════════════════════════════════════════════════════════════════════════════
// PUBLIC — About page (viewable by everyone, logged in or not)
// ═════════════════════════════════════════════════════════════════════════════
Route::view('/about', 'about')->name('about');

// ═════════════════════════════════════════════════════════════════════════════
// GUEST ROUTES — login, register, forgot password
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {

    // Auth
    Route::get('/login',     [LoginController::class,    'show'])->name('login');
    Route::post('/login',    [LoginController::class,    'submit'])->name('login.submit');
    // No public registration — old links go to activation
    Route::redirect('/register', '/activate', 301);

    // Student activation (first time): LRN + name + email code
    Route::get('/activate',         [StudentActivationController::class, 'show'])->name('activate');
    Route::post('/activate',        [StudentActivationController::class, 'start'])
        ->middleware('throttle:20,1')->name('activate.start');
    Route::get('/activate/verify',  [StudentActivationController::class, 'showVerify'])->name('activate.verify');
    Route::post('/activate/verify', [StudentActivationController::class, 'verify'])
        ->middleware('throttle:20,1')->name('activate.verify.submit');
    Route::post('/activate/resend', [StudentActivationController::class, 'resend'])
        ->middleware('throttle:5,10')->name('activate.resend');

    // Forgot Password
    Route::get('/forgot-password',
        [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password',
        [ForgotPasswordController::class, 'submit'])->name('password.email');
    Route::get('/forgot-password/verify',
        [ForgotPasswordController::class, 'showVerify'])->name('password.verify');
    Route::post('/forgot-password/verify',
        [ForgotPasswordController::class, 'verify'])->name('password.verify.submit');
    Route::get('/forgot-password/reset',
        [ForgotPasswordController::class, 'showReset'])->name('password.reset.form');
    Route::post('/forgot-password/reset',
        [ForgotPasswordController::class, 'reset'])->name('password.update');

            // ── INVITE / ACCOUNT ACTIVATION ─────────────────────────────
Route::get('/invite/{token}',  [App\Http\Controllers\Auth\InviteController::class, 'show'])
    ->name('invite.show');
 
Route::post('/invite/{token}', [App\Http\Controllers\Auth\InviteController::class, 'accept'])
    ->name('invite.accept');

    // ── PARENT ACTIVATION / RECOVERY (code from the child's account) ──
    // Throttled per IP. Generous (30/min) because many parents may share
    // one school Wi-Fi; the 8-character code is enough against guessing.
    Route::get('/parent/activate',  [ParentActivationController::class, 'showActivate'])->name('parent.activate');
    Route::post('/parent/activate', [ParentActivationController::class, 'activate'])
        ->middleware('throttle:30,1')->name('parent.activate.submit');
    Route::get('/parent/recover',   [ParentActivationController::class, 'showRecover'])->name('parent.recover');
    Route::post('/parent/recover',  [ParentActivationController::class, 'recover'])
        ->middleware('throttle:30,1')->name('parent.recover.submit');

});

// ═════════════════════════════════════════════════════════════════════════════
// OTP — mid-login session guard
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware('session.otp')->group(function () {
    Route::get('/otp',         [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');
});

// ═════════════════════════════════════════════════════════════════════════════
// LOGOUT
// ═════════════════════════════════════════════════════════════════════════════
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ═════════════════════════════════════════════════════════════════════════════
// NOTIFICATIONS (bell) — students and parents (layouts/app.blade.php).
// The layout calls /notifications/*, so these routes serve every role
// that uses it (students and parents alike).
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/notifications/data',       [NotificationController::class, 'data']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
    Route::post('/notifications/read-all',  [NotificationController::class, 'readAll']);
});

// ═════════════════════════════════════════════════════════════════════════════
// FORCE PASSWORD CHANGE — for teachers after first login
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/change-password',  fn() => view('auth.change-password'))->name('password.change');
    Route::post('/change-password', [App\Http\Controllers\Auth\ChangePasswordController::class, 'update'])->name('password.change.submit');
});

// ═════════════════════════════════════════════════════════════════════════════
// STUDENT ROUTES
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:student', 'approved'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/modules',   [StudentDashboardController::class, 'modules'])->name('modules');
        // Opens the file and counts the view for the teacher
        Route::get('/modules/{material}/open', [StudentDashboardController::class, 'openMaterial'])
            ->whereNumber('material')->name('modules.open');
        Route::get('/quizzes',                    [App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('quizzes');
        Route::get('/quizzes/{assignment}',       [App\Http\Controllers\Student\AssignmentController::class, 'show'])->name('quizzes.show');
        Route::post('/quizzes/{assignment}/submit',[App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('quizzes.submit');
        Route::get('/grades',    [StudentDashboardController::class, 'grades'])->name('grades');
        Route::get('/schedule',  [StudentDashboardController::class, 'schedule'])->name('schedule');
        Route::get('/messages',  [StudentDashboardController::class, 'messages'])->name('messages');
        Route::post('/messages', [StudentDashboardController::class, 'storeMessage'])->name('messages.store');
Route::get('/subjects',  [StudentDashboardController::class, 'subjects'])->name('subjects');
        Route::get('/enroll',    [StudentDashboardController::class, 'enrollmentForm'])->name('enroll');
        Route::post('/enroll',   [StudentDashboardController::class, 'submitEnrollment'])->name('enroll.submit');

        // Parent link — one-time code / QR for the parent
        Route::get('/parent-link',  [StudentParentLinkController::class, 'show'])->name('parent-link');
        Route::post('/parent-link', [StudentParentLinkController::class, 'store'])
            ->middleware('throttle:10,10')->name('parent-link.store');

        // Class attendance — enter the code shown by the teacher
        Route::get('/attendance',            [App\Http\Controllers\Student\ClassAttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance/{session}', [App\Http\Controllers\Student\ClassAttendanceController::class, 'mark'])
            ->whereNumber('session')->middleware('throttle:30,1')->name('attendance.mark');

        // Face registration (attendance camera)
        Route::get('/face-register',  [FaceRegistrationController::class, 'show'])->name('face.register');
        Route::post('/face-register', [FaceRegistrationController::class, 'store'])->name('face.store');

        // Strata Rush — 3D Science Game (Grade 11 & 12 only)
        Route::get('/science-game',             [App\Http\Controllers\Student\ScienceGameController::class, 'index'])->name('science-game');
        Route::get('/science-game/questions',   [App\Http\Controllers\Student\ScienceGameController::class, 'questions'])->name('science-game.questions');
        Route::post('/science-game/score',      [App\Http\Controllers\Student\ScienceGameController::class, 'submitScore'])->name('science-game.score');
        Route::get('/science-game/leaderboard', [App\Http\Controllers\Student\ScienceGameController::class, 'leaderboard'])->name('science-game.leaderboard');

        // Notifications (bell)
        Route::get('/notifications/data',       [NotificationController::class, 'data']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
        Route::post('/notifications/read-all',  [NotificationController::class, 'readAll']);
    });
// ═════════════════════════════════════════════════════════════════════════════
// TEACHER ROUTES
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:teacher', 'approved', 'force.password'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard',         [TeacherDashboardController::class, 'index'])->name('dashboard');
        // Class attendance (posted by the teacher, the student enters the class code).
        Route::get('/attendance',                    [App\Http\Controllers\Teacher\ClassAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance',                   [App\Http\Controllers\Teacher\ClassAttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/{session}',          [App\Http\Controllers\Teacher\ClassAttendanceController::class, 'show'])->whereNumber('session')->name('attendance.show');
        Route::get('/attendance/{session}/live',     [App\Http\Controllers\Teacher\ClassAttendanceController::class, 'live'])->whereNumber('session')->name('attendance.live');
        Route::post('/attendance/{session}/code',    [App\Http\Controllers\Teacher\ClassAttendanceController::class, 'code'])->whereNumber('session')->name('attendance.code');
        Route::post('/attendance/{session}/close',   [App\Http\Controllers\Teacher\ClassAttendanceController::class, 'close'])->whereNumber('session')->name('attendance.close');
        Route::post('/attendance/{session}/mark',    [App\Http\Controllers\Teacher\ClassAttendanceController::class, 'mark'])->whereNumber('session')->name('attendance.mark');

        // Face registration at the Raspberry Pi kiosk (adviser shows a one-time QR)
        Route::get('/face-kiosk',                [App\Http\Controllers\Teacher\FaceKioskController::class, 'index'])->name('face-kiosk.index');
        Route::post('/face-kiosk/{student}',     [App\Http\Controllers\Teacher\FaceKioskController::class, 'issue'])->whereNumber('student')->name('face-kiosk.issue');
        Route::get('/face-kiosk/codes/{code}',   [App\Http\Controllers\Teacher\FaceKioskController::class, 'status'])->whereNumber('code')->name('face-kiosk.status');
        Route::get('/students', [TeacherStudentController::class, 'index'])->name('students.index');

        // ── Enrollment review (adviser) ──
        Route::get('/enrollments',                     [TeacherEnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/enrollments/{enrollment}',        [TeacherEnrollmentController::class, 'show'])->name('enrollments.show');
        Route::post('/enrollments/{enrollment}/approve',[TeacherEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('/enrollments/{enrollment}/reject', [TeacherEnrollmentController::class, 'reject'])->name('enrollments.reject');
        // ->only(): just the routes that have controller methods
        Route::resource('gradebook',     GradebookController::class)->only(['index', 'store']);
        Route::resource('materials',     MaterialController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::patch('/materials/{material}/publish', [MaterialController::class, 'togglePublish'])->name('materials.publish');
        Route::resource('announcements', AnnouncementController::class)->only(['index', 'store', 'destroy']);

        // Quizzes / assignments (file-based)
        Route::get('/assignments',                       [App\Http\Controllers\Teacher\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create',                [App\Http\Controllers\Teacher\AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments',                      [App\Http\Controllers\Teacher\AssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/assignments/{assignment}',          [App\Http\Controllers\Teacher\AssignmentController::class, 'show'])->name('assignments.show');
        Route::delete('/assignments/{assignment}',       [App\Http\Controllers\Teacher\AssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::patch('/assignments/{assignment}/publish',[App\Http\Controllers\Teacher\AssignmentController::class, 'togglePublish'])->name('assignments.publish');
        Route::post('/submissions/{submission}/grade',   [App\Http\Controllers\Teacher\AssignmentController::class, 'grade'])->name('submissions.grade');

        // Messaging — chat with students in the teacher's sections
        Route::get('/messages',  [TeacherMessageController::class, 'index'])->name('messages');
        Route::post('/messages', [TeacherMessageController::class, 'store'])->name('messages.store');

        // Notifications (bell)
        Route::get('/notifications/data',       [NotificationController::class, 'data']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
        Route::post('/notifications/read-all',  [NotificationController::class, 'readAll']);
    });
// ═════════════════════════════════════════════════════════════════════════════
// PARENT ROUTES
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:parent', 'approved'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard',     [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/child-records', [ParentDashboardController::class, 'childRecords'])->name('child-records');
    });

// ═════════════════════════════════════════════════════════════════════════════
// ADMIN ROUTES — only accessible via /admin/login
// ═════════════════════════════════════════════════════════════════════════════
Route::prefix('admin')->name('admin.')->group(function () {

    // ── Admin login (guest only) ──────────────────────────────────
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login',  [AdminAuthController::class, 'show'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'submit'])->name('login.submit');
    });

    // ── Admin logout ──────────────────────────────────────────────
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // ── Protected admin area ──────────────────────────────────────
    // AuthenticateSession: changing the admin password logs out the other devices/sessions.
    Route::middleware(['auth:admin', \Illuminate\Session\Middleware\AuthenticateSession::class])->group(function () {
        // Notifications
        Route::get('/notifications', fn() => response()->json([
            'count'         => 0,
            'notifications' => [],
        ]))->name('notifications');

        // ── Admin Profile ─────────────────────────────────────
Route::get('/profile/edit',
    [AdminProfileController::class, 'editProfile'])->name('profile.edit');
Route::patch('/profile/update',
    [AdminProfileController::class, 'updateProfile'])->name('profile.update');
Route::get('/profile/change-password',
    [AdminProfileController::class, 'changePassword'])->name('profile.change-password');
Route::patch('/profile/update-password',
    [AdminProfileController::class, 'updatePassword'])->name('profile.update-password');

        // Core pages
        Route::get('/dashboard',  [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live');
        Route::get('/reports',    [ReportController::class,    'index'])->name('reports');
        Route::get('/reports/export/enrollment', [ReportController::class, 'exportEnrollment'])->name('reports.export.enrollment');
        Route::get('/reports/export/attendance', [ReportController::class, 'exportAttendance'])->name('reports.export.attendance');
        Route::get('/reports/export/class-attendance', [ReportController::class, 'exportClassAttendance'])->name('reports.export.class-attendance');
        Route::get('/reports/export/gate-scans', [ReportController::class, 'exportGateScans'])->name('reports.export.gate-scans');
        Route::get('/audit-logs', [AuditLogController::class,  'index'])->name('audit-logs.index');

        // ── Face Verification ─────────────────────────────────────
        Route::get('/face',                    [FaceVerificationController::class, 'index'])->name('face.index');
        Route::patch('/face/{registration}/approve', [FaceVerificationController::class, 'approve'])->name('face.approve');
        Route::get('/face/photos/{user}/{file}', [FaceVerificationController::class, 'photo'])
            ->whereNumber('user')->where('file', 'img_\d{2}\.(jpe?g|png)')->name('face.photo');
        // Kiosk QR (Raspberry Pi face registration)
        Route::get('/face/kiosk/students',       [FaceVerificationController::class, 'kioskStudents'])->name('face.kiosk.students');
        Route::post('/face/kiosk/{student}',     [FaceVerificationController::class, 'kioskIssue'])->whereNumber('student')->name('face.kiosk.issue');
        Route::get('/face/kiosk/codes/{code}',   [FaceVerificationController::class, 'kioskStatus'])->whereNumber('code')->name('face.kiosk.status');

        // ── LRN Master List ─────────────────────────────
        Route::get('/lrns',          [LrnController::class, 'index'])->name('lrns.index');
        Route::post('/lrns',         [LrnController::class, 'store'])->name('lrns.store');
        Route::delete('/lrns/{lrn}', [LrnController::class, 'destroy'])->whereNumber('lrn')->name('lrns.destroy');
        // Spreadsheet import: upload → preview (kept 30 min for this admin) → confirm
        Route::get('/lrns/template',  [LrnController::class, 'template'])->name('lrns.template');
        Route::post('/lrns/import',   [LrnController::class, 'import'])->middleware('throttle:10,1')->name('lrns.import');
        Route::prefix('/lrns/import/{token}')->where(['token' => '[A-Za-z0-9]{32}'])->group(function () {
            Route::get('/',          [LrnController::class, 'preview'])->name('lrns.import.show');
            Route::post('/',         [LrnController::class, 'confirm'])->name('lrns.import.confirm');
            Route::delete('/',       [LrnController::class, 'cancel'])->name('lrns.import.cancel');
            Route::get('/fix.xlsx',  [LrnController::class, 'problems'])->name('lrns.import.problems');
        });

        // ── Sections & Advisers ─────────────────────────
        Route::get('/sections',            [SectionController::class, 'index'])->name('sections.index');
        Route::post('/sections',           [SectionController::class, 'store'])->name('sections.store');
        Route::put('/sections/{section}',  [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}',[SectionController::class, 'destroy'])->name('sections.destroy');

        // Class schedules (Lunes-Biyernes, per section)
        Route::get('/schedules',              [ClassScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedules',             [ClassScheduleController::class, 'store'])->name('schedules.store');
        Route::put('/schedules/{schedule}',   [ClassScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('/schedules/{schedule}',[ClassScheduleController::class, 'destroy'])->name('schedules.destroy');
        Route::patch('/face/{registration}/reject',  [FaceVerificationController::class, 'reject'])->name('face.reject');

        // ── User Management ───────────────────────────────────────
        Route::get('/users',
    [UserManagementController::class, 'index'])->name('users.index');

        // ── STATIC routes — MUST come BEFORE {user} wildcard ─────
        Route::get('/users/create-teacher',
            [UserManagementController::class, 'createTeacher'])->name('users.create-teacher');
        Route::post('/users/store-teacher',
            [UserManagementController::class, 'storeTeacher'])->name('users.store-teacher');
        Route::get('/users/import-teachers',
            [UserManagementController::class, 'importTeachersForm'])->name('users.import-teachers');
        Route::post('/users/import-teachers',
            [UserManagementController::class, 'importTeachers'])->name('users.import-teachers.store');


        // ── WILDCARD {user} routes — AFTER all static routes ─────
        Route::get('/users/{user}',
            [UserManagementController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/approve',
            [UserManagementController::class, 'approve'])->name('users.approve');
        Route::patch('/users/{user}/reject',
            [UserManagementController::class, 'reject'])->name('users.reject');
        Route::patch('/users/{user}/release-lrn',
            [UserManagementController::class, 'releaseLrn'])->name('users.release-lrn');
        Route::post('/users/{user}/link-child',
            [UserManagementController::class, 'linkChild'])->name('users.link-child');
        Route::delete('/users/{user}/unlink-child/{child}',
            [UserManagementController::class, 'unlinkChild'])->name('users.unlink-child');
        Route::post('/users/{user}/activation-link',
            [UserManagementController::class, 'newActivationLink'])->name('users.activation-link');

        // ── Enrollment management ─────────────────────────────────
        Route::get('/enrollment',
            [EnrollmentController::class, 'index'])->name('enrollment.index');
        Route::patch('/enrollment/{enrollment}/approve',
            [EnrollmentController::class, 'approve'])->name('enrollment.approve');
        Route::patch('/enrollment/{enrollment}/reject',
            [EnrollmentController::class, 'reject'])->name('enrollment.reject');
     
    });


});

// ═════════════════════════════════════════════════════════════════════════════
// IOT ATTENDANCE API — ESP32-CAM / Raspberry Pi. No user login and no CSRF;
// X-Device-Key is the proof (ATTENDANCE_DEVICE_KEY). Throttled per IP.
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware('throttle:120,1')->group(function () {
    Route::post('/api/attendance',    [App\Http\Controllers\AttendanceController::class, 'store']);
    Route::get('/api/attendance',     [App\Http\Controllers\AttendanceController::class, 'index']);
    Route::get('/api/faces/approved', [App\Http\Controllers\AttendanceController::class, 'approvedFaces']);
    Route::get('/api/faces/{user}/{file}', [App\Http\Controllers\AttendanceController::class, 'facePhoto'])
        ->whereNumber('user')->where('file', 'img_\d{2}\.(jpe?g|png)');
    Route::post('/api/gate/unknown',  [App\Http\Controllers\AttendanceController::class, 'unknownFace']);

    // Face kiosk — Pi scans the adviser's one-time QR, then uploads photos for that student
    Route::get('/api/kiosk/face',  [App\Http\Controllers\FaceKioskController::class, 'lookup']);
    Route::post('/api/kiosk/face', [App\Http\Controllers\FaceKioskController::class, 'register'])->middleware('throttle:30,1');
});