#!/usr/bin/env python3
"""
DP-LMS gate camera — Raspberry Pi 5 + Camera Module at the school entrance.

Flow:
  1. Downloads every approved student's face photos (GET /api/faces/approved, then each photo with the
     device key) and turns them into face embeddings. Repeats every REFRESH_MINUTES for new registrations.
  2. A student stands in front of the camera. YuNet finds the face, SFace turns it into an embedding and it
     is compared with every student's photos (cosine similarity).
  3. Same student above MATCH_THRESHOLD on MATCH_FRAMES frames in a row -> "Welcome, Juan" on screen and
     POST /api/attendance (the server records present/late and notifies the parent).
  4. A face that matches nobody for UNKNOWN_FRAMES frames -> "Not recognized" and POST /api/gate/unknown
     (score only, no picture) for the recognition log.
  5. Server unreachable -> the scan waits in QUEUE_FILE with its real time and is sent when it is back.

Install (on top of the kiosk packages):
  mkdir -p models && cd models
  wget https://github.com/opencv/opencv_zoo/raw/main/models/face_detection_yunet/face_detection_yunet_2023mar.onnx
  wget https://github.com/opencv/opencv_zoo/raw/main/models/face_recognition_sface/face_recognition_sface_2021dec.onnx
Run:
  python3 gate_camera.py            (config.py and face_kiosk.py in the same folder)
  DISPLAY=:0 python3 gate_camera.py (when started over SSH, to show it on the Pi's screen)
"""

import json
import os
import queue
import shutil
import sys
import threading
import time
from datetime import datetime

import cv2
import numpy as np
import requests

import face_kiosk as kiosk  # camera, window, colour correction and config shared with the kiosk
from face_kiosk import API_BASE, HEADERS, GREEN, AMBER, RED, WHITE, config, say, safe_json, show

HERE = os.path.dirname(os.path.abspath(__file__))
DEVICE = str(getattr(config, "DEVICE_NAME", "gate-1"))[:64]
MODEL_DIR = getattr(config, "MODEL_DIR", os.path.join(HERE, "models"))
CACHE_DIR = getattr(config, "FACE_CACHE_DIR", os.path.join(HERE, "face_cache"))
QUEUE_FILE = getattr(config, "QUEUE_FILE", os.path.join(HERE, "gate_queue.jsonl"))
MATCH_THRESHOLD = float(getattr(config, "MATCH_THRESHOLD", 0.40))   # cosine similarity (OpenCV's SFace reference: 0.363)
MATCH_FRAMES = int(getattr(config, "MATCH_FRAMES", 3))             # same student this many frames in a row
UNKNOWN_FRAMES = int(getattr(config, "UNKNOWN_FRAMES", 10))        # unmatched this many frames -> "Not recognized"
GATE_FACE_MIN = float(getattr(config, "GATE_FACE_MIN", 0.12))       # face height as a share of the frame height
COOLDOWN = float(getattr(config, "COOLDOWN_SECONDS", 30))           # same student is not sent again within this
RESULT_SECONDS = float(getattr(config, "RESULT_SECONDS", 3))        # how long the greeting stays on screen
REFRESH_MINUTES = float(getattr(config, "REFRESH_MINUTES", 10))     # reload approved faces
DETECT_WIDTH = 640                                                  # detection runs on a smaller copy of the frame

YUNET = "face_detection_yunet_2023mar.onnx"
SFACE = "face_recognition_sface_2021dec.onnx"


# ── Models ──────────────────────────────────────────────────────────────────

def load_models():
    paths = [os.path.join(MODEL_DIR, YUNET), os.path.join(MODEL_DIR, SFACE)]
    missing = [os.path.basename(p) for p in paths if not os.path.exists(p)]
    if missing:
        sys.exit(f"Missing model file(s) in {MODEL_DIR}: {', '.join(missing)} — see the install steps at the top of this file.")
    detector = cv2.FaceDetectorYN.create(paths[0], "", (320, 320), 0.85, 0.3, 20)
    recognizer = cv2.FaceRecognizerSF.create(paths[1], "")
    return detector, recognizer


