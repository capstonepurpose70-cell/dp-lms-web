# DP-LMS — Backlog

Everything not finished yet, in the order we will tackle it. Defense: **November 6, 2026**.
Sources: `docs/FIX_BUGS.md` (Found, not fixed yet), `docs/IOT_CHECKLIST.md`, `docs/DEPLOY_CHECKLIST.md`,
and the known-issues list in CLAUDE.md. Update this file as items close (move them to FIX_BUGS.md).

| Priority | Meaning |
|---|---|
| **Now** | In progress |
| **P0** | Must be done before the defense |
| **P1** | Should be done before the defense if time allows |
| **P2** | Needs a decision from the school / the team first |
| **Later** | After the defense |

---

## Now — Raspberry Pi 5 setup (hardware)

Step by step with photos; details in `IOT_CHECKLIST.md` §A–C.

| # | Task | Notes |
|---|---|---|
| N1 | ✅ Flash Raspberry Pi OS (64-bit, desktop) | The old 16 GB card stalled twice; written on a **Samsung EVO Plus 128 GB** instead |
| N2 | ✅ First boot + full upgrade, SSH from the laptop | `dplms@dplms-gate` (IP from `ping -4 dplms-gate.local`; it can change) |
| N3 | ✅ 5-inch DSI display + touch | Detected automatically — no `config.txt` change needed |
| N4 | ✅ Camera | **OV5647** fisheye on port 0, auto-detected. Colours are pink (no IR filter) — fine, recognition uses grayscale; default tuning gives the clearer face (the `ov5647_noir.json` tuning turned it green) |
| N5 | ✅ Official 27 W USB-C power supply | `throttled=0x0`, 50 °C with the Active Cooler |
| N6 | Mount / stand at face height, extension cord, network at the gate | Pocket Wi-Fi or hotspot (school Wi-Fi with a login page will not work) |

## P0 — IoT software (paused for UI polish; kiosk photo quality B9-1…B9-6 and B9-11 done)

Planned in FIX_BUGS.md first, as usual.

| # | Task | Where |
|---|---|---|
| I1 ✅ | **Face photos are public** (`/storage/faces/{id}/img_01.jpg`, guessable). Move to private storage; the Pi downloads them only with the device key | `FaceRegistrationService`, `AttendanceController::approvedFaces()` |
| I2 ✅ | `gate_camera.py` — recognize faces, `POST /api/attendance { student_id }`, refresh `GET /api/faces/approved` every ~10 min. Send the numeric `user_id` as `student_id` (stable, not personal data; the server already accepts it — contract-tested) instead of the email `label` | `docs/raspberry-pi/` |
| I2b | **Faster gate response:** every arrival waits ~0.6–0.9 s for Google's OAuth token (measured) + one FCM call per parent phone before the Pi gets its answer. Cache the token (~50 min) and send the push right after the response | `PushNotificationService`, `AttendanceController::store()` |
| I3 ✅ | Offline queue — keep arrivals while Wi-Fi is down and resend (server already dedupes) | part of I2 |
| I3b | **Liveness** (no photo / phone screen) at the kiosk and the gate, and **appearance rules** at registration (no cap, sunglasses, mask; head-only crop). Decide: clear eyeglasses allowed? | FIX_BUGS B9-12, B9-13 |
| I4 | Mode switch: Registration (kiosk) vs Gate — one camera, one mode at a time | Pi |
| I5 | Auto-start on power-on (systemd), no keyboard needed at the gate | Pi |
| I6 | `config.py` on the Pi + `ATTENDANCE_DEVICE_KEY` on the server (same value, never committed) | Pi / `.env` / Railway |
| I7 | End-to-end test: register at kiosk → gate scan → parent push; same day again → no 2nd push; unknown face → nothing; Wi-Fi off/on → queued arrival sent; wrong key → 401 | `IOT_CHECKLIST.md` §G |

## P0 — Deploy (when "Railway is paid")

Follow `DEPLOY_CHECKLIST.md` in order. Nothing goes to `main` before this.

| # | Task |
|---|---|
| D1 | **Security first:** revoke the old Semaphore SMS key; delete `test@example.com` on the live DB if it exists; change the admin password after the first login (the old one is in Git history) |
| D2 | Railway: `web` + `scheduler` services, **volume** at `/app/storage/app/public`, pre-deploy `php artisan migrate --force` |
| D3 | Railway variables: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, DB, `ATTENDANCE_DEVICE_KEY`, `FIREBASE_CREDENTIALS_BASE64`, mail, `FACE_SELF_UPLOAD=false` |
| D4 | Back up the local DB; check duplicate mobile numbers before migrating |
| D5 | Fast-forward merge `feature/ui-refresh` → `main`, then the after-deploy commands and the smoke test |

