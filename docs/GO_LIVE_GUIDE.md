# DP-LMS go-live guide

Follow this from top to bottom after paying for Railway. Each part ends with a check. Do not move on until the check passes.

`docs/DEPLOY_CHECKLIST.md` has the longer reference for the same steps. Prices and limits below were checked on 2026-11-05, so confirm them on the provider's page before paying.

| Part | What | Time |
|---|---|---|
| 1 | Brevo account for email | 20 minutes |
| 2 | Railway plan and variables | 30 minutes |
| 3 | Deploy the code | 15 minutes |
| 4 | First commands on the server | 10 minutes |
| 5 | Load the school lists | 30 minutes |
| 6 | Test the live site | 30 minutes |
| 7 | Raspberry Pi at the school | 1 to 2 hours |

## What you pay for

| Service | Plan | Cost | Notes |
|---|---|---|---|
| Railway | Hobby | $5 a month, which includes $5 of usage | Usage above $5 is billed. Check Railway's Usage page after the first week and set a usage limit. |
| Brevo | Free | $0, no card needed | 300 emails a day. Read the limit below. |

Railway Hobby blocks normal email sending (SMTP), and that block is only lifted on Pro. DP-LMS does not use SMTP. It sends through Brevo's HTTPS API, so Hobby is enough.

**The 300 a day limit matters on activation days.** One student can use up to 3 emails in a day: activation code, login code, enrollment approval. Do not ask the whole school to activate on one day. Open it **one or two sections a day**. If the limit is reached, codes stop arriving until the next day, and nothing on the page says why.

Pay for a Brevo plan only if the school regularly goes above 300 emails a day.

---

## Part 1. Brevo (email)

1. Create a free account at brevo.com. Use the email address the school will keep.
2. Go to **Senders, Domains and Dedicated IPs → Senders** and add the sender address. Open the confirmation email and confirm it.
3. Go to **SMTP & API → API Keys** and create a key. Copy it now, because it is shown once. It goes into Railway in Part 2. Do not paste it in a chat or a document.
4. **Turn off IP blocking. This step is easy to miss.** Go to **Settings → Security → Authorized IPs** and choose **Deactivate blocking**. After 30 days Brevo starts refusing API calls from addresses it does not know, and Railway's address changes. If you skip this, login codes work for a month and then stop. The menu names may differ slightly in your account.

**About the sender address.** Brevo cannot authenticate `@gmail.com` or other free addresses, because only the owner of a domain can do that. A Gmail sender can still be used for the school's first run, but Brevo may replace it with one of its own addresses and some mail may land in spam. If the school owns a domain, authenticate it under **Senders, Domains and Dedicated IPs → Domains** and send from it.

**Check:** the sender shows as verified and you have the key.

---

## Part 2. Railway

1. Upgrade to **Hobby**.
2. The project needs three services:

| Service | Source | Start command | Public domain |
|---|---|---|---|
| `web` | this repo, branch `main` | keep the current one; it must run `php artisan storage:link` before the server starts | yes |
| `scheduler` | same repo, branch `main` | `php artisan schedule:work` | no |
| `MySQL` | Railway database | none | no |

3. On `web`, set the **Pre-deploy command** to `php artisan migrate --force`.
4. On `web`, add a **Volume** mounted at `/app/storage/app/public`. Without it, every deploy deletes uploaded files and face photos.
5. Make the device key on the laptop and keep it for step 6 and Part 7:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

6. Add these variables as shared variables so `web` and `scheduler` both get them:

| Variable | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | keep the existing one; if there is none, run `php artisan key:generate --show` |
| `APP_URL` | `https://<your-app>.up.railway.app` with no slash at the end. The logo in every email is loaded from here. |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | reference the MySQL service |
| `SESSION_SECURE_COOKIE` | `true` |
| `MAIL_MAILER` | `brevo` |
| `BREVO_API_KEY` | the key from Part 1 |
| `MAIL_FROM_ADDRESS` | the sender you verified in Part 1 |
| `MAIL_FROM_NAME` | `Sto. Domingo NHS` |
| `ATTENDANCE_DEVICE_KEY` | the device key from step 5 |
| `FIREBASE_CREDENTIALS_BASE64` | base64 of the Firebase service account file (`DEPLOY_CHECKLIST.md` shows the command) |
| `FACE_SELF_UPLOAD` | `false` |
| `ADMIN_SEED_EMAIL`, `ADMIN_SEED_PASSWORD` | first seed only; delete the password afterwards |

**Check:** Railway shows all three services and the volume is attached to `web`.

---

## Part 3. Deploy

