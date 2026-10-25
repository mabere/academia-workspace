# Tahap 11 — IndoEFL CorpusLab

## Cakupan implementasi

CorpusLab tersedia di proyek berpilar penelitian dan hanya dapat diakses staf pemilik/editor proyek. Mahasiswa dan akun umum tidak menerima akses korpus. Pemilik mencatat protokol; hanya superadmin dapat menandai review aplikasi selesai. Pengumpulan terkunci sebelum status protokol disetujui.

Protokol meminta tujuan penelitian, populasi, sampling, proses consent, otoritas dan nomor rujukan persetujuan etik, rencana anonimisasi/privasi, serta tanggal retensi. Informasi itu adalah metadata yang dimasukkan operator. Review superadmin mencatat gerbang internal aplikasi, tidak membuktikan persetujuan lembaga dan tidak menggantikan komite etik atau tinjauan perlindungan data.

Alur data setelah persetujuan:

1. Peneliti hanya memasukkan teks yang sudah diperiksa dan mengonfirmasi consent telah diperoleh serta dicatat di tempat yang disetujui protokol.
2. Formulir tidak meminta nama atau formulir consent. Kode partisipan dan referensi consent dibatasi ke karakter alfanumerik, titik, garis bawah, dan tanda hubung.
3. Sistem membantu mengganti pola email, URL, dan nomor panjang yang menyerupai nomor telepon. Teks yang disimpan adalah hasil pembersihan tersebut; teks asli tidak disimpan sebagai versi terpisah.
4. Reviewer proyek membuat anotasi, menyertakan atau mengecualikan entri, mencari istilah dengan cuplikan KWIC, lalu mengekspor CSV privat sesuai akses proyek.
5. Penarikan consent atau salah input menghapus entri beserta anotasinya dan mencatat alasan terstruktur tanpa menulis teks ke audit log.
6. Setelah `retention_until`, pembersihan terjadwal menghapus teks, kode partisipan, referensi consent, dan anotasi. Ringkasan protokol serta audit hitungan penghapusan tetap tersimpan.

Audit log hanya memuat ID target, tindakan, dan metadata terbatas. Query error saat penyimpanan teks/anotasi dicatat tanpa payload. Middleware memberi respons `no-store` untuk sesi terautentikasi. Data korpus tidak dimasukkan ke Output & Evidence; yang dapat muncul hanya metadata protokol disetujui sebelum masa retensi berakhir.

## Menjalankan pembersihan retensi

Perintah yang dijadwalkan:

```sh
php artisan corpuslab:purge-expired --dry-run
php artisan corpuslab:purge-expired
```

Gunakan `--dry-run` untuk melihat korpus yang akan diproses. Scheduler memeriksa korpus setiap hari pukul 02.00 waktu aplikasi. Pada server, pastikan Laravel scheduler dipanggil setiap menit (`php artisan schedule:run` melalui cron atau Task Scheduler). Penghapusan ini permanen. Atur tanggal retensi sesuai protokol yang berlaku dan pastikan kebijakan backup menghapus salinan korpus sesuai jadwal retensi institusi; penghapusan dari database aktif tidak menghapus salinan backup yang sudah dibuat.

Aplikasi masih memakai timezone Laravel `UTC` secara default. Tetapkan timezone institusi pada `config/app.php` sebelum mengandalkan waktu scheduler atau batas tanggal retensi dalam produksi.

## Batasan dan gerbang sebelum data nyata

- Penyimpanan saat ini berupa entri manual satu per satu; belum ada impor berkas massal, transkripsi audio/video, alignment, pipeline scraping, atau API eksternal.
- Pembersihan otomatis hanya menangani pola email, URL, dan angka yang menyerupai nomor telepon. Nama, nama lembaga, lokasi, kutipan unik, dan identitas kontekstual memerlukan pemeriksaan dan penggantian manual oleh peneliti. Fitur ini bukan jaminan anonimisasi.
- Kode partisipan tetap merupakan data pseudonim bila dapat dipadankan dengan daftar identitas lain. Simpan daftar penghubung secara terpisah dan jangan memasukkannya ke aplikasi.
- Checkbox consent mencatat pernyataan operator; tidak memverifikasi bahwa consent benar-benar diberikan atau mencakup penggunaan/retensi yang dimaksud.
- Persetujuan etik, dasar pemrosesan, klasifikasi data, akses tim, retensi backup, permintaan akses/penghapusan, dan keamanan hosting harus ditinjau institusi sebelum teks partisipan nyata dimasukkan.
- Tidak ada inferensi statistik otomatis. Hitungan kata, status inklusi, label, dan KWIC adalah alat bantu kerja; interpretasi analisis tetap tanggung jawab peneliti.

## Pemeriksaan implementasi

Verifikasi setelah implementasi mencakup sintaks/format PHP, rute, cache view, dan build aset. Test suite aplikasi tidak dijalankan pada tahap ini. Sebelum pilot korpus, lakukan uji manual lintas peran pada staging dengan data sintetis dan simulasi retensi/purge; jangan memakai teks partisipan asli untuk uji awal.
