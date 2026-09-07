# 01 — Broken Authorization on admin routes (BFLA + IDOR)

**Severity:** High · **CVSS:** 8.3 · **CWE:** 862 / 639

## Summary

All `/admin/*` task-management routes were protected only by `auth` middleware (no role check).
A standard `peserta` (participant) account could enumerate every submission, forge status/comment
updates, download other users' files, and trigger paid AI analysis.

## Affected routes

`/admin/tugas`, `/admin/tugas/{id}/status`, `/admin/tugas/{id}/komentar`,
`/admin/tugas/{id}/download`, `/admin/tugas/{id}/analisis-ai`

## Prerequisites

- Python 3.x + `requests`
- Access to the app (authorized)

## Usage

```bash
# Safe detection (no mutation). Exits 3 if patched, 0 if vulnerable:
python3 POC_admin_bypass.py --target http://127.0.0.1:8000 --check

# Full demonstration (MUTATES DATA + TRIGGERS PAID AI CALLS) — authorized only:
python3 POC_admin_bypass.py --target http://127.0.0.1:8000 --exploit --id 1
```

## What `--check` verifies

1. Register a fresh `peserta`.
2. `GET /admin/tugas`.
3. `200` with admin data → **VULNERABLE**. `403` → **patched**.

## Remedy applied

- Added `AdminMiddleware` (checks `role === 'admin'`, else `403`).
- Moved admin routes into `['auth','admin']` group.
- Added defense-in-depth `abort(403)` in each admin controller method.

## Disclaimer

Authorized testing / education only. Unauthorized use is illegal.