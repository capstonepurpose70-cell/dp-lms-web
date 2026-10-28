# Flutter app: start here

> Paste this whole file into the Claude Code session (or give it to the developer) of the Flutter project
> (`dp_lms`). Copy the files listed in §1 into that project's `docs/` folder first, so the session can
> read them. Backend: Laravel `dp-lms-web`, branch `feature/ui-refresh`. Last checked against the server
> on 2026-10-28.

---

## 0. Ground rules

1. **The server is the source of truth.** Use only the endpoints and JSON fields written in the documents
   below. If the app calls something that is not there, or a field is missing, stop and list it in your
   report instead of guessing or adding a workaround.
2. **Search before you write.** Look for existing services, models, screens and packages
   (`firebase_messaging`, `flutter_local_notifications`, the HTTP client, the auth/token store) and extend
   them. Do not create a second HTTP client, a second FCM setup or duplicate models.
3. **Send `Accept: application/json` on every request** and `Authorization: Bearer <token>` on every
   protected one.
4. **Show the server's own messages.** Every error has `message` and, for validation, `errors.<field>`.
   Display them as they are; do not hardcode your own wording.
5. Follow the project's existing state management, folder structure and naming.

---

## 1. What to read, in this order

| # | File | What it covers |
|---|---|---|
| 1 | `FLUTTER_HANDOFF.md` | Every backend change the app must follow: activation instead of register, login, forgot password, parent link (QR + code), parent activate/recover, quizzes, materials, enrollment form, grades, teacher endpoints, chat (typing, edit, unsend, bad-word filter), face status. Ends with a checklist (§9). |
| 2 | `FLUTTER_PROMPT_PARENT_ARRIVAL.md` | **Core feature.** FCM setup, token register/unregister, the "child arrived at school" push, foreground display, tap handling, notification inbox, arrival history. |
| 3 | `FLUTTER_PROMPT_CLASS_ATTENDANCE.md` | Teacher opens class attendance, students enter the 4-digit code, parents are notified. Reuses the FCM setup from #2. |
| 4 | `FLUTTER_PROMPT_FACE_KIOSK_QR.md` | Teacher (adviser) shows a one-time QR on the phone; the student holds it up to the Raspberry Pi kiosk to register their face. Same as the website. |
| 5 | `IOT_ARRIVAL_NOTIFICATIONS.md` | Reference: the full Raspberry Pi → server → parent flow, when no push is sent, and how to test end to end without the gate camera (§4). |

---

## 2. Work in this order

**P0. Session handling** (everything else depends on it)
- `401` on any protected call: the token is gone (logged out elsewhere, or the account was **recovered
  on another device** — recovery now ends every older login). Clear the stored token and go to login.
- `403 { "message": "Your account is not active. Please contact the school." }`: the account was rejected
  or banned while signed in. The server already deleted the token. Clear it and go to login with that message.
- Logout, in this order: `DELETE /api/fcm-token` → `POST /api/logout` → clear the token, cached user and
  any cached screens → open a **fresh** login screen with empty fields. If either call fails (offline),
  still clear everything locally.

**P0. Parent arrival notification** — all of `FLUTTER_PROMPT_PARENT_ARRIVAL.md`.
- Register the FCM token right after login **and** on every token refresh (`POST /api/fcm-token`).
- The push must work with the app in the foreground, background and closed.
- Tapping it opens the child's arrival history.

**P1. Face registration screen (student)** — `FLUTTER_HANDOFF.md` §8.1.
- Faces are registered only at the school kiosk with the adviser. When `GET /api/student/face` returns
  `self_upload: false`, hide the camera/upload screen and show only the status (`approved` → registered;
  otherwise show the server's `how_to` text).

**P1. Face registration QR (teacher)** — all of `FLUTTER_PROMPT_FACE_KIOSK_QR.md`.

**P1. Class attendance** — all of `FLUTTER_PROMPT_CLASS_ATTENDANCE.md`.

**P2. The rest of the checklist** in `FLUTTER_HANDOFF.md` §9.

---

## 3. Changes since `FLUTTER_HANDOFF.md` was written (2026-10-27 → 2026-10-28)

| Change | What the app does |
|---|---|
| Account recovery and password reset now end **every** older login (app tokens and web sessions) | Handle `401` as in P0. No new endpoint. |
| Parent averages (`/api/parent/*`) now use the same formula as the student's grades page | Nothing; same JSON shape, the numbers now match the student app. |
| `enrollment_status` in `/api/parent/*` is still only `"enrolled"` / `"not enrolled"` | Keep using those two values. Pending/returned will be added later and announced. |
| Gate camera (face recognition at the entrance) is being finished on the Raspberry Pi | Nothing changes for the app: the push payload and `/api/parent/attendance` stay as documented. Test with the method in `IOT_ARRIVAL_NOTIFICATIONS.md` §4. |

---

## 4. Done means

- [ ] Every item in `FLUTTER_HANDOFF.md` §9 works against the real server (not mocks).
- [ ] Parent phone receives the arrival push within a few seconds of `POST /api/attendance`, with the app
      in foreground, background and closed; tapping it opens the arrival history.
- [ ] Logging out then pressing Back never shows the previous account or its typed password.
- [ ] Recovering a parent account on phone B sends phone A to the login screen on its next request.
- [ ] Every error shown to the user is the server's `message` / `errors.*` text.

## 5. Report back

When you finish, write a short report with:
1. What was implemented, per section above.
2. Any endpoint or field the app needs that is **not** in these documents (name, method, why).
3. Anything in these documents that did not match the real server response (paste the response).
