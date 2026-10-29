# FIX BUGS — DP-LMS

Log of every bug found and fixed while polishing the system (branch `feature/ui-refresh`).
Each entry has: **Problem** (what the user sees) → **Where** (file and method) → **Fix** (what changed) →
**Tested** (how it was proven to work).

| Mark | Meaning |
|---|---|
| ✅ | Fixed and tested |
| 🔧 | Planned — not fixed yet |

> Tested on localhost (XAMPP + `php artisan serve`) with `php artisan demo:local` data.
> No real email was sent while testing (`MAIL_MAILER=array`), and all test data was removed afterwards.

---

## Summary

| Batch | Scope | Commit | Bugs |
|---|---|---|---|
| [1](#batch-1--security) | Security (mobile API, OTP) | `0d662e5` | 2 |
| [2](#batch-2--login-and-activate) | Login, Activate, Parent activate/recover | `6099ff7` | 7 |
| [3](#batch-3--otp-and-forgot--reset-password) | OTP, Forgot / Reset password | `7dc6571` | 5 |
| [4](#batch-4--teacher-pages) | Teacher layout, Dashboard, Students, Announcements | `0c245c5` `34f31cc` `00cc6fd` | 14 |
| [5](#batch-5--student-pages-part-1) | Student layout, Dashboard, Modules, Quizzes, Grades | `e6e3dde` `c7857ea` | 16 |
| [6](#batch-6--student-pages-part-2) | Subjects, Schedule, Attendance, Messages, Enrollment form, Face, Parent link | `787a205` | 13 |
| [7](#batch-7--parent-portal) | Parent portal, account recovery, report card | `69e3561` | 10 |
| [—](#code-comments) | Code comments in English | `857b7c2` | — |
| [8](#batch-8--logout-and-the-login-form) | Logout, login form reset, back/forward cache | — | 3 |
| [9](#batch-9--iot-face-kiosk-and-gate) | IoT: face kiosk photo quality, photo privacy, gate camera | — | 🔧 13 |
| [10](#batch-10--teacher-pages-part-2) | Teacher: Materials, Quizzes, Gradebook, Messages, Enrollments, Attendance, Face kiosk, dashboard + sidebar, app QR, live chat | — | 15 |
| [11](#batch-11--admin-part-1-sign-in-page) | Admin sign-in page, layout script error, live dashboard | — | 4 |

---

## Batch 1 — Security

### B1-1 ✅ A rejected or banned account could still use the mobile app
- **Problem:** after the admin rejected a user, or they were banned (3 bad face uploads), the website signed
  them out but the mobile app kept working — grades, messages, etc. were still readable.
- **Where:** `routes/api.php` (protected group had only `auth:sanctum` + `role:`, no `approved`);
  `app/Http/Middleware/ApprovedMiddleware.php`.
- **Fix:** added `approved` to the protected API group. On `api/*` the middleware answers
  `403 { "message": "Your account is not active…" }` and deletes the token (on the web: logout + redirect, as before).
  The new 403 is documented in `docs/FLUTTER_HANDOFF.md`.
- **Tested:** approved → `200`; rejected → `403` and 0 tokens left; next request → `401`.
  Web: approved → `200`, rejected → logout + redirect to `/login`.

### B1-2 ✅ "500 Server Error" on login
- **Problem:** after 5 login codes within an hour, the whole login page crashed.
- **Where:** `app/Services/OtpService.php` → `send()`; `app/Http/Controllers/Auth/LoginController.php` → `submit()`.
- **Fix:** the limit now throws `ActionNotAllowed` (with the minutes left), caught by `LoginController`
  and `OtpController::resend()` → the OTP page with a clear message.
- **Tested:** limit used up → `302 → /otp` with "Too many codes… try again in 60 minutes."

---

## Batch 2 — Login and Activate

### B2-1 ✅ Back button: stuck "Please wait…" / blank login page
- **Problem:** going Back to login or activate left the button disabled; leaving login through
  "Activate your account" and coming back showed a blank login.
- **Where:** `resources/views/auth/login.blade.php` (script), `resources/views/layouts/auth-card.blade.php` (`data-once`).
- **Fix:** `pageshow` resets the button and panels when the page comes from the back/forward cache.

### B2-2 ✅ "Remember me / Forgot" missing without JavaScript
- **Where:** `login.blade.php` → `<noscript>` style. **Fix:** added `.login-row`; fixed the logo centering.

### B2-3 ✅ Broken background when served from an XAMPP subfolder
- **Where:** `auth-card.blade.php` → `/images/bg.jpg`. **Fix:** `asset()`; fixed layer (also works on iPhone).

### B2-4 ✅ LRN / code cut off when pasted with spaces
- **Problem:** "1234 5678 9012" was cut by `maxlength` before the spaces were removed.
- **Where:** `auth-card.blade.php`, `activate.blade.php`, `activate-verify.blade.php`. **Fix:** `data-digits` (digits only, paste included).

### B2-5 ✅ Login accessibility
- **Where:** `login.blade.php`. **Fix:** the Password label is tied to its field; show-password is reachable by
  keyboard (was `tabindex=-1`, 17px); contrast of error/hint/button; `aria-invalid`/`aria-describedby`;
  focus outline; reduced motion (GSAP and particles).

### B2-6 ✅ Login and activate pages did not match
- **Fix:** one green theme, font, navbar and card for activate, verify, parent activate/recover.

### B2-7 ✅ Phone: clipped "S" and a large gap above the login
- **Where:** `login.blade.php` (`.login-card { overflow }`, mobile layout). **Tested:** screenshot at 390px.

---

## Batch 3 — OTP and Forgot / Reset password

### B3-1 ✅ "Send Reset Link" while a code is sent
- **Where:** `resources/views/auth/forgot-password.blade.php`. **Fix:** "Send code" + the right explanation.

### B3-2 ✅ "Resend code" was not a real resend
- **Problem:** only a link back to the form; the email had to be typed again.
- **Where:** `forgot-verify.blade.php`. **Fix:** a real POST resend to the same email (still rate limited).

### B3-3 ✅ The reset hint said only "Minimum 8 characters"
- **Problem:** following the hint always failed (upper/lower case, a number and a symbol are also required).
- **Where:** `reset-password.blade.php`. **Fix:** live checklist of every rule (same as the server).

### B3-4 ✅ The OTP countdown always started at 5:00
- **Where:** `app/Http/Controllers/Auth/OtpController.php` → `show()`; `otp.blade.php`.
- **Fix:** counts down from the code's real `expires_at`.

### B3-5 ✅ OTP page used Tailwind classes missing from the old CSS build
- **Fix:** all four pages use `layouts.auth-card` (inheriting every Batch 2 fix).
- **Tested (Batch 3):** the whole forgot flow (email → resend → wrong/right code → weak/good password → login)
  and the OTP flow (wrong/right code → dashboard).

---

## Batch 4 — Teacher pages

### B4-1 ✅ Stuck "loading" when the session expired
- **Problem:** a sidebar click showed the skeleton forever.
- **Where:** `resources/views/layouts/teacher.blade.php` → sidebar loader (`loadPage`).
- **Fix:** a full page load whenever the answer is not a teacher page (redirect / 401 / JSON) → ends on login.

### B4-2 ✅ Page JavaScript dead after Back/Forward
- **Problem:** e.g. the student modal would not open after going Back.
- **Fix:** one loader for clicks and Back/Forward that runs the page's scripts.

### B4-3 ✅ Error on the second visit to Materials
- **Problem:** `SyntaxError: Identifier has already been declared` → the upload button stopped working.
- **Fix:** each inline page script runs in its own block `{ … }`.

### B4-4 ✅ Other teacher layout issues
- Tab title never changed; Ctrl+click (new tab) did not work; absolute URLs (`/teacher/…`) → `route()`/`url()`;
  faint sidebar text; no focus outline; the page stayed hidden until every image loaded; "Log out" centered;
  the name in the top bar was cut off on phones.

### B4-5 ✅ Teacher announcements reached the WHOLE school
- **Problem:** the form had no section selector, and an announcement without a section counted as school-wide.
- **Where:** `app/Models/Announcement.php` → `scopeVisibleToSections()`; `Teacher/AnnouncementController.php`;
  `resources/views/teacher/announcements.blade.php`; `Api/Teacher/DashboardController.php` → `storeAnnouncement()`.
- **Fix:** a "Send to" field; "All my sections" only reaches the posting teacher's sections (web and API,
  students and parents).
- **Tested:** demo section → 1, another section → 0, no section → 0, parent → 1.

### B4-6 ✅ No error messages on the announcement form · B4-7 ✅ No push to parents
- **Where:** `announcements.blade.php`; `app/Services/ParentNotifier.php` → `announcement()` (new).

### B4-8 ✅ Sidebar icons broken on the teacher dashboard
- **Where:** `teacher/dashboard.blade.php` → `.nav-icon` (same class as the layout). **Fix:** `td-` prefix.

### B4-9 ✅ Wrong "My Subjects" / "My Sections" on the teacher dashboard
- **Where:** `Teacher/TeacherDashboardController.php`. **Fix:** one entry per class (subject + section); advisory section included.

### B4-10 ✅ Teacher's student modal always said "No grades recorded yet"
- **Where:** `Teacher/TeacherStudentController.php`; `teacher/students/index.blade.php`.
- **Fix:** current SY; adviser = every subject, subject teacher = own subject only; correct average.

### B4-11 ✅ Silently broken layout (6 Tailwind classes missing from the build)
- `gap-5`, `p-4`, `min-w-0`, `sm:col-span-2`, `py-14`, `w-14` → plain CSS on the three teacher pages.

### B4-12 ✅ Others: LRN search, escaped HTML, N+1 query, phone layout
- **Tested (Batch 4):** headless Edge 18/18 (SPA, Back/Forward, Materials ×2, expired session), no JS errors.

---

## Batch 5 — Student pages (part 1)

### B5-1 ✅ Clicking a notification never marked it read
- **Problem:** the red badge never cleared — the browser cancelled the request because the page left at once.
- **Where:** `resources/views/layouts/app.blade.php` → notifications script. **Fix:** `fetch(..., { keepalive: true })`.
- **Tested:** click a notification → `unread=0`.

### B5-2 ✅ Absolute URLs and an error every 30 seconds in the student/parent layout
- **Fix:** `route()`/`url()`; base URL on notification links; `.catch()` on polling.

### B5-3 ✅ "Assignments" always 0 on the student dashboard
- **Where:** `Student/StudentDashboardController.php` → `index()`. **Fix:** open quizzes not yet submitted.

### B5-4 ✅ Wrong advice for students not enrolled yet
- **Fix:** form / pending / rejected (with the reason) — each with the right button.

### B5-5 ✅ Could not enroll for the next school year
- **Where:** `StudentDashboardController.php` → `enrollmentForm()`. **Fix:** only the current SY is checked.

### B5-6 ✅ Stale data / pages visible after logout
- **Problem:** `Cache-Control: private, max-age=30` on every student page. **Fix:** removed.

### B5-7 ✅ Material views from the web were not counted
- **Where:** new route `student.modules.open` → `StudentDashboardController::openMaterial()`.
- **Fix:** counts the view and checks the material belongs to your class (`404` otherwise).

### B5-8 ✅ Broken quiz file link when `APP_URL` is wrong
- **Where:** `student/quiz-show.blade.php` (`Storage::url()` → `asset()`).

### B5-9 ✅ Quizzes: double submit, no "Missed", no error for typed answers
- **Where:** `Student/AssignmentController.php` → `index()`; `quizzes.blade.php`; `quiz-show.blade.php`.

### B5-10 ✅ No averages on My Grades
- **Where:** `StudentDashboardController.php` → `grades()`; `student/grades.blade.php`.
- **Fix:** Q1–Q4, average per subject, general average ("so far" while quarters are missing).

### B5-11 ✅ Others: contrast, focus, reduced motion, phone layout, N+1 query
- **Tested (Batch 5):** in-process 5/5; headless Edge 19/19 (all 12 student pages), no JS errors.

### Local setup (not code)
- **Problem:** every upload returned `403` on localhost. **Cause:** no `public/storage` link.
- **Fix:** `php artisan storage:link` (Railway: listed in `docs/DEPLOY_CHECKLIST.md`).

---

## Batch 6 — Student pages (part 2)

### B6-1 ✅ "Unread messages" never went down
- **Problem:** the website never marked a teacher's messages as read (only the mobile app did),
  so the dashboard count stayed forever.
- **Where:** `app/Http/Controllers/Student/StudentDashboardController.php` → `messages()`.
- **Fix:** opening the conversation with a teacher marks **only that teacher's** messages as read
  (`is_read = true`, `read_at = now()`).
- **Tested:** dashboard "2" unread → open Messages → dashboard "0"; DB `unread=0`.

### B6-2 ✅ Every teacher mixed into one "Conversation"
- **Problem:** one list for all teachers, newest first, labelled only "You" — no way to tell who a message went to.
- **Where:** `StudentDashboardController.php` → `messages()`, `storeMessage()`; `resources/views/student/messages.blade.php`.
- **Fix:**
  - Left: list of teachers = the section's teachers + past conversation partners, with the last message and
    unread count, sorted by latest activity. Right: the conversation with the selected teacher (`?with=<id>`),
    oldest first, with "Today / Yesterday / date", "seen" and "edited".
  - A reply goes to the selected teacher; after sending, the **same** conversation opens again.
  - A teacher who no longer handles the section can still be read but not answered (same rule as `storeMessage()`).
  - Enter = send, Shift+Enter = new line; double-submit guard. On phones the teacher list becomes chips.
- **Tested:** one contact + active conversation; oldest first; Enter → same conversation, my message last;
  DB `sent_by_student=1`; no sideways scroll on a phone (390px).

### B6-3 ✅ Messages with an attachment were empty
- **Problem:** a file sent by the teacher from the mobile app showed no link.
- **Where:** `messages.blade.php`. **Fix:** file link (📎 file name) + "edited" when edited.
- **Tested:** the link points to `/storage/chat_files/…pdf`.

### B6-4 ✅ Refreshing the Parent page created a new code
- **Problem:** the `POST` that creates the code returned a page. Refresh / "Confirm resubmission" →
  a new code that **cancelled the previous one**, which the parent may already have scanned.
- **Where:** `app/Http/Controllers/Student/ParentLinkController.php` → `store()`, `show()`;
  `resources/views/student/parent-link.blade.php`.
- **Fix:** POST → redirect → GET. The code travels once in the session (`session('parent_link')`) and the QR is
  rebuilt from the URL. A note says it is shown only once; the button shows "Creating…" and is disabled
  against double clicks.
- **Tested:** click → GET page with the code; reload → no code and nothing new created (DB `parent_codes=1`).

### B6-5 ✅ Enrollment form: full width / cramped on phones
- **Problem:** `max-w-2xl` and `mb-5` were missing from the build; Age/Birthdate/Gender stayed 3 columns on phones.
- **Where:** `resources/views/student/enrollment-form.blade.php`.
- **Fix:** plain CSS (`.ef-*`), max 720px wide; 1 column at ≤640px.
- **Tested:** form width ≤ 721px on a 1366px screen.

### B6-6 ✅ Enrollment form: "Student Type" unusable by keyboard
- **Problem:** the three choices were `<div onclick>` — unreachable by Tab, unreadable by screen readers.
- **Fix:** real radio buttons (`fieldset`) with the same look; the transfer fields appear for "Transferee".
- **Tested:** arrow keys → "Transferee" selected and transfer fields shown; submitted as `type=transfer`.

### B6-7 ✅ Enrollment form: other issues
- **Problem:** labels without `for`; 10px faint error text; Tagalog hints in an English UI; no
  double-submit guard; a dead `@section('sidebar')`.
- **Fix:** every input has an `id` + `label for`; 12.5px errors; English hints; no digits in names;
  Age filled from Birthdate (still editable); double-submit guard; dead section removed.
  Field names are **unchanged** — the controller's validation is the same.
- **Tested:** every field has a label; Age correct for 2012-03-15; submit → dashboard "Enrollment form
  submitted", DB `enrollment=pending`.

### B6-8 ✅ Face self-upload page broken when `FACE_SELF_UPLOAD` is on
- **Problem:** 22 Tailwind classes missing from the build → white-on-white Start button, no guide circle,
  no progress bar, alerts without padding.
- **Where:** `resources/views/student/face-register.blade.php` (off by default — the kiosk is used instead).
- **Fix:** plain CSS (`.fr-*`); `ring()` switches `ring-idle/ok/warn` classes. Camera logic untouched.
- **Tested (in-process, flag on):** coloured Start button and circle; no Tailwind classes.

### B6-9 ✅ Face self-upload: "Network error" on a validation error
- **Problem:** the upload sent no `Accept: application/json`, so it received an HTML redirect and
  `res.json()` failed.
- **Where:** `face-register.blade.php` → `upload()`. **Fix:** `Accept: application/json` + `res.json().catch()`,
  so the server's message is shown.
- **Tested:** missing images → `422` JSON (`message` + `errors.images`).

### B6-10 ✅ "Room Room 5" on the schedule
- **Problem:** "Room " was always prepended, but the room is free text (e.g. "ALS Room 7").
- **Where:** `resources/views/student/schedule.blade.php`; `StudentDashboardController.php` → `schedule()`.
- **Fix:** "Room " only for a bare number; adviser eager-loaded (one query).
- **Tested:** shows "Room 5" and "ALS Room 7"; never "Room Room".

### B6-11 ✅ My Subjects: rainbow covers and not clickable
- **Where:** `resources/views/student/subjects.blade.php`.
- **Fix:** one accent color (design rule); each card links to that subject's modules (`#subj-<id>`),
  sorted by name; readable text; no inline `onmouseover`; the empty state has a button.
- **Tested:** clicking a card → `/student/modules#subj-…` and the subject heading is there.

### B6-12 ✅ Attendance: stuck "Checking…" after Back
- **Where:** `resources/views/student/attendance.blade.php` → script.
- **Fix:** `pageshow` restores the button and refreshes the timer; correct padding; clearer empty text.

### B6-13 ✅ Face status: double padding and hard-coded colors
- **Where:** `resources/views/student/face-status.blade.php`. **Fix:** layout tokens, no extra padding.

### Caught while testing (before commit)
- Messages: the template's indentation showed inside the bubble → `pre-wrap` only on the text (`.ms-text`).
- Messages: the header avatar shrank (`.ms-head span` was too broad) → its own `.ms-head-role` class.
- Messages: the last-message preview did not hide on phone chips (inline `display:block`) → moved to CSS.

**Tested (Batch 6):** headless Edge 22/22, no JS errors; in-process FaceUpload 2/2; DB state correct;
all test data removed afterwards.

---

## Batch 7 — Parent portal

### B7-1 ✅ The parent saw a different (lower) average than the student
- **Problem:** the parent dashboard, Child's Records and `GET /api/parent/*` averaged every quarter grade the
  child ever had — all school years mixed, each quarter counted like a subject. With this year's grades
  (Math 77.75, Science 78.25) and one old 60 from last year, the parent saw **74.4 — below passing**, while
  the student's Grades page correctly showed **78.00**.
- **Where:** `ParentPortal/ParentDashboardController.php`; `parent/dashboard.blade.php`, `parent/child-records.blade.php`;
  `Api/ParentPortal/DashboardController.php` → `formatChild()`; `Student/StudentDashboardController.php` → `grades()`.
- **Fix:** one report-card calculation, `GradeService::reportCard()` (per school year → average per subject →
  general average), used by the student Grades page, both parent pages and the parent API. The API keeps
  the same keys; `general_average`, `passing_count` and `failing_count` now cover the current school year.
- **Tested:** dashboard and API show 78.00 / 78, same as the student page. API JSON compared before/after:
  only `general_average` (74.4 → 78) and `failing_count` (3 → 2) changed.

### B7-2 ✅ Account recovery and password reset left old logins signed in
- **Problem:** (CLAUDE.md §13 #12) recovering a parent account on the website kept the phone app logged in;
  recovering in the app kept "remembered" browsers (no OTP). Neither ended other open browser sessions,
  and neither did Forgot password — someone who knew the old password stayed in.
- **Where:** `Services/ParentLinkService.php` → `recover()`; `Services/PasswordResetService.php` → `reset()`;
  `Auth/ParentActivationController.php`, `Api/ParentLinkController.php` → `recover()`.
- **Fix:** new `Services/SessionRevoker.php` — deletes app tokens, trusted devices and database sessions —
  called inside both services, so web and API do exactly the same thing. The device that recovered is
  signed in again afterwards.
- **Tested:** live with two "devices": after device B recovered, device A was sent to login, the old app token
  got `401`, and only B's session and trusted device remained. In-process: web recovery, API recovery
  (exactly one new token) and password reset all end with 0 old tokens, sessions and devices.

### B7-3 ✅ Child's Records never showed a pending enrollment
- **Problem:** the badge read `studentEnrollment`, which only returns *enrolled* rows, so "Pending" could never
  appear; a submitted or returned form showed as "Not enrolled".
- **Where:** `ParentPortalService::enrollmentState()`; both parent views.
- **Fix:** also loads the latest enrollment request: Enrolled · SY / Form under review / Form returned
  (with the school's reason) / Not enrolled for this SY.
- **Tested:** each state rendered (in-process, rolled back).

### B7-4 ✅ "Recent grades" were not recent
- **Problem:** the first 8 grade rows in database order, from any school year.
- **Where:** `parent/dashboard.blade.php`. **Fix:** this school year's subjects, sorted by name, with their
  average and which quarters are recorded.

### B7-5 ✅ School arrivals (face kiosk) missing on the website
- **Problem:** gate arrivals were only available in the app (`/api/parent/attendance`).
- **Where:** `ParentDashboardController::childRecords()`, `parent/child-records.blade.php`.
- **Fix:** "School arrivals · last 30 days" (on time / late), using the same query as the API.

### B7-6 ✅ Web and app built the parent data separately
- **Problem:** the web and API controllers repeated the children, announcements and attendance queries and had
  already drifted apart (the app showed arrivals, the web did not).
- **Fix:** `Services/ParentPortalService.php`, used by both controllers, always scoped to the signed-in
  parent's children. API response shape unchanged (JSON compared before/after).

### B7-7 ✅ Child's Records: missing styles and cramped on phones
- **Problem:** 5 Tailwind classes missing from the build (`mb-5`, `min-w-0`, `space-y-4`, `p-10`,
  `hover:text-amber-800`); amber theme and inline styles; the 6-column table was cramped on a phone.
- **Where:** `parent/child-records.blade.php`.
- **Fix:** plain CSS with layout tokens; grades per school year (Q1–Q4, average, remarks) with a keyboard-friendly
  "Score breakdown"; tables scroll inside their card.

### B7-8 ✅ Parent dashboard: off-palette and low contrast
- **Problem:** its own brown/serif palette; faint text (`#b3a896` on white ≈ 2.3:1, needs 4.5:1); a dead
  `@section('sidebar')`; passing mark hard-coded as 75; announcements lost their line breaks.
- **Where:** `parent/dashboard.blade.php`. **Fix:** layout tokens, `Grade::PASSING`, `white-space: pre-line`.
- **Tested:** lowest text contrast measured in the browser: 4.76:1 on both pages.

### B7-9 ✅ Grade weight labels typed separately from the formula
- **Problem:** "Written (25%) / Perf. (50%) / Quarterly (25%)" was hard-coded in the view; a formula change
  (CLAUDE.md §13 #13) would leave the labels wrong.
- **Where:** `Models/Grade.php`, `parent/child-records.blade.php`. **Fix:** `Grade::WEIGHTS`, used by
  `computeFinal()` and by the labels. Results identical.

### B7-10 ✅ A past school year showed as "In progress"
- **Problem:** found while testing — a finished school year with missing quarters showed "In progress" and
  "General average (so far)" on the student Grades page and in Child's Records.
- **Where:** `GradeService::reportCard()` (`current` flag); `student/grades.blade.php`; `parent/child-records.blade.php`.
- **Fix:** past years say "Incomplete" and "General average".

**Tested (Batch 7):** in-process 7/7 (40 assertions, every test rolled back); headless Edge 17/17 on desktop
and phone, no JS errors; API before/after comparison; live two-device recovery test; student pages 5/5 still pass.

---

## Code comments

- **Problem:** about 450 code comments in 119 files were in Tagalog/Taglish, and several held bug ids
  (`B6-4`) or "Dati: …" history that belongs in the commit log, not in the code.
- **Fix:** every comment rewritten in plain English, explaining why the code is the way it is; history and
  bug ids removed; leftover copy-paste notes ("PALITAN NG:", "DAGDAG:") deleted.
- **Tested:** for every changed file the code with comments stripped is byte-identical to before
  (`php_strip_whitespace()` for PHP; comment-stripped compare for Blade/JS). All PHP lints; every inline
  script on all 24 teacher/student/parent pages parses; teacher SPA 18/18 and parent pages 17/17 in the browser.

---

## Batch 8 — Logout and the login form

### B8-1 ✅ Back after logout showed the typed LRN and password (in plain text)
- **Problem:** sign in (password shown with the eye icon, Remember me ticked) → logout → Back ×3: the login page
  came back with the LRN, the **password as plain text** and Remember me still ticked. On a shared school
  computer the next person could read them.
- **Where:** browser back/forward cache and form restoration — no page told the browser not to keep it.
- **Fix:**
  - `app/Http/Middleware/NoStorePages.php` (new, first in the `web` group): every HTML response, redirects
    included, is `Cache-Control: no-store, private`, so Back asks the server again. JSON and file downloads keep
    their own headers. It is *prepended*: Laravel sorts `auth` ahead of appended middleware, and the
    guest redirect skipped it.
  - `auth/login.blade.php` → `resetLoginForm()`: on Back/Forward (`pageshow` persisted or `back_forward`) and right
    after logout the form is reset — identifier back to what the server rendered (empty after logout), password
    cleared and hidden again, Remember me off, status text and button reset. A failed sign-in still keeps the
    typed LRN/email (never the password).
  - Same reset on `admin/login.blade.php`, and `layouts/auth-card.blade.php` clears and hides any password field
    (activate, reset password, parent activate/recover) on Back/Forward.

### B8-2 ✅ Back after logout showed the signed-in dashboard
- **Problem:** Back ×1 after logout showed the student's dashboard again (and Forward too) from the browser cache.
- **Fix:** `no-store` (above) + `layouts/app`, `layouts/teacher`, `layouts/admin`: a page restored from the
  back/forward cache reloads itself, so the server decides — a signed-out visitor gets the login page.

### B8-3 ✅ Logout did not reset the login page explicitly
- **Where:** `Auth/LoginController.php` → `logout()`; `Admin/AdminAuthController.php` → `logout()`.
- **Fix:** after `logout()` + `invalidate()` + `regenerateToken()` (unchanged), the redirect flashes `signed_out`
  into the new session; the login page shows "You have been signed out." and resets its form. One-time — the
  next visit is a normal login page.

**Tested (Batch 8):** headless Edge with a real sign-in (typed LRN/email → OTP) for a student and a teacher, then
logout and Back/Forward ×3: never a signed-in page, form always empty with the password hidden (36/36);
the client layer alone with a simulated cache restore (5/5); in-process 5/5 (flash is one-time, `no-store` on
pages and the guest redirect, JSON and CSV downloads untouched, failed sign-in keeps the LRN, admin logout).
Regression: parent 7/7, student pages 5/5, teacher SPA, all inline scripts on 24 pages parse, no JS errors.

---

## Batch 9 — IoT: face kiosk and gate

Found during the first real kiosk registration on the Raspberry Pi 5 (OV5647 fisheye, no IR filter). The 10 saved
photos were measured: face brightness ~60/255, colour B≈R≈80 / G≈48 (magenta), sharpness 15–34 except one at 84
(Laplacian variance of the face resized to 160×160).

### B9-1 ✅ Saved face photos contain the green guide box
- **Problem:** the box drawn for the on-screen guide is drawn on the frame that gets cropped and uploaded, so every
  stored photo (and later the gate model) has a green rectangle around the face.
- **Where:** `docs/raspberry-pi/face_kiosk.py` → `capture_faces()`.
- **Fix:** the guide is drawn only on the (mirrored) preview copy; photos are cropped from the untouched frame.

### B9-2 ✅ A non-face was saved as the student's face
- **Problem:** `img_01` is a piece of the room — a one-frame false detection was accepted.
- **Fix:** `find_face()` — a frame counts only when exactly one face is ≥ 20% of the frame height, not too close,
  near the centre (fisheye edges stretch faces) and in the same place for 3 frames in a row; live hints say what to change.

### B9-3 ✅ Blurry and dark photos are accepted
- **Problem:** no sharpness or brightness check; in dim light the auto-exposure uses long shutter times → motion blur.
- **Fix:** every candidate is scored (`face_quality()`: sharpness + brightness); frames below `SHARP_MIN` / `BRIGHT_MIN`
  are never used, the sharpest `PHOTOS` are kept (capture ends early once enough reach `SHARP_GOOD`), and a failed
  capture says why ("More light on your face" / "Hold still"). Frames below 60% of the session's sharpest one are
  dropped too (`SHARP_RELATIVE` — the head moved). Camera: exposure capped at 1/30 s, exposure target +0.5.
  New `focus_check.py` shows the live sharpness while the lens is turned: the real lens went from 15–34 to **143**.

### B9-4 ✅ Photos are violet
- **Problem:** the OV5647 module has no IR-cut filter, so infrared shows up as red + blue.
- **Fix:** gray-world white balance (gains from the whole frame, applied at 85%) + gamma brightening on the saved
  crop only (`COLOR_FIX`). Measured on a real photo: BGR 79/49/81 → 115/121/115.

### B9-5 ✅ Kiosk window does not fit the 5-inch screen
- **Problem:** a 1280×720 window on an 800×480 display; not mirrored, so moving left goes right.
- **Fix:** full-screen (`FULLSCREEN`), mirrored preview with large text and the live hints.

**Tested (B9-1…B9-5):** the real `face_kiosk.py` against the real local server with a simulated camera (a real one-time
QR issued by the server, then frames made from a Pi photo): good light → registered, QR used, photos replaced, no
green-box pixels, neutral colour; dark → refused with "More light on your face", QR still unused; blurry → refused with
"Hold still...", QR still unused. Thresholds come from the 10 photos of the first real registration.
Shaky (sharp and moving frames alternating) → only the sharp frames are stored. On the Pi: after focusing the lens
and putting the light in front of the face, the real registration stored 10 face photos with no box, neutral colour and
the face clearly visible (one motion-blurred frame among them led to `SHARP_RELATIVE`).

### B9-6 ✅ `haarcascade_frontalface_default.xml not found` on the new Pi OS
- **Problem:** Raspberry Pi OS (Debian 13) ships the OpenCV face detector files in `opencv-data`, not `python3-opencv`.
- **Fix:** install line and error message in `face_kiosk.py`, `RASPBERRY_PI_FACE_KIOSK.md`, `IOT_CHECKLIST.md`.

### B9-7 ✅ Face photos can be downloaded by anyone
- **Problem:** stored under `storage/app/public/faces/{user id}/img_01.jpg` — public and guessable. Anyone with the
  link (no login) can download every student's face, which is biometric data under the Data Privacy Act.
- **Fix (plan):** a private `faces` disk (`storage/app/faces`, never served by the web server) behind one helper
  (`Support/FacePhotos`). The admin review page shows them through an admin-only route; the Pi downloads them
  through `GET /api/faces/{user}/{file}` with the device key. `php artisan faces:make-private` moves the existing
  photos once. All responses `Cache-Control: no-store`.
- **Done:** as planned. The route only accepts `img_NN.jpg/png` names, so no other file can be asked for.
  `storage/app/faces` is covered by `storage/app/.gitignore`. Tested (PHPUnit, rolled back): the old public file
  is gone and the private one exists; the device gets `401` without or with a wrong key, `200 image/jpeg` +
  `no-store` with it, `404` for a path-traversal name, a missing photo, or a face set that is not approved;
  `/api/faces/approved` lists the new URLs; the admin photo route sends a guest and a student session to login
  and serves the admin; the review page shows the new URLs and none under `/storage/faces`.

### B9-11 ✅ Camera mounted upside down
- **Fix:** `ROTATE_180` in `config.py` → the sensor flips the image (`libcamera.Transform`), so detection, preview
  and saved photos are upright with no extra CPU work.

### B9-12 🔧 Liveness — a photo or a phone screen must not register or check in a student
- **Plan:** kiosk = active challenge in random order (turn left / turn right, blink) using face landmarks
  (OpenCV YuNet, runs on the Pi); gate = passive checks so the line stays fast (anti-spoof model + natural movement),
  with the challenge as a fallback when the passive check is unsure.

### B9-13 🔧 Appearance rules at registration
- **Plan:** refuse a covered face (cap/hat shadow over the forehead, sunglasses, mask) — the gate needs the face as it
  looks every day. Crop the stored photo to the head only, so a shirtless student's body is never stored.
- **Decision needed:** clear eyeglasses — allow (recommended: students who wear them daily are recognized better) or
  require removal at registration.

### B9-8 ✅ Gate recognizer (`gate_camera.py`) with an offline queue and a recognition log
- **Problem:** faces can be registered but nothing recognizes them at the gate, so Objective 1.1 (automated
  attendance through facial recognition) has no working path, and the study has no testing log for accuracy.
- **Fix (plan):**
  - **Pi:** `gate_camera.py` reuses the kiosk's camera code. OpenCV YuNet finds the face, SFace turns it into an
    embedding, cosine similarity against every approved student's photos (downloaded with the device key and
    cached). A student is checked in only when the same match repeats on consecutive frames above the threshold.
    The screen greets them by name with on-time/late. If the server is unreachable, scans go to a local queue
    (with the real scan time) and are sent when it is back.
  - **Server:** `POST /api/attendance` takes the optional `score`, `device` and `scanned_at` (a queued scan keeps
    its real time and on-time/late; at most 2 days old, never in the future). Every recognition and every
    unknown face (`POST /api/gate/unknown`, no image kept) goes into a new `gate_scans` log. Admin Reports gets a
    CSV export of that log for the study's testing data.
- **Done:** as planned, plus: without internet at start the gate uses the faces it saved last time
  (`face_cache/`, owner-only), new scans queue behind older ones so the order stays right, and the gallery is
  rebuilt with its own model instances (sharing OpenCV's DNN models with the camera thread crashed it — found in
  the simulator).
- **Tested:**
  - PHPUnit (8 tests, 56 assertions): recognized + repeat logged with score/device/attendance; a queued scan
    from yesterday 7:45 is saved at 7:45 as late and does not block today's scan; `scanned_at` 3 days old, 1 h
    ahead or unparsable and `score` 1.5 → `422` with nothing saved; unknown faces need the key and are logged
    without a student; the CSV export is admin-only, shows both rows, and a section filter drops unknown faces.
  - The real `gate_camera.py` with a fake camera against the local server: scores against the demo student's
    kiosk photos 0.74–0.87 for the same person, 0.17–0.18 for another person (threshold 0.40). Recognized →
    logged; other person → "Not recognized" logged. Server down: started from the saved faces, recognized
    (0.842), both scans queued; server back: sent in order with their real times (seen 09:28:39, received
    09:29:04).
  - Regression: Pi/Flutter contract 5/5, face upload 2/2.
### B9-9 🔧 Registration / gate mode switch and auto-start on power-on
### B9-10 🔧 Faster gate response (cache the FCM token, push after the reply)

---

## Batch 10 — Teacher pages (part 2)

Audited with full-page screenshots of every remaining teacher page at 1366 px and 390 px (demo data: a material,
two quizzes, grades, messages, two attendance sessions). No JS errors and no sideways scroll anywhere — the problems
are layout, states and consistency.

### B10-1 ✅ Materials: Unpublish and Delete unreachable on phones
- **Problem:** at 390 px the row's action buttons run off the card — "Unpublish" is cut and "Delete" invisible.
  Buttons are ~26 px tall (needs 44 px); `min-w-0 truncate px-2.5 py-16 w-14` are missing from the CSS build.
- **Where:** `resources/views/teacher/materials.blade.php`.
- **Fix (plan):** row = title/meta on top, actions in their own row on phones; 44 px buttons; plain CSS.

### B10-2 ✅ Material details: the three counters stack full width
- **Problem:** `grid-cols-3` is missing from the build, so Viewed / Hearts / Comments become three tall full-width cards.
- **Where:** `teacher/materials-show.blade.php`. **Fix (plan):** plain CSS row of three; sentence-case labels.

### B10-3 ✅ Quizzes: a closed quiz looks open
- **Problem:** a quiz past its deadline shows only "Published" — on the list and on its page.
- **Where:** `teacher/assignments.blade.php`, `teacher/assignment-show.blade.php`.
- **Fix (plan):** "Open until …" / "Closed …" state next to Published/Draft.

### B10-4 ✅ Create Quiz looks like a different app from Upload Material
- **Problem:** other width, back link, buttons; the browser's raw "Choose File" input.
- **Where:** `teacher/assignment-create.blade.php`. **Fix (plan):** same form shell and file picker as Upload Material.

### B10-5 ✅ Gradebook: scores and Edit hidden on phones
- **Problem:** at 390 px only Student and Subject are visible; quarter, scores, final grade and Edit are off-screen.
  Weight labels "(25%) (50%) (25%)" are typed in the view.
- **Where:** `teacher/gradebook.blade.php`, `Teacher/GradebookController`.
- **Fix (plan):** each grade becomes a compact row (student, subject · quarter, final + status, Edit) on phones;
  labels from `Grade::WEIGHTS`.

### B10-6 ✅ Messages: a file-only last message previews as "You:"
- **Where:** `teacher/messages.blade.php`. **Fix (plan):** show the file name; drop the "COMMUNICATION" eyebrow.

### B10-7 ✅ Attendance session: small mark buttons, odd placeholder
- **Problem:** P / L / A buttons ~32 px; a large "—" where the timer was once closed.
- **Where:** `teacher/attendance/show.blade.php`. **Fix (plan):** 44 px buttons with full labels for screen readers;
  closed state without the placeholder.

### B10-8 ✅ Enrollment request: avatar without its circle
- **Where:** `teacher/enrollments/show.blade.php`; empty list keeps a table header (`enrollments/index`).

### B10-9 ✅ Every page has its own width, header and buttons
- **Problem:** max-widths from 720 to 1100 px, some centred and some not; three back-link styles; "Create Quiz"
  and "Upload Material" are different buttons.
- **Fix (plan):** one page shell in `layouts/teacher.blade.php` (`.tp-page`, `.tp-head`, `.tp-back`, `.tp-btn`) used by
  every teacher page: 1040 px for lists, 760 px for forms.

### B10-10 ✅ Generated-looking details
- **Problem:** ALL-CAPS eyebrows ("COMMUNICATION", "NEW UPLOAD", "CLASS CODE — SHOW THIS TO THE CLASS", section
  labels), "→" / "←" in link text, " • " meta strings. Also in the student/parent pages from batches 5–7.
- **Fix (plan):** sentence-case headings, icons instead of arrow characters, meta as separate items.

### B10-11 ✅ Material and quiz cards: low-contrast meta text (#94A3B8-like on white)
### B10-12 ✅ Touch targets under 44 px on list actions (materials, quizzes, gradebook Edit)

### B10-13 ✅ Dashboard and sidebar look generated; pages appear late
- **Problem:** the whole page stayed invisible (`opacity: 0`) until GSAP, loaded from a CDN, finished an
  entrance animation; the menu was built by JavaScript at the end of the page, so it could appear after the
  content. Sidebar: navy with an icon box per item, a glowing blue active pill, all-caps "MAIN MENU", items
  sliding on hover. Top left: 30 px logo and 12 px school name squeezed against the toggle; on phones a dark
  block inside the white top bar. Dashboard: three identical stat cards plus four identical shortcut cards.
- **Where:** `layouts/teacher.blade.php`, `teacher/dashboard.blade.php`.
- **Fix:** UI only (no route, controller or state logic touched). Content shows on the first paint (no
  `opacity: 0`, no GSAP on teacher pages); the menu is rendered by Blade with the active item already marked
  (`aria-current`), the script only adds in-page navigation. White sidebar, slate text (10:1), active item =
  light tint + blue text + a thin bar, no shadows or glow, colour-only hover. Brand: 38 px logo aligned with the
  menu icons, 15 px name + subtitle; the toggle is the first control of the top bar (left of the school on
  phones). Dashboard: one summary strip, classes on the left, shortcuts as a list and students on the right.
  Also: hidden table labels (`.sr-only`) could widen the page on phones — tables are now their containing block.
- **Tested:** screenshots of every teacher page at 1366 and 390 px (normal, collapsed sidebar, phone menu open):
  no JS errors, no sideways scroll (Attendance had 478 px before the `.sr-only` fix). SPA test 18/18 (it now
  waits for the page to finish loading, because the content no longer hides behind a fade); all 12 teacher
  pages fetched and every inline script parses.

### B10-15 ✅ Messages: every chat click reloads the page, no live updates, the page scrolls
- **Problem:** opening a chat or sending a message reloaded the whole page; new messages from a student only
  showed after a manual reload; the chat was taller than the screen, so the page itself scrolled; faint lines.
- **Where:** `teacher/messages.blade.php` (view only — controller, routes, access rules unchanged).
- **Fix:** the page loads the same server-rendered page in the background and swaps in only the chat and the
  list (same access check and mark-as-read as a full load). Sending posts the same form (CSRF, bad-word filter,
  validation stay on the server); errors show above the box and the text is kept. Every 5 s (tab visible only)
  new messages and list previews are refreshed; the view sticks to the bottom only if it already was there; the
  timer stops when the teacher leaves the page. Layout fits the screen (only the list and the messages scroll),
  stronger dividers, search by name, day separators (Today / Yesterday / date), 44 px send button.
- **Tested (real browser, 1366 and 390 px, 22 checks):** no page scroll; chat opens with no reload (a page
  marker survives); scrolled to the newest message; Enter sends, bubble shows, box clears, list preview updates;
  a bad word shows the server's error and keeps the text; a message inserted for the student appears live
  without a reload; phone back button shows the list; search hides other chats; leaving through the sidebar
  leaves no errors from the old timer. Test messages deleted afterwards.

### B10-14 ✅ Teacher app cannot show the face-kiosk QR
- **Problem:** the adviser's one-time QR existed only on the website; the app had no endpoint for it.
- **Fix:** `GET /api/teacher/face-kiosk`, `POST /api/teacher/face-kiosk/{student}` (returns `qr_text` for
  `qr_flutter`), `GET /api/teacher/face-kiosk/codes/{code}` — same rules as the web page
  (`Api/Teacher/FaceKioskController`). Flutter task: `docs/FLUTTER_PROMPT_FACE_KIOSK_QR.md`.
- **Tested (PHPUnit, 2 tests / 33 assertions):** list → QR → the kiosk endpoint accepts the exact `qr_text` →
  `waiting` → a newer QR makes the old one `cancelled`; another teacher gets `403` for the QR and its status and
  an empty list; a student gets `403`; no token `401`.

---

## Batch 11 — Admin (part 1): sign-in page

### B11-1 ✅ Lockout countdown said 45 seconds; the server locks for 30 minutes
- **Problem:** after 5 wrong passwords the page showed a 45-second timer kept in `sessionStorage`; when it ended
  the form came back but the server kept refusing for 30 minutes.
- **Where:** `Admin/AdminAuthController`, `admin/login.blade.php`.
- **Fix:** the time comes from the same rate limiter the form post checks (`RateLimiter::availableIn`), also after
  a reload; when it ends the page reloads and the server decides again. Wrong passwords now say how many tries
  are left (from 3). Only the email is kept after a failed try, never the password.

### B11-2 ✅ Sign-in page slow and heavy
- **Problem:** the page stayed invisible (`opacity: 0`) until GSAP (CDN) finished; a 2 MB background photo; a
  logo floating forever; dead code for a "success" animation that never ran.
- **Fix:** no GSAP, no photo, no hidden body; the only decoration is an inline SVG (rings from the school seal).
  Labels tied to inputs, `autocomplete` username/current-password, a keyboard-reachable show-password button
  with `aria-pressed`, Caps Lock warning, a "You have signed out." notice, a link to the main sign-in page.
  Layout: school panel + form on desktop, short school header + form on phones.
- **Tested:** PHPUnit 4 tests / 29 assertions (page has no photo/GSAP/hidden body and has the labels; right
  password signs in; wrong passwords count down 3 → 1, then lock with ~1800 s from the server, shown again after
  a reload without the flash, and even the right password is refused while locked; logout notice + form flag).
  Logout regression 5/5. Screenshots 1440 / 1024 / 390: no JS errors, no sideways scroll.

### B11-3 ✅ JavaScript error on every admin page
- **Problem:** a stray `n` after the dark-mode script in the admin layout (`})();n`) threw
  `ReferenceError: n is not defined` on every admin page load.
- **Where:** `layouts/admin.blade.php`. **Fix:** removed. Admin dashboard now loads with no JS errors.

### B11-4 ✅ Dashboard: not live, two y-axes, dead code
- **Problem:** numbers and charts only changed on a reload; sign-ins and registrations shared one chart with two
  y-axes (different scales on one plot); an entrance animation called a library that is not loaded (`anime`);
  a new chart was created on every visit without the old one being freed; chart colours read the theme once.
- **Where:** `Admin/DashboardController`, `admin/dashboard.blade.php`, new `admin/partials/dashboard-pending` and
  `dashboard-activity`, route `admin.dashboard.live`.
- **Fix:** one data method used by the page and by `GET /admin/dashboard/live` (JSON, `no-store`, admin only); the
  two lists come back as HTML from the same partials the page uses (forms and CSRF unchanged). The page refreshes
  every 20 s while visible: numbers count to the new value, chart values glide to their new height, lists update
  unless the admin is pointing at or focused inside them; the timer stops when the page is left. Two charts with
  one axis each — Sign-ins (line, draws in from the left, crosshair tooltip) and Registrations and approvals
  (grouped bars rising with a short stagger, legend). Palette validated for colour-blind separation and contrast
  in light (`#2563EB`/`#0D9488`) and dark (`#3B82F6`/`#0D9488`); dark mode recolours without rebuilding; each
  chart has a screen-reader table; reduced motion turns the animation off.
- **Tested:** PHPUnit 3 tests / 44 assertions (page has both charts and no second axis; live endpoint needs an
  admin; JSON shape, 7 / days-in-month / 12 labels; a new pending parent and a login appear on the next refresh,
  with the approve form and CSRF field in the list HTML). Real browser: charts drawn, one y-axis each, month and
  year views, dark mode recolour, the live refresh ran on its own and stayed "Live", no JS errors; screenshots
  1440 / 390 with no sideways scroll.

---

## Found, not fixed yet

| | Issue | Where |
|---|---|---|
| 🔧 | `/api/parent/*` `enrollment_status` is still only "enrolled" / "not enrolled" (the web now shows pending/returned) | `Api/ParentPortal/DashboardController.php` — needs a Flutter change first |
| 🔧 | A parent with two children cannot link the second child themselves (the school office links them) | `ParentLinkService` — feature decision |
| 🔧 | Grade weights 25/50/25 for every subject (DepEd differs per subject group) | CLAUDE.md §13 #13 — confirm with the school |
| 🔧 | No "change password" inside the app for students/teachers/parents | CLAUDE.md §13 #14 |
| 🔧 | `GET /api/messages/thread/{id}` has no contact check | CLAUDE.md §13 #16 |
| 🔧 | Admin layout not audited yet | `resources/views/layouts/admin.blade.php` |
