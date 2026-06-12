<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\FaceVerificationController;
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
use App\Http\Controllers\Faculty\FacultyDashboardController;
use App\Http\Controllers\Teacher\TeacherStudentController;
use App\Http\Controllers\Admin\AdminProfileController;

// ═════════════════════════════════════════════════════════════════════════════
// ROOT — redirect based on role
// ═════════════════════════════════════════════════════════════════════════════
Route::get('/', function () {
    if (auth()->check()) {
        return match(auth()->user()->role) {
            'student' => redirect()->route('student.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent'  => redirect()->route('parent.dashboard'),
            'faculty' => redirect()->route('faculty.dashboard'),
            default   => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// ═════════════════════════════════════════════════════════════════════════════
// GUEST ROUTES — login, register, forgot password
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {

    // Auth
    Route::get('/login',     [LoginController::class,    'show'])->name('login');
    Route::post('/login',    [LoginController::class,    'submit'])->name('login.submit');
    Route::get('/register',  [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'submit'])->name('register.submit');

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
   // PALITAN NG:
Route::get('/invite/{token}',  [App\Http\Controllers\Auth\InviteController::class, 'show'])
    ->name('invite.show');
 
Route::post('/invite/{token}', [App\Http\Controllers\Auth\InviteController::class, 'accept'])
    ->name('invite.accept');
 
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
        Route::get('/quizzes',   [StudentDashboardController::class, 'quizzes'])->name('quizzes');
        Route::get('/grades',    [StudentDashboardController::class, 'grades'])->name('grades');
        Route::get('/messages',  [StudentDashboardController::class, 'messages'])->name('messages');
Route::get('/subjects',  [StudentDashboardController::class, 'subjects'])->name('subjects');
        Route::get('/enroll',    [StudentDashboardController::class, 'enrollmentForm'])->name('enroll');
        Route::post('/enroll',   [StudentDashboardController::class, 'submitEnrollment'])->name('enroll.submit');

        // Face registration (attendance camera)
        Route::get('/face-register',  [FaceRegistrationController::class, 'show'])->name('face.register');
        Route::post('/face-register', [FaceRegistrationController::class, 'store'])->name('face.store');

        // Notifications
        Route::get('/notifications/data', fn() => response()->json([
            'unread' => auth()->user()->unreadNotifications->count(),
            'items'  => auth()->user()->unreadNotifications->take(10)->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->data['title'],
                'subject'    => $n->data['subject'],
                'instructor' => $n->data['instructor'],
                'type'       => $n->data['type'],
                'url'        => $n->data['url'],
                'time'       => $n->created_at->diffForHumans(),
            ]),
        ]));
        Route::post('/notifications/{id}/read', function ($id) {
            auth()->user()->notifications()->findOrFail($id)->markAsRead();
            return response()->json(['ok' => true]);
        });
        Route::post('/notifications/read-all', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return response()->json(['ok' => true]);
        });
    });
// ═════════════════════════════════════════════════════════════════════════════
// TEACHER ROUTES
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:teacher', 'approved', 'force.password'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard',         [TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'teacherIndex'])->name('attendance.index');
        Route::get('/students', [TeacherStudentController::class, 'index'])->name('students.index');
        Route::resource('gradebook',     GradebookController::class);
        Route::resource('materials',     MaterialController::class);
        Route::resource('announcements', AnnouncementController::class);
       Route::get('/notifications/data', fn() => response()->json([
            'unread' => auth()->user()->unreadNotifications->count(),
            'items'  => auth()->user()->unreadNotifications->take(10)->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->data['title'] ?? '',
                'subject'    => $n->data['subject'] ?? '',
                'instructor' => $n->data['instructor'] ?? '',
                'type'       => $n->data['type'] ?? 'module',
                'url'        => $n->data['url'] ?? '#',
                'time'       => $n->created_at->diffForHumans(),
            ]),
        ]));
        Route::post('/notifications/{id}/read', function ($id) {
            auth()->user()->notifications()->findOrFail($id)->markAsRead();
            return response()->json(['ok' => true]);
        });
        Route::post('/notifications/read-all', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return response()->json(['ok' => true]);
        });
    });
