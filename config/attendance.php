<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Attendance Device Key
    |--------------------------------------------------------------------------
    | Shared secret that the Raspberry Pi (or any IoT device) must send in the
    | "X-Device-Key" header (gate attendance, approved faces, face kiosk).
    |
    | EMPTY: allowed ONLY when APP_ENV=local (XAMPP testing). Everywhere else every
    | device call is refused (401) until ATTENDANCE_DEVICE_KEY is set — use a long
    | random string and the SAME value in the Pi's config.py (DEVICE_KEY).
    */
    'device_key' => env('ATTENDANCE_DEVICE_KEY', ''),

    /*
    | Scan AFTER this time (24h, server timezone) = "late"; on or before = "present".
    */
    'late_after' => env('ATTENDANCE_LATE_AFTER', '07:30'),

    /*
    |--------------------------------------------------------------------------
    | Face self-upload (phone / web)
    |--------------------------------------------------------------------------
    | false (default): faces are registered ONLY at the Raspberry Pi kiosk with a
    | one-time QR from the adviser/admin — identity checked in person, same camera
    | as the gate, no admin review queue. true: students may also upload selfies,
    | which then wait for admin review in Face Verification.
    */
    'face_self_upload' => (bool) env('FACE_SELF_UPLOAD', false),
];
