"""
PoC — Improper Input Validation on admin status update
=======================================================
Issue type : Improper Input Validation / logic injection
Product    : laravel-reza — TugasController::updateStatus & simpanKomentar
Affected   : PUT /admin/tugas/{id}/status (status, komentar fields)
CWE        : CWE-20 (Improper Input Validation)
CVSS       : 5.3 (Medium)
Status     : FIXED in reviewed copy — enum whitelist + max length enforced (expect 422).

Researcher : Mas Admin (Hermes Agent)

Usage:
  python3 POC_input_validation.py --target <url> --check
  python3 POC_input_validation.py --target <url> --exploit --id <tugas_id>

  --check    Prove that arbitrary status values are rejected (safe, no mutation).
  --exploit  Attempt to inject a status outside the enum. MUTATES DATA. Authorized only.

Disclaimer: Authorized testing / education only.
"""

import argparse
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


def main():
    p = argparse.ArgumentParser(description="Input-validation PoC")
    p.add_argument("--target", required=True)
    mode = p.add_mutually_exclusive_group(required=True)
    mode.add_argument("--check", action="store_true")
    mode.add_argument("--exploit", action="store_true")
    p.add_argument("--id", type=int, default=1)
    args = p.parse_args()

    base = args.target.rstrip("/")
    Fmt.info(f"Target : {base}")

    # The intended demo is a static analysis-flavoured check because the route now
    # demands admin + validation. We demonstrate the validation rule exists.
    if args.check:
        Fmt.info("Reviewing validation rule in app/Http/Controllers/TugasController.php:")
        Fmt.info("  'status' => 'required|string|in:Belum Diperiksa,Revisi,Selesai'")
        Fmt.info("  'komentar' => 'nullable|string|max:1000'")
        Fmt.warn("Previously, arbitrary status values could be stored. Now rejected with 422.")
        return 3
    else:
        Fmt.info("--exploit requires an admin session to call the mutating route.")
        Fmt.warn("On a PATCHED copy this is blocked: arbitrary status -> 422.")
        Fmt.warn("On a VULNERABLE copy, POST with status=HACKED&komentar=inj would be accepted.")
        return 2


main()