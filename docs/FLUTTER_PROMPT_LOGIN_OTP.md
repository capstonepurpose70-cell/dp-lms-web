# Task for the Flutter app: email code at login

> Paste this whole file into the Claude Code session of the Flutter project (`dp_lms`).
> Backend: Laravel `dp-lms-web`, branch `feature/ui-refresh`. Everything below runs on the local server and is covered
> by automated tests (2026-11-06). Goal: the app signs in the same way as the website.

Until now the app signed in with the password alone. Now a correct password sends a 6-digit code to the person's email,
and the app gets its token only after that code is entered. This replaces "no OTP on mobile login" in `FLUTTER_HANDOFF.md`.

The server sends the email. **The app needs no email package, no API key and no mail settings.** It only needs one new screen.

---

## 0. Before writing code

1. Search the project for the login screen, the API client, the token store and what runs after login (FCM token, Terms
   screen). Extend them; do not duplicate.
2. Show server messages as they come (`message`, or the first item of `errors.<field>`).

---

## 1. The new login

### `POST /api/login`

```json
{ "email": "<email, 12-digit LRN or 09XXXXXXXXX>", "password": "...", "device_token": "<saved token, if any>" }
```

| Answer | Meaning | What the app does |
|---|---|---|
| `200` with `token` and `user` | Signed in. This phone is remembered. | Same as before: save the token, register FCM, check Terms. |
| `200` with `otp_required: true` | Password is right, code sent | Open the code screen |
| `401` | Wrong login or password | Show `message` |
| `403` with `status: "new_device"` | A parent with no email, on a new phone | Show `message` and a button to Parent recovery |
| `403` with another `status`, or no `status` | Not approved, inactive, or temporary password | Show `message` |
| `429` | Too many wrong passwords (5 tries, 30 minutes) | Show `message` |

The code answer:

```json
{ "otp_required": true, "otp_token": "…", "email_hint": "j***@gmail.com", "expires_in": 300, "message": "We sent a 6-digit code to your email." }
```

Always check `otp_required` first. A `200` no longer means there is a `token`.

### Code screen

- Title: **Check your email**. Text: "We sent a 6-digit code to `email_hint`."
- A 6-digit number field with `AutofillHints.oneTimeCode`, so pasting and auto-fill work.
- A countdown from `expires_in`.
- A checkbox, off by default: **Remember this phone for 30 days**. Below it: "Do not check this on a shared phone."
- **Send a new code**, available 60 seconds after the last one.
- A hint: "Didn't get it? Check your spam folder."
- **Back to sign in**, which drops the `otp_token`.

### `POST /api/login/verify`

```json
{ "otp_token": "…", "code": "482913", "remember_device": true }
```

| Answer | What the app does |
|---|---|
| `200` with `token`, `user`, and `device_token` if asked | Same as a normal login. Save `device_token` (part 2). |
| `422` with `errors.code` | Wrong or expired code: show it under the field and stay on the screen |
| `422` with `message` and no `errors` | The login expired: show it and go back to sign in |
| `429` | Too many tries: show `message` and go back to sign in |
| `403` | The account was blocked meanwhile: show `message` and go back to sign in |

### `POST /api/login/resend`

```json
{ "otp_token": "…" }
```

`200 { "message": "We sent a new code to your email.", "expires_in": 300 }`. Restart the countdown. The old code stops working.
`429` means too many codes were sent (5 an hour): show `message` and stay, because the last code still works.
`422` means the login expired: go back to sign in.

**Security:** keep `otp_token` in memory only, and never log the code, `otp_token` or `device_token`.

---

## 2. Remembered phones

- Save `device_token` in secure storage (`flutter_secure_storage`), never in plain preferences.
- Key it by the login text. A parent and child often share one phone, so one phone can hold more than one token.
- Send it as `device_token` in `POST /api/login` for that same login.
- **Keep it on logout**, so the next login on this phone needs no code.
- It lasts 30 days. If it expired or was cancelled, the server simply asks for a code. Save the new one if a new one comes.
- A password change or account recovery cancels it on the server. Nothing special is needed in the app.

---

## 3. Parents

`POST /api/parent/activate` and `POST /api/parent/recover` now also answer with `device_token`. **Save it** as in part 2,
using the mobile number as the key. Many parents have no email, so this token is what lets them sign in again on this phone.

On a new phone, or after 30 days, a parent with no email gets `403` with `status: "new_device"`. Open Parent recovery, where
they use the code from their child's account. A parent who has an email gets the normal code screen.

---

## 4. For testing

- Local server: with `MAIL_MAILER=log` the code is not sent. Get one with `php artisan demo:otp <login>`.
- The server has a switch, `API_LOGIN_OTP`. It is on by default. With `API_LOGIN_OTP=false`, `/api/login` answers with the
  `token` directly, as it did before. The website team keeps it off only until this update is in the released app.
- Handle both answers from `/api/login`. Do not remove the normal `token` case.

---

## 5. Checklist

- [ ] Right password shows the code screen with the masked email.
- [ ] Right code signs in, and FCM and Terms run the same as before.
- [ ] Wrong code shows the error and stays on the screen.
- [ ] Send a new code works, and the old code is refused.
- [ ] Remember this phone: logging out and in again asks for no code.
- [ ] Without it, the next login asks for a code.
- [ ] A parent with no email signs in on the activated phone, and sees Parent recovery on a new phone.
- [ ] `otp_token`, `device_token` and codes never appear in logs.
