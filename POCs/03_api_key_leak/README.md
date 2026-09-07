# 03 — API Key Leak in `/cek-key`

**Severity:** Critical · **CVSS:** 9.3 · **CWE:** 200 / 798

## Summary

A debug route `/cek-key` called `dd(env('GEMINI_API_KEY'))`, which dumped the full Gemini API key to
the response body. It was reachable **without authentication** and also sent the key via query string
in a Gemini request.

## Usage

```bash
# Probe only (safe):
python3 POC_api_key_leak.py --target http://127.0.0.1:8000 --check

# Show leaked key if present (evidence):
python3 POC_api_key_leak.py --target http://127.0.0.1:8000 --exploit
```

## Interpretation

- `404` → patch applied (endpoint removed).
- `200` + `AIza...` → key exposed (critical).

## Remedy applied

Deleted the `/cek-key` route entirely. **Operational:** rotate `GEMINI_API_KEY` / `GROQ_API_KEY`
because the key was previously in a public query string.

## Disclaimer

Authorized testing / education only.