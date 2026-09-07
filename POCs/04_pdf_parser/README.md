# 04 — RCE / XXE via PDF Parsing Pipeline

**Severity:** Medium · **CVSS:** 7.5 · **CWE:** 611 / 502

## Summary

`analisisAI()` parses a user-uploaded PDF using `Smalot\PdfParser\Parser`. Combined with the earlier
unvalidated file upload, a malicious PDF could target parser CVEs (path traversal / XXE) for code
execution / SSRF. Severity is **conditional** on the installed parser version.

## Usage

```bash
# Check prerequisites (safe):
python3 POC_pdf_parser.py --target http://127.0.0.1:8000 --check

# Demonstrate PDF acceptance (authorized): uploads minimal valid PDF
python3 POC_pdf_parser.py --target http://127.0.0.1:8000 --exploit --id 1
```

## Mitigations

- ✅ Uploads restricted to PDF (fixes the CWE-434 precondition).
- ⚠️ Keep `smalot/pdfparser` updated (`composer update`).
- ⚠️ Rate-limit `/admin/tugas/{id}/analisis-ai` (every call costs a Groq API call).

## Disclaimer

Authorized testing / education only.