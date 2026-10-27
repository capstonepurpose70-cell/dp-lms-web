@extends('layouts.app')
@section('title', 'Learning Modules')

@section('content')
{{-- Layout tokens for text colors (readable contrast) --}}
<style>
    .sm-wrap { max-width: 900px; margin: 0 auto; }
    .sm-head { margin-bottom: 22px; }
    .sm-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .sm-sub { font-size: 13.5px; color: var(--text-2); margin-top: 4px; }

    .sm-notice { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: var(--r-md); padding: 18px 20px; color: #92400E; }
    .sm-notice b { display: block; font-size: 14px; margin-bottom: 2px; }
    .sm-notice p { font-size: 13px; line-height: 1.5; }
    .sm-notice a { display: inline-flex; align-items: center; min-height: 40px; margin-top: 10px; padding: 0 16px; border-radius: 10px; background: #B45309; color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; }

    .sm-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 16px; }
    .sm-subject { display: flex; align-items: center; gap: 12px; padding: 14px 20px; background: #F8FAFF; border-bottom: 1px solid var(--border); }
    .sm-subject-ico { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; display: grid; place-items: center; background: var(--accent-lt); color: var(--accent); }
    .sm-subject-name { font-size: 14.5px; font-weight: 700; color: var(--text-1); }
    .sm-subject-meta { font-size: 12px; color: var(--text-3); margin-top: 1px; }

    .sm-row { display: flex; align-items: center; gap: 14px; padding: 13px 20px; border-bottom: 1px solid var(--border); }
    .sm-row:last-child { border-bottom: none; }
    .sm-type {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; display: grid; place-items: center;
        background: var(--accent-lt); color: var(--accent-dark); font-size: 10.5px; font-weight: 800; letter-spacing: .02em;
    }
    .sm-row-main { flex: 1; min-width: 0; }
    .sm-row-title { font-size: 13.5px; font-weight: 700; color: var(--text-1); overflow-wrap: anywhere; }
    .sm-row-meta { font-size: 12px; color: var(--text-3); margin-top: 2px; }
    .sm-row-desc { font-size: 12.5px; color: var(--text-2); margin-top: 3px; line-height: 1.5; overflow-wrap: anywhere; }

    .sm-open {
        display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 14px; flex-shrink: 0;
        border-radius: 10px; background: var(--accent-lt); color: var(--accent-dark);
        font-size: 13px; font-weight: 700; text-decoration: none; transition: background .15s;
    }
    .sm-open:hover { background: #DBEAFE; }
    .sm-nofile { font-size: 12px; color: var(--text-3); flex-shrink: 0; }

    .sm-empty { padding: 28px 20px; text-align: center; font-size: 13px; color: var(--text-3); }
    .sm-empty-big { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 44px 20px; text-align: center; }
    .sm-empty-big b { display: block; font-size: 14.5px; color: var(--text-1); margin-bottom: 4px; }
    .sm-empty-big p { font-size: 13px; color: var(--text-2); }

    @media (max-width: 560px) {
        .sm-row { flex-wrap: wrap; padding: 13px 16px; }
        .sm-row-main { flex-basis: calc(100% - 54px); }
        .sm-open, .sm-nofile { margin-left: 54px; }
        .sm-subject { padding: 13px 16px; }
    }
</style>

<div class="sm-wrap">
    <div class="sm-head">
        <h1 class="sm-title">Learning Modules</h1>
        <p class="sm-sub">
            @if($section) Materials for Grade {{ $section->grade_level }} – {{ $section->name }} @else Your learning materials @endif
        </p>
    </div>

    @if(!$section)
        <div class="sm-notice" role="status">
            <b>Not enrolled yet</b>
            <p>Your modules appear once you are placed in a section. Fill out the enrollment form if you haven't yet.</p>
            <a href="{{ route('student.dashboard') }}">Go to dashboard</a>
        </div>

    @elseif($materials->isEmpty())
        <div class="sm-empty-big">
            <b>No materials yet</b>
            <p>Your teachers haven't uploaded any materials. Check back later.</p>
        </div>

    @else
        @foreach($teacherSubjects as $ts)
            @php $subjectMaterials = $materials->get($ts->subject_id, collect()); @endphp

            <section class="sm-card" aria-labelledby="subj-{{ $ts->subject_id }}">
                <div class="sm-subject">
                    <span class="sm-subject-ico" aria-hidden="true">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <div>
                        <h2 class="sm-subject-name" id="subj-{{ $ts->subject_id }}">{{ $ts->subject?->name ?? 'Subject removed' }}</h2>
                        <p class="sm-subject-meta">
                            {{ $ts->teacher->name ?? 'Teacher to be assigned' }} · {{ $subjectMaterials->count() }} {{ Str::plural('material', $subjectMaterials->count()) }}
                        </p>
                    </div>
                </div>

                @forelse($subjectMaterials as $material)
                    @php
                        $ext   = strtolower((string) $material->file_type);
                        $label = ['docx' => 'DOC', 'pptx' => 'PPT', 'xlsx' => 'XLS', 'jpeg' => 'IMG', 'jpg' => 'IMG', 'png' => 'IMG', 'mp4' => 'VID'][$ext]
                                 ?? ($ext !== '' ? strtoupper(substr($ext, 0, 4)) : 'NOTE');
                    @endphp
                    <div class="sm-row">
                        <span class="sm-type" aria-hidden="true">{{ $label }}</span>
                        <div class="sm-row-main">
                            <p class="sm-row-title">{{ $material->title }}</p>
                            <p class="sm-row-meta">
                                {{ collect([$material->quarter, $material->week ? 'Week ' . $material->week : null, $material->created_at->format('M j, Y')])->filter()->implode(' · ') }}
                            </p>
                            @if($material->description)
                                <p class="sm-row-desc">{{ Str::limit($material->description, 160) }}</p>
                            @endif
                        </div>
                        @if($material->file_path)
                            <a href="{{ route('student.modules.open', $material) }}" target="_blank" rel="noopener" class="sm-open"
                               aria-label="Open {{ $material->title }} ({{ $label }}, opens in a new tab)">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Open
                            </a>
                        @else
                            <span class="sm-nofile">No file</span>
                        @endif
                    </div>
                @empty
                    <p class="sm-empty">No materials for this subject yet.</p>
                @endforelse
            </section>
        @endforeach
    @endif
</div>
@endsection
