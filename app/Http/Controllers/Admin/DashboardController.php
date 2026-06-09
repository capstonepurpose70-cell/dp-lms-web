<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // Helper to get counts for specific actions per day
        // We assume 'action' column in audit_logs contains strings like 'Login', 'Approval', 'Registration'
        // Adjust these strings if your actual log actions are different (e.g., 'user.login')
        
        $getChartData = function ($periodType) use ($today) {
            $dates = [];
            $regData = [];
            $aprData = [];
            $logData = [];

            // Determine date range based on period
            switch ($periodType) {
                case 'weekly':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    $groupBy = 'DAY';
                    break;
                case 'monthly':
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                    $groupBy = 'DAY'; // Can be MONTH if you want yearly view aggregated differently
                    break;
                case 'yearly':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    $groupBy = 'MONTH';
                    break;
                default:
                    return null;
            }

            // Fetch all logs in that range
            $logs = AuditLog::whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw("DATE(created_at) as log_date"), 'action')
                ->get();

            // Generate labels (Dates or Months)
            if ($periodType === 'yearly') {
                $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            } else {
                // For weekly/monthly, generate list of dates
                $currentDate = clone $startDate;
                while ($currentDate <= $endDate) {
                    $dates[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }
                
                if ($periodType === 'weekly') {
                    // Format labels as Mon, Tue, etc.
                    $tempDates = clone $startDate;
                    $labels = [];
                    while ($tempDates <= $endDate) {
                        $labels[] = $tempDates->format('D');
                        $tempDates->addDay();
                    }
                } else {
                    // Monthly labels: Jan 1, Jan 2...
                    $labels = array_map(fn($d) => date('M j', strtotime($d)), $dates);
                }
            }

            // Initialize arrays with 0s
            $regData = array_fill(0, count($labels), 0);
            $aprData = array_fill(0, count($labels), 0);
            $logData = array_fill(0, count($labels), 0);

            // Map logs to arrays
            foreach ($logs as $log) {
                $dateStr = $log->log_date;
                $action = strtolower($log->action); // Ensure case insensitivity

                // Find index of this date in our labels/dates array
                $index = -1;
                if ($periodType === 'yearly') {
                     // For yearly, we match by month number
                     $monthNum = (int)date('n', strtotime($dateStr));
                     $index = $monthNum - 1; 
                } else {
                     $index = array_search($dateStr, $dates);
                }

                if ($index !== false && $index >= 0) {
                    if (strpos($action, 'login') !== false || strpos($action, 'logged in') !== false) {
                        $logData[$index]++;
                    } elseif (strpos($action, 'approval') !== false || strpos($action, 'approve') !== false || strpos($action, 'status_change') !== false) {
                        $aprData[$index]++;
                    } elseif (strpos($action, 'register') !== false || strpos($action, 'signup') !== false || strpos($action, 'create_user') !== false) {
                        $regData[$index]++;
                    }
                }
            }

            return [
                'labels' => $labels,
                'reg'    => $regData,
                'apr'    => $aprData,
                'log'    => $logData,
            ];
        };

        return view('admin.dashboard', [
            'totalStudents'   => User::where('role', 'student')->count(),
            'totalTeachers'   => User::where('role', 'teacher')->count(),
            'totalParents'    => User::where('role', 'parent')->count(),
            'pendingStudents' => User::where('role', 'student')->where('status', 'pending')->count(),
            'pendingParents'  => User::where('role', 'parent')->where('status', 'pending')->count(),
            'todayLogs'       => AuditLog::whereDate('created_at', $today)->count(),
            'pendingUsers'    => User::where('status', 'pending')->latest()->take(5)->get(),
            'recentLogs'      => AuditLog::latest()->take(8)->get(),
            
            // Pass the generated data to the view
            'chartDataWeekly'   => $getChartData('weekly'),
            'chartDataMonthly'  => $getChartData('monthly'),
            'chartDataYearly'   => $getChartData('yearly'),
        ]);
    }
}