# DP-LMS IoT — what we still need (Raspberry Pi 5 gate camera + face kiosk)

Goal: a student walks up to the gate camera → the Pi recognizes the face → the server records
"arrived" → the parent's phone gets a push notification. The same Pi also registers faces at the
kiosk (adviser's one-time QR).

Legend: ✅ done · ⚠️ check · ❌ missing

---

## A. Hardware

| # | Item | Status | Notes |
|---|---|---|---|
| 1 | Raspberry Pi 5 | ✅ | |
| 2 | 5-inch DSI touch display (800×480) | ✅ | Pi is mounted on its back |
| 3 | Camera module | ✅ | **OV5647** 5 MP fisheye, CAM/DISP port 0, auto-detected. No IR filter → pink colours (recognition uses grayscale, so it does not matter) |
| 4 | **Official 27 W USB-C power supply (5.1 V / 5 A)** | ✅ | Raspberry Pi 27 W PSU, `vcgencmd get_throttled` = `0x0` |
| 5 | **Active Cooler for Pi 5** | ✅ | Installed, plugged into the Pi's 4-pin **FAN** port (not the display's 2-pin header) |
| 6 | Display ribbon (DSI) on the Pi | ✅ | In the second CAM/DISP port. **Write down which port is labelled 0 and which 1** — the display and camera settings need it |
| 7 | Camera cable for Pi 5 | ✅ | Both ribbons are 22-pin (Pi) to 15-pin (display / camera). Camera is a wide-angle (fisheye) module — read the sensor name printed on its board (e.g. OV5647) |
| 8 | microSD card | ✅ | Samsung EVO Plus 128 GB (the old ORICO 16 GB stalled while writing — do not use it) |
| 9 | microSD card reader for the laptop | ⚠️ | Only if the laptop has no microSD slot |
| 10 | Network at the gate | ⚠️ | School Wi-Fi, pocket Wi-Fi/phone hotspot, or an Ethernet cable |
| 11 | Stand / mount + extension cord | ❌ | Camera at face height (~1.2–1.5 m), light in front of the face (no backlight from the gate) |
| 12 | USB keyboard + mouse | optional | First setup only (or use SSH from the laptop) |

## B. SD card + OS

- [ ] **Back up the old Pi 3 card first** if it has anything you need (especially any old face
      recognition/gate script). Windows cannot open the Linux files — boot the card on the Pi 3 and copy
      the `.py` files to a USB drive.
- [ ] When Windows asks *"You need to format the disk"* → **Cancel** (every time).
- [ ] Raspberry Pi Imager: Device **Raspberry Pi 5** → OS **Raspberry Pi OS (64-bit)** with desktop
      (not Lite) → Storage: the SD card (~14–16 GB). The Pi 3's old system cannot boot a Pi 5 — a fresh
      write is required.
- [ ] Customisation: hostname `dplms-gate`, username + password, Wi-Fi, country **PH**, timezone
      **Asia/Manila**, **enable SSH**.
- [ ] Write → wait for Verify → eject. Imager erases the old Pi 3 system automatically.
- [ ] If writing/verify fails: Imager **Erase** (or *SD Card Formatter*), then write again; still failing → new card.

## C. First boot on the Pi

```bash
sudo apt update && sudo apt full-upgrade -y
sudo apt install -y python3-picamera2 python3-opencv opencv-data python3-requests
rpicam-hello -t 5000          # camera preview must appear (over SSH: DISPLAY=:0 rpicam-hello -t 30000)
```

