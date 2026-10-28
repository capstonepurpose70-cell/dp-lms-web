# Task for the Flutter app: teacher "Face registration" QR for the kiosk

> Paste this whole file into the Claude Code session of the Flutter project (`dp_lms`).
> Backend: Laravel `dp-lms-web`. Endpoints verified against the running server on 2026-10-28
> (automated test: list → QR → the Raspberry Pi kiosk accepts the QR text → status → a newer QR cancels it).
> Same feature as the website's Teacher › Face Registration page; only advisers can use it.

---

## 0. Before writing code

1. Search the project for the teacher screens, the HTTP client (Dio provider), and `qr_flutter`
   (already used for the student's parent-link QR). Reuse them; do not add a second HTTP client or QR package.
2. Follow the project's existing state management, routing and folder structure.
3. Show the server's `message` text on errors; do not hardcode your own.

## 1. How it works (what the teacher does)

1. Teacher opens **Face registration** (teacher menu) and sees their advisory students with a status.
2. Taps **Show QR** next to a student. The phone shows a big QR, the student's name and a countdown (10 min).
3. The student holds the phone up to the Raspberry Pi kiosk camera. The kiosk takes the face photos.
4. The app sees the status change to `registered` and shows "Face registered" with a check mark.

The QR works once, for that student only. A new QR for the same student cancels the older one.

## 2. Endpoints (Sanctum token, `Accept: application/json`)

### `GET /api/teacher/face-kiosk`

```json
{
  "students": [
    { "id": 66, "name": "Juan Dela Cruz", "lrn": "123456789012", "grade_level": 7, "section": "Rizal",
      "face_status": "approved", "blocked": false }
  ],
  "registered": 1
}
```

- `face_status`: `"approved"` (registered) · `"pending"` · `"rejected"` · `null` (no face yet)
- `blocked: true` → 3 inappropriate-photo warnings; hide the button and show "Blocked, ask the admin".
- A teacher who is not an adviser gets `"students": []` → empty state:
  "No students to register. Only a section's adviser registers faces here."

### `POST /api/teacher/face-kiosk/{studentId}` (max 20 per minute)

`200`:
```json
{
  "ok": true,
  "code_id": 15,
  "qr_text": "DPLMS-FACE:Qd3k…40 characters",
  "student": "Juan Dela Cruz",
  "expires_at": "2026-10-28T18:40:00+08:00",
  "server_time": "2026-10-28T18:30:00+08:00"
}
```

- Draw **exactly `qr_text`** with `QrImageView(data: qrText, …)`. White background, at least ~260 px,
  quiet zone on. Do not add or trim anything.
- Countdown: `skew = now - parse(server_time)`; remaining = `parse(expires_at) - (now - skew)`. The phone clock
  may be wrong, so always use the skew.
- Errors: `403 { ok:false, message }` (not this teacher's advisory student), `422 { ok:false, message }`
  (not enrolled in a section, or blocked). Show `message`.

### `GET /api/teacher/face-kiosk/codes/{code_id}`

`{ "status": "waiting" | "registered" | "expired" | "cancelled" }` (`403 { status: "forbidden" }` if the code is
another teacher's).

- Poll every **3 seconds** while the QR screen is open; **stop** when it closes, when the app goes to the
  background, or on any final status.
- `registered` → replace the QR with a check mark and "Face registered for {student}". When the teacher goes
  back, reload the list so the status shows "Registered".
- `expired` → "This QR expired." + **Make a new QR** button (calls the POST again).
- `cancelled` → "A newer QR replaced this one." + **Make a new QR**.

## 3. Screens

**Face registration (list)**
- Header: "Face registration". One line under it: "Each QR works once, for one student, for 10 minutes."
- Progress line: "{registered} of {students.length} registered".
- One row per student: name, status chip (Registered / Waiting for review / Rejected / No face yet),
  button **Show QR** (or **Register again** when already registered). Buttons at least 44 px tall.
- Pull to refresh.

**QR screen**
- Student name, the QR, countdown "Expires in 9:41", state text under it.
- Raise the screen brightness while this screen is open and restore it on close
  (`screen_brightness` package, only if it is not already in the project; otherwise skip).
- Keep the screen awake while waiting if the project already has `wakelock_plus`; do not add it otherwise.
- Three short steps under the QR: "Hold this screen up to the kiosk camera." /
  "The kiosk greets the student and takes their photos." / "This screen turns green when the face is saved."

## 4. Done means

- [ ] Adviser sees their students; a non-adviser sees the empty state; a student account never sees this menu.
- [ ] Show QR → the Raspberry Pi kiosk accepts the QR from the phone screen and registers the face; the app
      shows "Face registered" without a manual refresh.
- [ ] Expired and replaced QRs show the right message and **Make a new QR** works.
- [ ] Polling stops when leaving the screen (check the server log: no more status calls).
- [ ] No hardcoded error text; the server's `message` is shown.

## 5. Report back

What was implemented, anything in the API that did not match (paste the response), and any extra field the
app needs.
