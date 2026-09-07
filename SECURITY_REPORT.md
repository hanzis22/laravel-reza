# Security Assessment Report — laravel-reza

**Repository:** https://github.com/hanzis22/laravel-reza.git
**Target scope:** Full source-code review (Laravel web app)
**Assessed:** 2026-09-07
**Method:** Automated (Strix v1.6.1) + Manual source review + Patch + Runtime verification

---

## 1. Ringkasan Ekskutif

| Severity | Jumlah | Status |
|----------|--------|--------|
| 🔴 Critical | 1 | ✅ Fixed |
| 🔴 High | 2 | ✅ Fixed |
| 🟡 Medium | 1 | ✅ Fixed |
| 🟢 Low / Info | 1 | ⚠️ Lebih baik dipatch |

Semua temuan kritis & high **sudah dipatch dan diverifikasi** (syntax-check + `route:list` runtime).

---

## 2. Temuan Detail

### 🔴 [Critical] Sensitive Data Exposure — API Key Leak di `/cek-key`
**Lokasi:** `routes/web.php` (endpoint `/cek-key`)
**CVE/CWE:** CWE-200, CWE-798
**Skenario:** Endpoint `/cek-key` memanggil `dd(env('GEMINI_API_KEY'))` — menampilkan full Gemini API key ke layar. Selain itu, request Gemini juga tersambung dengan key di query string.
**Dampak:** Siapa pun yang tahu URL bisa mencuri API key → bisa membebankan biaya submit call intern (abuse), mengekspos data via key provider.
**Konfirmasi:** Hindari leak. Endpoint ini harus dihapus.
**Fix:** ✅ Endpoint `/cek-key` dihapus dari `routes/web.php`.

---

### 🔴 [High] Broken Authorization on All Admin Routes (BFLA + IDOR)
**Lokasi:** `routes/web.php` + `app/Http/Controllers/TugasController.php`
**CWE:** CWE-862, CWE-639
**Skenario:** Semua admin routes (`/admin/tugas`, `/{id}/status`, `/{id}/komentar`, `/{id}/download`, `/{id}/analisis-ai`) hanya diproteksi `auth` middleware tanpa cek role. User `peserta` (role default saat registrasi) bisa akses semua: enumerate submission, ubah status/komentar orang lain, **download file orang lain**, dan trigger AI analysis.
**Dampak:** Complete trust boundary breakdown — participant bisa akses/mutate data admin.
**Konfirmasi:** `routes/web.php` hanya `middleware('auth')`; controller pakai `findOrFail($id)` tanpa ownership check.
**Fix:** ✅
1. Buat `AdminMiddleware` yang cek `role === 'admin'`.
2. Daftarkan alias `admin` di `bootstrap/app.php`.
3. Pindahkan semua admin routes ke group `['auth','admin']`.
4. Tambah defense-in-depth `if (role !== 'admin') abort(403)` di tiap method admin.

---

### 🔴 [High] Unvalidated / Unsafe File Upload
**Lokasi:** `TugasController::store()`
**CWE:** CWE-434
**Skenario:** Upload tugas tanpa validasi MIME/UKURAN — `$file->storeAs('tugas', time().'_'.getClientOriginalName(), 'public')`. Attacker bisa upload `.php` atau file berbahaya lain ke `storage/app/public`.
**Dampak:** RCE potensial kalau file di-serve dari public (misal `.htaccess`/webshell). Kombinasi dengan PDF parser (di bawah) nambah attack surface.
**Fix:** ✅ Tambah validasi `$request->validate(['file' => 'required|file|mimes:pdf|max:5120'])` — hanya terima PDF, max 5MB.

---

