# Copy to config.py on the Raspberry Pi and fill in. NEVER commit config.py (it holds the device key).

# Base URL of the DP-LMS server (no trailing slash), e.g. "https://dp-lms.up.railway.app"
# or for local testing "http://192.168.1.10/dp-lms/dp-lms/public"
API_BASE = "https://YOUR-SERVER"

# Must equal ATTENDANCE_DEVICE_KEY on the server (.env locally, Railway variables in production)
DEVICE_KEY = "PUT-THE-SAME-LONG-RANDOM-KEY-HERE"

# Face kiosk options
PHOTOS = 10            # photos per student (server accepts 3–15)
CAPTURE_TIMEOUT = 40   # seconds to collect the photos before giving up
SHOW_WINDOW = True     # False if the Pi runs without a desktop/monitor
