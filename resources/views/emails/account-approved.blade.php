@extends('emails.layout')
@section('title', 'Your account is approved')
@section('preview', 'You can now sign in and send your enrollment form.')

@section('content')
    <p style="margin:0 0 4px;">Hello, {{ $user->greetingName() }}.</p>
    <p style="margin:0;">The school approved your DP-LMS account. Sign in and complete your enrollment form so your adviser can place you in a section.</p>
    @include('emails.partials.button', ['url' => url('/login'), 'label' => 'Sign in'])
@endsection
