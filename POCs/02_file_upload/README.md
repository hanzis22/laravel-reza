# 02 — Unvalidated File Upload

**Severity:** High · **CVSS:** 8.1 · **CWE:** 434

## Summary

`TugasController::store()` stored uploaded files without any MIME/size validation and persisted them
to `storage/app/public` using the attacker-controlled original filename. A user could upload a webshell
or other dangerous file type.

## Usage

```bash
# Safe detection (upload blocked?):
python3 POC_file_upload.py --target http://127.0.0.1:8000 --check

# Full demonstration (stores payload):
python3 POC_file_upload.py --target http://127.0.0.1:8000 --exploit
```

## What `--check` verifies

Uploads a `text/plain` payload. `422` → patched (rejected by `mimes:pdf`). `302`/`200` → vulnerable.

## Remedy applied

```php
$request->validate([
    'file' => 'required|file|mimes:pdf|max:5120',
]);
```
Also added `judul` / `minggu` validation and changed storage name to a server-generated prefix.

## Disclaimer

Authorized testing / education only.