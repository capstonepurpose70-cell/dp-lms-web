<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Attendance Device Key
    |--------------------------------------------------------------------------
    | Shared secret that the Raspberry Pi (or any IoT device) must send in the
    | "X-Device-Key" header when posting to POST /api/attendance.
    |
    | Leave EMPTY to disable the check (e.g. local LAN testing).
    | Set ATTENDANCE_DEVICE_KEY in your .env to a long random string before
    | hosting publicly, and use the SAME value in the Pi's config.py (DEVICE_KEY).
    */
    'device_key' => env('ATTENDANCE_DEVICE_KEY', ''),

    /*
    | Scan AFTER this time (24h, server timezone) = "late"; on or before = "present".
    */
    'late_after' => env('ATTENDANCE_LATE_AFTER', '07:30'),
];