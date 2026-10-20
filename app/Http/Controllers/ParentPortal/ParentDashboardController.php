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

        // School-wide + para sa section ng mga anak lang (dati lahat ng section)
        $announcements = Announcement::published()
            ->forAudience('parent')
            ->visibleToSections($children->map(fn ($c) => $c->studentSectionId()))
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
            ])
            ->get();

        return view('parent.child-records', compact('children'));
    }
}