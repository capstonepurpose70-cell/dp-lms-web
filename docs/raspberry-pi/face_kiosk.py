#!/usr/bin/env python3
"""
DP-LMS face registration kiosk — Raspberry Pi 5 + Camera Module.

Flow:
  1. Adviser/admin clicks "Show QR" in DP-LMS for ONE student (valid 10 min, works once).
  2. Student holds the QR up to this camera.
  3. GET  /api/kiosk/face?code=...   -> "Hi Juan Dela Cruz!" (does not use the QR)
  4. Captures PHOTOS clear face photos (one steady face, centred, sharp, bright enough).
  5. POST /api/kiosk/face  code + images[]  -> saved to that student, already approved.
  6. The gate recognizer picks the new face up on its next GET /api/faces/approved.

Install (Raspberry Pi OS Bookworm or newer; the face detector XML files are in opencv-data):
  sudo apt update && sudo apt install -y python3-picamera2 python3-opencv opencv-data python3-requests
Run:
  python3 face_kiosk.py            (needs config.py next to this file)
  DISPLAY=:0 python3 face_kiosk.py (when started over SSH, to show it on the Pi's screen)
"""

import os
import sys
import time

import cv2
import numpy as np
import requests

try:
    import config  # config.py next to this file (see config.example.py)
except ImportError:
    sys.exit("Missing config.py — copy config.example.py to config.py and fill it in.")

API_BASE = config.API_BASE.rstrip("/")
HEADERS = {"X-Device-Key": config.DEVICE_KEY, "Accept": "application/json"}
PHOTOS = int(getattr(config, "PHOTOS", 10))            # server accepts 3–15
CAPTURE_TIMEOUT = int(getattr(config, "CAPTURE_TIMEOUT", 40))
SHOW_WINDOW = bool(getattr(config, "SHOW_WINDOW", True))  # False when running without a desktop
FULLSCREEN = bool(getattr(config, "FULLSCREEN", True))
COLOR_FIX = bool(getattr(config, "COLOR_FIX", True))      # NoIR cameras look violet without it
ROTATE_180 = bool(getattr(config, "ROTATE_180", False))   # camera mounted upside down

# Photo quality (sharpness = Laplacian variance of the face resized to 160x160; measured on the
# OV5647 fisheye: blurry 15–34, clear 80+)
SHARP_MIN = float(getattr(config, "SHARP_MIN", 25))       # below this a frame is not used at all
SHARP_GOOD = float(getattr(config, "SHARP_GOOD", 60))     # PHOTOS frames this sharp end the capture early
SHARP_RELATIVE = float(getattr(config, "SHARP_RELATIVE", 0.6))  # also drop frames below 60% of the session's sharpest (movement)
BRIGHT_MIN = float(getattr(config, "BRIGHT_MIN", 45))     # mean face brightness (0–255)
FACE_MIN = float(getattr(config, "FACE_MIN", 0.20))       # face height as a share of the frame height
FACE_MAX = float(getattr(config, "FACE_MAX", 0.75))
STEADY_FRAMES = int(getattr(config, "STEADY_FRAMES", 3))  # same face position this many frames in a row
COLLECT_SECONDS = float(getattr(config, "COLLECT_SECONDS", 8))  # keep looking for sharper frames this long
EXPOSURE_VALUE = float(getattr(config, "EXPOSURE_VALUE", 0.5))  # brighter faces against a bright background
MAX_EXPOSURE_US = int(getattr(config, "MAX_EXPOSURE_US", 33333))  # short shutter = less motion blur

QR_PREFIX = "DPLMS-FACE:"
WINDOW = "DP-LMS Face Kiosk"
GREEN, AMBER, RED, WHITE = (0, 200, 0), (0, 190, 255), (0, 0, 230), (255, 255, 255)


