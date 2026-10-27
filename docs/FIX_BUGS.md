# FIX BUGS — DP-LMS

Talaan ng lahat ng bug na nahanap at inayos habang pinapakinis ang system (branch `feature/ui-refresh`).
Bawat bug ay may: **Problema** (ano ang nakikita ng user) → **Saan** (file at method) → **Ayos** (ano ang
binago) → **Nasubukan** (paano napatunayang gumagana).

| Tanda | Ibig sabihin |
|---|---|
| ✅ | Naayos at nasubukan |
| 🔧 | Plano — inaayos pa |

> Nasubukan sa localhost (XAMPP + `php artisan serve`) gamit ang `php artisan demo:local` na data.
> Walang totoong email na naipadala sa testing (`MAIL_MAILER=array`), at binura ang lahat ng test data pagkatapos.

---

## Buod

| Batch | Saklaw | Commit | Bugs |
|---|---|---|---|
| [1](#batch-1--security) | Security (mobile API, OTP) | `0d662e5` | 2 |
| [2](#batch-2--login-at-activate) | Login, Activate, Parent activate/recover | `6099ff7` | 7 |
| [3](#batch-3--otp-at-forgot--reset-password) | OTP, Forgot / Reset password | `7dc6571` | 5 |
| [4](#batch-4--teacher-pages) | Teacher layout, Dashboard, Students, Announcements | `0c245c5` `34f31cc` `00cc6fd` | 14 |
| [5](#batch-5--student-pages-part-1) | Student layout, Dashboard, Modules, Quizzes, Grades | `e6e3dde` `c7857ea` | 16 |
| [6](#batch-6--student-pages-part-2) | Subjects, Schedule, Attendance, Messages, Enrollment form, Face, Parent link | — | 13 |

---

## Batch 1 — Security

### B1-1 ✅ Gumagana pa ang mobile app ng na-reject o na-ban na account
- **Problema:** Kapag ni-reject ng admin ang isang user o na-ban (3 maling face upload), natatanggal siya sa
  website pero gumagana pa rin ang mobile app niya — nababasa pa ang grades, messages, atbp.
- **Saan:** `routes/api.php` (protected group — `auth:sanctum` + `role:` lang, walang `approved`);
  `app/Http/Middleware/ApprovedMiddleware.php`.
- **Ayos:** Idinagdag ang `approved` sa protected API group. Sa `api/*`, ang middleware ay sumasagot ng
  `403 { "message": "Your account is not active…" }` at binubura ang token (sa website: logout + redirect, gaya ng dati).
  Naidagdag sa `docs/FLUTTER_HANDOFF.md` ang bagong 403.
- **Nasubukan:** approved → `200`; ni-reject → `403` at 0 na ang token; sunod na request → `401`.
  Web: approved → `200`, rejected → logout + redirect sa `/login`.

### B1-2 ✅ "500 Server Error" sa login
- **Problema:** Pagkatapos ng 5 login code sa loob ng isang oras, nag-e-error ang buong login page.
- **Saan:** `app/Services/OtpService.php` → `send()`; `app/Http/Controllers/Auth/LoginController.php` → `submit()`.
- **Ayos:** Ang limit ay `ActionNotAllowed` na (may bilang ng minuto) na sinasalo ng `LoginController`
  at `OtpController::resend()` → dinadala sa OTP page na may malinaw na mensahe.
- **Nasubukan:** inubos ang limit → `302 → /otp` na may "Too many codes… try again in 60 minutes."

---

## Batch 2 — Login at Activate

### B2-1 ✅ Back button: nakapirming "Please wait…" / blangkong login page
- **Problema:** Pag-Back sa login o activate, nananatiling disabled ang button; pag-alis sa login gamit ang
  "Activate your account", blangko ang login pagbalik.
- **Saan:** `resources/views/auth/login.blade.php` (script), `resources/views/layouts/auth-card.blade.php` (`data-once`).
- **Ayos:** `pageshow` reset ng button at ng mga panel kapag galing sa back/forward cache.

### B2-2 ✅ Hindi lumalabas ang "Remember me / Forgot" kapag walang JavaScript
- **Saan:** `login.blade.php` → `<noscript>` style. **Ayos:** idinagdag ang `.login-row`; inayos ang pagka-center ng logo.

### B2-3 ✅ Sirang background kapag nasa XAMPP subfolder
- **Saan:** `auth-card.blade.php` → `/images/bg.jpg`. **Ayos:** `asset()`; fixed layer (gumagana rin sa iPhone).

### B2-4 ✅ Naputol na LRN / code kapag nag-paste ng may espasyo
- **Problema:** "1234 5678 9012" → naputol ng `maxlength` bago natanggal ang espasyo.
- **Saan:** `auth-card.blade.php`, `activate.blade.php`, `activate-verify.blade.php`. **Ayos:** `data-digits` (digits lang, pati sa paste).

### B2-5 ✅ Accessibility ng login
- **Saan:** `login.blade.php`. **Ayos:** label ng Password nakakabit na; ang show-password ay naaabot na ng
  keyboard (dati `tabindex=-1`, 17px); contrast ng error/hint/button; `aria-invalid`/`aria-describedby`;
  focus outline; reduced motion (GSAP at particles).

### B2-6 ✅ Hindi magkatugma ang hitsura ng login at activate
- **Ayos:** iisang berdeng tema, font, navbar at card para sa activate, verify, parent activate/recover.

### B2-7 ✅ Phone: naputol na "S" at malaking puwang sa itaas ng login
- **Saan:** `login.blade.php` (`.login-card { overflow }`, mobile layout). **Nasubukan:** screenshot sa 390px.

---

## Batch 3 — OTP at Forgot / Reset password

### B3-1 ✅ "Send Reset Link" pero code ang ipinapadala
- **Saan:** `resources/views/auth/forgot-password.blade.php`. **Ayos:** "Send code" + tamang paliwanag.

### B3-2 ✅ Hindi totoong "Resend code"
- **Problema:** link lang pabalik sa form; kailangang i-type ulit ang email.
- **Saan:** `forgot-verify.blade.php`. **Ayos:** totoong POST resend sa parehong email (may rate limit pa rin).

### B3-3 ✅ "Minimum 8 characters" lang ang hint sa reset
- **Problema:** laging babagsak ang sumusunod sa hint (kailangan din ng malaki/maliit na letra, numero, simbolo).
- **Saan:** `reset-password.blade.php`. **Ayos:** live checklist ng lahat ng patakaran (kapareho ng server).

### B3-4 ✅ Laging 5:00 ang OTP countdown
- **Saan:** `app/Http/Controllers/Auth/OtpController.php` → `show()`; `otp.blade.php`.
- **Ayos:** countdown mula sa totoong `expires_at` ng code.

### B3-5 ✅ OTP page na Tailwind sa lumang CSS build
- **Ayos:** lahat ng apat na page ay `layouts.auth-card` na (minana ang lahat ng ayos ng Batch 2).
- **Nasubukan (Batch 3):** buong forgot flow (email → resend → maling/tamang code → mahinang/tamang password → login)
  at OTP flow (maling/tamang code → dashboard).

---

## Batch 4 — Teacher pages

### B4-1 ✅ Naiipit sa "loading" kapag expired ang session
- **Problema:** Sidebar click → skeleton habambuhay.
- **Saan:** `resources/views/layouts/teacher.blade.php` → sidebar loader (`loadPage`).
- **Ayos:** full page load kapag hindi teacher page ang sagot (redirect / 401 / JSON) → napupunta sa login.

### B4-2 ✅ Patay ang JavaScript ng page pagkatapos ng Back/Forward
- **Problema:** hal. hindi bumubukas ang student modal pagkatapos mag-Back.
- **Ayos:** iisang loader para sa click at Back/Forward na nagpapatakbo ng script ng page.

### B4-3 ✅ Error sa ikalawang bukas ng Materials
- **Problema:** `SyntaxError: Identifier has already been declared` → patay ang upload button.
- **Ayos:** bawat inline script ng page ay pinapatakbo sa sariling block `{ … }`.

### B4-4 ✅ Iba pa sa teacher layout
- Hindi nagbabago ang title ng tab; hindi gumagana ang Ctrl+click (new tab); absolute URLs (`/teacher/…`)
  → `route()`/`url()`; malabong sidebar text; walang focus outline; nakatago ang page hanggang ma-load ang
  lahat ng image; nakagitna ang "Log out"; naputol na pangalan sa top bar sa phone.

### B4-5 ✅ Announcement ng teacher → lumalabas sa BUONG paaralan
- **Problema:** walang section selector ang form, at ang walang section ay itinuturing na school-wide.
- **Saan:** `app/Models/Announcement.php` → `scopeVisibleToSections()`; `Teacher/AnnouncementController.php`;
  `resources/views/teacher/announcements.blade.php`; `Api/Teacher/DashboardController.php` → `storeAnnouncement()`.
- **Ayos:** may "Send to" na; ang "All my sections" ay para lang sa mga section ng nag-post na teacher (web at API,
  student at parent).
- **Nasubukan:** demo section → 1, ibang section → 0, walang section → 0, parent → 1.

### B4-6 ✅ Walang error message sa announcement form · B4-7 ✅ Walang push sa magulang
- **Saan:** `announcements.blade.php`; `app/Services/ParentNotifier.php` → `announcement()` (bago).

### B4-8 ✅ Nasisira ang sidebar icons sa teacher dashboard
- **Saan:** `teacher/dashboard.blade.php` → `.nav-icon` (kapareho ng class ng layout). **Ayos:** prefix `td-`.

### B4-9 ✅ Mali ang "My Subjects" / "My Sections" sa teacher dashboard
- **Saan:** `Teacher/TeacherDashboardController.php`. **Ayos:** bawat klase (subject + section); kasama ang advisory section.

### B4-10 ✅ Laging "No grades recorded yet" sa student modal ng teacher
- **Saan:** `Teacher/TeacherStudentController.php`; `teacher/students/index.blade.php`.
- **Ayos:** kasalukuyang SY; adviser = lahat ng subject, subject teacher = sariling subject; tamang average.

### B4-11 ✅ Tahimik na sirang layout (6 na Tailwind class na wala sa build)
- `gap-5`, `p-4`, `min-w-0`, `sm:col-span-2`, `py-14`, `w-14` → plain CSS ang tatlong teacher page.

### B4-12 ✅ Iba pa: LRN search, naka-escape na HTML, N+1 query, phone layout
- **Nasubukan (Batch 4):** headless Edge 18/18 (SPA, Back/Forward, Materials ×2, expired session), walang JS error.

---

## Batch 5 — Student pages (part 1)

### B5-1 ✅ Hindi nami-mark as read ang notification kapag kinlik
- **Problema:** laging may pulang badge — kinakansela ng browser ang request dahil umaalis agad ang page.
- **Saan:** `resources/views/layouts/app.blade.php` → notifications script. **Ayos:** `fetch(..., { keepalive: true })`.
- **Nasubukan:** click sa notification → `unread=0`.

### B5-2 ✅ Absolute URLs at error kada 30 segundo sa student/parent layout
- **Ayos:** `route()`/`url()`; base URL sa link ng notification; `.catch()` sa polling.

### B5-3 ✅ Laging "0" ang Assignments sa student dashboard
- **Saan:** `Student/StudentDashboardController.php` → `index()`. **Ayos:** bukas na quiz na hindi pa naipapasa.

### B5-4 ✅ Maling payo sa hindi pa enrolled
- **Ayos:** form / pending / rejected (may dahilan) — may tamang button.

### B5-5 ✅ Hindi makaka-enroll sa susunod na school year
- **Saan:** `StudentDashboardController.php` → `enrollmentForm()`. **Ayos:** kasalukuyang SY lang ang tinitingnan.

### B5-6 ✅ Lumang data / nakikita pagkatapos mag-logout
- **Problema:** `Cache-Control: private, max-age=30` sa lahat ng student page. **Ayos:** tinanggal.

### B5-7 ✅ Hindi nabibilang ang material views mula sa web
- **Saan:** bagong route `student.modules.open` → `StudentDashboardController::openMaterial()`.
- **Ayos:** binibilang ang view at sinisigurong sa klase mo ang material (`404` kung hindi).

### B5-8 ✅ Sirang file link ng quiz kapag mali ang `APP_URL`
- **Saan:** `student/quiz-show.blade.php` (`Storage::url()` → `asset()`).

### B5-9 ✅ Quizzes: double submit, walang "Missed", walang error ng typed answer
- **Saan:** `Student/AssignmentController.php` → `index()`; `quizzes.blade.php`; `quiz-show.blade.php`.

### B5-10 ✅ Walang average sa My Grades
- **Saan:** `StudentDashboardController.php` → `grades()`; `student/grades.blade.php`.
- **Ayos:** Q1–Q4, average kada subject, general average ("so far" hangga't kulang ang quarter).

### B5-11 ✅ Iba pa: contrast, focus, reduced motion, phone layout, N+1 query
- **Nasubukan (Batch 5):** in-process 5/5; headless Edge 19/19 (lahat ng 12 student page), walang JS error.

### Local setup (hindi code)
- **Problema:** `403` ang lahat ng upload sa localhost. **Dahilan:** walang `public/storage` link.
- **Ayos:** `php artisan storage:link` (sa Railway: nasa `docs/DEPLOY_CHECKLIST.md`).

---

## Batch 6 — Student pages (part 2)

### B6-1 ✅ Hindi nababawasan ang "Unread messages"
- **Problema:** Hindi kailanman mina-mark as read ng website ang mga mensahe ng teacher (sa mobile app lang),
  kaya habambuhay ang bilang sa dashboard.
- **Saan:** `app/Http/Controllers/Student/StudentDashboardController.php` → `messages()`.
- **Ayos:** kapag binuksan ang usapan sa isang teacher, ang mga mensahe **niya lang** ang mina-mark as read
  (`is_read = true`, `read_at = now()`). Hindi nagagalaw ang unread ng ibang teacher.
- **Nasubukan:** dashboard "2" unread → buksan ang Messages → dashboard "0"; DB `unread=0`.

### B6-2 ✅ Pinaghahalo ang lahat ng teacher sa iisang "Conversation"
- **Problema:** iisang listahan para sa lahat ng teacher, bago→luma ang ayos, at "You" lang ang nakasulat —
  hindi alam kung kanino ipinadala.
- **Saan:** `StudentDashboardController.php` → `messages()`, `storeMessage()`; `resources/views/student/messages.blade.php`.
- **Ayos:**
  - Kaliwa: listahan ng teacher = mga teacher ng section mo + mga dating nakausap, may huling mensahe at
    bilang ng unread, nakaayos ayon sa pinakabagong usapan. Kanan: usapan sa napiling teacher (`?with=<id>`),
    luma→bago, may "Today / Yesterday / petsa", "seen" at "edited".
  - Ang sagot ay napupunta sa napiling teacher; pagkatapos mag-send, bumabalik sa **parehong** usapan.
  - Kung hindi na teacher ng section mo, mababasa pa rin ang usapan pero hindi na makakasagot (gaya ng
    patakaran ng `storeMessage()` dati).
  - Enter = send, Shift+Enter = bagong linya; double-submit guard. Sa phone, chips ang listahan ng teacher.
- **Nasubukan:** isang contact + aktibong usapan; luma ang una; Enter → nasa parehong usapan, sa huli ang
  mensahe ko; DB `sent_by_student=1`; walang horizontal scroll sa phone (390px).

### B6-3 ✅ Walang laman ang mensaheng may attachment
- **Problema:** kapag file ang ipinadala ng teacher mula sa mobile app, walang lumalabas na link.
- **Saan:** `messages.blade.php`. **Ayos:** link ng file (📎 pangalan ng file) + "edited" kapag na-edit.
- **Nasubukan:** lumalabas ang link papunta sa `/storage/chat_files/…pdf`.

### B6-4 ✅ Bagong code ang nagagawa kapag ni-refresh ang Parent page
- **Problema:** page ang ibinabalik ng `POST` na gumagawa ng code. Pag-refresh / "Confirm resubmission" →
  bagong code at **nabubura ang nauna**, na maaaring nai-scan na ng magulang.
- **Saan:** `app/Http/Controllers/Student/ParentLinkController.php` → `store()`, `show()`;
  `resources/views/student/parent-link.blade.php`.
- **Ayos:** POST → redirect → GET. Ang code ay dinadala ng session nang isang beses (`session('parent_link')`),
  at ginagawa ulit ang QR mula sa URL. May paalala na isang beses lang ito makikita; "Creating…" + disabled
  ang button para hindi madoble ang click.
- **Nasubukan:** click → GET page na may code; reload → wala nang code at walang bagong nagawa (DB `parent_codes=1`).

### B6-5 ✅ Enrollment form: umaabot sa buong lapad / siksik sa phone
- **Problema:** wala sa build ang `max-w-2xl` at `mb-5`; ang Age/Birthdate/Gender ay 3 column kahit sa phone.
- **Saan:** `resources/views/student/enrollment-form.blade.php`.
- **Ayos:** plain CSS (`.ef-*`), max 720px ang lapad; 1 column sa ≤640px.
- **Nasubukan:** lapad ng form ≤ 721px sa 1366px na screen.

### B6-6 ✅ Enrollment form: hindi magagamit ng keyboard ang "Student Type"
- **Problema:** `<div onclick>` ang tatlong pagpipilian — hindi maaabot ng Tab / hindi mabasa ng screen reader.
- **Ayos:** totoong radio buttons (`fieldset`) na may parehong hitsura; lumalabas ang transfer fields kapag "Transferee".
- **Nasubukan:** arrow keys → "Transferee" napili at lumabas ang transfer fields; na-submit bilang `type=transfer`.

### B6-7 ✅ Enrollment form: iba pa
- **Problema:** walang `for` ang mga label; 10px at malabong error text; Tagalog na hint sa English na UI; walang
  double-submit guard; patay na `@section('sidebar')`.
- **Ayos:** lahat ng input may `id` + `label for`; 12.5px na error; English na hint; bawal ang numero sa pangalan;
  awtomatikong Age mula sa Birthdate (puwede pa ring baguhin); double-submit guard; tinanggal ang patay na section.
  **Hindi binago** ang mga pangalan ng field — pareho pa rin ang validation sa controller.
- **Nasubukan:** lahat ng field may label; Age = tamang edad mula 2012-03-15; submit → dashboard "Enrollment form
  submitted", DB `enrollment=pending`.

### B6-8 ✅ Face self-upload: sira ang page kapag naka-on ang `FACE_SELF_UPLOAD`
- **Problema:** 22 Tailwind class ang wala sa build → walang kulay ang Start button (puti sa puti), hindi
  lumalabas ang bilog na gabay at ang progress bar, walang padding ang mga alert.
- **Saan:** `resources/views/student/face-register.blade.php` (naka-off sa default — kiosk ang gamit).
- **Ayos:** plain CSS (`.fr-*`); `ring()` ay nagpapalit na ng class na `ring-idle/ok/warn`. Hindi ginalaw ang logic ng camera.
- **Nasubukan (in-process, naka-on ang flag):** may kulay na Start button at bilog; walang Tailwind class.

### B6-9 ✅ Face self-upload: "Network error" kahit validation error
- **Problema:** walang `Accept: application/json` ang upload, kaya HTML redirect ang natatanggap at
  nagkakamali ang `res.json()`.
- **Saan:** `face-register.blade.php` → `upload()`. **Ayos:** `Accept: application/json` + `res.json().catch()`,
  kaya ang mensahe ng server ang lumalabas.
- **Nasubukan:** kulang na larawan → `422` na JSON (`message` + `errors.images`).

### B6-10 ✅ "Room Room 5" sa schedule
- **Problema:** laging dinadagdagan ng "Room " ang room, pero malayang text ito (hal. "ALS Room 7").
- **Saan:** `resources/views/student/schedule.blade.php`; `StudentDashboardController.php` → `schedule()`.
- **Ayos:** "Room " lang kapag numero ang room; eager-load ang adviser (isang query na lang).
- **Nasubukan:** lumalabas ang "Room 5" at "ALS Room 7"; walang "Room Room".

### B6-11 ✅ My Subjects: rainbow cover at hindi maki-click
- **Saan:** `resources/views/student/subjects.blade.php`.
- **Ayos:** iisang accent color (design rule); card → link papunta sa modules ng subject (`#subj-<id>`),
  naka-alpabeto; mas malinaw na text; tinanggal ang inline `onmouseover`; may button ang empty state.
- **Nasubukan:** click sa card → `/student/modules#subj-…` at nandoon ang heading ng subject.

### B6-12 ✅ Attendance: nakapirming "Checking…" pagkatapos ng Back
- **Saan:** `resources/views/student/attendance.blade.php` → script.
- **Ayos:** `pageshow` → bumabalik ang button at nag-a-update ang oras; tamang padding; mas malinaw na empty text.

### B6-13 ✅ Face status: dobleng padding at hardcoded na kulay
- **Saan:** `resources/views/student/face-status.blade.php`. **Ayos:** layout tokens, walang dagdag na padding.

### Nahuli habang sinusubukan (bago na-commit)
- Messages: lumalabas ang indent ng template sa loob ng bubble → `pre-wrap` sa text lang (`.ms-text`).
- Messages: lumiliit ang avatar sa header (masyadong malawak ang `.ms-head span`) → sariling class `.ms-head-role`.
- Messages: hindi nawawala ang huling mensahe sa chips sa phone (inline `display:block`) → inilipat sa CSS.

**Nasubukan (Batch 6):** headless Edge 22/22, walang JS error; in-process FaceUpload 2/2; DB state tama;
binura ang lahat ng test data pagkatapos.
