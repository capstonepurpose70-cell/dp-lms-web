@extends('emails.layout')
@section('title', 'Activate your teacher account')
@section('preview', 'Set your password to start using DP-LMS.')

@section('content')
    <p style="margin:0 0 4px;">Hello, {{ $teacher->greetingName() }}.</p>
    <p style="margin:0;">The school created your DP-LMS teacher account. Set your password to activate it.</p>
    @include('emails.partials.button', ['url' => $inviteUrl, 'label' => 'Activate my account'])
    <p style="margin:16px 0 0; font-size:13.5px; color:#5b6675;">This link expires in {{ $expiresIn }}. If the button does not work, copy this address into your browser:</p>
    <p style="margin:6px 0 0; font-size:13px; word-break:break-all; color:#17427a;">{{ $inviteUrl }}</p>
    <p style="margin:12px 0 0; font-size:13.5px; color:#5b6675;">If you did not expect this email, you can ignore it.</p>
@endsection
