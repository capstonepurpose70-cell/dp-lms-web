<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Services\GradeService;
use App\Services\ParentPortalService;

class ParentDashboardController extends Controller
{
    private const RECORD_DAYS = 30;

    public function __construct(private ParentPortalService $portal)
    {
    }

    public function index()
    {
        $children = $this->portal->children(auth()->user());

        return view('parent.dashboard', [
            'children'      => $children,
            'cards'         => $children->mapWithKeys(fn ($c) => [$c->id => $this->portal->currentYearCard($c)]),
            'states'        => $children->mapWithKeys(fn ($c) => [$c->id => $this->portal->enrollmentState($c)]),
            'announcements' => $this->portal->announcements($children),
            'schoolYear'    => GradeService::currentSchoolYear(),
            'passing'       => Grade::PASSING,
        ]);
    }

    public function childRecords()
    {
        $children = $this->portal->children(auth()->user());

        return view('parent.child-records', [
            'children'        => $children,
            'reportCards'     => $children->mapWithKeys(fn ($c) => [$c->id => GradeService::reportCard($c->grades)]),
            'states'          => $children->mapWithKeys(fn ($c) => [$c->id => $this->portal->enrollmentState($c)]),
            'arrivals'        => $this->portal->arrivals($children, self::RECORD_DAYS),
            'classAttendance' => $this->portal->classAttendance($children, self::RECORD_DAYS),
            'days'            => self::RECORD_DAYS,
            'schoolYear'      => GradeService::currentSchoolYear(),
            'passing'         => Grade::PASSING,
            'weights'         => Grade::WEIGHTS,
        ]);
    }
}
