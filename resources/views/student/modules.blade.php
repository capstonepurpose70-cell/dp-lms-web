@extends('layouts.app')
@section('title', 'Learning Modules')

@section('content')
<style>
    .fade-up {
        animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }

    .subject-section {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
        margin-bottom: 16px;
        animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }

    .subject-header {
        padding: 14px 20px;
        background: #f8faff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .subject-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .material-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 13px 20px;
        border-bottom: 1px solid #f9fafb;
        transition: background 0.15s ease;
        text-decoration: none;
    }

    .material-row:last-child { border-bottom: none; }

    .material-row:hover {
        background: #f8faff;
    }

    .material-file-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: -0.5px;
        transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1);
    }

    .material-row:hover .material-file-icon {
        transform: scale(1.1) rotate(-4deg);
    }

    .download-btn {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 5px;
        background: #eff6ff;
        color: #2563eb;
        border: none;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        flex-shrink: 0;
        transition: background 0.2s ease, transform 0.15s ease;
    }

    .download-btn:hover {
        background: #dbeafe;
        transform: translateY(-1px);
    }

    .empty-box {
        padding: 40px 20px;
        text-align: center;
    }
</style>

<div style="max-width:896px;margin:0 auto;">

    <div class="fade-up" style="margin-bottom:24px;">
        <h1 style="font-size:24px;font-weight:700;color:#1f2937;">Learning Modules</h1>
        <p style="font-size:14px;color:#9ca3af;margin-top:4px;">
            @if($section)
                Materials for {{ $section->name }}
            @else
                Your learning materials
            @endif
        </p>
    </div>

    @if(!$section)
        {{-- Not enrolled yet --}}
        <div class="fade-up" style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:24px;text-align:center;">
            <svg width="32" height="32" fill="none" stroke="#f59e0b"
                stroke-width="2" viewBox="0 0 24 24" style="display:block;margin:0 auto 12px;">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p style="color:#b45309;font-weight:600;font-size:14px;">Not yet enrolled</p>
            <p style="color:#d97706;font-size:12px;margin-top:4px;">
                Please wait for the administrator to assign your section.
            </p>
        </div>

    @elseif($materials->isEmpty())
        {{-- Enrolled but no materials yet --}}
        <div class="fade-up" style="background:#fff;border:1px solid #f3f4f6;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:48px;text-align:center;">
            <div style="width:64px;height:64px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="28" height="28" fill="none" stroke="#93c5fd"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168
                           5.477 3 6.253v13C4.168 18.477 5.754 18 7.5
                           18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5
                           16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832
                           18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p style="color:#6b7280;font-weight:600;font-size:14px;">No materials available yet</p>
            <p style="color:#9ca3af;font-size:12px;margin-top:4px;">
                Your teachers haven't uploaded any materials yet. Check back later.
            </p>
        </div>

    @else
        {{-- Materials grouped by subject --}}
        @foreach($teacherSubjects as $ts)
            @php
                $subjectMaterials = $materials->get($ts->subject_id, collect());
            @endphp

            <div class="subject-section">
                <div class="subject-header">
                    <div class="subject-icon">
                        <svg width="16" height="16" fill="none" stroke="#3b82f6"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5
                                   5S4.168 5.477 3 6.253v13C4.168 18.477 5.754
                                   18 7.5 18s3.332.477 4.5 1.253m0-13C13.168
                                   5.477 14.754 5 16.5 5c1.747 0 3.332.477
                                   4.5 1.253v13C19.832 18.477 18.247 18 16.5
                                   18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <p style="font-size:14px;font-weight:700;color:#111827;">
                            {{ $ts->subject?->name ?? 'Subject removed' }}
                        </p>
                        <p style="font-size:11px;color:#9ca3af;">
                            {{ $ts->teacher->name ?? 'TBA' }}
                            &nbsp;·&nbsp;
                            {{ $subjectMaterials->count() }} material(s)
                        </p>
                    </div>
                </div>

                @if($subjectMaterials->count())
                    @foreach($subjectMaterials as $material)
                    @php
                        $ext = strtolower($material->file_type ?? 'file');
                        $colors = [
                            'pdf'  => ['bg' => '#fef2f2', 'color' => '#dc2626', 'label' => 'PDF'],
                            'doc'  => ['bg' => '#eff6ff', 'color' => '#2563eb', 'label' => 'DOC'],
                            'docx' => ['bg' => '#eff6ff', 'color' => '#2563eb', 'label' => 'DOC'],
                            'ppt'  => ['bg' => '#fff7ed', 'color' => '#ea580c', 'label' => 'PPT'],
                            'pptx' => ['bg' => '#fff7ed', 'color' => '#ea580c', 'label' => 'PPT'],
                            'xls'  => ['bg' => '#f0fdf4', 'color' => '#16a34a', 'label' => 'XLS'],
                            'xlsx' => ['bg' => '#f0fdf4', 'color' => '#16a34a', 'label' => 'XLS'],
                            'mp4'  => ['bg' => '#faf5ff', 'color' => '#9333ea', 'label' => 'VID'],
                            'png'  => ['bg' => '#f0fdf4', 'color' => '#0891b2', 'label' => 'IMG'],
                            'jpg'  => ['bg' => '#f0fdf4', 'color' => '#0891b2', 'label' => 'IMG'],
                            'jpeg' => ['bg' => '#f0fdf4', 'color' => '#0891b2', 'label' => 'IMG'],
                            'zip'  => ['bg' => '#fefce8', 'color' => '#ca8a04', 'label' => 'ZIP'],
                        ];
                        $style = $colors[$ext] ?? ['bg' => '#f3f4f6', 'color' => '#6b7280', 'label' => strtoupper($ext)];
                    @endphp
                    <div class="material-row">
                        <div class="material-file-icon"
                            style="background:{{ $style['bg'] }};color:{{ $style['color'] }};">
                            {{ $style['label'] }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <p style="font-size:13px;font-weight:600;color:#111827;
                                       white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $material->title }}
                            </p>
                            <p style="font-size:11px;color:#9ca3af;margin-top:1px;">
                                @if($material->quarter) {{ $material->quarter }} @endif
                                @if($material->week) · Week {{ $material->week }} @endif
                                · {{ $material->created_at->format('M d, Y') }}
                            </p>
                            @if($material->description)
                                <p style="font-size:11px;color:#6b7280;margin-top:2px;">
                                    {{ Str::limit($material->description, 80) }}
                                </p>
                            @endif
                        </div>
                        @if($material->file_path)
                            <a href="{{ asset('storage/' . $material->file_path) }}"
                                target="_blank"
                                class="download-btn">
                                <svg width="13" height="13" fill="none"
                                    stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4
                                           4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        @endif
                    </div>
                    @endforeach
                @else
                    <div class="empty-box">
                        <p style="font-size:13px;color:#9ca3af;">
                            No materials uploaded for this subject yet.
                        </p>
                    </div>
                @endif
            </div>
        @endforeach
    @endif

</div>
@endsection