@extends('layouts.auth-card')

@section('title', 'Terms of Use')
@section('wrap-class', 'is-wide')
@section('heading', 'Terms of Use')
@section('desc', 'The rules for using DP-LMS, the learning portal of Sto. Domingo National High School.')

@section('content')
@include('legal.partials.styles')
<div class="lg-doc">
    <p class="lg-updated">Last updated: November 3, 2026</p>

    <h2>1. Who may use DP-LMS</h2>
    <p>DP-LMS is for enrolled students, their parents or guardians, and the teachers and staff of Sto. Domingo National High School. Students activate their account with the LRN on the school's official list; parents link through a code made by their child; teacher accounts are created by the school.</p>

    <h2>2. Your account</h2>
    <ul>
        <li>Keep your password and sign-in codes to yourself. You are responsible for what is done with your account.</li>
        <li>Use your real name and details. Do not sign in as someone else or let others use your account.</li>
        <li>Tell your adviser or the school office right away if you think someone else has used your account.</li>
    </ul>

    <h2>3. Acceptable use</h2>
    <p>Use DP-LMS only for school work and school communication. You must not:</p>
    <ul>
        <li>cheat on quizzes or activities, or share answers meant to be done alone;</li>
        <li>post or send messages that are rude, threatening, or bullying, or that share another person's private information;</li>
        <li>upload harmful files, try to get into accounts or data that are not yours, or disrupt the system;</li>
        <li>copy or share learning materials outside the school without the teacher's permission.</li>
    </ul>

    <h2>4. Attendance and face recognition</h2>
    <p>The school gate camera and class attendance codes record when a student arrives and attends class, and parents are notified. Registering a face is needed for the gate camera; students who cannot or will not register should tell their adviser so attendance can be recorded another way.</p>

    <h2>5. Grades and records</h2>
    <p>Grades, attendance and enrollment shown in DP-LMS come from your teachers and the school. The school's official records (such as the report card) prevail if there is a difference — report any error to your adviser.</p>

    <h2>6. When the school may limit access</h2>
    <p>The school may suspend or close an account that breaks these terms, or that is no longer needed (for example after a student leaves the school), following the school's discipline policies.</p>

    <h2>7. Changes</h2>
    <p>The school may update these terms. When they change in an important way, you will be asked to read and accept them again.</p>

    <h2>8. Contact</h2>
    <p>Sto. Domingo National High School, Sto. Domingo, San Manuel, Pangasinan · <a href="mailto:300360@deped.gov.ph">300360@deped.gov.ph</a> · 0968-852-8413. See also the <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
</div>
@endsection

@section('footer')
    <span><a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login') }}">Back</a></span>
    <span><a href="{{ route('privacy') }}">Privacy Policy</a></span>
@endsection
