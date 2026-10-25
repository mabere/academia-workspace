# Tahap 0 — Audit dan keputusan dasar

Tanggal audit: 23 Oktober 2026  
Ruang lingkup: pemeriksaan read-only terhadap workspace `academic-hub`; belum ada perubahan kode aplikasi.

## Ringkasan kondisi

- Proyek sudah berupa aplikasi Laravel 13.17.0 dengan Livewire 4.1 dan PHP CLI 8.5.11. Pola UI saat ini adalah Laravel/Blade; Vite/Tailwind menjadi toolchain aset.
- WriteWise sudah memiliki autentikasi dasar, peran `public`, `student`, `lecturer`, dan `admin`, proyek, tugas, rubrik awal, penugasan mahasiswa, submission dengan revisi terpisah, serta feedback manual dengan status draf/publikasi.
- LitSynth, validasi pakar, GuruBahasa, Output & Evidence, dan IndoEFL CorpusLab belum terlihat di rute/model/migrasi aktif. Dashboard menandai WriteWise sebagai pilot.
- Tidak ada direktori `.git` pada root workspace. Karena itu perubahan belum terlindungi riwayat/branch Git dan perintah `git status` tidak dapat digunakan.
- `.env.example` memakai SQLite secara default. PHP CLI XAMPP tidak memuat `pdo_sqlite` secara default, walaupun DLL tersedia. Dengan memuat `pdo_sqlite` dan `sqlite3` saat proses PHP dijalankan, PHPUnit dapat memakai SQLite in-memory. Jangan mengubah `php.ini` global hanya untuk proyek ini.
- Pada tindak lanjut tahap 1, Pint, PHPStan, PHPUnit, dan build aset berhasil. Daftar rute juga berhasil dimuat.
- `phpunit.xml` sudah mengisolasi tes ke SQLite in-memory. Setelah driver tersedia, jalankan kembali tes; jangan mengubah database `.env` hanya untuk menyiasati tes.

## Keputusan produk dan teknis

1. Lanjutkan dengan Laravel, Livewire, Blade, dan pola yang ada. Tambahkan teknologi baru hanya bila kebutuhan pilot membuktikannya.
2. Gunakan WriteWise sebagai pilot alur pengajaran: dosen membuat tugas, mahasiswa yang ditugaskan mengirim revisi, dosen meninjau feedback, lalu menerbitkannya.
3. Pendaftaran publik boleh menghasilkan akun `student` atau `public` saja. Peran `lecturer`/`admin` diberikan melalui mekanisme operator tepercaya.
4. Jangan mengirim teks mahasiswa atau dokumen penelitian ke layanan AI sampai provider, payload yang diizinkan, pemberitahuan/persetujuan, retensi, penghapusan, dan batas biaya ditetapkan. Implementasi saat ini menolak permintaan AI dan memberi tahu pengguna bahwa teks tidak dikirim.
5. Data akademik dan unggahan mendatang harus mengikuti izin server-side. File privat, retensi/penghapusan, backup/pemulihan, serta verifikasi akun belum lengkap; jangan gunakan data riil sampai kontrol yang diperlukan siap.
6. Aktifkan Git pada workspace sebelum perubahan implementasi berikutnya agar diff dan pemulihan dapat ditinjau.

## Peta tahap 0–11

| Tahap | Fokus | Gerbang hasil |
|---|---|---|
| 0 | Audit dan keputusan dasar | Kondisi, risiko, keputusan, dan urutan kerja tercatat |
| 1 | Identitas dan akses | Peran istimewa tepercaya; akses server diuji lintas peran |
| 2 | Proyek Tri Dharma | Siklus proyek dan keanggotaan sesuai izin |
| 3 | Kerangka bersama | Navigasi/dashboard dari data nyata, status modul jujur, error konsisten |
| 4 | WriteWise | Revisi tersimpan terpisah; feedback draf tersembunyi sampai diterbitkan |
| 5 | Gerbang AI | Provider dan kebijakan data disetujui sebelum integrasi aktif |
| 6 | LitSynth | Sumber privat, ekstraksi terlacak ke halaman, verifikasi manusia |
| 7 | Validasi ahli | Undangan terbatas, statistik terdefinisi, audit dan pencabutan akses |
| 8 | GuruBahasa | Materi draf dapat ditinjau, disunting, disimpan, dan diekspor |
| 9 | Output & Evidence | Artefak lintas modul dapat ditemukan tanpa melewati izin |
| 10 | Integrasi dan hardening | Akses, privasi, backup, aksesibilitas, dokumentasi, dan kesiapan operasional diperiksa |
| 11 | IndoEFL CorpusLab | Dipertimbangkan setelah MVP stabil dan protokol etik/privacy disetujui |

## Urutan ketergantungan

Audit/keputusan → identitas/akses → proyek → kerangka bersama → WriteWise → uji pilot. Gerbang provider dan kebijakan data mendahului AI Gateway. Setelah alur pilot tervalidasi, LitSynth mengarah ke portal validasi ahli; GuruBahasa berjalan sebagai modul pengabdian; keduanya memberi data terverifikasi untuk Output & Evidence, kemudian hardening. CorpusLab tetap fase lanjutan.