// ═════════════════════════════════════════════════════════════════════════════
// FACULTY ROUTES
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:faculty', 'approved'])
    ->prefix('faculty')
    ->name('faculty.')
    ->group(function () {
        Route::get('/dashboard',
            [FacultyDashboardController::class, 'index'])->name('dashboard');
        Route::get('/enrollments',
            [FacultyDashboardController::class, 'enrollments'])->name('enrollments');
        Route::get('/enrollments/{request}',
            [FacultyDashboardController::class, 'showEnrollment'])->name('enrollments.show');
        Route::post('/enrollments/{request}/approve',
            [FacultyDashboardController::class, 'approveEnrollment'])->name('enrollments.approve');
        Route::post('/enrollments/{request}/reject',
            [FacultyDashboardController::class, 'rejectEnrollment'])->name('enrollments.reject');
            // sa loob ng faculty routes group
Route::get('/teachers',
    [FacultyDashboardController::class, 'teachers'])->name('teachers.index');
Route::get('/teachers/{teacher}/assign',
    [FacultyDashboardController::class, 'assignTeacher'])->name('teachers.assign');
Route::post('/teachers/{teacher}/assign',
    [FacultyDashboardController::class, 'saveAssignment'])->name('teachers.assign.save');
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
    Route::middleware('auth:admin')->group(function () {
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
        Route::get('/reports',    [ReportController::class,    'index'])->name('reports');
        Route::get('/audit-logs', [AuditLogController::class,  'index'])->name('audit-logs.index');

        // ── Face Verification ─────────────────────────────────────
        Route::get('/face',                    [FaceVerificationController::class, 'index'])->name('face.index');
        Route::patch('/face/{registration}/approve', [FaceVerificationController::class, 'approve'])->name('face.approve');
        Route::patch('/face/{registration}/reject',  [FaceVerificationController::class, 'reject'])->name('face.reject');

        // ── User Management ───────────────────────────────────────
        Route::get('/users',
    [UserManagementController::class, 'index'])->name('users.index');

        // ── STATIC routes — MUST come BEFORE {user} wildcard ─────
        Route::get('/users/create-teacher',
            [UserManagementController::class, 'createTeacher'])->name('users.create-teacher');
        Route::post('/users/store-teacher',
            [UserManagementController::class, 'storeTeacher'])->name('users.store-teacher');

        Route::get('/users/create-faculty',
            [UserManagementController::class, 'createFaculty'])->name('users.create-faculty');
        Route::post('/users/store-faculty',
            [UserManagementController::class, 'storeFaculty'])->name('users.store-faculty');

        // ── WILDCARD {user} routes — AFTER all static routes ─────
        Route::get('/users/{user}',
            [UserManagementController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/approve',
            [UserManagementController::class, 'approve'])->name('users.approve');
        Route::patch('/users/{user}/reject',
            [UserManagementController::class, 'reject'])->name('users.reject');
        Route::get('/users/{user}/assign-teacher',
            [UserManagementController::class, 'showAssignTeacher'])->name('users.assign-teacher');
        Route::post('/users/{user}/assign-teacher',
            [UserManagementController::class, 'assignTeacher'])->name('users.assign-teacher.submit');
        Route::post('/users/{user}/link-child',
            [UserManagementController::class, 'linkChild'])->name('users.link-child');
        Route::delete('/users/{user}/unlink-child/{child}',
            [UserManagementController::class, 'unlinkChild'])->name('users.unlink-child');

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
// IOT ATTENDANCE API — ESP32-CAM, no auth, no CSRF
// ═════════════════════════════════════════════════════════════════════════════
Route::post('/api/attendance', [App\Http\Controllers\AttendanceController::class, 'store']);
Route::get('/api/attendance',  [App\Http\Controllers\AttendanceController::class, 'index']);