def detect(detector, img, width=None):
    """Faces as YuNet rows (x, y, w, h, 5 landmarks, score) in img coordinates, largest first."""
    H, W = img.shape[:2]
    scale = 1.0
    small = img
    if width and W > width:
        scale = width / W
        small = cv2.resize(img, (width, int(H * scale)), interpolation=cv2.INTER_AREA)
    detector.setInputSize((small.shape[1], small.shape[0]))
    _, faces = detector.detect(small)
    if faces is None:
        return []
    faces = faces.copy()
    faces[:, :14] /= scale
    return sorted(faces, key=lambda f: f[2] * f[3], reverse=True)


def embedding(recognizer, img, face, gains=None):
    """L2-normalised SFace feature of one detected face. gains: same colour fix the kiosk applied to the photos."""
    aligned = recognizer.alignCrop(img, face)
    if gains is not None:
        aligned = kiosk.brighten(np.clip(aligned.astype(np.float32) * gains, 0, 255).astype(np.uint8))
    feat = recognizer.feature(aligned).flatten().astype(np.float32)
    return feat / max(float(np.linalg.norm(feat)), 1e-6)


# ── Approved faces from the server ──────────────────────────────────────────

class Gallery:
    """Every approved student's embeddings. Rebuilt in the background and swapped in one assignment."""

    def __init__(self):
        self.ids = np.zeros((0,), np.int64)    # one entry per photo
        self.feats = np.zeros((0, 128), np.float32)
        self.names = {}

    def best(self, feat):
        """(user_id, name, score) of the closest student, or (None, None, best score)."""
        if not len(self.ids):
            return None, None, 0.0
        scores = self.feats @ feat
        i = int(np.argmax(scores))
        score = float(scores[i])
        if score < MATCH_THRESHOLD:
            return None, None, score
        uid = int(self.ids[i])
        return uid, self.names.get(uid, "Student"), score


def private_dir(path):
    os.makedirs(path, mode=0o700, exist_ok=True)  # face photos are biometric data: owner only
    return path


def build_gallery(detector, recognizer):
    r = requests.get(f"{API_BASE}/api/faces/approved", headers=HEADERS, timeout=20)
    if r.status_code == 401:
        raise RuntimeError("Unauthorized device — check DEVICE_KEY in config.py.")
    r.raise_for_status()
    faces = safe_json(r).get("faces", [])

    private_dir(CACHE_DIR)
    gallery = Gallery()
    ids, feats, keep = [], [], set()
    for f in faces:
        uid = int(f["user_id"])
        keep.add(str(uid))
        folder = private_dir(os.path.join(CACHE_DIR, str(uid)))
        meta_path, feat_path = os.path.join(folder, "meta.json"), os.path.join(folder, "features.npy")
        version = f.get("reviewed_at") or ""
        try:
            with open(meta_path) as fh:
                cached = json.load(fh).get("version") == version and os.path.exists(feat_path)
        except (OSError, ValueError):
            cached = False

        if not cached:
            person = []
            for url in f.get("images", []):
                img_r = requests.get(url, headers=HEADERS, timeout=20)
                if img_r.status_code != 200:
                    continue
                img = cv2.imdecode(np.frombuffer(img_r.content, np.uint8), cv2.IMREAD_COLOR)
                found = detect(detector, img) if img is not None else []
                if found:
                    # The kiosk photos are already colour-corrected: no gains here
                    person.append(embedding(recognizer, img, found[0]))
            if not person:
                say("No usable face in the photos of", f.get("name", str(uid)))
                continue
            np.save(feat_path, np.stack(person))
            with open(meta_path, "w") as fh:
                json.dump({"version": version, "name": f.get("name", "Student")}, fh)

        person = np.load(feat_path)
        ids += [uid] * len(person)
        feats.append(person)
        gallery.names[uid] = f.get("name", "Student")

    # Students who are no longer approved: their cached faces go too
    for name in os.listdir(CACHE_DIR):
        if name not in keep:
            shutil.rmtree(os.path.join(CACHE_DIR, name), ignore_errors=True)

    if feats:
        gallery.ids, gallery.feats = np.array(ids, np.int64), np.concatenate(feats)
    return gallery


