@extends('emails.layout')
@section('title', 'Reset your password')
@section('preview', 'Your password reset code expires in 10 minutes.')

@section('content')
    <p style="margin:0;">Use this code to reset the password of <strong>{{ $email }}</strong>. It expires in 10 minutes.</p>
    @include('emails.partials.code', ['code' => $code])
    <p style="margin:0; font-size:13.5px; color:#5b6675;">If you did not ask to reset your password, you can ignore this email. Your password stays the same. Never share this code with anyone.</p>
@endsection
