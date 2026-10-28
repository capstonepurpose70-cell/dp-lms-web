<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\ClassAttendanceSession;
use App\Models\ClassSchedule;
use App\Models\LearningMaterial;
use App\Models\OfficialLrn;
use App\Models\ParentLinkCode;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Support\FacePhotos;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Dummy data for manual testing on localhost ONLY (APP_ENV=local):
 * 1 teacher (adviser + 2 classes), 1 section, 1 LRN, 1 student.
 * Goes through the real EnrollmentService so the data matches the real flow.
 */
class LocalDemoData extends Command
{
    protected $signature = 'demo:local
        {--email= : Your real email; accounts become you+demoteacher@… / you+demostudent@… so login codes reach your inbox}
        {--fresh : Only add the LRN — activate and enroll the student yourself}
        {--remove : Delete all demo data (teacher, student, section, LRN and everything they created)}';

    protected $description = 'Local-only test data: 1 teacher, 1 section, 1 LRN, 1 student';

    private const TEACHER_EMAIL = 'demo.teacher@dplms.test';
    private const TEACHER_EMP   = 'DEMO-T-001';
    private const TEACHER_PASS  = 'DemoTeacher#2026';
    private const STUDENT_EMAIL = 'demo.student@dplms.test';
    private const STUDENT_PASS  = 'DemoStudent#2026';
    private const STUDENT_NAME  = 'Demo Student';
    private const BIRTHDATE     = '2013-06-15';
    private const LRN           = '999999000001';
    private const SECTION       = 'DEMO SECTION';
    private const GRADE         = '7';
    private const ROOM          = 'DEMO ROOM';

    public function handle(EnrollmentService $enrollments): int
    {
        // Never on Railway / production
        if (! app()->environment('local')) {
            $this->error('demo:local only runs when APP_ENV=local.');
            return self::FAILURE;
        }

        if ($this->option('remove')) {
            $this->removeAll();
            return self::SUCCESS;
        }

        // Login needs a 6-digit code sent by email (new device). Gmail/Outlook deliver you+tag@ to you@.
        $teacherEmail = self::TEACHER_EMAIL;
        $studentEmail = self::STUDENT_EMAIL;
        if ($this->option('email')) {
            $email = strtolower(trim((string) $this->option('email')));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Invalid --email address.');
                return self::FAILURE;
            }
            [$local, $domain] = explode('@', $email, 2);
            $local        = explode('+', $local, 2)[0];
            $teacherEmail = "{$local}+demoteacher@{$domain}";
            $studentEmail = "{$local}+demostudent@{$domain}";
            if (User::whereIn('email', [$teacherEmail, $studentEmail])->exists()) {
                $this->error("{$teacherEmail} or {$studentEmail} is already used by another account.");
                return self::FAILURE;
            }
        }

        if ($this->demoTeacher() || User::where('email', self::TEACHER_EMAIL)->exists() || OfficialLrn::where('lrn', self::LRN)->exists()
            || Section::where('name', self::SECTION)->where('grade_level', self::GRADE)->exists()) {
            $this->error('Demo data already exists. Run: php artisan demo:local --remove');
            return self::FAILURE;
        }

        $sy = SchoolYear::current();
        if (! $sy) {
            $this->error('No active school year. Set one first (Admin → School Year), then run this again.');
            return self::FAILURE;
        }

        $subjects = Subject::where('grade_level', self::GRADE)->where('is_active', true)
            ->whereIn('name', ['Mathematics', 'Science'])->orderBy('name')->get();
        if ($subjects->isEmpty()) {
            $subjects = Subject::where('grade_level', self::GRADE)->where('is_active', true)->orderBy('id')->take(2)->get();
        }
        if ($subjects->isEmpty()) {
            $this->error('No active Grade 7 subjects. Run: php artisan db:seed --class=SubjectSeeder');
            return self::FAILURE;
        }

        config(['mail.default' => 'array']);   // no real email goes out
        $fresh = (bool) $this->option('fresh');

        $student = DB::transaction(function () use ($sy, $subjects, $fresh, $enrollments, $teacherEmail, $studentEmail) {
            $teacher = User::forceCreate([
                'name'                 => 'Demo Teacher',
                'email'                => $teacherEmail,
                'password'             => self::TEACHER_PASS,
                'role'                 => 'teacher',
                'status'               => 'approved',
                'employee_id'          => self::TEACHER_EMP,
                'must_change_password' => false,
                'email_verified_at'    => now(),
            ]);

            $section = Section::create([
                'name'        => self::SECTION,
                'grade_level' => self::GRADE,
                'adviser_id'  => $teacher->id,
                'school_year' => $sy->label,
                'is_active'   => true,
            ]);

            // Same as Class Schedules: each class row also grants the teacher_subjects load
            foreach ($subjects->values() as $i => $subject) {
                foreach (range(1, 5) as $day) {
                    ClassSchedule::create([
                        'section_id'  => $section->id,
                        'subject_id'  => $subject->id,
                        'teacher_id'  => $teacher->id,
                        'day_of_week' => $day,
                        'start_time'  => sprintf('%02d:00', 8 + $i),
                        'end_time'    => sprintf('%02d:00', 9 + $i),
                        'room'        => self::ROOM,
                        'school_year' => $sy->label,
                    ]);
                }
                TeacherSubject::firstOrCreate(
                    ['user_id' => $teacher->id, 'subject_id' => $subject->id, 'section_id' => $section->id],
                    ['grade_level' => self::GRADE, 'school_year' => $sy->label]
                );
            }

            $official = OfficialLrn::create([
                'lrn'          => self::LRN,
                'student_name' => self::STUDENT_NAME,
                'grade_level'  => self::GRADE,
            ]);

            if ($fresh) {
                return null;
            }

            // Same fields as StudentActivationService::verify()
            $student = User::create([
                'name'        => self::STUDENT_NAME,
                'email'       => $studentEmail,
                'password'    => self::STUDENT_PASS,
                'role'        => 'student',
                'status'      => 'approved',
                'lrn'         => self::LRN,
                'grade_level' => self::GRADE,
                'birthdate'   => self::BIRTHDATE,
            ]);
            $student->forceFill(['email_verified_at' => now()])->save();
            $official->update(['claimed_by' => $student->id]);

            // Enrollment form → adviser approves (real services)
            $request = $enrollments->submit($student, [
                'student_type'     => 'new',
                'full_name'        => self::STUDENT_NAME,
                'age'              => 13,
                'birthdate'        => self::BIRTHDATE,
                'gender'           => 'Male',
                'address'          => 'Sto. Domingo, Nueva Ecija',
                'guardian_name'    => 'Demo Parent',
                'last_school'      => 'Sto. Domingo Elementary School',
                'last_grade_completed' => 'Grade 6',
                'general_average'  => 88.5,
            ]);
            $enrollments->approve($request, $section, $teacher->id);

            return $student->fresh();
        });