def find_cascade():
    """Frontal-face Haar cascade: pip OpenCV ships cv2.data; Debian's opencv-data installs it under /usr/share."""
    candidates = []
    if hasattr(cv2, "data"):
        candidates.append(os.path.join(cv2.data.haarcascades, "haarcascade_frontalface_default.xml"))
    candidates += [
        "/usr/share/opencv4/haarcascades/haarcascade_frontalface_default.xml",
        "/usr/share/opencv/haarcascades/haarcascade_frontalface_default.xml",
    ]
    for path in candidates:
        if os.path.exists(path):
            return cv2.CascadeClassifier(path)
    sys.exit("haarcascade_frontalface_default.xml not found — sudo apt install opencv-data")


def open_camera():
    from picamera2 import Picamera2  # imported here so the file can be syntax-checked off the Pi

    cam = Picamera2()
    # "RGB888" in Picamera2 = pixels in B,G,R order, which is what OpenCV expects.
    settings = {"main": {"size": (1280, 720), "format": "RGB888"}}
    if ROTATE_180:
        from libcamera import Transform  # the sensor flips the image itself — no extra CPU per frame
        settings["transform"] = Transform(hflip=1, vflip=1)
    cam.configure(cam.create_preview_configuration(**settings))
    cam.start()
    try:
        cam.set_controls({"FrameDurationLimits": (10000, MAX_EXPOSURE_US), "ExposureValue": EXPOSURE_VALUE})
    except Exception as e:  # older libcamera / other sensors: keep the defaults
        say("Camera controls not applied:", str(e))
    time.sleep(1.0)  # let auto-exposure settle
    return cam


def open_window():
    if not SHOW_WINDOW:
        return
    cv2.namedWindow(WINDOW, cv2.WINDOW_NORMAL)
    if FULLSCREEN:
        cv2.setWindowProperty(WINDOW, cv2.WND_PROP_FULLSCREEN, cv2.WINDOW_FULLSCREEN)


def show(frame, lines, color=WHITE, box=None, box_color=GREEN):
    """Mirrored preview (moving left goes left) with text on top. Never touches the frame itself."""
    if not SHOW_WINDOW:
        return
    view = cv2.flip(frame, 1)
    W = view.shape[1]
    if box is not None:
        x, y, w, h = box
        cv2.rectangle(view, (W - x - w, y), (W - x, y + h), box_color, 3)
    for i, text in enumerate(lines):
        y = 60 + i * 60
        cv2.putText(view, text, (30, y), cv2.FONT_HERSHEY_SIMPLEX, 1.5, (0, 0, 0), 8, cv2.LINE_AA)
        cv2.putText(view, text, (30, y), cv2.FONT_HERSHEY_SIMPLEX, 1.5, color, 3, cv2.LINE_AA)
    cv2.imshow(WINDOW, view)
    cv2.waitKey(1)


def say(*lines):
    print(" | ".join(lines), flush=True)


def safe_json(r):
    try:
        return r.json()
    except ValueError:
        return {}


def lookup(code):
    """Returns (student dict, None) or (None, error message)."""
    try:
        r = requests.get(f"{API_BASE}/api/kiosk/face", params={"code": code}, headers=HEADERS, timeout=10)
    except requests.RequestException as e:
        return None, f"Network error: {e}"
    if r.status_code == 401:
        return None, "Unauthorized device — check DEVICE_KEY in config.py."
    data = safe_json(r)
    if r.status_code != 200 or not data.get("ok"):
        return None, data.get("message", f"Server error ({r.status_code}).")
    return data["student"], None


# ── Photo quality ────────────────────────────────────────────────────────────

def find_face(cascade, gray):
    """The one usable face, or (None, hint). Usable = large enough, not too close, near the centre."""
    H, W = gray.shape
    faces = cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=6, minSize=(int(H * 0.10), int(H * 0.10)))
    large = [f for f in faces if f[3] >= H * FACE_MIN]
    if len(large) > 1:
        return None, "Only ONE face, please"
    if not large:
        if len(faces):
            return None, "Come a little closer"
        # In a dark room the detector finds nothing at all — say why instead of "look at the camera"
        return None, ("More light on your face" if gray.mean() < BRIGHT_MIN else "Look straight at the camera")
    x, y, w, h = large[0]
    if h > H * FACE_MAX:
        return None, "Move back a little"
    if abs(x + w / 2 - W / 2) > W * 0.20 or abs(y + h / 2 - H / 2) > H * 0.25:
        return None, "Move to the middle"
    return (x, y, w, h), None