- [ ] Display works (black screen → it needs a line in `/boot/firmware/config.txt` from the display maker's manual)
- [ ] Camera works (`no cameras available` → check the ribbon direction/cable type and the camera model)
- [ ] Temperature under load is OK with the cooler: `vcgencmd measure_temp` (stay below ~80 °C)

## D. Server (laptop for local testing)

- [ ] Set a device key in `.env`: `ATTENDANCE_DEVICE_KEY=<long random>`
      (`php -r "echo bin2hex(random_bytes(32));"`). Same value goes in the Pi's `config.py`.
- [ ] Confirm the late cutoff with the school: `ATTENDANCE_LATE_AFTER=07:30` (default 07:30).
- [ ] Run the server so the Pi can reach it: `php artisan serve --host=0.0.0.0 --port=8000`
- [ ] Laptop IP: `ipconfig` → IPv4 (e.g. `192.168.1.10`). Allow port **8000** in Windows Firewall (Private network).
- [ ] From the Pi: `curl -H "X-Device-Key: <key>" http://<laptop-ip>:8000/api/faces/approved` → `200`
      (wrong key → `401`).
- [ ] Firebase push: `storage/app/firebase/firebase-service-account.json` ✅ (local). On Railway use `FIREBASE_CREDENTIALS_BASE64`.

Server endpoints already built (all need the `X-Device-Key` header):

| Endpoint | Used by | What it does |
|---|---|---|
| `GET /api/faces/approved` | gate script | downloads registered faces (photo URLs per student) |
| `POST /api/attendance` | gate script | records arrival (once per student per day) + push to the parent |
| `GET /api/kiosk/face?code=…` | kiosk script | checks the adviser's one-time QR |
| `POST /api/kiosk/face` | kiosk script | uploads the student's face photos (saved as registered) |

## E. Pi software

| # | Item | Status |
|---|---|---|
| 1 | `docs/raspberry-pi/face_kiosk.py` — face registration with the one-time QR | ✅ built (tested against the server) |
| 2 | `docs/raspberry-pi/config.py` (copy of `config.example.py`: `API_BASE`, `DEVICE_KEY`) | ❌ create on the Pi (never commit it) |
| 3 | **`gate_camera.py`** — recognize faces at the gate and call `POST /api/attendance` | ❌ **not built yet** (use the old Pi 3 script if there is one, or we build it with OpenCV's built-in face recognition — no heavy install) |
| 4 | Mode switch: **Registration** (kiosk) vs **Gate** — one Pi, one camera, only one can run at a time | ❌ |
| 5 | Auto-start on power-on (systemd service), so no keyboard is needed at the gate | ❌ |
| 6 | Keep working when Wi-Fi drops: queue arrivals and resend later | ❌ (part of `gate_camera.py`) |
| 7 | **Face photos are public files** (`/storage/faces/{user id}/img_01.jpg` — guessable). Move them to private storage and let the Pi download them only with the device key, before the gate script and before going live | ❌ server fix |

## F. Data + parent app

- [ ] Students are enrolled in sections and **parents are linked** (Student → Parent link).
- [ ] Each test student has a registered face (Teacher → Face Registration → show QR to the Pi).
- [ ] Parent installed the Flutter app and **logged in** (that registers the phone for push — `POST /api/fcm-token`).
- [ ] Flutter handles the arrival notification (see `docs/FLUTTER_PROMPT_PARENT_ARRIVAL.md`).

## G. End-to-end test

1. [ ] Register a face at the kiosk → Admin → Face Verification shows **Registered**.
2. [ ] Walk to the gate camera → attendance saved (present or late by the cutoff) → parent phone gets **"arrived"**.
3. [ ] Same student again the same day → no second record, no second notification (server already enforces this).
4. [ ] Unknown face → nothing is sent.
5. [ ] Turn Wi-Fi off, scan, turn it back on → the queued arrival is sent.
6. [ ] Wrong device key → `401`.

## H. Defense-day kit

- [ ] Pi + display + camera, **27 W power supply**, extension cord, stand
- [ ] Phone hotspot or pocket Wi-Fi (school Wi-Fi may block devices)
- [ ] Laptop with XAMPP + backup DB (fallback if Railway or internet fails)
- [ ] `config.py` → `API_BASE` = live URL, or the laptop IP for the local fallback (the IP can change on a new network)
- [ ] Two parent phones logged in, to show the notification live
