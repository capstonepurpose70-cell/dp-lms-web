<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EnrollmentRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Download all users as a CSV file.
     */
    public function users(): StreamedResponse
    {
        $filename = 'users_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Role', 'Status', 'LRN', 'Employee ID', 'Registered']);

            User::orderBy('role')->orderBy('name')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $u) {
                    fputcsv($out, [
                        $u->name,
                        $u->email,
                        $u->role,
                        $u->status,
                        $u->lrn,
                        $u->employee_id,
                        optional($u->created_at)->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    /**
     * Download all enrollment requests as a CSV file.
     */
    public function enrollments(): StreamedResponse
    {
        $filename = 'enrollments_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Full Name', 'Grade Level', 'School Year', 'Type', 'Age', 'Gender', 'Status', 'Submitted']);

            EnrollmentRequest::orderByDesc('created_at')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $e) {
                    fputcsv($out, [
                        $e->full_name,
                        $e->grade_level,
                        $e->school_year,
                        $e->student_type,
                        $e->age,
                        $e->gender,
                        $e->status,
                        optional($e->created_at)->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }
}