def overlap(a, b):
    """Intersection over union of two (x, y, w, h) boxes."""
    ax, ay, aw, ah = a
    bx, by, bw, bh = b
    ix = max(0, min(ax + aw, bx + bw) - max(ax, bx))
    iy = max(0, min(ay + ah, by + bh) - max(ay, by))
    inter = ix * iy
    return inter / float(aw * ah + bw * bh - inter)


def face_quality(gray, box):
    """(sharpness, brightness) of the inner face, independent of how big the face is."""
    x, y, w, h = box
    m = int(w * 0.15)
    roi = cv2.resize(gray[y + m:y + h - m, x + m:x + w - m], (160, 160), interpolation=cv2.INTER_AREA)
    return cv2.Laplacian(roi, cv2.CV_64F).var(), float(roi.mean())


def color_gains(frame):
    """
    Gray-world white balance from the whole scene: NoIR sensors turn infrared into red + blue.
    Applied at 85% — a coloured room (e.g. violet walls) would otherwise push skin towards green.
    """
    means = frame.reshape(-1, 3).astype(np.float32).mean(axis=0)
    full = np.clip(means.mean() / np.maximum(means, 1.0), 0.5, 2.5)
    return 1.0 + 0.85 * (full - 1.0)


def brighten(img, target=125.0):
    """Gamma towards a mean brightness of `target`; only ever brightens."""
    lum = float(cv2.cvtColor(img, cv2.COLOR_BGR2GRAY).mean())
    if lum >= target or lum < 5:
        return img
    gamma = np.log(target / 255.0) / np.log(lum / 255.0)
    lut = np.clip(((np.arange(256) / 255.0) ** gamma) * 255.0, 0, 255).astype(np.uint8)
    return cv2.LUT(img, lut)


def crop_photo(frame, box):
    """Face down to the neck (+ margin), colour-corrected, ≤ 600 px tall (server limit 2 MB per photo)."""
    x, y, w, h = box
    H, W = frame.shape[:2]
    x0, y0 = max(0, x - int(0.25 * w)), max(0, y - int(0.30 * h))
    x1, y1 = min(W, x + w + int(0.25 * w)), min(H, y + h + int(0.45 * h))
    crop = frame[y0:y1, x0:x1]
    if COLOR_FIX:
        crop = brighten(np.clip(crop.astype(np.float32) * color_gains(frame), 0, 255).astype(np.uint8))
    if crop.shape[0] > 600:
        scale = 600 / crop.shape[0]
        crop = cv2.resize(crop, (int(crop.shape[1] * scale), 600), interpolation=cv2.INTER_AREA)
    ok, jpg = cv2.imencode(".jpg", crop, [int(cv2.IMWRITE_JPEG_QUALITY), 90])
    return jpg.tobytes() if ok else None


