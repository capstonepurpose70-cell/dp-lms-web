#!/usr/bin/env python3
"""
DP-LMS face registration kiosk — Raspberry Pi 5 + Camera Module.

Flow:
  1. Adviser/admin clicks "Show QR" in DP-LMS for ONE student (valid 10 min, works once).
  2. Student holds the QR up to this camera.
  3. GET  /api/kiosk/face?code=...   -> "Hi Juan Dela Cruz!" (does not use the QR)
  4. Captures PHOTOS face photos (exactly one face in frame, cropped face-to-neck).
  5. POST /api/kiosk/face  code + images[]  -> saved to that student, already approved.
  6. The gate recognizer picks the new face up on its next GET /api/faces/approved.

Install (Raspberry Pi OS Bookworm):
  sudo apt update && sudo apt install -y python3-picamera2 python3-opencv python3-requests
Run:
  python3 face_kiosk.py            (needs config.py next to this file)
"""

import os
import sys
import time

import cv2
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
QR_PREFIX = "DPLMS-FACE:"


def find_cascade():
    """Frontal-face Haar cascade: pip OpenCV ships cv2.data; Debian's apt package installs it under /usr/share."""
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
    sys.exit("haarcascade_frontalface_default.xml not found — install python3-opencv.")


def open_camera():
    from picamera2 import Picamera2  # imported here so the file can be syntax-checked off the Pi

    cam = Picamera2()
    # "RGB888" in Picamera2 = pixels in B,G,R order, which is what OpenCV expects.
    cam.configure(cam.create_preview_configuration(main={"size": (1280, 720), "format": "RGB888"}))
    cam.start()
    time.sleep(1.0)  # let auto-exposure settle
    return cam


def show(frame, lines, color=(255, 255, 255)):
    if not SHOW_WINDOW:
        return
    view = frame.copy()
    for i, text in enumerate(lines):
        y = 40 + i * 42
        cv2.putText(view, text, (20, y), cv2.FONT_HERSHEY_SIMPLEX, 1.0, (0, 0, 0), 5, cv2.LINE_AA)
        cv2.putText(view, text, (20, y), cv2.FONT_HERSHEY_SIMPLEX, 1.0, color, 2, cv2.LINE_AA)
    cv2.imshow("DP-LMS Face Kiosk", view)
    cv2.waitKey(1)


def say(*lines):
    print(" | ".join(lines), flush=True)


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


def safe_json(r):
    try:
        return r.json()
    except ValueError:
        return {}


def capture_faces(cam, cascade, student_name):
    """Collect PHOTOS JPEGs with exactly one reasonably large face. Returns list of bytes (may be shorter on timeout)."""
    photos, started, last = [], time.time(), 0.0
    while len(photos) < PHOTOS and time.time() - started < CAPTURE_TIMEOUT:
        frame = cam.capture_array()
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=6, minSize=(140, 140))

        if len(faces) != 1:
            show(frame, [f"Hi {student_name}!", "Only ONE face, look straight at the camera",
                         f"{len(photos)}/{PHOTOS}"], (0, 200, 255))
            continue

        x, y, w, h = faces[0]
        cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 200, 0), 2)
        show(frame, [f"Hi {student_name}!", "Hold still...", f"{len(photos)}/{PHOTOS}"], (0, 220, 0))

        if time.time() - last < 0.35:  # small gap so photos are not identical
            continue
        last = time.time()

        # Crop face down to the neck (+ margin), keep it small (server limit 2 MB per photo)
        H, W = frame.shape[:2]
        x0, y0 = max(0, x - int(0.25 * w)), max(0, y - int(0.30 * h))
        x1, y1 = min(W, x + w + int(0.25 * w)), min(H, y + h + int(0.45 * h))
        crop = frame[y0:y1, x0:x1]
        if crop.shape[0] > 600:
            scale = 600 / crop.shape[0]
            crop = cv2.resize(crop, (int(crop.shape[1] * scale), 600))
        ok, jpg = cv2.imencode(".jpg", crop, [int(cv2.IMWRITE_JPEG_QUALITY), 90])
        if ok:
            photos.append(jpg.tobytes())
    return photos


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
                show(frame, ["QR not accepted", err[:60]], (0, 0, 255))
                time.sleep(3)
                continue

            name = student["name"]
            say("Registering:", name, f"Grade {student.get('grade_level')} {student.get('section') or ''}")
            photos = capture_faces(cam, cascade, name)
            if len(photos) < 3:
                say("Not enough clear photos — try again with a new scan.")
                show(frame, ["Not enough clear photos", "Scan the QR again"], (0, 0, 255))
                recent.pop(text, None)  # same QR may be retried (it is still unused)
                time.sleep(3)
                continue

            ok, message = register(text, photos)
            say("OK:" if ok else "FAILED:", message)
            show(cam.capture_array(), [message[:60], "Next student, please"], (0, 220, 0) if ok else (0, 0, 255))
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
