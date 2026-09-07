# PoC Collection — laravel-reza

This repository contains **Proof-of-Concept (PoC)** scripts for security vulnerabilities identified
in the `laravel-reza` Laravel web application (source review + patch verification, 2026-09-07).

## Structure

Follows a per-vulnerability layout: each finding has its own folder with a standalone PoC script,
its `README.md`, and optional `requirements.txt`.

```
POCs/
├── README.md                      # This file
├── 01_admin_bypass/               # Broke Authorization on admin routes (BFLA + IDOR)
│   ├── POC_admin_bypass.py
│   └── README.md
├── 02_file_upload/                # Unvalidated file upload
│   ├── POC_file_upload.py
│   └── README.md
├── 03_api_key_leak/               # Sensitive data exposure in /cek-key
│   ├── POC_api_key_leak.py
│   └── README.md
├── 04_pdf_parser/                 # RCE / XXE via PDF parsing pipeline
│   ├── POC_pdf_parser.py
│   └── README.md
└── 05_input_validation/           # Improper input validation on updateStatus
    ├── POC_input_validation.py
    └── README.md
```

## Conventions (following Octomany / CyberSecPlayground style)

- **Check vs Exploit split** — every script has `--check` (detect only, safe) and `--exploit`
  (full demonstration) modes. Always run `--check` first.
- **Full header docstring** — CVE/issue type, affected component, impact, researcher, references.
- **Reproducible output** — colour-coded `[+]/[*]/[-]/[!]` status lines and exit codes.
- **CLI args** — `--target`, `--check`, `--exploit`, `--id`, `--debug` as appropriate.

## Usage

```bash
# Safe detection first:
python3 POCs/01_admin_bypass/POC_admin_bypass.py --target http://127.0.0.1:8000 --check

# Full demonstration (authorized environments only):
python3 POCs/01_admin_bypass/POC_admin_bypass.py --target http://127.0.0.1:8000 --exploit --id 1
```

## ⚠️ Disclaimer

These PoCs are for **educational and authorized security testing purposes only**. Use them ONLY on
systems you own or have explicit written permission to test. Unauthorized use is illegal and
unethical.

**The admin-bypass and API-key POCs can MUTATE DATA and TRIGGER PAID AI API CALLS.** Do not run
`--exploit` against production.

## Findings index

| ID | Finding | Severity | CWE | CVSS |
|----|---------|----------|-----|------|
| 01 | Broken Authorization on admin routes (BFLA + IDOR) | High | CWE-862 / CWE-639 | 8.3 |
| 02 | Unvalidated file upload | High | CWE-434 | 8.1 |
| 03 | API key leak in `/cek-key` | Critical | CWE-200 / CWE-798 | 9.3 |
| 04 | RCE / XXE via PDF parsing pipeline | Medium | CWE-611 / CWE-502 | 7.5 |
| 05 | Improper input validation on `updateStatus` | Medium | CWE-20 | 5.3 |

---
*Compiled by Mas Admin (Hermes Agent) — Klinik Sehat Bersama project.*