def capture_faces(cam, cascade, student_name):
    """
    Returns (photos, problem): the PHOTOS sharpest usable frames as JPEG bytes, and — when fewer than 3 were
    usable — what went wrong most often, for the on-screen message.
    """
    candidates = []                     # (sharpness, jpeg)
    started = time.time()
    last_taken, prev_box, steady, best = 0.0, None, 0, 0.0
    problems = {}

    def usable():
        """Candidates sharp enough on their own AND compared with the best frame (a moving head blurs)."""
        floor = max(SHARP_MIN, SHARP_RELATIVE * best)
        return [c for c in candidates if c[0] >= floor]

    while time.time() - started < CAPTURE_TIMEOUT:
        frame = cam.capture_array()
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        kept = usable()
        good = sum(1 for s, _ in kept if s >= SHARP_GOOD)
        progress = f"{min(len(kept), PHOTOS)}/{PHOTOS}"

        box, hint = find_face(cascade, gray)
        if box is None:
            prev_box, steady = None, 0
            problems[hint] = problems.get(hint, 0) + 1
            show(frame, [f"Hi {student_name}!", hint, progress], AMBER)
            continue

        steady = steady + 1 if prev_box is not None and overlap(box, prev_box) > 0.5 else 1
        prev_box = box
        sharp, bright = face_quality(gray, box)

        if bright < BRIGHT_MIN:
            hint = "More light on your face"
        elif sharp < SHARP_MIN:
            hint = "Hold still..."
        elif steady < STEADY_FRAMES:
            hint = "Hold still..."
        else:
            hint = None

        if hint:
            problems[hint] = problems.get(hint, 0) + 1
            show(frame, [f"Hi {student_name}!", hint, progress], AMBER, box, AMBER)
            continue

        show(frame, [f"Hi {student_name}!", "Good! Keep looking at the camera", progress], GREEN, box)
        if time.time() - last_taken >= 0.35:  # small gap so photos are not identical
            jpg = crop_photo(frame, box)
            if jpg:
                candidates.append((sharp, jpg))
                best = max(best, sharp)
                last_taken = time.time()

        enough = len(usable()) >= PHOTOS
        if good >= PHOTOS or (enough and time.time() - started >= COLLECT_SECONDS):
            break

    kept = sorted(usable(), key=lambda c: c[0], reverse=True)
    photos = [jpg for _, jpg in kept[:PHOTOS]]
    problem = max(problems, key=problems.get) if problems and len(photos) < 3 else None
    return photos, problem


def register(code, photos):
    files = [("images[]", (f"face_{i + 1:02d}.jpg", data, "image/jpeg")) for i, data in enumerate(photos)]
    try:
        r = requests.post(f"{API_BASE}/api/kiosk/face", data={"code": code}, files=files, headers=HEADERS, timeout=60)
    except requests.RequestException as e:
        return False, f"Network error: {e}"
    data = safe_json(r)
    if r.status_code == 200 and data.get("ok"):
        return True, data.get("message", "Face registered.")
    if r.status_code == 422 and isinstance(data.get("errors"), dict):
        first = next(iter(data["errors"].values()), ["Invalid upload."])
        return False, first[0]
    return False, data.get("message", f"Server error ({r.status_code}).")


def main():
    cascade = find_cascade()
    detector = cv2.QRCodeDetector()
    cam = open_camera()
    open_window()
    say("Kiosk ready.", f"Server: {API_BASE}")
    recent = {}  # code -> time, so the same QR is not processed twice in a row

    try:
        while True:
            frame = cam.capture_array()
            show(frame, ["DP-LMS Face Registration", "Show your QR code to the camera"])
            text, _, _ = detector.detectAndDecode(frame)
            if not text or not text.startswith(QR_PREFIX):
                continue
            if time.time() - recent.get(text, 0) < 15:
                continue
            recent[text] = time.time()

            student, err = lookup(text)
            if err:
                say("QR refused:", err)
                show(frame, ["QR not accepted", err[:40]], RED)
                time.sleep(3)
                continue

            name = student["name"]
            say("Registering:", name, f"Grade {student.get('grade_level')} {student.get('section') or ''}")
            photos, problem = capture_faces(cam, cascade, name)
            if len(photos) < 3:
                reason = problem or "Not enough clear photos"
                say("Not enough clear photos:", reason, "— scan the QR again.")
                show(cam.capture_array(), ["Not enough clear photos", reason, "Scan the QR again"], RED)
                recent.pop(text, None)  # same QR may be retried (it is still unused)
                time.sleep(4)
                continue

            say("Uploading", f"{len(photos)} photos")
            ok, message = register(text, photos)
            say("OK:" if ok else "FAILED:", message)
            show(cam.capture_array(), [message[:40], "Next student, please"], GREEN if ok else RED)
            if not ok:
                recent.pop(text, None)
            time.sleep(4)
    except KeyboardInterrupt:
        pass
    finally:
        cam.stop()
        if SHOW_WINDOW:
            cv2.destroyAllWindows()


if __name__ == "__main__":
    main()