## P0 — Flutter app

| # | Task | Notes |
|---|---|---|
| F1 | Rebuild the **release APK** | Last release (2026-10-21) predates class attendance and kiosk mode |
| F2 | Activation flow instead of register (`/api/register` → 410) | `FLUTTER_HANDOFF.md` §2 |
| F3 | Login accepts LRN / mobile, handles the 403 (temporary password); keep the JSON key `email` | |
| F4 | Arrival push: FCM token on login, `DELETE` on logout, `dp_lms_channel`, open child attendance on tap | `IOT_ARRIVAL_NOTIFICATIONS.md` §3 |
| F5 | Two parent phones logged in for the live demo | Defense kit |

## P0 — Defense day kit

27 W power supply, extension cord, stand · hotspot / pocket Wi-Fi · laptop with XAMPP + backup DB ·
`config.py` pointing to the live URL (or the laptop IP as fallback) · two parent phones.

---

## P1 — Remaining UI polish + backend checks

Same flow as batches 1–8: plan in FIX_BUGS.md → fix → test → commit.

| # | Task | Where |
|---|---|---|
| U1 | **Admin** layout + pages (not audited yet; sign-in page done in Batch 11) | `layouts/admin.blade.php`, `admin/login.blade.php`, `resources/views/admin/*` |
| U2b ✅ | **Teacher dashboard + sidebar polish** (requested 2026-10-28): clean, professional look; minimalist sidebar with highly visible text, no glow/neon; fix the logo + school name block at the top left. UI only — no backend or state logic changes; no rendering delays | `layouts/teacher.blade.php`, `teacher/dashboard.blade.php` |
| U2 | Teacher pages not polished yet: Materials, Quizzes/Assignments, Gradebook, Messages, Enrollments, Class attendance, Face kiosk | `resources/views/teacher/*` |
| U3 | **Change password** inside the app for students/teachers/parents (with current-password check); `/change-password` today only serves the forced change and always redirects to the teacher dashboard | `Auth/ChangePasswordController`, `auth/change-password.blade.php` |
| U4 | `GET /api/messages/thread/{id}` — no contact check (shows name/role/section of any user id) | `Api/MessageController` |
| U5 | `UserManagementController::approve()` — no role/status guard (can "approve" a teacher who never activated) | Admin |
| U6 | API `storeMaterial` always published and no "enrolled students" check (the web has both); API assignment `due_date` has no `after:now` | `Api/Teacher/*` |

## P2 — Needs a decision first

| # | Question | Notes |
|---|---|---|
| Q1 | Grade weights: 25/50/25 for every subject? DepEd differs (e.g. JHS Math/Science 40/40/20, MAPEH/TLE 20/60/20) | Now one place to change: `Grade::WEIGHTS` |
| Q2 | Parent with two children: let them link the second child themselves (with the child's code), or keep it with the school office? | `ParentLinkService` |
| Q3 | Show "pending / returned" enrollment in the parent app? (web already does) | Needs a Flutter change before the API value set changes |
| Q4 | SHS strands (ABM, HUMSS, SMAW…) — subjects are per grade only, so all strands see the same subjects | Schema change |

## School data (from the school)

- Class lists for **7-AQUINO** and **8-RAMOS**
- SY 2026–2027 class schedule per section
- Teacher accounts for the 23 teachers (Admin → Import Teachers), then `AdvisorySectionSeeder`
- Real teaching load (`teacher_subjects`) via Admin → Class Schedules
- Official JHS lunch break
- Confirm the late cutoff (`ATTENDANCE_LATE_AFTER`, default 07:30)

---

## Later — housekeeping

| # | Task |
|---|---|
| H1 | Tailwind build is stale (April) — rebuild once, or keep plain CSS everywhere |
| H2 | Test suite: stock `ExampleTest` fails (302); migrations are MySQL-only so `RefreshDatabase` on SQLite is impossible — add a MySQL test database and real feature tests (the scratch tests from batches 5–8 are a start) |
| H3 | Remove: 4 migrations named `.php.php`, the zero-byte junk files in the project root, `dplms_be_final.zip` in the repo, unused `layouts/faculty.blade.php` |
| H4 | Duplicate Anime.js (v4 npm + v3 CDN); GSAP / Chart.js / face-api load from CDNs |
| H5 | Translate the remaining Tagalog docs to English (`IOT_ARRIVAL_NOTIFICATIONS.md`, `FLUTTER_HANDOFF.md`, parts of the Flutter prompts) |

## Later — features

- Printed modules & textbooks (panel revision)
- Prescriptive analytics
- Flutter: class schedules screen (web only today)