## Kriteria pilot WriteWise

- Dosen membuat tugas dan menetapkan mahasiswa yang sudah terdaftar.
- Mahasiswa hanya dapat melihat tugas yang ditetapkan kepadanya dan membuat revisi baru tanpa menimpa revisi lama.
- Dosen menyusun/mengedit feedback sebagai draf dan menerbitkannya secara eksplisit.
- Mahasiswa tidak melihat feedback sebelum publikasi.
- Akses silang antar-mahasiswa ditolak oleh server.
- Tes fitur tersebut lulus di lingkungan tes yang memiliki driver database.

## Status pelaksanaan tahap 1 (23 Oktober 2026)

Selesai:

- `pdo_sqlite` dan `sqlite3` tersedia melalui DLL instalasi PHP aktif dan berhasil dimuat untuk proses pemeriksaan tanpa mengedit `php.ini`.
- Registrasi kini memasukkan hanya field tervalidasi yang diizinkan secara eksplisit.
- Halaman proyek mensyaratkan pengguna berstatus staf selain sebagai pemilik/anggota; mahasiswa yang kebetulan ada di pivot anggota tidak dapat membuka proyek.
- Akses mahasiswa hanya ke tugas yang ditetapkan tetap ditegakkan di server. Pendaftaran akun publik tidak dapat memilih role istimewa. Perubahan role hanya tersedia melalui perintah console tepercaya; production memerlukan konfirmasi `--force`.
- Tes regresi untuk batas akses proyek/tugas, registrasi, dan perintah role ditambahkan.
- Pemeriksaan: Pint lolos; PHPStan lolos; PHPUnit 8 tes/29 assertion lolos; Vite build lolos.

Masih perlu:

1. Lengkapi pemulihan akun dan verifikasi email sebelum pilot dengan pengguna nyata; kebijakan apakah akun belum terverifikasi boleh masuk belum diputuskan.
2. Peran `admin` saat ini diperlakukan sebagai staf luas; cakupan admin perlu ditinjau pada tahap kebijakan akses.
3. Jangan mengaktifkan integrasi AI atau unggahan dokumen privat sebelum gerbang data dan kontrol penyimpanan siap.

## Status pelaksanaan tahap 2 (23 Oktober 2026)

Selesai:

- Pemilik dapat membuat, melihat, mengubah, dan mengarsipkan proyek Tri Dharma.
- Pemilik dapat menambahkan/menghapus editor proyek. Hanya akun dosen/admin yang dapat menjadi editor; mahasiswa dan pengguna umum ditolak.
- Editor dapat melihat proyek aktif dan mengelola tugas WriteWise. Hanya pemilik yang dapat mengubah metadata proyek, mengelola keanggotaan, atau mengarsipkan.
- Proyek arsip dikeluarkan dari daftar aktif. Detail/tugas lama tetap dapat dibaca, sedangkan perubahan dan pembuatan tugas baru ditolak.
- Operasi tambah/hapus editor dicatat di audit log.
- Tes mencakup batas akses pemilik/editor, role yang boleh menjadi anggota, daftar proyek aktif, dan lifecycle arsip.

Pemeriksaan tahap 2: Pint lolos; PHPStan lolos; PHPUnit 10 tes/49 assertion lolos; Vite build lolos.

Git tetap ditunda sesuai keputusan pengguna sampai implementasi pengembangan selesai.

## Perbaikan registrasi dan provisioning superadmin (23 Oktober 2026)

- Error registrasi terjadi karena database lokal `database/database.sqlite` masih hanya memiliki migrasi dasar. Migrasi proyek, penambahan `users.role`, dan tabel WriteWise tercatat pending.
- Migrasi pending dijalankan memakai `migrate --force` (bukan `migrate:fresh`), sehingga data/tabel yang sudah ada tidak dihapus. Status migrasi kini seluruhnya `Ran`.
- Role `superadmin` ditambahkan sebagai role staf istimewa dan tetap tidak dapat dipilih saat registrasi publik. Operator dapat membuat akun awal melalui `hub:superadmin:create email nama`; password dimasukkan secara tersembunyi dan dikonfirmasi di console. Production mensyaratkan `--force` setelah verifikasi operator.
- `hub:user-role` kini juga menerima role `superadmin` untuk akun existing.
- Verifikasi: tes registrasi dan provisioning superadmin termasuk dalam PHPUnit; Pint, PHPStan, dan 12 tes/58 assertion lolos. `migrate:status` menunjukkan seluruh enam migrasi selesai.

## Status pelaksanaan tahap 3 (23 Oktober 2026)

Implementasi kerangka bersama diperbarui:

