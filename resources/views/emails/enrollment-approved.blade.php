@extends('emails.layout')
@section('title', 'You are enrolled')
@section('preview', 'Your enrollment for school year ' . $request->school_year . ' is approved.')

@section('content')
    @php
        $rows = [
            'Grade level' => 'Grade ' . $request->grade_level,
            'Section'     => $section->name,
            'Subjects'    => $subjectCount . ' ' . \Illuminate\Support\Str::plural('subject', $subjectCount),
            'School year' => $request->school_year,
        ];
    @endphp
    <p style="margin:0 0 4px;">Hello, {{ $student->greetingName() }}.</p>
    <p style="margin:0;">Your adviser approved your enrollment. Here are your details.</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:18px 0 0; border-top:1px solid #e6e9ee;">
        @foreach($rows as $label => $value)
            <tr>
                <td style="padding:10px 0; border-bottom:1px solid #e6e9ee; font-size:14px; color:#5b6675;">{{ $label }}</td>
                <td align="right" style="padding:10px 0; border-bottom:1px solid #e6e9ee; font-size:14px; font-weight:bold; color:#2b3442;">{{ $value }}</td>
            </tr>
        @endforeach
    </table>
    @include('emails.partials.button', ['url' => url('/login'), 'label' => 'Open my dashboard'])
@endsection