Pushing to `main` deploys the site, so do this only after Parts 1 and 2.

1. Back up the local database:

```bash
C:\xampp\mysql\bin\mysqldump.exe -u root dp_lms > dp_lms_backup.sql
```

2. Make sure everything is pushed to `feature/ui-refresh`, then merge:

```bash
git checkout main
git pull origin main
git merge --ff-only feature/ui-refresh
git push origin main
git checkout feature/ui-refresh
```

**Check:** the `web` deploy finishes in Railway and `https://<your-app>.up.railway.app/up` opens.

---

## Part 4. First commands

Run these in **Railway → web → Shell**:

```bash
php artisan migrate:status
php artisan db:seed --force
php artisan storage:link
php artisan schedule:list
php artisan mail:check your.own.email@gmail.com
```

- Every migration should say **Ran**.
- `mail:check` sends a sample code email and prints the answer from Brevo. `Accepted for delivery` means Brevo took it. Anything else tells you what is wrong (see Troubleshooting).

Open the email and confirm that the school logo shows and that it did not land in spam.

Sign in to the live admin page and change the admin password. Delete `ADMIN_SEED_PASSWORD` from Railway.

**Check:** you received the sample email and the logo appears.

---

## Part 5. Load the school lists

The live database starts without the school's data. Nothing imported on the laptop goes with the code. The files are in `Documents/DP-LMS-go-live` and stay out of the repository because they hold real names.

1. **Sections & Advisers:** add every section first. The list is in `DEPLOY_CHECKLIST.md` part 4b, including Grade 11 ACADEMIC and TECHPRO.
2. **Faculty Master List → Import list:** `1-Faculty-import.xlsx`. Expect 24 ready.
3. **LRN Master List → Import list:** `2-JHS-LRN-import.xlsx`. Leave the grade empty. Expect 253 ready.
4. **LRN Master List → Import list:** `3-SHS-LRN-import.xlsx`. Leave the grade empty. Expect 117 ready.
5. Add the students from the two `to-check-with-advisers` files one by one after their advisers confirm the LRN.
6. Run this once, after the teachers are imported:

```bash
php artisan db:seed --class=AdvisorySectionSeeder --force
```

7. In **Sections & Advisers**, remove the sample sections the school does not use. The first seed adds sample names such as STEM-A, ABM-A and HUMSS-A. A section with students in it should not be removed.
8. After they activate, set the second advisory of Abegail Gabriel (Grade 11 ALS) and Roumella Sampilo (Grade 12 HUMSS EXPLORER) in **Sections & Advisers**.
9. Add class schedules in **Class Schedules**. Teachers see their classes only after this.

**Check:** the counts match. Fix anything marked "to fix" before confirming an import.

---

## Part 6. Test the live site

Use one real teacher and one real student. Those accounts stay.

- [ ] Teacher activates with their employee ID and gets the code by email.
- [ ] Student activates with their LRN and gets the code by email.
- [ ] Student sends the enrollment form, the adviser approves it, and the student gets the approval email with the logo.
- [ ] **Google Meet:** the teacher pastes a link in Google Meet. The student sees **Join Google Meet** on the dashboard and a student from another section does not.
- [ ] **Grade percentages:** in Gradebook the teacher picks a class and quarter, then types the percentage beside Written works, Performance tasks and Quarterly assessment. The total must be 100, and the Final updates as they type.
- [ ] **Save grade** saves the percentages with the grade and locks that class and quarter. Other quarters stay open.
- [ ] Teacher sends attendance, the student checks in with one tap, and the parent's phone gets a notification.
- [ ] Upload a file, redeploy, and confirm the file still opens. This proves the volume works.

---

## Part 7. Raspberry Pi at the school

The Pi talks to the live site over the internet, the same as any phone. It does not need the laptop, a local IP address, or the school network. Any internet connection works: pocket Wi-Fi, a phone hotspot, or school Wi-Fi.

The kiosk (`face_kiosk.py`) and the gate camera (`gate_camera.py`) share one camera, so **run only one at a time**.

### Do this at home first

1. On the Pi, open the config:

```bash
cd ~/dplms
nano config.py
```

2. Set these two lines. Use the same key as `ATTENDANCE_DEVICE_KEY` in Railway:

```python
API_BASE = "https://<your-app>.up.railway.app"
DEVICE_KEY = "the same long key"
```

   There must be no slash at the end of `API_BASE`. `config.py` holds the key, so it is never committed.

3. Save the Wi-Fi that will be used at the school. Do this for the pocket Wi-Fi and for the backup phone hotspot, turning each one on first:

