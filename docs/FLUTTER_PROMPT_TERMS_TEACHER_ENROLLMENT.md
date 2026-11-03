# Task for the Flutter app: Terms agreement, teacher self-activation, enrollment and chat updates

> Paste this whole file into the Claude Code session of the Flutter project (`dp_lms`).
> Backend: Laravel `dp-lms-web`, branch `feature/ui-refresh`. Everything below runs on the local server and is
> covered by automated tests (2026-11-03). Goal: the app follows the same steps and rules as the website.

---

## 0. Before writing code

1. Search the project for the login, activation, enrollment and chat screens and the API client. Extend them;
   do not duplicate.
2. Keep the existing token handling (`Authorization: Bearer <token>`, `Accept: application/json`).
3. Show server messages as they come (`message`, or the first item of `errors.<field>`); they are already worded
   for users.

---

## 1. Terms of Use and Privacy Policy (every role)

The website does not let anyone use the portal until they agree once. The app must do the same.

**Login and activation responses now include `user.terms_accepted`** (boolean), from `POST /api/login`,
`POST /api/activate/verify` (student) and `POST /api/teacher/activate/verify`:

```json
{ "token": "…", "user": { "id": 12, "name": "…", "role": "student", "status": "approved", "terms_accepted": false } }
```

| Call (with token) | Body | Answer |
|---|---|---|
| `GET /api/terms` | — | `200 { "accepted": false, "accepted_at": null, "terms_url": "https://…/terms", "privacy_url": "https://…/privacy" }` |
| `POST /api/terms/accept` | `{ "terms": true }` | `200 { "accepted": true, "accepted_at": "2026-11-03T19:20:11+08:00" }`; without `terms: true` → `422 errors.terms` |

**Screen "Before you continue"** — after login or activation whenever `terms_accepted` is `false`, and on app
start when `GET /api/terms` answers `accepted: false`:
- Text: "Please read and agree to the Terms of Use and Privacy Policy to use DP-LMS."
- Two links that open `terms_url` and `privacy_url` in the in-app browser.
- Checkbox "I agree to the Terms of Use and the Privacy Policy" and **Agree and continue** (disabled until ticked)
  → `POST /api/terms/accept` → the role's home screen.
- **Sign out** (calls `POST /api/logout`).
- No way back into the app from this screen without agreeing.

> The API does not refuse other calls yet, so current app builds keep working. Once this update is out, the plan
> is to make the server refuse every call until the user agrees — build the screen now.

---

## 2. Teacher self-activation (new)

Teachers no longer wait for an invite. The school admin loads the **Faculty Master List** (employee ID + name, and
the advisory section if any). A teacher activates the way a student does with the LRN.

Add **"Teacher, first time here? Activate with your employee ID"** on the login screen.

### Step 1 — details
`POST /api/teacher/activate` (no token; max 20 per minute)

```json
{
  "employee_id": "4512345",
  "name": "Maria Lopez Santos",
  "email": "maria.santos@deped.gov.ph",
  "password": "Teacher#2026",
  "password_confirmation": "Teacher#2026",
  "terms": true
}
```

- `employee_id`: letters, numbers and dashes, max 50 (uppercased by the server).
- `name`: as on the faculty list; word order does not matter ("Santos, Maria Lopez" also works).
- `password`: at least 8 characters with upper and lower case, a number and a symbol — show the same live
  checklist as the student activation screen.
- `terms`: the checkbox with the two links from section 1 — required.

`201 { "message": "A 6-digit code was sent to your email.", "activation_token": "…", "email_masked": "ma•••@deped.gov.ph" }`

Errors (`422`, show under the field):
- `errors.employee_id` "The employee ID and name do not match the faculty list…" — the same message for a wrong ID
  **and** a wrong name, on purpose. Do not try to tell them apart.