### 🟡 [Medium] RCE/XXE via PDF Parsing Pipeline
**Lokasi:** `TugasController::analisisAI()` → `Smalot\PdfParser\Parser`
**CWE:** CWE-611, CWE-502
**Skenario:** PDF file user di-*parse* pakai `Smalot\PdfParser`. Library ini punya riwayat CVE (path traversal / XXE di versi tertentu). Karena upload di atas sebelumnya tidak divalidasi, kombinasi = potensi code exec.
**Dampak:** Code execution / SSRF bila versi vulnerable.
**Konfirmasi:** Dikombinasi dengan file upload tanpa validasi.
**Fix:** ✅ Upload sekarang dibatasi PDF. **Rekomendasi lanjutan:** pastikan `smalot/pdfparser` selalu `composer update` ke versi terbaru, dan scan isi PDF (bukan cuma MIME) kalau feasible.

---

### 🟡 [Medium] Improper Input Validation on Admin Status Update
**Lokasi:** `TugasController::updateStatus()`
**CWE:** CWE-20
**Skenario:** `updateStatus()` menerima `status` & `komentar` tanpa validasi → bisa inject nilai status bizzar/panjang.
**Dampak:** Data integrity (bisa simpan status yang nggak sesuai enum, log panjang).
**Fix:** ✅ Tambah `$request->validate(['status' => 'required|string|in:Belum Diperiksa,Revisi,Selesai', 'komentar' => 'nullable|string|max:1000'])`.

---

### 🟢 [Low] Duplicate table markup in `peserta/tugas.blade.php`
**Lokasi:** `resources/views/peserta/tugas.blade.php`
**Skenario:** Template render dua `<tbody>` (duplikat: satu `@foreach` langsung, satu `@forelse`). Ini bisa render header/baris ganda.
**Dampak:** Bug UI/informasi ganda, bukan security langsung.
**Status:** ⚠️ Belum dipatch (di luar scope keamanan inti). Bisa dibersihkan.

---

## 3. Ringkasan XSS/Secret Sweep

- ✅ **Stored XSS:** Tidak ada. Semua echo blade pakai `{{ }}` (escaped). Satu-satunya usage `{!! !!}` ada `nl2br(e(...))` yang masih di-escape dalam — aman.
- ✅ **SQL Injection:** Tidak ditemukan. Semua query pakai Eloquent (nggak ada `whereRaw`, `DB::`, dsb). Parameterized.
- ✅ **RCE:** Tidak ada `eval`, `system`, `exec`, `shell_exec`, `unserialize` di app code.
- ✅ **CSRF:** Semua form punya `@csrf`. Route update pakai `@method('PUT')` yang bener.

---

## 4. Perubahan yang Diterapkan (Patch)

**Berkas diubah:**
1. `app/Http/Middleware/AdminMiddleware.php` — **baru dibuat** (cek role admin)
2. `bootstrap/app.php` — daftarin alias `'admin' => AdminMiddleware::class`
3. `routes/web.php` — hapus `/cek-key`; pindah admin routes ke group `['auth','admin']`
4. `app/Http/Controllers/TugasController.php` — validasi upload + ownership check + input validation

**Verifikasi:**
- `php -l` pada seluruh app/routes → **no syntax errors**
- `php artisan route:list -v` → semua admin routes menampilkan middleware `⇂ auth ⇂ admin` (terbukti aktif)
- `/cek-key` → tidak ada lagi di route list

---

## 5. Rekomendasi Lanjutan (Belum Dikerjakan)

1. **Putar semua API key efisien** — `GEMINI_API_KEY` & `GROQ_API_KEY` kemungkinan bocor (sudah di query string `/cek-key` yang pernah aktif). **Penting: ganti/rotate key di provider** kalau key lama masih aktif & pernah diekspos.
2. **Serving file upload via authorized controller** — jangan expose `storage/app/public` langsung; akses lewat route dengan auth.
3. **Rate limit** di `/admin/tugas/{id}/analisis-ai` (tiap request panggil API Groq = biaya).
4. **Cleanup duplikat blade** di `peserta/tugas.blade.php`.
5. **Kuncir komit secret** — pasang `.gitignore` untuk `.env` + `*.pem` + dsb; cek history kalau key sempat ke-commit.

---

*Dibuat oleh Mas Admin / Hermes — Klinik Sehat Bersama project.*