def cached_gallery():
    """The faces from the last successful load, so a gate that starts without internet still recognizes."""
    gallery = Gallery()
    ids, feats = [], []
    if os.path.isdir(CACHE_DIR):
        for name in os.listdir(CACHE_DIR):
            folder = os.path.join(CACHE_DIR, name)
            try:
                with open(os.path.join(folder, "meta.json")) as fh:
                    meta = json.load(fh)
                person = np.load(os.path.join(folder, "features.npy"))
            except (OSError, ValueError):
                continue
            ids += [int(name)] * len(person)
            feats.append(person)
            gallery.names[int(name)] = meta.get("name", "Student")
    if feats:
        gallery.ids, gallery.feats = np.array(ids, np.int64), np.concatenate(feats)
    return gallery


# ── Sending scans (background thread, offline queue) ────────────────────────

class Sender(threading.Thread):
    """Posts scans without holding up the camera. Anything it cannot deliver waits in QUEUE_FILE."""

    def __init__(self):
        super().__init__(daemon=True)
        self.jobs = queue.Queue()
        self.results = {}  # user_id -> server status text for the greeting

    def submit(self, event):
        self.jobs.put(event)

    def post(self, event):
        """True = done (delivered, or refused for good). False = try again later."""
        path = "/api/attendance" if event["kind"] == "attendance" else "/api/gate/unknown"
        body = {k: v for k, v in event.items() if k != "kind"}
        try:
            r = requests.post(f"{API_BASE}{path}", json=body, headers=HEADERS, timeout=10)
        except requests.RequestException:
            return False
        if r.status_code == 401:
            say("Unauthorized device — check DEVICE_KEY in config.py. Scan kept in the queue.")
            return False
        if r.status_code == 429 or r.status_code >= 500:
            return False
        if event["kind"] == "attendance":
            data = safe_json(r)
            if r.status_code == 200 and data.get("success"):
                already = "already" in data.get("message", "").lower()
                self.results[int(body["student_id"])] = (
                    "Already checked in today" if already else ("Late" if data.get("status") == "late" else "On time")
                )
            else:
                say("Scan refused:", data.get("message", f"HTTP {r.status_code}"))
        return True

    def save_for_later(self, event):
        with open(QUEUE_FILE, "a") as fh:
            fh.write(json.dumps(event) + "\n")

    def flush_queue(self):
        if not os.path.exists(QUEUE_FILE):
            return
        with open(QUEUE_FILE) as fh:
            events = [json.loads(line) for line in fh if line.strip()]
        left = []
        for i, event in enumerate(events):
            if not self.post(event):
                left = events[i:]  # keep the order; the rest waits for the next try
                break
        if left:
            with open(QUEUE_FILE, "w") as fh:
                fh.writelines(json.dumps(e) + "\n" for e in left)
        else:
            os.remove(QUEUE_FILE)
            say("Queued scans sent.")

    def run(self):
        last_flush = 0.0  # scans left from the last run are sent first
        while True:
            if time.time() - last_flush > 30:
                last_flush = time.time()
                self.flush_queue()
            try:
                event = self.jobs.get(timeout=5)
            except queue.Empty:
                continue
            if os.path.exists(QUEUE_FILE):
                # Older scans are still waiting: queue behind them so the order stays right
                self.save_for_later(event)
                say("Scan queued behind earlier ones.")
            elif not self.post(event):
                self.save_for_later(event)
                say("Server unreachable — scan queued.")


def scan_event(kind, score, user_id=None):
    event = {
        "kind": kind,
        "score": round(min(max(score, 0.0), 1.0), 4),
        "device": DEVICE,
        "scanned_at": datetime.now().astimezone().isoformat(timespec="seconds"),
    }
    if user_id is not None:
        event["student_id"] = str(user_id)
    return event


# ── Main loop ───────────────────────────────────────────────────────────────