        $this->newLine();
        $this->info($fresh ? 'Demo teacher, section and LRN created (student not activated yet).' : 'Demo data created.');
        $this->table(['Account', 'Login', 'Password'], array_filter([
            ['Teacher (adviser of ' . self::SECTION . ')', $teacherEmail, self::TEACHER_PASS],
            $student ? ['Student (Grade 7 – ' . self::SECTION . ')', $studentEmail . '  or LRN ' . self::LRN, self::STUDENT_PASS] : null,
        ]));
        $this->line('Classes: ' . $subjects->pluck('name')->implode(', ') . ' — Mon–Fri, ' . self::ROOM);

        if ($fresh) {
            $this->line('Next: open /activate → LRN ' . self::LRN . ', name "' . self::STUDENT_NAME . '", any birthdate (8+ years old),');
            $this->line('your REAL email (the 6-digit code is sent there). Then fill the enrollment form and approve it as the teacher.');
        }
        $this->warn('First login on each browser asks for a 6-digit code (sent by email). Get it here instead:');
        $this->warn('  php artisan demo:otp ' . $teacherEmail . '   (or the student email / LRN)');
        $this->line('Remove everything later: php artisan demo:local --remove');

        return self::SUCCESS;
    }

    private function demoTeacher(): ?User
    {
        return User::where('role', 'teacher')->where('employee_id', self::TEACHER_EMP)->first();
    }

    private function removeAll(): void
    {
        $teacher  = $this->demoTeacher() ?? User::where('email', self::TEACHER_EMAIL)->first();
        $students = User::where('role', 'student')
            ->where(fn ($q) => $q->where('lrn', self::LRN)->orWhere('email', self::STUDENT_EMAIL))->get();
        $section  = Section::where('name', self::SECTION)->where('grade_level', self::GRADE)->first();

        $studentIds = $students->pluck('id');
        // Parent accounts linked ONLY to the demo student (created while testing the parent link)
        $parents = User::where('role', 'parent')->whereIn('id', $students->pluck('parent_id')->filter())
            ->get()->filter(fn ($p) => User::where('parent_id', $p->id)->whereNotIn('id', $studentIds)->doesntExist());

        $ids = collect([$teacher?->id])->filter()->merge($studentIds)->merge($parents->pluck('id'))->values();

        DB::transaction(function () use ($ids, $studentIds, $students, $section, $teacher) {
            $disk = Storage::disk('public');

            if ($teacher) {
                LearningMaterial::where('user_id', $teacher->id)->whereNotNull('file_path')
                    ->pluck('file_path')->each(fn ($p) => $disk->delete($p));
                Assignment::where('user_id', $teacher->id)->pluck('id')->each(function ($aid) use ($disk) {
                    $disk->deleteDirectory("assignments/{$aid}");
                    $disk->deleteDirectory("submissions/{$aid}");
                });
                ClassAttendanceSession::where('teacher_id', $teacher->id)->delete();
            }
            $studentIds->each(fn ($sid) => FacePhotos::delete($sid));

            // Not cascaded by foreign keys
            Attendance::whereIn('user_id', $studentIds)
                ->orWhereIn('student_id', $students->pluck('email')->filter()->values())->delete();
            ParentLinkCode::whereIn('student_id', $studentIds)->delete();
            if ($section) {
                ClassAttendanceSession::where('section_id', $section->id)->delete();
                ClassSchedule::where('section_id', $section->id)->delete();
            }
            if ($teacher) {
                ClassSchedule::where('teacher_id', $teacher->id)->delete();
            }
            DB::table('notifications')->where('notifiable_type', User::class)->whereIn('notifiable_id', $ids)->delete();
            DB::table('personal_access_tokens')->where('tokenable_type', User::class)->whereIn('tokenable_id', $ids)->delete();
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                DB::table('sessions')->whereIn('user_id', $ids)->delete();
            }
            AuditLog::whereIn('user_id', $ids)->where('role', '!=', 'admin')->delete();

            OfficialLrn::where('lrn', self::LRN)->delete();
            User::whereIn('id', $studentIds)->update(['parent_id' => null]);
            User::whereIn('id', $ids)->delete();   // grades, messages, materials, quizzes, enrollment… cascade
            $section?->delete();
        });

        $this->info("Removed demo data: {$ids->count()} account(s)" . ($section ? ', section ' . self::SECTION : '') . ', LRN ' . self::LRN . '.');
    }
}
