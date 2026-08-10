@extends('layouts.app')
@section('title', 'Submit Enrollment')

@section('sidebar')
    <a href="{{ route('student.dashboard') }}"
        class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('student.enroll') }}"
        class="{{ request()->routeIs('student.enroll*') ? 'active' : '' }}">
        Enrollment Form
    </a>
    <a href="{{ route('student.modules') }}"
        class="{{ request()->routeIs('student.modules') ? 'active' : '' }}">
        Learning Modules
    </a>
    <a href="{{ route('student.grades') }}"
        class="{{ request()->routeIs('student.grades') ? 'active' : '' }}">
        My Grades
    </a>
@endsection

@section('content')
<style>
    .form-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        padding: 24px;
        margin-bottom: 16px;
        animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }

    .section-title {
        font-size: 11px;
        font-weight: 700;
        color: #2563eb;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1.5px solid #eff6ff;
    }

    .field-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .field-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 9px;
        padding: 10px 13px;
        font-size: 13px;
        color: #111827;
        background: #fafafa;
        outline: none;
        font-family: inherit;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        appearance: none;
    }

    .field-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        background: #fff;
    }

    .field-input.error {
        border-color: #fca5a5;
        background: #fff5f5;
    }

    select.field-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 13px;
        padding-right: 30px;
        cursor: pointer;
    }

    .field-error {
        font-size: 10px;
        color: #ef4444;
        margin-top: 3px;
        font-weight: 500;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    .type-row {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

  .type-btn {
    flex: 1;
    padding: 10px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    background: #fafafa;
    color: #6b7280;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s ease;
    user-select: none;
    -webkit-user-select: none;
    position: relative;
    z-index: 10;
}

    .type-btn:hover { border-color: #3b82f6; color: #2563eb; }

    .type-btn.selected {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
    }

    .transfer-fields {
        display: none;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 10px;
    }

    .transfer-fields.show { display: grid; }

    .btn-submit {
        width: 100%;
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 14px rgba(37,99,235,0.28);
        position: relative;
        overflow: hidden;
    }

    .btn-submit::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, transparent,
            rgba(255,255,255,0.18), transparent);
        transition: left 0.5s ease;
    }

    .btn-submit:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(37,99,235,0.38);
    }

    .btn-submit:hover::before { left: 150%; }
    .btn-submit:active { transform: scale(0.98); }
</style>

