# DP-LMS — Pre-defense deploy checklist (Railway)

Defense: **November 6, 2026**. Pushing to `main` auto-deploys on Railway, so do steps 1–2 before merging.

---

## 0. One-time security fixes (do these first)

- [ ] **Revoke the old Semaphore SMS key** at semaphore.co → Account → API. It was committed in
      `config/services.php` (and inside `dplms_be_final.zip`), so it is public in Git history.
      Create a new key only if you will use SMS, and put it in the Railway variable `SEMAPHORE_API_KEY`.
- [ ] Delete the default seed account if it exists on the live DB: user `test@example.com`
      (old `DatabaseSeeder` created it with password `password`).
- [ ] After the first login on the live site, change the admin password (Admin → Profile).

## 1. Railway setup (once)

**Services**

| Service | Source | Start command | Public domain |
|---|---|---|---|
| `web` | this repo, branch `main` | keep current; it must run `php artisan storage:link` before the server starts | yes |
| `scheduler` | same repo, branch `main` | `php artisan schedule:work` | no |
| `MySQL` | Railway plugin | — | no |

- **Pre-deploy command** (web): `php artisan migrate --force`
- **Volume** (web): mount at `/app/storage/app/public` — face photos, materials, quiz files and
  submissions live there. Without it every redeploy deletes them.
- The `scheduler` service closes ended class-attendance sessions every minute (marks absent + notifies
  parents). Without it, absents are only written when someone opens the attendance pages.

**Variables** (set on `web` and `scheduler` — use Railway *shared variables*)

| Variable | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `php artisan key:generate --show` (keep the existing one if already set) |
| `APP_URL` | `https://<your-app>.up.railway.app` |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | reference the MySQL service variables |
| `ATTENDANCE_DEVICE_KEY` | long random string: `php -r "echo bin2hex(random_bytes(32));"` — same value in the Pi `config.py` |
| `FIREBASE_CREDENTIALS_BASE64` | base64 of the Firebase service-account JSON (project `dp-lms`) |
| `MAIL_MAILER` / `BREVO_API_KEY` / `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` | your mail settings |
| `FACE_SELF_UPLOAD` | `false` (kiosk-only face registration) |
| `ADMIN_SEED_EMAIL` / `ADMIN_SEED_PASSWORD` | only for the first seed; remove the password afterwards |
| `SEMAPHORE_API_KEY` | optional (new key only) |

Base64 the Firebase JSON on Windows (PowerShell):

```powershell
[Convert]::ToBase64String([IO.File]::ReadAllBytes("storage\app\firebase\firebase-service-account.json"))
```

## 2. Before merging (local)

- [ ] Commit everything on `feature/ui-refresh` and push it.
- [ ] Back up the local database (demo fallback):
      `C:\xampp\mysql\bin\mysqldump.exe -u root dp_lms > dp_lms_backup.sql`
- [ ] If the live DB already has users, check for duplicate mobile numbers — migration
      `2026_09_25_100100` stops on duplicates (it lists them):
      `SELECT contact_number, COUNT(*) FROM users WHERE contact_number IS NOT NULL GROUP BY contact_number HAVING COUNT(*) > 1;`

## 3. Merge to `main` (this deploys)

`main` is an ancestor of `feature/ui-refresh`, so this is a fast-forward (no conflicts):

```bash
git checkout main
git pull origin main
git merge --ff-only feature/ui-refresh
git push origin main
git checkout feature/ui-refresh
```

New migrations that run on deploy: parent link codes, trusted devices, nullable email, unique
contact number, birthdate, schema-drift repair, submission feedback, `general_average`,
class attendance tables, face kiosk codes.

## 4. After deploy (Railway → web → Shell)

```bash
php artisan migrate:status              # all "Ran"
php artisan db:seed --force             # admin (if none) + subjects; sections only on an empty DB
php artisan db:seed --class=AdvisorySectionSeeder --force   # once, after teachers are imported
php artisan storage:link
php artisan faces:make-private          # once: moves face photos saved before 2026-10-28 out of public storage
php artisan schedule:list               # shows attendance:finalize every minute
```

## 5. Smoke test (live URL)

- [ ] Admin login → Dashboard, Users, Sections, Class Schedules, Enrollment, Reports, Face Verification open.
- [ ] Import teachers → activation link works → teacher sets password.
- [ ] Class Schedules: assign a teacher → teacher sees the class in Gradebook / Attendance.
- [ ] Teacher opens class attendance → student enters the code → parent phone gets the push.
- [ ] Teacher uploads a material (published) → student sees it; Draft → Publish works.
- [ ] Gradebook: encode a grade → student sees it.
- [ ] Face kiosk: adviser shows QR → Pi registers → Face Verification shows "Registered".
- [ ] Pi gate: wrong `X-Device-Key` → `401`; right key → arrival push to parent.
- [ ] Upload a file, redeploy, file still opens (volume works).

## 6. Defense day

- Keep XAMPP + the backup DB ready as a fallback if Railway or the school Wi-Fi fails.
- Pi `config.py`: `API_BASE` = the live URL (or the laptop IP for the local fallback) and the same `DEVICE_KEY`.
- Rollback: in Railway → Deployments, redeploy the previous successful deployment.
  Migrations here only add tables/columns, so the old code still runs on the new schema.
