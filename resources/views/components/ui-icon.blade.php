{{--
    One outline icon set for the teacher pages: 24-unit grid, round caps and joins, drawn to read at 18–20 px.
    Usage: <x-ui-icon name="gradebook" /> or <x-ui-icon name="attendance" size="18" />
--}}
@props(['name', 'size' => 20, 'stroke' => 1.75])
@php
    $paths = [
        'dashboard'     => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
        'gradebook'     => '<path d="M4.5 19V5.5a2 2 0 0 1 2-2h12.5v14H6.5a2 2 0 0 0-2 2Zm0 0a2 2 0 0 0 2 2H19"/><path d="M9 8h6M9 11.5h4"/>',
        'materials'     => '<path d="M3.5 7.5a2 2 0 0 1 2-2h3.6l2 2.2h7.4a2 2 0 0 1 2 2v7.8a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2Z"/>',
        'quizzes'       => '<rect x="8.5" y="2.5" width="7" height="3.5" rx="1"/><path d="M15.5 4.25H17a2 2 0 0 1 2 2V19.5a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6.25a2 2 0 0 1 2-2h1.5"/><path d="m9 12.5 1.5 1.5 3.5-3.5M9 17.5h6"/>',
        'announcements' => '<path d="M3.5 10.5v3a1 1 0 0 0 1 1H7l7 4.5V5L7 9.5H4.5a1 1 0 0 0-1 1Z"/><path d="M7 14.5 8.2 20h2.3l-.9-4.6"/><path d="M17.5 9.5a3.5 3.5 0 0 1 0 5"/>',
        'students'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M15.5 4.7a3.5 3.5 0 0 1 0 6.6M18 14.4a6.5 6.5 0 0 1 3.5 5.6"/>',
        'enrollments'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 11.2-4.5"/><path d="M18.5 13v7M15 16.5h7"/>',
        'attendance'    => '<rect x="3.5" y="4.5" width="17" height="16" rx="2"/><path d="M8 2.5v4M16 2.5v4M3.5 9.5h17"/><path d="m9 15 2 2 4-4"/>',
        'face'          => '<path d="M3.5 8V5.5a2 2 0 0 1 2-2H8M16 3.5h2.5a2 2 0 0 1 2 2V8M20.5 16v2.5a2 2 0 0 1-2 2H16M8 20.5H5.5a2 2 0 0 1-2-2V16"/><path d="M9 9.5v1M15 9.5v1"/><path d="M9 15a4 4 0 0 0 6 0"/>',
        'messages'      => '<path d="M20.5 11.5a8 8 0 0 1-11.7 7.1L3.5 20l1.4-4.9A8 8 0 1 1 20.5 11.5Z"/>',
        'logout'        => '<path d="M10 4.5H6.5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2H10"/><path d="M14.5 16.5 19 12l-4.5-4.5M19 12H9.5"/>',
        'bell'          => '<path d="M6 9.5a6 6 0 0 1 12 0c0 4.5 1.5 6.5 2.5 7.5h-17C4.5 16 6 14 6 9.5Z"/><path d="M10 20.5a2.2 2.2 0 0 0 4 0"/>',
        'chevron-right' => '<path d="m9.5 6 6 6-6 6"/>',
    ];
@endphp
<svg {{ $attributes->merge(['aria-hidden' => 'true', 'focusable' => 'false']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round">{!! $paths[$name] ?? '' !!}</svg>