- `errors.employee_id` "An account already exists for this employee ID…" — offer **Sign in** and **Forgot password**.
- `errors.employee_id` "Too many attempts for this employee ID…" — 5 wrong tries per hour, 10 per day.
- `errors.email` already registered; `errors.terms` box not ticked.
- `429` — "Please wait a minute and try again."

### Step 2 — email code
`POST /api/teacher/activate/verify` `{ "activation_token": "…", "code": "123456" }`

`201`, same shape as `/api/login`:
`{ "message": "Account activated.", "token": "…", "user": { …, "role": "teacher", "employee_id": "4512345", "terms_accepted": true } }`
→ save the token, open the teacher home. If the faculty list named an advisory section that is still free, the
teacher is already its adviser.

Errors: `errors.code` — wrong code (5 tries), or expired after 10 minutes ("Please start again" → back to step 1).

### Resend
`POST /api/teacher/activate/resend` `{ "activation_token": "…" }` → `200 { "message": "A new code was sent." }`
(3 resends; max 5 calls per 10 minutes). Enable **Resend code** after a 60-second countdown.

Screens: copy the student activation screens (stepper "Your details → Verify email") with **Employee ID** in place
of the LRN, and the footer "Not on the faculty list? Ask the school admin."

---

## 3. Student activation — same calls

`POST /api/activate`, `/api/activate/verify` and `/api/activate/resend` are unchanged. The user comes back with
`terms_accepted: false` → show the section 1 screen once.

---

## 4. Enrollment

**`GET /api/student/dashboard` now has an `enrollment` block** — use it to decide what the enrollment screen shows:

```json
"enrollment": { "status": "returned", "remarks": "Please fix your birthdate.", "school_year": "2026-2027" }
```

| `status` | Show |
|---|---|
| `none` | The enrollment form. |
| `pending` | "Your form was sent. Your adviser will review it." No form. |
| `returned` | **"Your adviser returned your form"** with `remarks`, and the form again (pre-fill it with what the app saved when it was sent, so only the fix is needed). |
| `enrolled` | The section (from `section`) — no form. |

`POST /api/student/enroll` — same fields as before. Mirror the server rules:
- **Age comes from `birthdate`** on the server. Show it read-only next to the date and do not send it (an `age`
  from older builds is ignored, not refused).
- `birthdate` must make the student **10 to 25 years old**, otherwise `422 errors.birthdate`. Limit the date picker
  to that range.
- `grade_level` and `school_year` are set by the server — show them read-only.
- Already enrolled → `422 errors.student_type` "You are already enrolled for this school year."
- Nothing to change for this, but good to know: a student listed in a section on the school's LRN list goes
  straight to that section's adviser, so approval is faster.

---

## 5. Chat

Match the website:
- **Drafts:** keep what the user typed per conversation (key `draft:<myUserId>:<otherUserId>`) in local storage;
  restore it when the chat opens; clear it when the message is sent. In the chat list, show **"Draft:"** in red
  before the preview of chats with an unsent draft. **Delete all drafts on sign-out** (shared school devices).
- On tablets in landscape, give the open chat the full width (collapse side navigation while it is open).

---

## 6. Messages after an action

The website shows success and error messages as a short toast at the top that leaves after about 3 seconds. In the
app use a floating `SnackBar` (3 seconds, with a close action) for the same messages; keep field errors under the
fields.

---

## 7. Check before you finish

- [ ] Login with an account that has not agreed → "Before you continue" → agree → home; the next login skips it.
- [ ] Sign out from "Before you continue" works.
- [ ] Teacher activation: wrong name → the one generic message; right details → code e-mail → verify → teacher home
      with `terms_accepted: true`; activating the same ID again → "already exists".
- [ ] Resend countdown; wrong code message; expired code goes back to step 1.
- [ ] Enrollment: each `status` shows the right screen; age shown from the date; date picker limited to 10–25 years.
- [ ] Chat draft survives closing the app, is gone after sending, and every draft is gone after sign-out.
- [ ] `flutter analyze` clean, no new warnings.
