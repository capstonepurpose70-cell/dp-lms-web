@extends('layouts.auth-card')

@section('title', 'Privacy Policy')
@section('wrap-class', 'is-wide')
@section('heading', 'Privacy Policy')
@section('desc', 'How DP-LMS collects, uses and protects personal information, following the Data Privacy Act of 2012 (Republic Act No. 10173).')

@section('content')
@include('legal.partials.styles')
<div class="lg-doc">
    <p class="lg-updated">Last updated: November 3, 2026</p>

    <h2>1. What we collect</h2>
    <ul>
        <li><b>Students:</b> LRN, full name, birthdate, sex, address, grade and section, email, the enrollment form (including parents' or guardian's names and contact number), grades, quiz answers, class attendance, gate arrivals, and messages with teachers.</li>
        <li><b>Face photos:</b> a few photos of the student's face, used only so the school gate camera can recognize the student. These are sensitive personal information and are kept private.</li>
        <li><b>Parents and guardians:</b> name, mobile number, optional email, and which student they are linked to.</li>
        <li><b>Teachers:</b> name, email, employee ID, mobile number, advisory section, and the classes and grades they handle.</li>
        <li><b>Technical records:</b> sign-in times, the device used for sign-in codes, and a log of important actions, to keep accounts secure.</li>
    </ul>

    <h2>2. Why we use it</h2>
    <ul>
        <li>to run classes: enrollment, sections, schedules, learning materials, quizzes, grades and attendance;</li>
        <li>to tell parents when their child arrives at school, and to share grades and records with linked parents;</li>
        <li>to keep accounts secure and to look into misuse;</li>
        <li>to prepare reports the school needs, such as attendance and enrollment summaries.</li>
    </ul>
    <p>We do not sell personal information or use it for advertising.</p>

    <h2>3. Who can see it</h2>
    <ul>
        <li>Students see their own records. Linked parents see their own child's records.</li>
        <li>Teachers see the students in the classes and sections they handle. School administrators manage accounts and records.</li>
        <li>Information is shared outside the school only when the law requires it, for example with the Department of Education.</li>
    </ul>

    <h2>4. How we protect it</h2>
    <p>Passwords are stored encrypted, sign-in from a new device needs a one-time code, each role sees only what it needs, face photos are stored outside public access, and important actions are logged.</p>

    <h2>5. How long we keep it</h2>
    <p>Records are kept while the student is enrolled and afterwards for as long as school record rules require. Face photos are deleted when they are no longer needed for the gate camera, for example when the student leaves the school.</p>

    <h2>6. Your rights</h2>
    <p>Under the Data Privacy Act you may ask to be informed about, to access, and to correct your personal information; to object to or ask for deletion of data that is not required by school rules; and to file a complaint with the National Privacy Commission. Parents or guardians may exercise these rights for minor students.</p>

    <h2>7. Contact</h2>
    <p>For privacy questions or requests, contact the school: Sto. Domingo National High School, Sto. Domingo, San Manuel, Pangasinan · <a href="mailto:300360@deped.gov.ph">300360@deped.gov.ph</a> · 0968-852-8413. See also the <a href="{{ route('terms') }}">Terms of Use</a>.</p>
</div>
@endsection

@section('footer')
    <span><a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login') }}">Back</a></span>
    <span><a href="{{ route('terms') }}">Terms of Use</a></span>
@endsection
