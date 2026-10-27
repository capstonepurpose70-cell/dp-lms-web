@extends('layouts.app')
@section('title', 'Enrollment Form')

@section('content')
{{-- B6-5/B6-6/B6-7: plain CSS (max-w-2xl / mb-5 were missing from the build), responsive grid,
     real radio buttons for Student Type, labels tied to fields, readable errors, English hints. --}}
<style>
    .ef-wrap { max-width: 720px; margin: 0 auto; }
    .ef-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; }
    .ef-sub { font-size: 13.5px; color: var(--text-2); margin: 4px 0 20px; line-height: 1.55; }

    .ef-errors { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: 12px 16px; margin-bottom: 18px; font-size: 13.5px; }
    .ef-errors ul { padding-left: 18px; line-height: 1.6; }

    .ef-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); padding: 22px; margin-bottom: 16px; }
    .ef-section { font-size: 12px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--accent-dark); padding-bottom: 10px; margin-bottom: 16px; border-bottom: 1px solid var(--border); }

    .ef-grid { display: grid; gap: 14px; margin-bottom: 14px; }
    .ef-2 { grid-template-columns: 1fr 1fr; }
    .ef-3 { grid-template-columns: 1fr 1fr 1fr; }
    .ef-field { min-width: 0; }
    .ef-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-1); margin-bottom: 6px; }
    .ef-label .req { color: #B91C1C; }
    .ef-label .opt { font-weight: 500; color: var(--text-3); }
    .ef-input {
        width: 100%; min-height: 44px; border: 1.5px solid var(--border); border-radius: 10px; padding: 10px 12px;
        font-size: 14px; color: var(--text-1); background: #F8FAFC; font-family: inherit;
        transition: border-color .15s, box-shadow .15s;
    }
    .ef-input:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-glow); }
    .ef-input[readonly] { background: #F1F5F9; color: var(--text-2); cursor: not-allowed; }
    .ef-input.is-error { border-color: #F87171; background: #FFF7F7; }
    .ef-hint { font-size: 12px; color: var(--text-3); margin-top: 5px; line-height: 1.45; }
    .ef-error { font-size: 12.5px; color: #B91C1C; margin-top: 5px; font-weight: 600; }

    /* Student type: real radios (dati <div onclick> — hindi maaabot ng keyboard) */
    .ef-types { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; border: 0; padding: 0; margin: 0 0 14px; }
    .ef-types legend { font-size: 13px; font-weight: 700; color: var(--text-1); margin-bottom: 6px; padding: 0; }
    .ef-type { position: relative; }
    .ef-type input { position: absolute; opacity: 0; inset: 0; margin: 0; cursor: pointer; }
    .ef-type span {
        display: flex; align-items: center; justify-content: center; min-height: 44px; padding: 8px;
        border: 1.5px solid var(--border); border-radius: 10px; background: #F8FAFC;
        font-size: 13.5px; font-weight: 700; color: var(--text-2); text-align: center;
    }
    .ef-type input:checked + span { border-color: var(--accent); background: var(--accent-lt); color: var(--accent-dark); }
    .ef-type input:focus-visible + span { outline: 3px solid var(--accent); outline-offset: 2px; }
    .ef-transfer[hidden] { display: none; }

    .ef-submit {
        width: 100%; min-height: 50px; border: none; border-radius: 12px; cursor: pointer;
        background: var(--accent); color: #fff; font-size: 15px; font-weight: 700; font-family: inherit;
        box-shadow: 0 6px 16px var(--accent-glow);
    }
    .ef-submit:hover { background: var(--accent-dark); }
    .ef-submit[disabled] { opacity: .7; cursor: progress; }
    .ef-foot { font-size: 12.5px; color: var(--text-2); text-align: center; margin-top: 10px; }

    @media (max-width: 640px) {
        .ef-2, .ef-3 { grid-template-columns: 1fr; }
        .ef-card { padding: 18px 16px; }
    }
</style>

@php
    $err = fn ($f) => $errors->has($f) ? 'is-error' : '';
    $type = old('student_type', 'new');
@endphp

<div class="ef-wrap">
    <h1 class="ef-title">Enrollment Form</h1>
    <p class="ef-sub">Fill out every required field (<span style="color:#B91C1C;">*</span>). Your adviser reviews the form and places you in a section.</p>

    @if($errors->any())
        <div class="ef-errors" role="alert">
            <b>Please fix the following:</b>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('student.enroll.submit') }}" id="efForm" novalidate>
        @csrf

        {{-- 1. Enrollment --}}
        <section class="ef-card" aria-labelledby="ef-s1">
            <h2 class="ef-section" id="ef-s1">Enrollment</h2>

            <div class="ef-grid ef-2">
                <div class="ef-field">
                    <label class="ef-label" for="ef-grade">Grade level</label>
                    <input id="ef-grade" type="text" class="ef-input" readonly aria-describedby="ef-grade-hint"
                           value="{{ $userGrade ? 'Grade ' . $userGrade : 'To be confirmed by the school' }}">
                    <p class="ef-hint" id="ef-grade-hint">Set automatically from your school records.</p>
                </div>
                <div class="ef-field">
                    <label class="ef-label" for="ef-sy">School year</label>
                    <input id="ef-sy" type="text" class="ef-input" readonly value="{{ $activeSy->label ?? 'Current school year' }}">
                </div>
            </div>

            <fieldset class="ef-types">
                <legend>Student type <span class="req" style="color:#B91C1C;">*</span></legend>
                @foreach(['new' => 'New student', 'old' => 'Old student', 'transfer' => 'Transferee'] as $value => $label)
                    <label class="ef-type">
                        <input type="radio" name="student_type" value="{{ $value }}" @checked($type === $value)>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </fieldset>
            @error('student_type')<p class="ef-error">{{ $message }}</p>@enderror

            <div class="ef-grid ef-2 ef-transfer" id="efTransfer" @if($type !== 'transfer') hidden @endif>
                <div class="ef-field">
                    <label class="ef-label" for="ef-last-school">Last school attended</label>
                    <input id="ef-last-school" type="text" name="last_school" value="{{ old('last_school') }}" maxlength="255"
                           placeholder="School name" class="ef-input {{ $err('last_school') }}">
                    @error('last_school')<p class="ef-error">{{ $message }}</p>@enderror
                </div>
                <div class="ef-field">
                    <label class="ef-label" for="ef-last-grade">Last grade completed</label>
                    <input id="ef-last-grade" type="text" name="last_grade_completed" value="{{ old('last_grade_completed') }}" maxlength="50"
                           placeholder="e.g. Grade 7" class="ef-input {{ $err('last_grade_completed') }}">
                    @error('last_grade_completed')<p class="ef-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="ef-field" style="max-width:320px;">
                <label class="ef-label" for="ef-ga">General average last school year <span class="opt">(optional)</span></label>
                <input id="ef-ga" type="number" name="general_average" value="{{ old('general_average') }}" min="60" max="100" step="0.01"
                       inputmode="decimal" placeholder="e.g. 88.50" aria-describedby="ef-ga-hint" class="ef-input {{ $err('general_average') }}">
                @error('general_average')<p class="ef-error">{{ $message }}</p>@enderror
                <p class="ef-hint" id="ef-ga-hint">From your last report card (Form 138). Leave blank if you don't know it.</p>
            </div>
        </section>

        {{-- 2. Personal --}}
        <section class="ef-card" aria-labelledby="ef-s2">
            <h2 class="ef-section" id="ef-s2">Personal information</h2>

            <div class="ef-grid ef-2">
                <div class="ef-field">
                    <label class="ef-label" for="ef-name">Full name <span class="req">*</span></label>
                    <input id="ef-name" type="text" name="full_name" value="{{ old('full_name', $user->name) }}" maxlength="255" required
                           autocomplete="name" data-letters class="ef-input {{ $err('full_name') }}" aria-describedby="ef-name-hint"
                           @error('full_name') aria-invalid="true" @enderror>
                    @error('full_name')<p class="ef-error">{{ $message }}</p>@enderror
                    <p class="ef-hint" id="ef-name-hint">Letters only, as on your birth certificate.</p>
                </div>
                <div class="ef-field">
                    <label class="ef-label" for="ef-address">Home address <span class="req">*</span></label>
                    <input id="ef-address" type="text" name="address" value="{{ old('address') }}" maxlength="500" required
                           autocomplete="street-address" placeholder="House no., street, barangay, town"
                           class="ef-input {{ $err('address') }}" @error('address') aria-invalid="true" @enderror>
                    @error('address')<p class="ef-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="ef-grid ef-3">
                <div class="ef-field">
                    <label class="ef-label" for="ef-birthdate">Birthdate <span class="req">*</span></label>
                    <input id="ef-birthdate" type="date" name="birthdate" value="{{ old('birthdate') }}" required
                           max="{{ now()->subDay()->toDateString() }}" autocomplete="bday"
                           class="ef-input {{ $err('birthdate') }}" @error('birthdate') aria-invalid="true" @enderror>
                    @error('birthdate')<p class="ef-error">{{ $message }}</p>@enderror
                </div>
                <div class="ef-field">
                    <label class="ef-label" for="ef-age">Age <span class="req">*</span></label>
                    <input id="ef-age" type="number" name="age" value="{{ old('age') }}" min="10" max="25" required inputmode="numeric"
                           class="ef-input {{ $err('age') }}" aria-describedby="ef-age-hint" @error('age') aria-invalid="true" @enderror>
                    @error('age')<p class="ef-error">{{ $message }}</p>@enderror
                    <p class="ef-hint" id="ef-age-hint">Filled in from your birthdate.</p>
                </div>
                <div class="ef-field">
                    <label class="ef-label" for="ef-gender">Sex <span class="req">*</span></label>
                    <select id="ef-gender" name="gender" required class="ef-input {{ $err('gender') }}" @error('gender') aria-invalid="true" @enderror>
                        <option value="">Select…</option>
                        <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                    </select>
                    @error('gender')<p class="ef-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- 3. Family --}}
        <section class="ef-card" aria-labelledby="ef-s3">
            <h2 class="ef-section" id="ef-s3">Family information</h2>
            <p class="ef-hint" style="margin:-6px 0 14px;">Your parent uses one of these names to link their DP-LMS account with yours.</p>

            <div class="ef-grid ef-2">
                @foreach(['mother_name' => "Mother's full name", 'father_name' => "Father's full name", 'guardian_name' => "Guardian's full name"] as $field => $label)
                    <div class="ef-field">
                        <label class="ef-label" for="ef-{{ $field }}">{{ $label }} <span class="opt">(optional)</span></label>
                        <input id="ef-{{ $field }}" type="text" name="{{ $field }}" value="{{ old($field) }}" maxlength="255" data-letters
                               class="ef-input {{ $err($field) }}">
                        @error($field)<p class="ef-error">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div class="ef-field">
                    <label class="ef-label" for="ef-gcontact">Guardian's contact number <span class="opt">(optional)</span></label>
                    <input id="ef-gcontact" type="tel" name="guardian_contact" value="{{ old('guardian_contact') }}" maxlength="20"
                           inputmode="tel" autocomplete="tel" placeholder="09XXXXXXXXX" class="ef-input {{ $err('guardian_contact') }}">
                    @error('guardian_contact')<p class="ef-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <button type="submit" class="ef-submit">Submit enrollment form</button>
        <p class="ef-foot">You'll see the result on your dashboard once your adviser reviews it.</p>
    </form>
</div>

<script>
(function () {
    // Transferee → show last school fields
    const transfer = document.getElementById('efTransfer');
    document.querySelectorAll('input[name="student_type"]').forEach(function (r) {
        r.addEventListener('change', function () { transfer.hidden = r.value !== 'transfer' || !r.checked; });
    });

    // Names: letters only (same rule as the server)
    document.querySelectorAll('input[data-letters]').forEach(function (i) {
        i.addEventListener('input', function () { const v = i.value.replace(/[0-9]/g, ''); if (v !== i.value) i.value = v; });
    });

    // Age from birthdate (B6-7) — still editable
    const bd = document.getElementById('ef-birthdate'), age = document.getElementById('ef-age');
    function fillAge() {
        if (!bd.value) return;
        const b = new Date(bd.value + 'T00:00:00'), n = new Date();
        let a = n.getFullYear() - b.getFullYear();
        if (n.getMonth() < b.getMonth() || (n.getMonth() === b.getMonth() && n.getDate() < b.getDate())) a--;
        if (a > 0 && a < 120) age.value = a;
    }
    bd.addEventListener('change', fillAge);
    if (bd.value && !age.value) fillAge();

    // No double submit; Back restores the button
    const form = document.getElementById('efForm'), btn = form.querySelector('.ef-submit');
    form.addEventListener('submit', function () { btn.disabled = true; btn.textContent = 'Submitting…'; });
    window.addEventListener('pageshow', function (e) { if (e.persisted) { btn.disabled = false; btn.textContent = 'Submit enrollment form'; } });
})();
</script>
@endsection