def main():
    detector, recognizer = load_models()
    kiosk.WINDOW = "DP-LMS Gate"
    gallery = Gallery()
    state = {"gallery": gallery, "loaded": 0.0, "loading": False}

    def refresh():
        try:
            # Own model instances: OpenCV's DNN models must not be shared with the camera loop's thread
            g = build_gallery(*load_models())
            state["gallery"] = g
            say("Faces loaded:", f"{len(g.names)} students, {len(g.ids)} photos")
        except Exception as e:  # keep the last gallery; try again next round
            say("Could not load faces:", str(e))
            if not len(state["gallery"].ids):
                state["gallery"] = cached_gallery()
                say("Using saved faces:", f"{len(state['gallery'].names)} students")
            state["loaded"] = time.time() - REFRESH_MINUTES * 60 + 60  # retry in a minute
        state["loading"] = False

    sender = Sender()
    sender.start()
    cam = kiosk.open_camera()
    kiosk.open_window()
    say("Gate ready.", f"Server: {API_BASE}", f"Device: {DEVICE}")

    streak_id, streak, unknown_streak = None, 0, 0
    last_sent, last_unknown = {}, 0.0
    result = None  # (until, lines, colour, user_id)

    try:
        while True:
            if not state["loading"] and time.time() - state["loaded"] > REFRESH_MINUTES * 60:
                state["loading"], state["loaded"] = True, time.time()
                threading.Thread(target=refresh, daemon=True).start()

            frame = cam.capture_array()
            g = state["gallery"]
            H = frame.shape[0]
            faces = [f for f in detect(detector, frame, DETECT_WIDTH) if f[3] >= H * GATE_FACE_MIN]
            box = tuple(int(v) for v in faces[0][:4]) if faces else None

            if result and time.time() < result[0]:
                lines, colour, uid = result[1], result[2], result[3]
                if uid is not None and uid in sender.results:
                    lines = lines + [sender.results[uid]]
                show(frame, lines, colour, box, colour)
                continue
            result = None

            if not faces:
                streak_id, streak, unknown_streak = None, 0, 0
                show(frame, ["DP-LMS", "Look at the camera" if len(g.ids) else "Loading faces..."], WHITE)
                continue
            if not len(g.ids):
                show(frame, ["DP-LMS", "Loading faces..."], WHITE, box, AMBER)
                continue

            gains = kiosk.color_gains(cv2.resize(frame, (160, 90))) if kiosk.COLOR_FIX else None
            uid, name, score = g.best(embedding(recognizer, frame, faces[0], gains))

            if uid is None:
                streak_id, streak = None, 0
                unknown_streak += 1
                if unknown_streak >= UNKNOWN_FRAMES:
                    unknown_streak = 0
                    if time.time() - last_unknown > 10:
                        last_unknown = time.time()
                        sender.submit(scan_event("unknown", score))
                        say("Not recognized", f"best score {score:.3f}")
                    result = (time.time() + RESULT_SECONDS, ["Not recognized", "Please see the guard"], RED, None)
                    continue
                show(frame, ["DP-LMS", "Hold still..."], WHITE, box, AMBER)
                continue

            unknown_streak = 0
            streak = streak + 1 if uid == streak_id else 1
            streak_id = uid
            if streak < MATCH_FRAMES:
                show(frame, ["DP-LMS", "Hold still..."], WHITE, box, AMBER)
                continue

            streak_id, streak = None, 0
            now = time.time()
            if now - last_sent.get(uid, 0) > COOLDOWN:
                last_sent[uid] = now
                sender.results.pop(uid, None)
                sender.submit(scan_event("attendance", score, uid))
                say("Recognized:", name, f"score {score:.3f}")
            result = (now + RESULT_SECONDS, [f"Welcome, {name.split()[0]}!", datetime.now().strftime("%I:%M %p").lstrip("0")], GREEN, uid)
    except KeyboardInterrupt:
        pass
    finally:
        cam.stop()
        if kiosk.SHOW_WINDOW:
            cv2.destroyAllWindows()


if __name__ == "__main__":
    main()
