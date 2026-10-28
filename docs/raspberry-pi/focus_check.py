#!/usr/bin/env python3
"""
Lens focus helper for the kiosk / gate camera.

Shows the live sharpness of your face (the same number the kiosk uses to accept photos) while you
slowly turn the camera lens. Stop where the number is highest, with your face where students will stand
(~50 cm). Needs face_kiosk.py and config.py in the same folder.

Run:  DISPLAY=:0 python3 focus_check.py      (Ctrl+C to stop)
"""

import time

import cv2

import face_kiosk as kiosk


def main():
    cascade = kiosk.find_cascade()
    cam = kiosk.open_camera()
    kiosk.open_window()
    best, best_at = 0.0, 0.0
    print("Turn the lens slowly; Ctrl+C to stop.", flush=True)

    try:
        while True:
            frame = cam.capture_array()
            gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
            box, hint = kiosk.find_face(cascade, gray)
            if box is None:
                kiosk.show(frame, ["Focus check", hint or "Look at the camera"], kiosk.AMBER)
                continue

            sharp, bright = kiosk.face_quality(gray, box)
            if sharp > best:
                best, best_at = sharp, time.time()
            color = kiosk.GREEN if sharp >= kiosk.SHARP_GOOD else (kiosk.AMBER if sharp >= kiosk.SHARP_MIN else kiosk.RED)
            kiosk.show(frame, [f"Sharpness {sharp:.0f}  (best {best:.0f})",
                               f"Need {kiosk.SHARP_MIN:.0f}+, ideal {kiosk.SHARP_GOOD:.0f}+",
                               f"Brightness {bright:.0f}  (need {kiosk.BRIGHT_MIN:.0f}+)"], color, box, color)
            print(f"sharpness {sharp:6.1f}  best {best:6.1f}  brightness {bright:5.1f}", end="\r", flush=True)
    except KeyboardInterrupt:
        print(f"\nBest sharpness: {best:.0f}" + (f" ({time.time() - best_at:.0f}s ago)" if best else ""))
    finally:
        cam.stop()
        if kiosk.SHOW_WINDOW:
            cv2.destroyAllWindows()


if __name__ == "__main__":
    main()
