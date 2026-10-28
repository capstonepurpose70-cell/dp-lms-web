# Copy to config.py on the Raspberry Pi and fill in. NEVER commit config.py (it holds the device key).

# Base URL of the DP-LMS server (no trailing slash), e.g. "https://dp-lms.up.railway.app"
# or for local testing "http://192.168.1.10:8000" (php artisan serve --host=0.0.0.0 --port=8000)
API_BASE = "https://YOUR-SERVER"

# Must equal ATTENDANCE_DEVICE_KEY on the server (.env locally, Railway variables in production)
DEVICE_KEY = "PUT-THE-SAME-LONG-RANDOM-KEY-HERE"

# Face kiosk options
PHOTOS = 10            # photos per student (server accepts 3–15)
CAPTURE_TIMEOUT = 40   # seconds to collect the photos before giving up
SHOW_WINDOW = True     # False if the Pi runs without a desktop/monitor
FULLSCREEN = True      # kiosk window fills the 5-inch screen
COLOR_FIX = True       # white balance for cameras without an IR filter (violet pictures)
ROTATE_180 = False     # True if the camera is mounted upside down (picture is inverted)

# Photo quality — only change these if good photos keep getting refused (lower) or blurry ones get in (raise)
SHARP_MIN = 25         # sharpness below this is never used (the sharpest PHOTOS frames are kept anyway)
SHARP_GOOD = 60        # PHOTOS frames this sharp finish the capture early
SHARP_RELATIVE = 0.6   # also drop frames below 60% of the sharpest one (the head moved)
BRIGHT_MIN = 45        # face brightness 0–255 ("More light on your face" below this)

# Gate camera (gate_camera.py)
DEVICE_NAME = "gate-1"   # shows in the recognition log; use a different name per camera
MATCH_THRESHOLD = 0.40   # similarity needed to recognize (raise if students get mixed up, lower if often "Not recognized")
MATCH_FRAMES = 3         # same student this many frames in a row before checking in
UNKNOWN_FRAMES = 10      # frames of an unmatched face before "Not recognized"
GATE_FACE_MIN = 0.12     # ignore faces smaller than this share of the picture height (people far behind)
COOLDOWN_SECONDS = 30    # the same student is not sent again within this
RESULT_SECONDS = 3       # how long "Welcome, Juan!" stays on screen
REFRESH_MINUTES = 10     # reload approved faces (new kiosk registrations)
