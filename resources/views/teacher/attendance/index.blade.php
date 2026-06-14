@extends('layouts.teacher')
@section('title', 'Attendance')

@section('content')
<style>
    .att-card {
        background:#fff;
        border-radius:16px;
        border:1.5px solid #f1f5f9;
        box-shadow:0 2px 12px rgba(0,0,0,0.04);
        overflow:hidden;
    }
    .att-header {
        padding:16px 20px;
        border-bottom:1px solid #f3f4f6;
        display:flex;
        justify-content:space-between;
        align-items:center;
    }
    .att-row {
        display:flex;
        align-items:center;
        gap:12px;
        padding:12px 20px;
        border-bottom:1px solid #f9fafb;
        transition:background 0.15s;
    }
    .att-row:last-child { border-bottom:none; }
    .att-row:hover { background:#f8faff; }
    .att-avatar {
        width:36px; height:36px;
        border-radius:50%;
        background:linear-gradient(135deg,#3b82f6,#1d4ed8);
        display:flex; align-items:center; justify-content:center;
        font-size:13px; font-weight:700; color:white; flex-shrink:0;
    }
    .badge-present {
        padding:2px 10px; border-radius:20px;
        font-size:10px; font-weight:700;
        background:#dcfce7; color:#15803d;
    }
    .badge-iot {
        padding:2px 10px; border-radius:20px;
        font-size:10px; font-weight:600;
        background:#eff6ff; color:#2563eb;
    }
</style>

<div class="max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Attendance</h1>
        <p class="text-sm text-gray-400 mt-1">
            {{ now()->format('l, F d, Y') }} · IoT-recorded attendance
        </p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="att-card p-5">
            <p class="text-3xl font-bold text-gray-800">{{ $attendances->total() }}</p>
            <p class="text-sm text-gray-400 mt-1">Total Records</p>
        </div>
        <div class="att-card p-5">
            <p class="text-3xl font-bold text-green-600">
                {{ $attendances->where('status','present')->count() }}
            </p>
            <p class="text-sm text-gray-400 mt-1">Present Today</p>
        </div>
        <div class="att-card p-5">
            <p class="text-3xl font-bold text-blue-600">
                {{ $attendances->where('source','iot')->count() }}
            </p>
            <p class="text-sm text-gray-400 mt-1">Via IoT Device</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="att-card">
        <div class="att-header">
            <p style="font-size:13px;font-weight:700;color:#111827;">
                Attendance Log
            </p>
            <span style="font-size:12px;color:#9ca3af;">
                {{ $attendances->total() }} records
            </span>
        </div>

        @forelse($attendances as $record)
        <div class="att-row">
            <div class="att-avatar">
                {{ strtoupper(substr($record->student_name ?? '?', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p style="font-size:13px;font-weight:600;color:#111827;">
                    {{ $record->student_name ?? $record->student_id }}
                </p>
                <p style="font-size:11px;color:#9ca3af;">
                    {{ $record->user->section->name ?? '—' }}
                </p>
            </div>
            <div class="text-right flex-shrink-0">
                <p style="font-size:11px;color:#6b7280;">
                    {{ \Carbon\Carbon::parse($record->attended_at)->format('M d, Y h:i A') }}
                </p>
                <div class="flex gap-1 justify-end mt-1">
                    <span class="badge-present">{{ strtoupper($record->status) }}</span>
                </div>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;">
            @if(!($hasSections ?? true))
            <p style="font-size:14px;color:#6b7280;font-weight:600;">
                You have no assigned sections yet.
            </p>
            <p style="font-size:13px;color:#9ca3af;margin-top:6px;">
                Once a faculty assigns you to a section, your students' attendance will appear here.
            </p>
            @else
            <p style="font-size:13px;color:#9ca3af;">
                No attendance records yet for your section. Waiting for the attendance device...
            </p>
            @endif
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($attendances->hasPages())
    <div class="mt-4">
        {{ $attendances->links() }}
    </div>
    @endif

</div>
@endsection