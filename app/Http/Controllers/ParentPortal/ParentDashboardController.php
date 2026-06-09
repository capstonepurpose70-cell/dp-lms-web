<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class ParentDashboardController extends Controller
{
    public function index()
    {
        $children = auth()->user()
            ->children()
            ->with([
                'grades.subject',
                'section',
                'studentEnrollment.section',
            ])
            ->get();

        $announcements = Announcement::published()
            ->forAudience('parent')
            ->with('author')
            ->latest()
            ->take(5)
            ->get();

        return view('parent.dashboard', compact('children', 'announcements'));
    }

    public function childRecords()
    {
        $children = auth()->user()
            ->children()
            ->with([
                'grades.subject',
                'section',
                'studentEnrollment.section',
                'enrollments',
            ])
            ->get();

        return view('parent.child-records', compact('children'));
    }
}