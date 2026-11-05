# Task for the Flutter app: Google Meet links and grade percentages

> Paste this whole file into the Claude Code session of the Flutter project (`dp_lms`).
> Backend: Laravel `dp-lms-web`, branch `feature/ui-refresh`. Everything below runs on the local server and is covered
> by automated tests (2026-11-05). Goal: the app behaves the same as the website.

---

## 0. Before writing code

1. Search the project for the student dashboard, the teacher gradebook, the grades models, the notification inbox and
   the API client. Extend them; do not duplicate.
2. Show server messages as they come (`message`, or the first item of `errors.<field>`).
3. All changes on the server are additions, so an app that is not updated yet keeps working.

---

## 1. Google Meet links

There is one link for each subject of a section. The teacher assigned to that class manages it.

### Student

`GET /api/student/meet-links` returns the links of the student's own section this term. It can be empty.

```json
[
  { "id": 12, "subject": "Science", "teacher": "Dela Cruz, Juan", "url": "https://meet.google.com/abc-defg-hij", "updated_at": "2026-11-05T19:02:11+08:00" }
]
```

- Show a card near the top of Home titled **Online classes**, one row per link: subject, teacher, and a **Join Google Meet** button.
- The button opens `url` outside the app (`url_launcher`, external application mode).
- Hide the card when the list is empty. `teacher` can be `null`: show "Teacher to be assigned".
- Reload the list when Home opens and on pull to refresh.

### Teacher

| Call | Body | Answer |
|---|---|---|
| `GET /api/teacher/meet-links` | | Every class of the teacher (below) |
| `POST /api/teacher/meet-links` | `section_id`, `subject_id`, `url` | `200 { "message": "Link published.", "url": "https://meet.google.com/abc-defg-hij" }` |
| `DELETE /api/teacher/meet-links` | `section_id`, `subject_id` | `200 { "message": "Link removed." }` |

```json
[
  { "class": "37-4", "section_id": 37, "subject_id": 4, "subject": "Science", "section": "RIZAL", "grade_level": "7", "url": null }
]
```

- `url` is `null` until the teacher publishes one.
- Saving again replaces the link. Use the `url` from the answer, because the server cleans it (it drops `?authuser=0`).
- The server accepts a full Meet link, `meet.google.com/abc-defg-hij`, or just `abc-defg-hij`.
- Errors: `422 errors.url` when it is not a Google Meet link, `403` when the teacher does not teach that class.
- Ask for confirmation before removing.

### Notification

When a link is published or changed, the section gets an inbox entry and a push:

| Where | Value |
|---|---|
| `GET /api/notifications` | `type: "meet"`, `title: "Google Meet link for Science"`, `data.subject: "Online class"`, `data.url: "/student/dashboard"` |
| Push data | `type: "meet"`, `id` |

Open Home when it is tapped. Unknown `type` values must already show with a default icon.

---

## 2. Grade percentages

Written works, performance tasks and quarterly assessment count **25, 50 and 25** unless the teacher changes them.
Percentages belong to one subject of one section in one quarter.

**Remove any hard-coded 0.25, 0.50 or 0.25 from the app.** The server computes every final grade. A grade preview in
the app must use the percentages of that class and quarter.

### Teacher

`GET /api/teacher/grade-weights?section_id=37&subject_id=4` returns the four quarters:

```json
[
  { "quarter": "Q1", "written_works": 40, "performance_tasks": 40, "quarterly_assessment": 20, "locked": true },
  { "quarter": "Q2", "written_works": 25, "performance_tasks": 50, "quarterly_assessment": 25, "locked": false }
]
```

`POST /api/teacher/grade-weights`

```json
{ "section_id": 37, "subject_id": 4, "quarter": "Q2", "written_works": 40, "performance_tasks": 40, "quarterly_assessment": 20, "all_quarters": false }
```

Answer: `200 { "message": "Percentages saved.", "saved": ["Q2"] }`

| Rule | Server answer |
|---|---|
| Whole numbers from 0 to 100 | `422` with `errors.<field>` |
| The three must add up to exactly 100 | `422` with `errors.written_works` |
| `locked: true`: a grade already exists for that class and quarter | `422` with `message` |
| The teacher does not teach that class | `403` |

- `all_quarters: true` saves every quarter that has no grade yet and skips the locked ones. `saved` lists the quarters that changed.
- Show a live total and disable Save unless it is 100.
- Show a locked quarter as read only, with: "Locked because this class already has grades in that quarter."

### Grades in the API

`weights` was added to each row of `GET /api/teacher/gradebook` and `GET /api/student/grades`:

```json
{ "quarter": "Q1", "written_works": "80.00", "final_grade": "82.00", "weights": { "written_works": 40, "performance_tasks": 40, "quarterly_assessment": 20 } }
```

These are the percentages that grade was computed with. Older grades report 25, 50 and 25. Use them wherever the app
shows a score breakdown. Grades in one list can have different percentages, so do not read one set for the whole list.

`POST /api/teacher/grades` is unchanged; the server applies the percentages of that class and quarter.

---

## 3. Checklist

- [ ] Home shows Online classes with Join Google Meet, and hides the card when there are no links.
- [ ] Join opens Google Meet outside the app.
- [ ] Teacher publishes, replaces and removes a link; a wrong link shows the server's message.
- [ ] `type: "meet"` shows in the inbox and opens Home.
- [ ] No hard-coded percentages remain in the app.
- [ ] Teacher sets percentages with a live total; Save is off unless the total is 100.
- [ ] A locked quarter is read only.
- [ ] Each grade's breakdown uses its own `weights`.