```bash
sudo nmcli dev wifi connect "NETWORK NAME" password "NETWORK PASSWORD"
sudo nmcli connection modify "NETWORK NAME" connection.autoconnect yes connection.autoconnect-priority 10
```

   Give the pocket Wi-Fi a higher priority number than the phone hotspot. To see saved networks: `nmcli connection show`.

4. Test the connection and the key:

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://<your-app>.up.railway.app/up
curl -s -o /dev/null -w "%{http_code}\n" -H "Accept: application/json" -H "X-Device-Key: WRONG" https://<your-app>.up.railway.app/api/faces/approved
curl -s -o /dev/null -w "%{http_code}\n" -H "Accept: application/json" -H "X-Device-Key: THE-REAL-KEY" https://<your-app>.up.railway.app/api/faces/approved
```

   Expect `200`, then `401`, then `200`.

5. Register one student with the kiosk, then start the gate and let the Pi download the faces while it is on a strong connection:

```bash
cd ~/dplms
DISPLAY=:0 python3 face_kiosk.py
DISPLAY=:0 python3 gate_camera.py
```

   Run them one after the other. Remove `DISPLAY=:0` if you are typing on the Pi itself. Press `Ctrl+C` to stop either program.

**Check:** the gate says `Gate ready` and shows the live server address.

### At the school

1. Mount the camera at face height, about 1.2 to 1.5 m, with light in front of the face. Do not point it at a bright doorway.
2. Use the official 27 W power supply and an extension cord.
3. Turn on the pocket Wi-Fi first, then the Pi. It connects by itself.
4. On a phone hotspot, turn off the setting that shuts the hotspot down when idle, and keep the phone plugged in.
5. Start the gate:

```bash
cd ~/dplms
python3 gate_camera.py
```

6. Scan the test student once. The screen should say Welcome, and the parent's phone should get the arrival notification.

**Check:** the scan appears under **School arrivals** on the parent's Child's Records page, with the right time.

### Good to know

- **The internet drops:** the gate keeps working with the faces it already has. Scans wait in `gate_queue.jsonl` and go out when the connection returns, with their real time, so on time and late stay correct.
- **New students:** the gate checks for new faces every 10 minutes.
- **Mobile data:** each student's photos are downloaded once and then kept on the Pi. Let the first download finish on a strong connection, then daily use is small. Registering students at the kiosk uploads photos, so do big registration days on a connection with plenty of data.
- **Start on boot (optional, not yet tried on this Pi, so test it at home first):**

```bash
mkdir -p ~/.config/autostart
cat > ~/.config/autostart/dplms-gate.desktop <<'EOF'
[Desktop Entry]
Type=Application
Name=DP-LMS Gate
Exec=bash -c "sleep 20; cd ~/dplms && python3 gate_camera.py >> gate.log 2>&1"
EOF
```

  To use the kiosk, stop the gate first, because they share the camera.

---

## Troubleshooting

| What you see | What to do |
|---|---|
| `mail:check` says `401` | The `BREVO_API_KEY` is wrong, or Brevo is blocking the address. Check the key, then do Part 1 step 4. |
| `mail:check` mentions the sender | Verify `MAIL_FROM_ADDRESS` in Brevo. It must match exactly. |
| `mail:check` says `Mailer: log` | `MAIL_MAILER` is not `brevo`. Fix the variable on `web`. |
| Codes stopped arriving today | Check Brevo's statistics. The 300 a day limit may be used up. It resets the next day. |
| Codes never arrive for anyone | In Railway logs, search for `OTP email failed`. It shows Brevo's answer. |
| Email arrives but the logo is missing | `APP_URL` must be the live https address with no slash at the end. |
| Email lands in spam | Mark it "Not spam" for now. The lasting fix is a school domain (Part 1). |
| Uploaded files vanish after a deploy | The volume is missing or mounted at the wrong path. |
| Absent is not marked after class | The `scheduler` service is not running. |
| Pi says `Unauthorized device` | `DEVICE_KEY` and `ATTENDANCE_DEVICE_KEY` differ. Copy the key again, with no spaces. |
| Pi `curl` cannot reach the site | Check the Wi-Fi with `nmcli connection show --active`, and open the site on a phone on the same network. |
| Pi HTTPS error about a certificate | The Pi clock is wrong. Connect to the internet, then run `timedatectl`. |
| Gate stays on `Loading faces...` | The first start needs the internet. Check `API_BASE`. |
| More Pi problems | `docs/RASPBERRY_PI_FACE_KIOSK.md` part 6 |
| A deploy breaks the site | In Railway → Deployments, redeploy the last good one. Migrations only add tables and columns, so the old code still runs. |
