@extends('emails.layout')
@section('title', 'Your verification code')
@section('preview', 'Your DP-LMS code expires in 5 minutes.')

@section('content')
    <p style="margin:0 0 4px;">Hello, {{ $userName }}.</p>
    <p style="margin:0;">Enter this code in DP-LMS to continue. It expires in 5 minutes.</p>
    @include('emails.partials.code', ['code' => $code])
    <p style="margin:0; font-size:13.5px; color:#5b6675;">If you did not ask for this code, you can ignore this email. Never share it with anyone, including school staff.</p>
@endsection
