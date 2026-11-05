# Task for the Flutter app: going live with the school's real lists

> Paste this whole file into the Claude Code session of the Flutter project (`dp_lms`).
> Backend: Laravel `dp-lms-web`. Goal: real teachers and students activate from the app the same way as on the website.

The school's teacher list and student LRN list are loaded by the admin on the live site. **No account is created for
anyone**: each person activates their own. There is no sign-up without being on a list. No API changed for this.

---

## 1. Point the app to the live server

- Keep the base URL in one configuration value, and use the Railway HTTPS address for release builds.
- Do not ship a build that points to `127.0.0.1` or a local network address.
- Do not hardcode test accounts, LRNs or employee IDs anywhere in the app.

## 2. First screen for someone with no account

Offer three clear paths; there is no "Register":

| Who | Screen | Call |
|---|---|---|
| Student | Activate with LRN | `POST /api/activate`, then `POST /api/activate/verify` |
| Teacher | Activate with employee ID | `POST /api/teacher/activate`, then `POST /api/teacher/activate/verify` |
| Parent | Link with the child's code | the parent activation screen you already have |

Exact fields: `FLUTTER_HANDOFF.md` and `FLUTTER_PROMPT_TERMS_TEACHER_ENROLLMENT.md`.

## 3. What people will type, and what to do with it

- **LRN:** 12 digits. Numeric keyboard; allow no other characters.
- **Employee ID:** capitals, digits and dashes. Convert to upper case as they type. Do not force a number keyboard.
- **Name:** any order works ("Ana Lopez Mendoza" or "Mendoza, Ana Lopez"); two words must match the list.
- The account keeps the **school's spelling**. After verify, show `user.name` from the response, never what was typed.
- Show the server's message as it is (`message` or the first `errors.<field>`). It already says what to do, for example when
  the LRN and name do not match, or when the account was already activated.
- After activation a student goes to the enrollment form. Their grade and section come from the list, so do not ask for
  them. The form goes to the adviser of their listed section.

## 4. Test on the live server before release

Use one real teacher and one real student, with their permission.

- [ ] Wrong LRN or name: the server's message shows, and nothing crashes.
- [ ] Student activates, gets the emailed code, and lands on the enrollment form.
- [ ] Teacher activates and sees their advisory section.
- [ ] The adviser sees the form and approves it; the student's inbox shows `Your enrollment was approved`.
- [ ] Signing in again on a new phone asks for the emailed code.
- [ ] No address or account from testing is left in the release build.
