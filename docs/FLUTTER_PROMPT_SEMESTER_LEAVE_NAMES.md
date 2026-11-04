# Task for the Flutter app: teachers who leave, names written surname first, Senior High semesters

> Paste this whole file into the Claude Code session of the Flutter project (`dp_lms`).
> Backend: Laravel `dp-lms-web`, branch `feature/ui-refresh`. Everything below runs on the local server and is
> covered by automated tests (2026-11-04). Goal: the app behaves the same as the website.

---

## 0. Before writing code

1. Search the project for the login screen, the session/401 handling, the API client and every place that shows a
   user's first name or avatar letter. Extend them; do not duplicate.
2. Show server messages as they come (`message`, or the first item of `errors.<field>`).

---

## 1. A teacher who left the school (retired, resigned, transferred)

The school admin marks the teacher as left on the website (Faculty Master List → **Mark as left**). At that moment:

- the account status becomes **`inactive`**;
- every app token of that teacher is deleted, so the next protected call answers **`401`**;
- their advisory section, classes and teaching load move to another teacher (or wait for a new one).

**Login answer for that account** — `POST /api/login`:

```json
403 { "message": "This account is no longer active. Please contact the school.", "status": "inactive" }
```

Every `403` from `/api/login` for a non-approved account now also carries `status`
(`"pending"`, `"rejected"` or `"inactive"`). The messages for pending and rejected are unchanged
(`"Account not yet approved."`).

**What the app does**
- `401` while signed in: the existing rule applies — clear the token, cached user and cached screens, open a fresh
  login screen.
- `403` on login: show `message` under the form. Do not offer "resend code", "activate" or "register" for
  `status: "inactive"`; the person has to contact the school.
- Teacher activation with the employee ID of a teacher who left is refused by the server (`422 errors.employee_id`);
  show the message as usual.
- If the admin reinstates the teacher, they sign in normally again. No app change needed.

There is nothing for the admin in the app; marking a teacher as left is done on the website only.

---

## 2. Names written surname first

School lists write names as **`Surname, Given names`** (for example `"Dela Cruz, Juan Reyes"`). The website greets
by the given name and uses two letters for the avatar. Use the same rule everywhere the app shows a greeting or an
avatar letter (teacher, student and parent screens):

| `name` from the API | Greeting name | Avatar letters |
|---|---|---|
| `Dela Cruz, Juan Reyes` | `Juan` | `JD` |
| `Maria Santos` | `Maria` | `MS` |
| `Juan` | `Juan` | `J` |
| empty | (greet without a name: "Good morning") | `?` |

Rule:
1. Trim the name. If it has a comma, the part **after** the first comma is the given name(s) and the part before is
   the surname.
2. Without a comma, the first word is the given name and the last word is the surname.
3. Greeting name = first word of the given name(s).
4. Avatar = first letter of the given name + first letter of the surname, upper case (one letter when there is no
   surname).

```dart
({String greeting, String initials}) nameParts(String? raw) {
  final name = (raw ?? '').trim();
  String given, surname;
  if (name.contains(',')) {
    final i = name.indexOf(',');
    surname = name.substring(0, i).trim();
    given = name.substring(i + 1).trim();
    if (given.isEmpty) { given = surname; surname = ''; }
  } else {
    final words = name.isEmpty ? <String>[] : name.split(RegExp(r'\s+'));
    given = words.isNotEmpty ? words.first : '';
    surname = words.length > 1 ? words.last : '';
  }
  final greeting = given.isEmpty ? '' : given.split(RegExp(r'\s+')).first;
  String first(String s) => s.isEmpty ? '' : s.characters.first;
  final initials = (first(given) + first(surname)).toUpperCase();
  return (greeting: greeting, initials: initials.isEmpty ? '?' : initials);
}
```

**Student activation now stores the school's spelling.** After `POST /api/activate/verify`, `user.name` is the name
from the school's LRN list (for example `"Mendoza, Ana Lopez"`), not what the student typed (`"Anna Mendoza Lopez"`).
Show `user.name` from the response and from later API calls; never keep the typed name in the app.

Always show the **full** `name` where the person is identified (profile, chat header, lists); the short form is
only for greetings and avatars. The API is unchanged — no new fields.

---

## 3. Senior High semesters (only if the app shows a class timetable)

The API has no timetable endpoint yet, so there is nothing to change today. For when one is added:

- A class row has `semester`: `null` = whole school year (Grade 7–10), `1` or `2` = Senior High semester.
- The school year has a current semester (set by the admin on the website: Class Schedules → "Switch students
  to the 2nd semester").
- A Grade 11/12 student sees only the current semester's classes plus whole-year classes. The website already
  filters this way; an API endpoint would return the same filtered list.

---

## 4. Student notifications: two new kinds in the inbox

`GET /api/notifications` now also lists these for students (same shape as the others):

| `type` | `title` | `data.url` on the web | Open in the app |
|---|---|---|---|
| `announcement` | the announcement's title (`data.subject` is `"Announcement"`, `data.instructor` the teacher) | `/student/dashboard` | Home / announcements |
| `enrollment` | `Your enrollment was approved` (`data.subject`: `Grade 7, PRACTICE`) | `/student/dashboard` | Home |
| `enrollment` | `Your enrollment form was returned` (`data.subject`: `Open it to see what to fix`) | `/student/enroll` | Enrollment form with the reason |

The announcement push is unchanged, so a student gets the push and an inbox entry. Unknown `type` values must not crash
the inbox: show the title with a default icon. `instructor` can be empty, so do not print a separator after `subject`.

---

## 5. Checklist

- [ ] `POST /api/login` `403` shows `message`; `status: "inactive"` hides activation/resend actions.
- [ ] `401` while signed in returns to a fresh login screen (existing rule).
- [ ] Greeting uses the given name; avatar shows two letters; "Dela Cruz, Juan Reyes" → "Juan", "JD".
- [ ] Full name still shown in profile, chat and lists.
- [ ] Inbox shows `announcement` and `enrollment` rows and opens the right screen.
