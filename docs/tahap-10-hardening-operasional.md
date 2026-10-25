# Tahap 10 — Integrasi dan hardening operasional

Tahap ini merapikan batas akses lintas modul, perlindungan berkas, header HTTP, serta prosedur operasi dan pemulihan. Perubahan bersifat kompatibel dengan alur pilot yang sudah ada.

## Perubahan aplikasi

- Disk `local` menunjuk ke `storage/app/private` dan `serve` dinonaktifkan. PDF LitSynth hanya dapat diunduh melalui route terautentikasi yang memeriksa akses proyek.
- Semua respons mendapat `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: no-referrer`, dan kebijakan izin kamera, mikrofon, serta geolokasi yang dinonaktifkan.
- Halaman berautentikasi dan halaman token validator diberi `Cache-Control: private, no-store` agar konten privat tidak disimpan cache browser/proxy.
- Penyimpanan PDF, pembuatan metadata, dan pencatatan audit awal kini konsisten. Jika pencatatan gagal, file yang baru disimpan akan dibersihkan. Kegagalan menghitung SHA-256 menghentikan proses.
- Penghapusan PDF dilakukan setelah transaksi penghapusan data selesai agar kegagalan basis data tidak lebih dulu menghilangkan berkas sumber.
- Audit menemukan dan memperbaiki variabel `$project` yang tidak ditangkap dalam transaksi pembaruan metadata/matriks sumber.
- Validasi konten, batas akses proyek, unduhan PDF privat, serta status publikasi lintas modul diperiksa saat review integrasi. Artefak Output Evidence tetap hanya untuk pemilik/editor proyek dan staf.

## Pengaturan produksi

Gunakan HTTPS dan setidaknya konfigurasi berikut pada `.env` produksi:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

Pertahankan `APP_KEY` yang sama ketika memindahkan atau memulihkan aplikasi. Jangan masukkan `.env`, kredensial database, kunci AI, PDF sumber, ataupun backup ke Git atau web root. Pastikan folder `storage` dan `bootstrap/cache` dapat ditulis oleh pengguna PHP, tetapi tidak dapat dijelajahi publik. Atur batas upload PHP/web server lebih besar dari `LITSYNTH_MAX_PDF_KILOBYTES` bila unggahan PDF sebesar itu memang diizinkan.

Setelah mengubah `.env`, jalankan `php artisan config:clear` lalu `php artisan config:cache`. Jalankan migrasi produksi secara terencana dengan `php artisan migrate --force`. Jika pemrosesan antrean kelak digunakan, jalankan worker yang diawasi process manager dan siapkan restart worker setelah deploy. Alur AI saat ini tetap nonaktif secara default; jangan aktifkan sebelum kebijakan pemrosesan data disetujui.

## Backup

Backup basis data dan `storage/app/private` dalam satu jendela waktu agar metadata dan PDF cocok. Simpan salinan terenkripsi di luar server dan di luar web root, batasi aksesnya, dan tetapkan masa retensi. Sertakan konfigurasi deployment secara aman; simpan `APP_KEY` di secret manager yang terpisah.

### SQLite

Jangan menyalin file SQLite yang sedang aktif sebagai satu-satunya strategi backup. Gunakan SQLite Online Backup API atau CLI `sqlite3` agar snapshot konsisten:

```sh
sqlite3 /path/to/database.sqlite ".backup '/secure-backup/academic-hub-YYYYMMDD.sqlite'"
tar -czf /secure-backup/academic-hub-private-YYYYMMDD.tar.gz -C /path/to/storage/app private
```

Jalankan file backup di lokasi privat yang hanya dapat dibaca akun deployment/backup. Bila CLI SQLite tidak tersedia, gunakan fasilitas backup konsisten dari host atau hentikan penulisan aplikasi sebelum menyalin database.

### MySQL

Gunakan `mysqldump` yang sesuai dengan versi server. Untuk tabel InnoDB:

```sh
mysqldump --single-transaction --routines --triggers --user=DB_USER --host=DB_HOST DB_NAME > /secure-backup/academic-hub-YYYYMMDD.sql
tar -czf /secure-backup/academic-hub-private-YYYYMMDD.tar.gz -C /path/to/storage/app private
```

Masukkan kata sandi melalui berkas opsi terlindungi atau secret manager, bukan argumen command line atau repositori.

## Pemulihan

1. Siapkan lingkungan pemulihan privat dengan versi aplikasi dan ekstensi PHP yang kompatibel. Jangan menimpa produksi saat latihan.
2. Pulihkan snapshot database dan arsip `storage/app/private` yang berasal dari waktu backup yang sama; pulihkan kepemilikan dan izin folder untuk pengguna PHP.
3. Pasang `.env` yang tepat, termasuk `APP_KEY`, koneksi database, URL HTTPS, dan `FILESYSTEM_DISK=local`. Jangan menggunakan `.env` dari mesin lain tanpa memeriksa rahasianya.
4. Jalankan `php artisan migrate:status`, lalu `php artisan config:clear` dan `php artisan config:cache`. Jangan menjalankan `migrate:fresh` pada data yang hendak dipulihkan.
5. Periksa login, akses peran, daftar proyek, metadata LitSynth, unduhan PDF terotorisasi, artefak terbit, dan log. Catat waktu pemulihan serta backup yang digunakan.
6. Latih pemulihan berkala di staging. Backup belum dianggap andal sampai pemulihan database dan file berhasil diperiksa bersama.

## Batas operasional pilot

- Indeks Output Evidence menggabungkan artefak proyek yang dapat diakses dalam memori. Ini sesuai untuk pilot; bila jumlah data membesar, pindahkan filter/paginasi ke query per sumber sebelum peningkatan skala.
- Ekstraksi PDF memakai `pdftotext` lokal opsional. Tanpa binary tersebut, dokumen tetap privat dan matriks dapat dilengkapi manual.
- Pengiriman notifikasi pakar, AI eksternal, dan antrean ekstraksi belum menjadi prasyarat alur saat ini.
- Pemeriksaan aksesibilitas dan responsif dilakukan melalui review komponen/layout yang ada; verifikasi lintas browser dan pembaca layar masih perlu dijadwalkan sebelum peluncuran luas.

## Pemeriksaan perubahan

Pemeriksaan sintaks PHP, formatter, daftar route, dan kompilasi view dilakukan tanpa menjalankan test suite. Test suite tidak dijalankan pada tahap ini. Checklist deploy dan pemulihan di atas harus dipakai pada staging sebelum peluncuran produksi.