- Navigasi menandai halaman aktif, menyesuaikan tautan proyek untuk staf, membungkus konten dengan baik di layar sempit, dan menyediakan tautan lewati ke konten utama.
- Pesan status sukses dan validasi dipusatkan pada partial bersama dengan peran aksesibilitas yang sesuai.
- Dashboard mengambil ringkasan proyek aktif yang dimiliki atau dikelola sebagai editor, tugas, serta submission. Mahasiswa hanya melihat tugas terbit yang ditetapkan kepada mereka; pengguna umum mendapat empty state yang jelas.
- Kartu modul menyebut status aktual: WriteWise tersedia dengan feedback manual; AI nonaktif; modul lain direncanakan atau fase lanjutan.
- Daftar proyek menjelaskan akses pemilik/editor, pemilik proyek, dan pilar Tri Dharma.

Git tetap ditunda sesuai keputusan pengguna. Pemeriksaan tes/build tidak dijalankan pada perubahan tahap 3 ini.

### Refaktor shell dashboard sebelum tahap 4

- Layout kini memisahkan navigasi atas, sidebar workspace yang dapat diciutkan, area konten, dan footer.
- Sidebar menempel pada area kerja antara header dan footer. Saat diciutkan, grid konten melebar; footer tetap sejajar dengan kolom konten dan tidak memakai kolom sidebar.
- Pada layar kecil, sidebar berubah menjadi drawer dengan backdrop; tombol mendukung Escape, `aria-expanded`, dan label aksesibel.
- Halaman publik memakai header, konten, dan footer tanpa sidebar.
- Pemeriksaan tes/build tidak dijalankan untuk refaktor ini.

## Status pelaksanaan tahap 4 — WriteWise ELT (23 Oktober 2026)

Implementasi pilot kini mendukung:

- Dosen/editor membuat dan menyunting tugas serta penugasan mahasiswa.
- Setiap perubahan tugas menyimpan snapshot versi baru. Migrasi membuat snapshot v1 untuk tugas lama tanpa menghapus data.
- Rubrik memiliki versi tersendiri; perubahan nama/skala/kriteria menyimpan versi baru dan versi lama tetap tersimpan.
- Submission dan setiap revisi menyimpan referensi versi tugas serta rubrik yang berlaku saat revisi dikirim.
- Mahasiswa hanya dapat membuka tugas terbit yang ditugaskan kepadanya; tugas draf ditolak server-side.
- Riwayat revisi dapat dibaca dan dibandingkan berdampingan. Feedback dosen disimpan sebagai draf, dapat disunting, dan hanya tampil ke mahasiswa setelah diterbitkan.
- AI tetap nonaktif; endpoint feedback AI tetap menolak permintaan tanpa mengirim teks ke provider.

Migrasi diterapkan dengan `php artisan migrate`, tanpa menghapus data. Pint lolos; sintaks controller/model/migrasi lolos; Blade berhasil dikompilasi; rute pembaruan tugas terdaftar. PHPUnit/build tidak dijalankan pada giliran ini.

## Status gerbang AI tahap 5 (23 Oktober 2026)

- Konfigurasi AI dipisahkan untuk aktivasi, persetujuan pemrosesan data, izin payload teks mahasiswa, provider, secret, model, timeout, dan versi prompt. Aktivasi serta dua flag persetujuan default `false`.
- Ditambahkan kontrak provider server-side dan `AiGateway`. Gateway menolak permintaan sebelum adapter dipanggil bila gerbang belum lengkap, konfigurasi tidak cocok, atau adapter belum terpasang.
- Keluaran provider divalidasi sebelum dibuat sebagai feedback `draft`. Dosen tetap harus meninjau, menyunting, dan menerbitkannya secara manual.
- Permintaan yang diblokir hanya mencatat aktor, revisi target, dan kode alasan di audit log; teks mahasiswa dan secret tidak dicatat.
- Belum ada provider adapter dan belum ada teks yang dikirim keluar. Provider, persetujuan pemrosesan, payload yang diizinkan, retensi/penghapusan, pemberitahuan/consent, dan batas biaya masih harus diputuskan sebelum aktivasi.

## Status tahap 11 — IndoEFL CorpusLab (23 Oktober 2026)

- Modul korpus lokal tersedia pada proyek penelitian: pencatatan protokol dan metadata persetujuan etik, review aplikasi oleh superadmin, pengumpulan manual dengan pernyataan consent, pembersihan pola email/URL/nomor telepon, anotasi, inklusi/eksklusi, pencarian KWIC, dan ekspor CSV terotorisasi.
- Teks asli tidak disimpan sebagai versi terpisah. Pembersihan otomatis terbatas dan wajib ditinjau manusia; tidak menjamin anonimisasi.
- Scheduler menjalankan purge entri dan anotasi setelah masa retensi. Operator perlu memasang pemanggil scheduler Laravel di server dan menyelaraskan retensi backup.
- Migrasi `2026_10_23_140000_create_indoefl_corpus_tables` diterapkan secara aditif. Status migration seluruhnya Ran.
- Pemeriksaan sintaks PHP, Pint, cache Blade, route list, scheduler, dan build aset berhasil. Test suite tidak dijalankan pada tahap ini.
- Jangan memasukkan teks partisipan nyata sebelum institusi mengonfirmasi persetujuan etik, consent, kontrol akses, retensi, serta perlindungan backup. Detail batasan terdapat pada `docs/tahap-11-indoefl-corpuslab.md`.
