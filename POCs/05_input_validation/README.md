# 05 — Improper Input Validation on `updateStatus`

**Severity:** Medium · **CVSS:** 5.3 · **CWE:** 20

## Summary

`TugasController::updateStatus()` / `simpanKomentar()` accepted arbitrary `status` / `komentar`
values, allowing a non-enum value (and oversized strings) to be stored.

## Usage

```bash
# Check the validation rule / behavior (safe):
python3 POC_input_validation.py --target http://127.0.0.1:8000 --check
```

## Interpretation

- **Vulnerable:** `POST /admin/tugas/1/status` with `status=HACKED` succeeds (stored).
- **Patched:** rejected with `422` because `status` must be one of `Belum Diperiksa, Revisi, Selesai`.

## Remedy applied

```php
'status'   => 'required|string|in:Belum Diperiksa,Revisi,Selesai',
'komentar' => 'nullable|string|max:1000',
```

## Disclaimer

Authorized testing / education only.