<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Enrollment Form</h1>
        <p class="text-sm text-gray-400 mt-1">
            Fill out all required fields accurately.
            Your enrollment will be reviewed by your adviser.
        </p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm
                    rounded-xl px-4 py-3 mb-5">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('student.enroll.submit') }}">
        @csrf

        {{-- Section 1: Enrollment --}}
        <div class="form-card">
            <p class="section-title">Enrollment Information</p>

            <div class="form-grid-3">
                <div>
                    <label class="field-label">Grade Level</label>
                    <input type="text" class="field-input" readonly
                           value="{{ $userGrade ? 'Grade ' . $userGrade : 'To be confirmed by the school' }}"
                           style="background:#f3f4f6; cursor:not-allowed;">
                    <p style="font-size:11px;color:#6b7280;margin:4px 0 0;">Awtomatikong itinatakda mula sa iyong school records.</p>
                </div>
                <div>
                    <label class="field-label">School Year</label>
                    <input type="text" class="field-input" readonly
                           value="{{ $activeSy->label ?? 'Current School Year' }}"
                           style="background:#f3f4f6; cursor:not-allowed;">
                    <p style="font-size:11px;color:#6b7280;margin:4px 0 0;">Kasalukuyang aktibong school year.</p>
                </div>
            </div>

            <div class="mb-3">
                <label class="field-label mb-2">Student Type *</label>
                <div class="type-row">
                    <div class="type-btn {{ old('student_type','new') === 'new' ? 'selected' : '' }}"
                        onclick="selectType('new',this)">
                        New Student
                    </div>
                    <div class="type-btn {{ old('student_type') === 'old' ? 'selected' : '' }}"
                        onclick="selectType('old',this)">
                        Old Student
                    </div>
                    <div class="type-btn {{ old('student_type') === 'transfer' ? 'selected' : '' }}"
                        onclick="selectType('transfer',this)">
                        Transfer
                    </div>
                </div>
                <input type="hidden" name="student_type" id="student_type"
                    value="{{ old('student_type','new') }}">

                <div class="transfer-fields {{ old('student_type') === 'transfer' ? 'show' : '' }}"
                    id="transfer-fields">
                    <div>
                        <label class="field-label">Last School Attended</label>
                        <input type="text" name="last_school"
                            value="{{ old('last_school') }}"
                            placeholder="School name"
                            class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Last Grade Completed</label>
                        <input type="text" name="last_grade_completed"
                            value="{{ old('last_grade_completed') }}"
                            placeholder="e.g. Grade 7"
                            class="field-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Personal Info --}}
        <div class="form-card">
            <p class="section-title">Personal Information</p>

            <div class="form-grid-2">
                <div>
                    <label class="field-label">Full Name *</label>
                    <input type="text" name="full_name"
                        value="{{ old('full_name', $user->name) }}"
                        placeholder="Complete full name"
                        oninput="blockNumbers(this)"
                        required
                        class="field-input {{ $errors->has('full_name') ? 'error' : '' }}">
                    <p style="font-size:10px;color:#9ca3af;margin-top:2px;">
                        Letters only, no numbers
                    </p>
                    @error('full_name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label">Address *</label>
                    <input type="text" name="address"
                        value="{{ old('address') }}"
                        placeholder="Complete home address"
                        required
                        class="field-input">
                    @error('address')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-grid-3">
                <div>
                    <label class="field-label">Age *</label>
                    <input type="number" name="age" min="10" max="25"
                        value="{{ old('age') }}"
                        placeholder="Age" required
                        oninput="blockLetters(this)"
                        class="field-input {{ $errors->has('age') ? 'error' : '' }}">
                    @error('age')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label">Birthdate *</label>
                    <input type="date" name="birthdate"
                        value="{{ old('birthdate') }}"
                        required
                        class="field-input {{ $errors->has('birthdate') ? 'error' : '' }}">
                    @error('birthdate')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                    <p id="birthHint" style="font-size:12px;color:#16a34a;margin-top:5px;font-weight:600;line-height:1.4;"></p>
                </div>
                <div>
                    <label class="field-label">Gender *</label>
                    <select name="gender" required
                        class="field-input {{ $errors->has('gender') ? 'error' : '' }}">
                        <option value="">— Select —</option>
                        <option value="Male"   {{ old('gender')=='Male'   ?'selected':'' }}>Male</option>
                        <option value="Female" {{ old('gender')=='Female' ?'selected':'' }}>Female</option>
                    </select>
                    @error('gender')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 3: Family Info --}}
        <div class="form-card">
            <p class="section-title">Family Information</p>

            <div class="form-grid-2">
                <div>
                    <label class="field-label">Mother's Name</label>
                    <input type="text" name="mother_name"
                        value="{{ old('mother_name') }}"
                        placeholder="Mother's full name"
                        oninput="blockNumbers(this)"
                        class="field-input">
                    @error('mother_name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label">Father's Name</label>
                    <input type="text" name="father_name"
                        value="{{ old('father_name') }}"
                        placeholder="Father's full name"
                        oninput="blockNumbers(this)"
                        class="field-input">
                    @error('father_name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="field-label">Guardian's Name</label>
                    <input type="text" name="guardian_name"
                        value="{{ old('guardian_name') }}"
                        placeholder="Guardian's full name"
                        oninput="blockNumbers(this)"
                        class="field-input">
                    @error('guardian_name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label">Guardian's Contact</label>
                    <input type="text" name="guardian_contact"
                        value="{{ old('guardian_contact') }}"
                        placeholder="09XXXXXXXXX"
                        oninput="blockLetters(this)"
                        class="field-input">
                    @error('guardian_contact')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            Submit Enrollment Form
        </button>
    </form>
</div>

<script>
    function selectType(type, el) {
        document.querySelectorAll('.type-btn').forEach(function(b) {
            b.classList.remove('selected');
        });
        el.classList.add('selected');
        document.getElementById('student_type').value = type;
        var tf = document.getElementById('transfer-fields');
        tf.classList.toggle('show', type === 'transfer');
    }

    // Block numbers in name fields
    function blockNumbers(input) {
        input.value = input.value.replace(/[0-9]/g, '');
    }

    // Block letters in number fields
    function blockLetters(input) {
        input.value = input.value.replace(/[a-zA-Z]/g, '');
    }

    // Age -> auto-compute Birthdate (estimated birth year from age)
    (function () {
        var ageEl = document.querySelector('input[name="age"]');
        var bdEl  = document.querySelector('input[name="birthdate"]');
        var hint  = document.getElementById('birthHint');
        if (!ageEl || !bdEl) return;
        var MONTHS = ['January','February','March','April','May','June',
                      'July','August','September','October','November','December'];
        ageEl.addEventListener('input', function () {
            var age = parseInt(ageEl.value, 10);
            if (isNaN(age) || age < 1 || age > 120) { if (hint) hint.textContent = ''; return; }
            var now = new Date();
            var by  = now.getFullYear() - age;
            var mm  = String(now.getMonth() + 1).padStart(2, '0');
            var dd  = String(now.getDate()).padStart(2, '0');
            bdEl.value = by + '-' + mm + '-' + dd;
            if (hint) hint.textContent = 'Estimated: born around ' + MONTHS[now.getMonth()] + ' ' + by + '. I-adjust ang eksaktong araw kung kailangan.';
        });
    })();
</script>
@endsection