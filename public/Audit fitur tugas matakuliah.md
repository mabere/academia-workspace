# Audit fitur tugas matakuliah

> aplikasi academia-hub ini sudah berjalan dan sudah deploy di hosting. Sebagai senior developer dan auditor, lakukan audit aplikasi khususnya fitur tugas matakuliah

<details><summary>4 previous messages</summary>

> Saya akan menelusuri alur fitur tugas matakuliah dari rute, komponen, model, migrasi, hingga kontrol akses dan upload berkas. Audit ini saya fokuskan pada temuan yang bisa dibuktikan dari kode, lalu saya rangkum risiko dan prioritas perbaikannya tanpa mengubah aplikasi.
>
> <details><summary>Ran 2 commands</summary>
>
> - Ran `Get-ChildItem -Force | Select-Object Mode,Name; rg -n -i "tugas|assignment|coursework|deadline|submission" app routes resources database tests`
> - Ran `Get-Content app/Http/Controllers/CourseAssignmentController.php; Get-Content app/Policies/WritingAssignmentPolicy.php; Get-Content routes/web.php | Select-Object -Skip 95 -First 45; rg -n "assignment_students|writing_assignments|writing_submissions|assignment_questions|assignment_question_answers" database/migrations`
>
> </details>
>
> Alur utamanya berada di `CourseAssignmentController` dan memakai tabel WriteWise bersama tugas proyek. Saya sedang memeriksa batas akses mahasiswa/dosen, konsistensi pendaftaran kelas, penilaian, serta validasi ujian dan ekspor nilai—area yang paling mungkin berdampak langsung pada kerahasiaan dan integritas nilai.
>
> <details><summary>Ran 5 commands</summary>
>
> - Ran `rg -n "function (store|update|destroy|grade|printGrades|exportGrades)|Route::middleware|courses.assignments|where\('course_id'|assignment->students|submission" app/Http/Controllers/CourseAssignmentController.php app/Http/Controllers/AssignmentExamController.php app/Http/Controllers/CourseStudentController.php routes/web.php; Get-Content app/Http/Controllers/CourseAssignmentController.php | Select-Object -Skip 300 -First 260; Get-Content app/Http/Controllers/AssignmentExamController.php`
> - Ran `Get-Content resources/views/courses/grades-print.blade.php; Get-Content resources/views/assignments/exam.blade.php | Select-Object -First 180; Get-Content app/Http/Controllers/WritingAssignmentController.php | Select-Object -Skip 100 -First 120; Get-Content app/Http/Controllers/CourseStudentController.php | Select-Object -Skip 100 -First 100; Get-Content database/migrations/2026_10_22_080100_create_writewise_tables.php`
> - Ran `rg -n "answer_key|printGrades|grades.print|assignments.exam|status.*published|due_at" resources/views/assignments/exam.blade.php app/Http/Controllers/AssignmentExamController.php; Get-Content app/Http/Controllers/CourseStudentController.php | Select-Object -First 35; rg -n "->students\(\)->sync|assignment_students|sync\(" app/Http/Controllers`
> - Ran `rg -n "function enroll|students\(\)|assignment_students|syncWithoutDetaching|attach\(" app/Http/Controllers/CourseStudentController.php; rg -n "function printGrades|if \(! \$user->isStaff\)|assignments.submissions|where\('course_id'|function submitExam|function take|function streamAudio|function recordAudioPlay|isEnrolled|function update\(|status' => \$data\['status'\]" app/Http/Controllers/CourseAssignmentController.php app/Http/Controllers/AssignmentExamController.php app/Policies/WritingAssignmentPolicy.php`
> - Ran `rg -n "function printGrades|function exportGrades|function submitExam|function take|function streamAudio|function recordAudioPlay|isEnrolled|function update\(|function unenroll|assignment->students\(\)->sync|function submitRevision" app/Http/Controllers/CourseAssignmentController.php app/Http/Controllers/AssignmentExamController.php app/Http/Controllers/CourseStudentController.php app/Policies/WritingAssignmentPolicy.php; rg -n "grades.print|grades.export" routes/web.php`
>
> </details>
</details>

# Hasil audit fitur tugas matakuliah

Audit dilakukan secara statis pada kode lokal aplikasi. Saya tidak mengubah kode atau mengakses hosting produksi. Temuan berikut layak diprioritaskan sebelum perubahan fitur berikutnya.

## Temuan utama

1. **Risiko tinggi — mahasiswa bisa melihat nilai seluruh kelas.**
   Endpoint cetak rekap mengizinkan mahasiswa yang terdaftar membuka halaman nilai, lalu memuat semua mahasiswa, nilai tugas, nilai akhir, dan statistik kelas. Ini membocorkan data akademik antarmahasiswa. Batasi endpoint cetak untuk dosen pengampu dan administrator, sama seperti endpoint ekspor.
   [CourseAssignmentController.php:476](./app/Http/Controllers/CourseAssignmentController.php:476) · [grades-print.blade.php](./resources/views/courses/grades-print.blade.php)

2. **Risiko tinggi — pengiriman ujian tidak menegakkan status terbit, tenggat, atau timer di server.**
   `submitExam` memeriksa pendaftaran mahasiswa di kelas, tetapi tidak memastikan tugas berstatus `published`, tenggat belum lewat, dan ujian sudah dimulai. Timer diperiksa hanya jika `started_at` sudah ada. Karena pemeriksaan tenggat berada di halaman `take`, permintaan langsung ke endpoint pengumpulan dapat melewati aturan tersebut. Penegakan harus dilakukan kembali pada endpoint pengumpulan.
   [AssignmentExamController.php:268](./app/Http/Controllers/AssignmentExamController.php:268) · [AssignmentExamController.php:193](./app/Http/Controllers/AssignmentExamController.php:193)

3. **Risiko tinggi — perubahan pendaftaran kelas tidak memperbarui akses tugas.**
   Saat tugas dibuat, mahasiswa yang terdaftar disalin ke `assignment_students`. Namun, saat mahasiswa dikeluarkan dari kelas, kode hanya menghapus keanggotaannya dari kelas. Kebijakan pengumpulan tugas menilai akses dari daftar `assignment_students`; akibatnya, mantan mahasiswa masih dapat melihat dan mengumpulkan tugas tipe tulisan yang sudah ditugaskan kepadanya. Samakan sumber pemeriksaan akses atau sinkronkan daftar akses ketika pendaftaran berubah.
   [CourseAssignmentController.php:137](./app/Http/Controllers/CourseAssignmentController.php:137) · [WritingAssignmentPolicy.php:31](./app/Policies/WritingAssignmentPolicy.php:31) · [CourseStudentController.php:140](./app/Http/Controllers/CourseStudentController.php:140)

4. **Risiko sedang — soal ujian dan audio tidak memeriksa publikasi tugas.**
   Halaman ujian, endpoint audio, dan pencatatan pemutaran hanya memeriksa apakah pengguna terdaftar di kelas. Mahasiswa dapat mencoba membuka materi ujian yang masih berstatus draf jika mengetahui URL tugas. Periksa status tugas dan cakupan mahasiswa pada setiap endpoint tersebut.
   [AssignmentExamController.php:113](./app/Http/Controllers/AssignmentExamController.php:113) · [AssignmentExamController.php:193](./app/Http/Controllers/AssignmentExamController.php:193) · [AssignmentExamController.php:146](./app/Http/Controllers/AssignmentExamController.php:146)

5. **Risiko sedang — validasi data soal dan bobot belum memadai.**
   Validasi soal menerima `options` sebagai array tanpa validasi ukuran, format, atau batas teks per pilihan. `answer_key` tidak divalidasi terhadap pilihan yang tersedia. Untuk tugas hybrid, bobot pilihan ganda dan esai masing-masing dapat diatur 0–100 tanpa aturan agar jumlahnya 100. Ini dapat menghasilkan soal atau perhitungan nilai yang tidak konsisten.
   [AssignmentExamController.php:46](./app/Http/Controllers/AssignmentExamController.php:46) · [CourseAssignmentController.php:230](./app/Http/Controllers/CourseAssignmentController.php:230)

## Catatan audit

Kontrol akses untuk pengumpulan tulisan memakai kebijakan terpusat, dan ekspor CSV sudah membatasi akses ke dosen pengampu serta administrator. Namun, beberapa endpoint ujian memakai pemeriksaan akses tersendiri, sehingga aturan publikasi dan pendaftaran bisa berbeda antarjalur.

Saya belum menjalankan pengujian; audit ini berbasis pembacaan kode. Prioritas perbaikan yang disarankan: tutup kebocoran rekap nilai, tegakkan aturan ujian pada server, lalu rapikan sinkronisasi akses saat mahasiswa keluar dari kelas.

> Sebelum memperbaiki hasil temuan audit, ada juga yang perlu didiskusikan terkait tugas yang sudah disubmit mahasiswa tidak bisa diakses/tidak ada section menampilkan tugas. Yang ada hanya link berapa submisi yang sudah masuk. ini tugas yang sudah ada dan terkait: [https://academic-hub.test/assignments/6](https://academic-hub.test/assignments/6)
> periksa. dan hasil pemeriksaan halaman dosen di tab tugas status submission masih draft/terkirim meski mahasiswa sudah mengklik tombol kirim tugas

<details><summary>2 previous messages</summary>

> Saya akan telusuri halaman tugas `/assignments/6`, cara halaman dosen memuat daftar submisi, serta apa yang dilakukan tombol kirim. Saya akan membandingkan status yang tersimpan dengan label yang ditampilkan untuk mencari penyebabnya.
>
> <details><summary>Ran 4 commands</summary>
>
> - Ran `Get-Content app/Http/Controllers/WritingAssignmentController.php | Select-Object -First 180; Get-Content resources/views/assignments/show.blade.php; rg -n "submissions|submission|Draft|Terkirim|status" resources/views/courses resources/views/assignments`
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1435 -First 140; rg -n "assignments.*submissions|with\(.*submissions|load\(.*assignments|assignments" app/Http/Controllers/CourseController.php app/Models/Course.php; Get-Content app/Models/WritingSubmission.php; Get-Content database/migrations/2026_10_27_135000_add_grading_to_writing_submissions_table.php; Get-Content database/migrations/2026_10_29_150000_add_multimodal_assessment_fields_and_tables.php | Select-Object -Skip 35 -First 35`
> - Ran `Get-Content app/Http/Controllers/CourseController.php | Select-Object -Skip 75 -First 60; rg -n "status' => 'submitted'|status' => 'draft'|firstOrCreate\(\['assignment_id'|submitted_at.*now" app/Http/Controllers/WritingAssignmentController.php app/Http/Controllers/AssignmentExamController.php; Get-Content app/Http/Controllers/WritingAssignmentController.php | Select-Object -Skip 175 -First 55; rg -n "CREATE TABLE.*writing_submissions|writing_submissions" database`
> - Ran `php artisan tinker --execute="dump(DB::table('writing_assignments')->where('id',6)->first(['id','title','status','course_id','assignment_type'])); dump(DB::table('writing_submissions')->where('assignment_id',6)->get(['id','student_id','status','submitted_at','graded_at','score'])); dump(DB::table('writing_revisions')->join('writing_submissions','writing_submissions.id','=','writing_revisions.submission_id')->where('writing_submissions.assignment_id',6)->get(['writing_revisions.id','writing_revisions.submission_id','writing_revisions.revision_number','writing_revisions.submitted_at']));"`
>
> </details>
</details>

Saya sudah menelusuri alur tugas dan tampilan dosen di kode. **Belum ada perubahan yang saya lakukan.**

### Akses isi tugas yang dikumpulkan

Isi submission memang ditampilkan di halaman detail tugas: dosen dapat membuka `/assignments/6` dan melihat revisi serta teks yang dikirim. Namun, di tab tugas mata kuliah, panel dosen hanya menampilkan daftar mahasiswa, status, dan kolom nilai. Tidak ada tautan dari baris submission untuk membuka isi tugas. Tombol **“Tampilkan Daftar Submisi”** juga harus dibuka terlebih dahulu. Ini sesuai dengan kesan bahwa yang tersedia hanya jumlah submission, tanpa akses yang jelas ke isinya.

- Daftar ringkas dan tombol buka panel: [courses/show.blade.php:1476](./resources/views/courses/show.blade.php:1476)
- Tautan mahasiswa ke detail tugas, tanpa tautan serupa di tabel dosen: [courses/show.blade.php:1542](./resources/views/courses/show.blade.php:1542)
- Tampilan isi revisi untuk dosen di halaman detail: [assignments/show.blade.php:440](./resources/views/assignments/show.blade.php:440)

### Arti status “Draf/Terkirim”

Pada alur tugas tulisan, tombol kirim **memang menyimpan status `submitted`** dan waktu pengiriman. Jadi label dosen **“Draf/Terkirim”** bukan bukti bahwa pengiriman gagal; label itu menggabungkan status menunggu penilaian dengan konsep draf, sehingga tidak menjelaskan keadaan submission dengan tepat. Kode juga mengirim notifikasi dengan istilah “Submisi Draf Tugas Baru”, yang menambah kebingungan.
[WritingAssignmentController.php:200](./app/Http/Controllers/WritingAssignmentController.php:200) · [WritingAssignmentController.php:214](./app/Http/Controllers/WritingAssignmentController.php:214) · [courses/show.blade.php:1509](./resources/views/courses/show.blade.php:1509)

Ada satu masalah status yang lebih mendasar untuk tugas ujian: saat mahasiswa hanya membuka halaman ujian, `take()` sudah membuat record submission. Kolom status di database memiliki nilai bawaan `submitted`. Akibatnya, membuka ujian berpotensi tercatat dan terhitung sebagai submission sebelum mahasiswa menekan tombol kirim.
[AssignmentExamController.php:220](./app/Http/Controllers/AssignmentExamController.php:220) · [create_writewise_tables.php:54](./database/migrations/2026_10_22_080100_create_writewise_tables.php:54)

**Kesimpulan:** untuk tugas tulisan, status terkirim memang disimpan, tetapi labelnya ambigu dan akses isi submission dari panel dosen kurang jelas. Untuk ujian, ada risiko record tercipta terlalu awal dengan status bawaan terkirim.

Saya belum bisa memastikan kondisi data aktual tugas ID 6 di hosting atau database produksi; pemeriksaan ini menyimpulkan perilaku dari kode lokal. Perbaikan yang disarankan mencakup tautan **“Lihat submission”** dari panel dosen, label status yang membedakan terkirim dan sudah dinilai, serta status awal ujian yang tidak tercatat sebagai terkirim sebelum pengumpulan.

> Bagus. Sebelum melakukan perbaikan, kita susun dulu rancangan komprehensif perbaikan agar alur pengerjaannya lebih terarah dan fokus.

Baik. Saya usulkan rancangan perbaikan yang menata **alur submission dari awal sampai dinilai**, lalu menutup temuan akses dan validasi dari audit. Fokusnya agar dosen dapat menemukan dan memeriksa isi tugas dengan jelas, sementara status yang terlihat sesuai dengan tindakan mahasiswa.

## Rancangan alur tugas

### 1. Tetapkan arti status

Gunakan status yang sama dan jelas di semua halaman:

| Status | Arti |
|---|---|
| **Belum dimulai** | Mahasiswa belum mengerjakan atau membuka tugas. Belum ada submission. |
| **Sedang dikerjakan** | Mahasiswa sudah memulai pengerjaan, tetapi belum menekan tombol kirim. |
| **Terkirim** | Mahasiswa sudah mengirim tugas dan menunggu penilaian. |
| **Sudah dinilai** | Dosen sudah menyimpan nilai. |

Untuk tugas tulisan, “sedang dikerjakan” dapat berasal dari draf yang disimpan; bila aplikasi belum memiliki fitur simpan draf tersendiri, status mahasiswa tetap “Belum dimulai” sampai pengiriman pertama.

Untuk ujian, membuka halaman tidak boleh otomatis dihitung sebagai pengiriman. Pencatatan waktu mulai dan pembuatan record pengerjaan dilakukan melalui aksi mulai yang eksplisit. Jawaban baru berstatus “Terkirim” setelah mahasiswa mengumpulkannya.

### 2. Jadikan panel dosen sebagai pintu masuk ke isi submission

Di tab tugas mata kuliah:

- Tampilkan jumlah **terkirim**, **sedang dikerjakan**, dan **sudah dinilai** secara terpisah.
- Sediakan tombol yang jelas, seperti **Lihat submission**, untuk membuka isi tugas.
- Tampilkan nama mahasiswa, waktu kirim, status, nilai, dan tindakan membuka detail.
- Pada detail submission, tampilkan versi/revisi, teks atau jawaban ujian, tautan artefak, catatan mahasiswa, serta riwayat penilaian.
- Pertahankan penilaian cepat di daftar bila berguna, tetapi sediakan jalur yang jelas ke isi lengkap.
- Pastikan mahasiswa hanya melihat submission, nilai, dan umpan balik miliknya sendiri.

Untuk tugas ID 6, halaman detail yang sudah ada dapat menjadi halaman pemeriksaan submission. Panel mata kuliah perlu mengarahkan dosen ke sana atau ke halaman detail submission yang lebih spesifik.

### 3. Perbaiki siklus status dan penilaian

- Pengiriman pertama menetapkan `submitted` dan `submitted_at`.
- Penilaian menetapkan `graded`, `graded_at`, dan `graded_by`.
- Jika revisi tambahan diizinkan setelah dinilai, tetapkan aturan apakah revisi baru mengembalikan status ke `submitted` dan meminta penilaian ulang.
- Samakan istilah di halaman dosen, halaman mahasiswa, notifikasi, cetak nilai, dan ekspor.
- Jangan tampilkan kata “Draf” pada submission yang sudah terkirim. Bila draf memang tersedia, bedakan draf pengerjaan mahasiswa dari draf feedback dosen.

### 4. Tegakkan aturan ujian di server

Aturan harus diperiksa pada setiap endpoint yang membaca atau mengubah ujian:

- Tugas harus berstatus terbit.
- Mahasiswa harus masih berhak mengakses tugas dan terdaftar sesuai aturan kelas.
- Tenggat dan waktu ujian harus diperiksa saat mengirim jawaban, bukan hanya ketika halaman ujian dibuka.
- Pengumpulan kedua ditolak kecuali kebijakan tugas mengizinkan percobaan ulang.
- Waktu mulai, batas waktu, dan status submission ditetapkan server.
- Akses soal dan audio mengikuti aturan yang sama.

### 5. Satukan sumber hak akses mahasiswa

Saat ini ada akses berdasarkan daftar mahasiswa tugas dan akses berdasarkan keanggotaan kelas. Tetapkan satu aturan utama untuk tugas matakuliah:

- Mahasiswa terdaftar di kelas dapat mengakses tugas terbit kelas tersebut.
- Pengeluaran mahasiswa dari kelas langsung mencabut akses tugas matakuliah.
- Jika ada tugas yang memang hanya untuk sebagian mahasiswa, simpan aturan pengecualian itu secara eksplisit dan jangan bergantung pada daftar lama yang tidak tersinkron.
- Dosen pengampu dan administrator tetap memiliki akses sesuai peran.

### 6. Tutup kebocoran data nilai

- Halaman cetak rekap nilai kelas hanya untuk dosen pengampu dan administrator.
- Endpoint ekspor tetap memakai aturan akses yang sama.
- Halaman mahasiswa menampilkan data dirinya saja jika ada kebutuhan melihat ringkasan nilai.
- Akses mahasiswa ke halaman nilai kelas dan rekap mahasiswa lain harus ditolak.

### 7. Perketat validasi soal dan konfigurasi nilai

- Validasi jumlah pilihan, teks pilihan, format kunci jawaban, dan kecocokan kunci dengan pilihan yang tersedia.
- Pastikan nilai tiap soal dan jumlah poin menghasilkan perhitungan yang valid.
- Untuk tugas hybrid, bobot pilihan ganda dan esai harus mengikuti aturan yang disepakati—saran saya jumlahnya wajib 100%.
- Saat format tugas atau bobot berubah, jangan mengubah hasil ujian yang sudah terkumpul secara diam-diam. Simpan konfigurasi yang digunakan untuk attempt atau tandai dampaknya dengan jelas.

## Urutan pengerjaan yang disarankan

1. **Tetapkan aturan produk:** status, apakah ada draf mahasiswa, aturan revisi setelah dinilai, dan apakah ujian mengizinkan satu atau beberapa percobaan.
2. **Perbaiki model lifecycle submission:** cegah halaman GET membuat submission terkirim; siapkan migrasi data yang aman bila status awal atau data lama perlu diperbaiki.
3. **Tutup akses dan integritas:** rekap nilai, pemeriksaan ujian di server, serta sinkronisasi pendaftaran kelas.
4. **Perbaiki antarmuka dosen:** tautan langsung ke isi submission, status terpisah, dan tampilan detail yang lengkap.
5. **Rapikan validasi dan perhitungan nilai:** soal, bobot hybrid, revisi, dan dampak perubahan konfigurasi.
6. **Verifikasi peran dan alur ujung ke ujung:** mahasiswa belum mulai, mulai ujian, mengirim, dosen memeriksa isi, memberi nilai, mahasiswa melihat hasil, serta akses setelah dikeluarkan dari kelas.

## Kriteria selesai

Perbaikan dianggap tuntas ketika dosen dapat membuka isi setiap submission dari tab tugas tanpa menebak URL; mahasiswa yang baru membuka ujian belum tercatat mengirim; status konsisten di halaman, notifikasi, dan rekap; aturan tenggat dan akses berlaku pada permintaan langsung; serta mahasiswa tidak dapat melihat rekap nilai kelas atau submission mahasiswa lain.

Keputusan yang paling memengaruhi rancangan teknis adalah **apakah mahasiswa boleh menyimpan draf sebelum mengirim** dan **apakah submission yang sudah dinilai boleh direvisi**. Rekomendasi awal saya: ujian memiliki satu pengiriman final, sedangkan tugas tulisan boleh mengirim revisi; revisi setelah penilaian mengembalikan status ke “Terkirim” dan meminta dosen menilai ulang.

> KEDUA HAL INI JAWABANNYA YA: mahasiswa bisa menyimpan sebagai draft untuk dikirim nanti dan jawaban bisa direvisi sesuai kebijakan dosen.

Baik, kedua hal itu masuk sebagai kebutuhan inti rancangan:

- **Mahasiswa dapat menyimpan draf** dan melanjutkannya sebelum menekan tombol kirim.
- **Mahasiswa dapat merevisi jawaban sesuai kebijakan dosen.**

Dengan keputusan ini, alur statusnya menjadi:

1. **Belum dimulai** — belum ada jawaban tersimpan.
2. **Draf** — jawaban sudah tersimpan, tetapi belum dikirim; tidak dihitung sebagai submission terkirim.
3. **Terkirim** — mahasiswa sudah mengirim dan menunggu penilaian.
4. **Sudah dinilai** — dosen sudah memberi nilai.

Jika dosen mengizinkan revisi, mahasiswa dapat mengirim revisi sesuai ketentuan tugas. Revisi setelah penilaian akan kembali ke status **Terkirim** untuk dinilai ulang. Riwayat jawaban dan penilaian sebelumnya perlu tetap tersimpan agar perubahan dapat ditelusuri.

Untuk ujian bertimer, penyimpanan draf perlu tetap mengikuti aturan waktu yang ditetapkan dosen. Membuka halaman ujian tidak boleh membuat submission terkirim; sistem perlu membedakan pengerjaan yang sedang berlangsung dari jawaban yang sudah dikumpulkan.

Rancangan lengkap sebelumnya tetap berlaku, dengan dukungan draf dan revisi ini ditambahkan ke lifecycle submission.

> YA SEPAKAT DENGAN RANCANGAN INI

<details><summary>2 previous messages</summary>

> Saya mulai menerapkan rancangan yang disepakati. Saya akan menjaga pekerjaan tetap pada alur tugas matakuliah: draf dan revisi, akses dosen ke isi submission, status yang akurat, serta kontrol akses ujian dan rekap nilai. Saya cek kondisi perubahan yang sudah ada dulu agar tidak menimpa pekerjaan lain.
>
> <details><summary>Ran 3 commands</summary>
>
> - Ran `git status --short; Get-Content database/migrations/2026_10_22_080100_create_writewise_tables.php | Select-Object -Skip 15 -First 90; Get-Content database/migrations/2026_10_27_120000_create_courses_tables.php | Select-Object -Skip 65 -First 35; Get-Content database/migrations/2026_10_29_160000_add_artifact_and_translation_fields_to_revisions.php; rg -n "revisions.store|body_text|Kirim|Simpan" resources/views/assignments/show.blade.php; Get-Content resources/views/assignments/show.blade.php | Select-Object -Skip 130 -First 185; Get-Content app/Models/WritingAssignment.php | Select-Object -First 180`
> - Ran `git diff -- app/Http/Controllers/CourseController.php resources/views/courses/show.blade.php; Get-Content resources/views/assignments/show.blade.php | Select-Object -Skip 292 -First 75; Get-Content routes/web.php | Select-Object -Skip 165 -First 20; rg -n "allow.*revision|revision.*allow|due_at" resources/views/courses/assignments/edit.blade.php app/Http/Controllers/CourseAssignmentController.php`
> - Ran `git diff --stat; rg -n "function assignmentTypeLabel|selected_option|answer_text|question_text|->score|allow_revisions" app/Models app/Http/Controllers resources/views/courses/show.blade.php`
>
> </details>
</details>

<details><summary>8 previous messages</summary>

> Stream error
>
> Reconnecting 2/5
> stream disconnected before completion: An existing connection was forcibly closed by the remote host. (os error 10054)
>
> <details><summary>Created a file, edited 2 files, ran a command</summary>
>
> - Ran `Get-Content app/Models/AssignmentQuestionAnswer.php; Get-Content app/Models/AssignmentQuestion.php; Get-Content database/migrations/2026_10_29_150000_add_multimodal_assessment_fields_and_tables.php | Select-Object -First 45; Get-Content app/Http/Controllers/CourseStudentController.php | Select-Object -Skip 136 -First 22; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 260 -First 150`
> - Wrote `./database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php` (+45 -0)
> - Wrote `./app/Models/WritingAssignment.php` (+2 -0)
> - Wrote `./app/Models/WritingSubmission.php` (+6 -0)
>
> </details>
>
> Saya melihat ada perubahan lokal yang sudah menambahkan panel pemeriksaan submission di tab dosen. Saya akan mempertahankan pekerjaan itu dan menyelaraskan data yang dipakai panel dengan nama kolom model yang sebenarnya, sambil melanjutkan perbaikan lifecycle submission di sisi server.
>
> <details><summary>Edited 11 files, ran 23 commands</summary>
>
> - Wrote `./routes/web.php` (+1 -0)
> - Wrote `./app/Http/Controllers/WritingAssignmentController.php` (+45 -0)
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+5 -3)
> - Ran `rg -n "due_at|name=\"status\"|allow_revisions" resources/views/courses/show.blade.php resources/views/courses/assignments/edit.blade.php; Get-Content app/Http/Controllers/CourseAssignmentController.php | Select-Object -First 60; Get-Content app/Http/Controllers/CourseAssignmentController.php | Select-Object -Skip 235 -First 42`
> - Ran `rg -n 'due_at|name="status"|assignment_type' resources/views/courses/show.blade.php resources/views/courses/assignments/edit.blade.php`
> - Wrote `./app/Http/Controllers/CourseController.php` (+5 -1)
> - Ran `Get-Content resources/views/courses/assignments/edit.blade.php | Select-Object -Skip 87 -First 28; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1360 -First 28; rg -n "assignment->submissions->count\(\)" resources/views/courses/show.blade.php`
> - Wrote `./resources/views/courses/assignments/edit.blade.php` (+6 -0)
> - Wrote `./resources/views/courses/show.blade.php` (+6 -0)
> - Ran `rg -n "Izinkan mahasiswa|Izinkan revisi|submitted_submissions_count|assignmentTypeLabel|question_text|selected_option|answer_text|ans\.score" resources/views/courses/show.blade.php resources/views/courses/assignments/edit.blade.php app/Http/Controllers/CourseController.php`
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 455 -First 26; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1364 -First 30`
> - Wrote `./resources/views/courses/show.blade.php` (+6 -6)
> - Ran `rg -n "allow_revisions|Penempatan Pertemuan|Tenggat Waktu" resources/views/courses/show.blade.php; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1365 -First 24`
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1518 -First 60; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1588 -First 48; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 2592 -First 38`
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 2450 -First 150; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1410 -First 75`
> - Wrote `./resources/views/courses/show.blade.php` (+5 -5)
> - Wrote `./resources/views/courses/show.blade.php` (+16 -7)
> - Wrote `./resources/views/assignments/show.blade.php` (+13 -7)
> - Wrote `./resources/views/assignments/show.blade.php` (+0 -0)
> - Ran `rg -n -C 1 'textarea name="notes"' resources/views/assignments/show.blade.php; rg -n "function saveDraft|submitted_at && !|public function submit\(" app/Http/Controllers/WritingAssignmentController.php`
> - Wrote `./resources/views/assignments/show.blade.php` (+2 -0)
> - Ran `Get-Content resources/views/assignments/exam.blade.php | Select-Object -Skip 195 -First 145; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 190 -First 95; rg -n "function openSubmissionModal|activeRevIndex" resources/views/courses/show.blade.php`
> - Ran `Get-Content resources/views/assignments/exam.blade.php | Select-Object -Last 65; rg -n "assignments.exam.take|exam.submit" routes/web.php; rg -n "function openAudio|firstWhere\('student_id'|showSubmissions" resources/views/courses/show.blade.php`
> - Wrote `./database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php` (+2 -0)
> - Wrote `./app/Http/Controllers/WritingAssignmentController.php` (+5 -0)
> - Wrote `./app/Http/Controllers/CourseStudentController.php` (+4 -1)
> - Wrote `./routes/web.php` (+2 -0)
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+77 -17)
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+4 -3)
> - Wrote `./resources/views/assignments/exam.blade.php` (+22 -4)
> - Ran `rg -n -C 2 "KONDISI 2|KONDISI 3|@else|@endif|examForm|submitted.*graded|timeLimit" resources/views/assignments/exam.blade.php | Select-Object -Last 55`
> - Wrote `./resources/views/assignments/exam.blade.php` (+3 -4)
> - Ran `Get-Content app/Http/Controllers/CourseStudentController.php | Select-Object -Skip 25 -First 90; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 260 -First 40; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 325 -First 34`
> - Wrote `./app/Http/Controllers/CourseStudentController.php` (+3 -0)
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+3 -2)
> - Ran `rg -n -C 3 "Mulai ujian sebelum|firstOrCreate\(|WritingSubmission::where\(" app/Http/Controllers/AssignmentExamController.php`
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+3 -2)
> - Ran `Get-Content resources/views/assignments/show.blade.php | Select-Object -Skip 205 -First 30; Get-Content resources/views/assignments/show.blade.php | Select-Object -Skip 348 -First 24; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 320 -First 34; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 380 -First 25`
> - Ran `rg -n "Tenggat Tugas Telah Berakhir|@if\(\$assignment->due_at" resources/views/assignments/show.blade.php; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 407 -First 48`
> - Ran `Get-Content resources/views/assignments/show.blade.php | Select-Object -Skip 232 -First 21`
> - Wrote `./resources/views/assignments/show.blade.php` (+4 -0)
> - Ran `rg -n -C 2 "Tugas sudah dikirim|due_at &&" resources/views/assignments/show.blade.php`
> - Ran `Get-Content resources/views/assignments/exam.blade.php | Select-Object -Skip 285 -First 23; Get-Content resources/views/assignments/exam.blade.php | Select-Object -Last 18; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1418 -First 25; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1538 -First 34`
> - Ran `rg -n "submitted_submissions_count|Terkirim|Draf \{|assignment->submissions->count\(\)|@if\(\$submission &&" resources/views/courses/show.blade.php resources/views/assignments/exam.blade.php`
> - Ran `rg -n -C 2 "allow_revisions|submitted_at.*draft|status' => 'submitted'" app/Http/Controllers/CourseAssignmentController.php; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 125 -First 75; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 220 -First 120`
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+1 -1)
> - Ran `Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 108 -First 65; Get-Content resources/views/assignments/exam.blade.php | Select-Object -First 54`
> - Wrote `./resources/views/assignments/exam.blade.php` (+6 -2)
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+1 -0)
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1605 -First 36; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1650 -First 24; rg -n "@if\(\$user|allow_revisions" resources/views/courses/assignments/edit.blade.php`
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+9 -0)
> - Wrote `./resources/views/assignments/exam.blade.php` (+13 -5)
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+8 -1)
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+7 -2)
>
> </details>
>
> Perubahan utama sudah mulai diterapkan. Draf tugas sekarang punya penyimpanan tersendiri, pengiriman final tetap membuat revisi yang dapat ditelusuri, dan ujian memerlukan aksi mulai sebelum timer atau submission aktif. Saya juga mempertahankan panel penilaian yang sudah ada di perubahan lokal, sambil membetulkan field yang tidak cocok dengan model. Saya lanjut memeriksa konsistensi route, tampilan, dan migrasi.
>
> <details><summary>Created a file, edited 9 files, ran 42 commands</summary>
>
> - Ran `php -l app/Http/Controllers/WritingAssignmentController.php`
> - Ran `php -l app/Http/Controllers/AssignmentExamController.php`
> - Ran `php -l app/Http/Controllers/CourseStudentController.php`
> - Ran `php -l app/Http/Controllers/CourseAssignmentController.php`
> - Ran `php -l app/Http/Controllers/CourseController.php`
> - Ran `php -l app/Models/WritingAssignment.php`
> - Ran `php -l app/Models/WritingSubmission.php`
> - Ran `php -l database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php`
> - Ran `php -l routes/web.php`
> - Ran `php artisan view:cache`
> - Ran `git diff --check; git status --short; git diff --stat; rg -n "assignmentTypeLabel|due_date|selected_option|answer_text|score_awarded|assignment->typeLabel|allow_revisions|draft.store|exam.start|exam.draft" app resources/views routes database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php`
> - Ran `Get-Content app/Models/WritingAssignment.php | Select-Object -Skip 76 -First 38; Get-Content app/Http/Controllers/WritingAssignmentController.php | Select-Object -Skip 149 -First 140; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 205 -First 142`
> - Ran `rg -n "grade_action_url|name=\"score\"|Catatan Evaluasi|Simpan Nilai" resources/views/courses/show.blade.php; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 2470 -First 80; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1615 -First 26`
> - Ran `rg -n -F 'grade_action_url' resources/views/courses/show.blade.php; rg -n -F 'name="score"' resources/views/courses/show.blade.php; rg -n -F 'name="feedback_notes"' resources/views/courses/show.blade.php`
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1628 -First 30; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 2646 -First 45`
> - Wrote `./resources/views/courses/show.blade.php` (+7 -1)
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+1 -0)
> - Wrote `./app/Http/Controllers/WritingAssignmentController.php` (+5 -1)
> - Wrote `./resources/views/assignments/show.blade.php` (+2 -4)
> - Wrote `./resources/views/assignments/show.blade.php` (+1 -1)
> - Wrote `./resources/views/assignments/show.blade.php` (+2 -1)
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+10 -0)
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+16 -3)
> - Ran `Get-Content resources/views/assignments/questions.blade.php | Select-Object -Skip 60 -First 60; Get-Content resources/views/courses/assignments/edit.blade.php | Select-Object -Skip 42 -First 62; rg -n "mcq_weight_percentage|essay_weight_percentage" resources/views/courses/assignments/edit.blade.php resources/views/courses/show.blade.php | Select-Object -First 20`
> - Ran `rg -n "options\[|answer_key" resources/views/assignments/questions.blade.php`
> - Ran `rg -n "Bobot|Porsi|Essay|mcq_weight" resources/views/courses/show.blade.php`
> - Ran `php artisan view:cache`
> - Ran `php -l app/Http/Controllers/AssignmentExamController.php`
> - Ran `php -l app/Http/Controllers/CourseAssignmentController.php`
> - Ran `php -l app/Http/Controllers/WritingAssignmentController.php`
> - Ran `php -l resources/views/courses/show.blade.php`
> - Ran `php artisan route:list --name=assignments.exam`
> - Ran `git diff --check`
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 2483 -First 7; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 2529 -First 7; Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 2556 -First 7`
> - Wrote `./resources/views/courses/show.blade.php` (+3 -3)
> - Ran `rg -n "abort_unless\(\$isLecturer|submitted_submissions|allow_revisions|Draf mahasiswa belum|function grade|function saveDraft|status' => 'draft'" app/Http/Controllers/AssignmentExamController.php app/Http/Controllers/CourseAssignmentController.php app/Http/Controllers/CourseController.php resources/views/courses/show.blade.php; git diff --check; git status --short`
> - Wrote `./database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php` (+33 -1)
> - Wrote `./app/Models/WritingSubmissionAttempt.php` (+41 -0)
> - Wrote `./app/Models/WritingSubmission.php` (+8 -0)
> - Wrote `./app/Models/AssignmentQuestionAnswer.php` (+2 -0)
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+87 -7)
> - Wrote `./app/Http/Controllers/CourseController.php` (+2 -0)
> - Wrote `./resources/views/courses/show.blade.php` (+8 -1)
> - Wrote `./resources/views/courses/show.blade.php` (+19 -0)
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+14 -0)
> - Ran `rg -n -C 6 "WritingSubmissionAttempt" app/Http/Controllers/CourseAssignmentController.php`
> - Ran `Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 398 -First 110; Get-Content database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php`
> - Wrote `./database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php` (+34 -0)
> - Wrote `./database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php` (+3 -1)
> - Wrote `./database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php` (+5 -0)
> - Ran `rg -n "questions.store|assignment_type.*hybrid|answer_key|courses.assignments.update|grades.print" tests`
> - Ran `Get-Content tests/Feature/CourseAssignmentEditAndDeleteTest.php | Select-Object -Skip 115 -First 60; Get-Content tests/Feature/AssignmentExamFeatureTest.php | Select-Object -Skip 122 -First 32; Get-Content tests/Feature/AssignmentExamFeatureTest.php | Select-Object -Skip 237 -First 30`
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+4 -2)
> - Ran `Get-Content tests/Feature/CourseGradingTest.php | Select-Object -Skip 228 -First 42; Get-Content tests/Feature/AssignmentExamFeatureTest.php | Select-Object -Skip 150 -First 75`
> - Wrote `./app/Http/Controllers/CourseController.php` (+4 -2)
> - Ran `php -l app/Http/Controllers/AssignmentExamController.php`
> - Ran `php artisan view:cache`
> - Ran `php -l app/Http/Controllers/CourseAssignmentController.php`
> - Ran `php -l app/Http/Controllers/CourseController.php`
> - Ran `php -l app/Models/WritingSubmissionAttempt.php`
> - Ran `php -l database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php`
> - Ran `git diff --check`
> - Ran `rg -n "function saveDraft|function submit\(|function startExam|function saveExamDraft|function submitExam|function grade\(|function printGrades|function unenroll|submitted_submissions_count|allow_revisions|Riwayat percobaan ujian|Simpan Draf|Mulai Ujian" app/Http/Controllers/WritingAssignmentController.php app/Http/Controllers/AssignmentExamController.php app/Http/Controllers/CourseAssignmentController.php app/Http/Controllers/CourseController.php app/Http/Controllers/CourseStudentController.php resources/views/assignments/show.blade.php resources/views/assignments/exam.blade.php resources/views/courses/show.blade.php resources/views/courses/assignments/edit.blade.php database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php; git diff --stat`
> - Ran `Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 212 -First 20; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 265 -First 25; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 430 -First 102`
> - Ran `Select-String -Path app/Http/Controllers/AssignmentExamController.php -Pattern "status === 'published'" | ForEach-Object {"$($_.LineNumber):$($_.Line.Trim())"};`
> - Wrote `./app/Http/Controllers/AssignmentExamController.php` (+1 -1)
>
> </details>
>
> Bagian alur draf, revisi ujian, riwayat percobaan, dan hak akses sudah tersambung. Perubahan lokal yang sebelumnya sudah menambahkan ruang penilaian tetap dipakai; panel kini menampilkan status dan isi jawaban dari kolom model yang benar. Saya sedang melakukan pemeriksaan akhir tanpa menjalankan test suite atau menerapkan migrasi ke database.
>
> <details><summary>Ran 12 commands</summary>
>
> - Ran `php -l app/Http/Controllers/AssignmentExamController.php`
> - Ran `php -l app/Http/Controllers/WritingAssignmentController.php`
> - Ran `php -l app/Http/Controllers/CourseAssignmentController.php`
> - Ran `php artisan view:cache`
> - Ran `php -l app/Http/Controllers/CourseController.php`
> - Ran `php -l app/Http/Controllers/CourseStudentController.php`
> - Ran `php -l app/Models/AssignmentQuestionAnswer.php`
> - Ran `php -l app/Models/WritingSubmission.php`
> - Ran `php -l app/Models/WritingSubmissionAttempt.php`
> - Ran `php -l database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php`
> - Ran `php artisan route:list --name=assignments.draft`
> - Ran `git diff --check`
>
> </details>
</details>

Perbaikan sudah diterapkan di workspace, termasuk perubahan lokal yang sebelumnya menambahkan panel pemeriksaan submission.

Mahasiswa kini bisa menyimpan draf tugas dan melanjutkannya sebelum dikirim. Ujian dimulai lewat tindakan **Mulai Ujian**; membuka halaman tidak lagi mencatat submission. Dosen dapat mengatur apakah revisi diizinkan, dan riwayat percobaan ujian beserta jawabannya disimpan. Panel dosen juga membedakan draf, submission terkirim, dan submission yang sudah dinilai, serta menyediakan akses ke isi jawaban. Saya turut membatasi akses rekap nilai dan memperbarui sinkronisasi akses tugas saat pendaftaran kelas berubah.

Perubahan utama ada di [halaman tugas mahasiswa](./resources/views/assignments/show.blade.php), [halaman ujian](./resources/views/assignments/exam.blade.php), [tab tugas mata kuliah](./resources/views/courses/show.blade.php), dan [migrasi lifecycle submission](./database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php).

Pemeriksaan sintaks PHP, kompilasi Blade, dan pendaftaran route berhasil. **Test suite tidak dijalankan.** Migrasi database juga belum diterapkan; migrasi tersebut perlu dijalankan sebelum aplikasi memakai perubahan ini.

> [https://academic-hub.test/courses/eng6549-kelas-a-2023](https://academic-hub.test/courses/eng6549-kelas-a-2023)
> SQLSTATE[HY000]: General error: 1 no such table: writing_submission_attempts (Connection: sqlite, Database: C:\Users\satuh\Herd\academic-hub\database\database.sqlite, SQL: select \* from "writing_submission_attempts" where "writing_submission_attempts"."submission_id" in (2) order by "attempt_number" asc)

<details><summary>2 previous messages</summary>

> Error itu terjadi karena kode halaman sudah memakai tabel baru `writing_submission_attempts`, sedangkan migrasi lifecycle belum diterapkan ke SQLite lokal. Saya akan menerapkan migrasi yang tertunda pada database lokal lalu membuka ulang halaman untuk memastikan tabelnya tersedia.
>
> <details><summary>Ran 2 commands</summary>
>
> - Ran `php artisan migrate`
> - Ran `php artisan migrate:status`
>
> </details>
</details>

Migrasi `2026_11_05_100000_add_assignment_submission_lifecycle` berhasil diterapkan ke SQLite lokal, dan `migrate:status` mengonfirmasi statusnya **Ran**. Tabel `writing_submission_attempts` kini tersedia. Coba muat ulang halaman mata kuliah.

> mantap. sudah aman. Selanjutnya sepertinya kita perlu rancang ulang workflow tugas serta medesain ulang format tugas. Lakukan dulu audit lengkap terkait alur tugas serta format tugas yang ada saat ini. Pastikan fitur tugas ini bisa mengakomodir semua matakuliah yang masing-masing punya karakteristik berbeda. Fokus diskusi tanpa menyentuh kode

<details><summary>2 previous messages</summary>

> Saya akan mengaudit alur tugas dari pembuatan sampai umpan balik, lalu memetakan format tugas dan batasannya terhadap kebutuhan berbagai matakuliah. Fokus saya pada perilaku yang benar-benar tersedia di aplikasi saat ini, celah antarformat, dan rancangan konseptual yang bisa dibahas—tanpa mengubah kode.
>
> <details><summary>Ran 6 commands</summary>
>
> - Ran `rg -n "assignment_type|isWriting|isStimulusExam|isHybrid|isTranslation|isMilestone|function (store|update|destroy|grade|take|submit|saveDraft|startExam|submitExam|saveExamDraft)|allow_revisions|draft_body_text|time_limit_minutes|mcq_weight_percentage|essay_weight_percentage|milestones|rubric" app/Http/Controllers app/Models resources/views/assignments resources/views/courses/assignments resources/views/courses/show.blade.php database/migrations/2026_10_29_150000_add_multimodal_assessment_fields_and_tables.php database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php routes/web.php`
> - Ran `Get-Content app/Models/WritingAssignment.php | Select-Object -Skip 52 -First 52; Get-Content resources/views/assignments/exam.blade.php | Select-Object -Skip 316 -First 70; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 395 -First 110; Get-Content resources/views/courses/assignments/edit.blade.php | Select-Object -Skip 40 -First 188; Get-Content app/Http/Controllers/CourseAssignmentController.php | Select-Object -First 150`
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1306 -First 125; Get-Content resources/views/courses/assignments/edit.blade.php | Select-Object -Skip 135 -First 70; Get-Content app/Http/Controllers/CourseAssignmentController.php | Select-Object -Skip 255 -First 115; Get-Content app/Http/Controllers/CourseAssignmentController.php | Select-Object -Skip 420 -First 73; Get-Content app/Models/WritingSubmission.php | Select-Object -First 190`
> - Ran `Get-Content resources/views/courses/show.blade.php | Select-Object -Skip 1360 -First 70; Get-Content resources/views/courses/assignments/edit.blade.php | Select-Object -Skip 15 -First 32; Get-Content resources/views/assignments/questions.blade.php | Select-Object -Skip 130 -First 70; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 34 -First 82`
> - Ran `rg -n "function (store|update|grade|printGrades|exportGrades|recalculateCourseStudentGrade|generateWithAi)|function (show|submit|saveDraft)|function (take|startExam|saveExamDraft|submitExam|storeQuestion|destroyQuestion)|isWriting|isStimulusExam|isHybrid|isTranslation|isMilestone|Schema::create\('assignment_questions|Schema::create\('assignment_question_answers|Schema::create\('writing_submission_attempts|function calculateCompositeScore|function currentMilestoneTitle" app/Http/Controllers/CourseAssignmentController.php app/Http/Controllers/WritingAssignmentController.php app/Http/Controllers/AssignmentExamController.php app/Models/WritingAssignment.php app/Models/WritingSubmission.php database/migrations/2026_10_29_150000_add_multimodal_assessment_fields_and_tables.php database/migrations/2026_11_05_100000_add_assignment_submission_lifecycle.php resources/views/courses/show.blade.php resources/views/courses/assignments/edit.blade.php resources/views/assignments/exam.blade.php resources/views/assignments/show.blade.php; Get-Content app/Http/Controllers/CourseAssignmentController.php | Select-Object -Skip 635 -First 25; Get-Content resources/views/assignments/questions.blade.php | Select-Object -Skip 82 -First 32; Get-Content app/Http/Controllers/AssignmentExamController.php | Select-Object -Skip 510 -First 34`
> - Ran `rg -n "calculateCompositeScore\(|essay_score|criteria_scores|assignment_weight|weight_percentage|group_id|group_submission|team" app/Http/Controllers app/Models resources/views/assignments resources/views/courses database/migrations | Select-Object -First 140; rg -n "document_file|artifact_url|store\(" app/Http/Controllers/WritingAssignmentController.php resources/views/assignments/show.blade.php`
>
> </details>
</details>

# Audit alur dan format tugas matakuliah

Audit ini membaca alur yang ada di kode lokal, termasuk perubahan lifecycle submission yang baru diterapkan. **Saya tidak mengubah kode.** Fokusnya adalah memetakan kemampuan saat ini dan menilai seberapa baik modelnya melayani matakuliah dengan bentuk tugas berbeda.

## Alur yang tersedia saat ini

1. **Dosen membuat tugas dari halaman matakuliah.** Formulir menawarkan lima jenis tugas, tetapi form pembuatan utamanya hanya meminta judul, tenggat, sesi kuliah, dan instruksi. Pengaturan khusus—seperti audio, teks sumber terjemahan, bobot ujian, atau milestone—baru tersedia setelah tugas dibuat dan diedit. Untuk ujian stimulus dan hybrid, alur pembuatan kemudian mengarahkan dosen ke halaman pengelolaan soal. [Form tugas matakuliah](./resources/views/courses/show.blade.php:1310) · [Form edit tugas](./resources/views/courses/assignments/edit.blade.php:35)

2. **Mahasiswa mengerjakan tugas.** Tugas esai, terjemahan, dan milestone memakai editor tulisan yang sama. Mahasiswa dapat menyimpan draf dan mengirim revisi sesuai izin dosen. Untuk ujian, mahasiswa mulai ujian terlebih dahulu, lalu dapat menyimpan jawaban sebagai draf sebelum mengumpulkannya. Riwayat revisi tulisan dan percobaan ujian juga disimpan. [Form pengumpulan tulisan](./resources/views/assignments/show.blade.php:180) · [Form ujian](./resources/views/assignments/exam.blade.php:293)

3. **Dosen melihat submission dan memberi nilai.** Isi tulisan, jawaban soal, status, dan nilai tersedia melalui ruang penilaian. Penilaian dasarnya berupa satu nilai keseluruhan dan catatan; beberapa alur juga mendukung draf feedback AI yang dapat ditinjau dosen. [Panel tugas dan penilaian](./resources/views/courses/show.blade.php:1480) · [Pengendali penilaian](./app/Http/Controllers/CourseAssignmentController.php:426)

## Format tugas yang tersedia

| Jenis | Kemampuan yang ada | Batasan utama |
|---|---|---|
| **Esai akademik** | Jawaban teks; unggah DOCX/PDF/TXT untuk ekstraksi teks; umpan balik AI dan manual; draf serta revisi | Berkas asli tidak menjadi lampiran submission. Tidak ada dukungan bawaan untuk kode, tabel data, gambar, audio/video, atau berkas lain. |
| **Ujian stimulus** | Audio atau teks bacaan, pilihan ganda, batas pemutaran audio, timer, penilaian otomatis pilihan ganda | Isian singkat dan esai yang tersedia di pengelola soal belum memiliki alur penilaian per butir yang utuh. |
| **Hybrid** | Pilihan ganda ditambah satu jawaban esai, dengan bobot yang tampil di konfigurasi dan halaman ujian | Bobot tidak diterapkan oleh alur penilaian yang berjalan. Dosen memasukkan satu nilai keseluruhan; nilai esai terpisah dan perhitungan komposit tidak tersambung. |
| **Terjemahan** | Teks sumber, bahasa sumber/sasaran, jawaban teks target, dan catatan penerjemah | Tetap memakai editor esai yang sama. Tidak ada side-by-side editor, unggah dokumen sumber/hasil, komentar per segmen, atau penilaian terminologi khusus yang dapat dioperasikan langsung. |
| **Proposal/proyek bertahap** | Daftar milestone, pilihan tahap ketika mengirim, tautan artefak, refleksi, dan riwayat revisi | Tenggat milestone belum menjadi tenggat yang ditegakkan sistem. Nilai dan feedback masih berada pada submission keseluruhan, bukan per tahap. |

Jenis tugas dan soal didefinisikan di [WritingAssignment](./app/Models/WritingAssignment.php:56) dan tabel soal di [migrasi multimoda](./database/migrations/2026_10_29_150000_add_multimodal_assessment_fields_and_tables.php:23).

## Temuan penting

### 1. **Kritis — jenis soal tidak sama dengan kemampuan menilainya**

Pengelola soal mengizinkan pilihan ganda, isian singkat, dan esai. Namun, halaman ujian hanya menampilkan pilihan ganda dan isian singkat; esai dibuat sebagai satu kolom khusus hanya pada format hybrid. Perhitungan skor otomatis hanya menghitung pilihan ganda. Kunci jawaban isian singkat tidak digunakan untuk memberi nilai. Akibatnya, dosen dapat membuat soal yang tidak tampil atau tidak ikut dinilai sebagaimana namanya menyiratkan. [Pengelola soal](./resources/views/assignments/questions.blade.php:82) · [Tampilan soal](./resources/views/assignments/exam.blade.php:316) · [Perhitungan submission](./app/Http/Controllers/AssignmentExamController.php:370)

### 2. **Kritis — bobot hybrid belum mengendalikan nilai akhir**

Bobot pilihan ganda dan esai dapat disimpan dan ditampilkan, tetapi penilaian dosen menyimpan satu nilai langsung sebagai nilai submission. Fungsi perhitungan nilai komposit ada di model, tetapi tidak dipanggil oleh alur penilaian yang ditemukan. Tampilan seolah menjanjikan bobot 30/70, sementara nilai akhir belum dihitung dari dua komponen itu. [Bobot hybrid](./app/Http/Controllers/CourseAssignmentController.php:426) · [Fungsi skor komposit](./app/Models/WritingSubmission.php:88)

### 3. **Tinggi — pembuatan dan pengaturan format terpisah**

Form pembuatan memungkinkan dosen memilih jenis tugas, tetapi belum menyediakan konfigurasi yang membuat jenis tersebut langsung siap dipakai. Pengaturan penting baru muncul di halaman edit. Ini membuat jenis tugas lebih mirip label awal daripada alur pembuatan yang lengkap, terutama untuk terjemahan, listening, hybrid, dan proyek bertahap.

### 4. **Tinggi — nilai akhir matakuliah memakai rata-rata sederhana**

Nilai matakuliah dihitung sebagai rata-rata semua submission yang memiliki nilai. Bobot tugas, kategori asesmen, atau tugas yang belum dikumpulkan tidak tampak diperhitungkan dalam rumus ini. Dua tugas kecil dan tugas akhir bisa memberi kontribusi yang sama, sementara mahasiswa tanpa submission bernilai tidak ikut dalam rata-rata tersebut. [Perhitungan nilai matakuliah](./app/Http/Controllers/CourseAssignmentController.php:648)

### 5. **Tinggi — sistem berpusat pada individu dan jawaban teks**

Satu submission dibuat per pasangan tugas-mahasiswa. Pengelompokan tim tidak tersedia. Untuk penyerahan artefak, dukungan utamanya berupa tautan pada milestone; fitur unggah dokumen tugas hanya mengekstrak teks dan tidak mempertahankan berkas asli. Ini membatasi praktikum, proyek kelompok, desain, presentasi, portofolio, dan tugas yang jawabannya berbentuk data atau kode.

### 6. **Sedang — milestone belum menjadi workflow bertahap penuh**

Mahasiswa dapat memilih milestone, tetapi tanggal tahap belum ditegakkan sebagai tenggat tersendiri dan dosen tidak menilai tiap tahap sebagai unit yang berbeda. Selain itu, `current_milestone_id` juga digunakan untuk menghitung pemutaran audio. Satu kolom dengan dua arti tersebut membuat model data rawan bentrok saat fitur digabung. [Model submission](./app/Models/WritingSubmission.php:57) · [Konfigurasi milestone](./app/Http/Controllers/CourseAssignmentController.php:82)

### 7. **Sedang — perubahan tugas tidak selalu memiliki snapshot lengkap**

Versi tugas menyimpan sebagian informasi teks tugas, sedangkan soal, konfigurasi stimulus, dan struktur jawaban dikelola terpisah. Perubahan setelah mahasiswa mulai mengerjakan dapat membuat konteks yang dipakai ketika submission dinilai berbeda dari yang dilihat mahasiswa. Penghapusan soal juga dapat menghapus jawaban terkait karena relasi databasenya menggunakan cascade.

### 8. **Sedang — konfigurasi edit berpotensi mengirim nilai timer yang keliru**

Form edit memiliki input `time_limit_minutes` pada lebih dari satu panel format. Panel yang disembunyikan secara visual tetap berada di form; input tersembunyi CSS tetap dapat terkirim. Ini berpotensi membuat nilai timer berasal dari field format lain.

## Kesesuaian terhadap karakter matakuliah

| Karakter tugas | Kesesuaian saat ini |
|---|---|
| Esai, refleksi, ringkasan | **Cukup baik** untuk teks dan feedback umum |
| Bahasa, penerjemahan, listening | **Sebagian**; ada format khusus, tetapi penilaian dan media masih terbatas |
| Pilihan ganda | **Cukup baik** untuk skor otomatis dasar |
| Isian singkat atau soal uraian per butir | **Belum memadai**; dukungan penilaian tidak konsisten |
| Matematika, statistik, akuntansi | **Rendah**; belum ada jawaban numerik, toleransi nilai, rumus, atau dataset sebagai komponen asesmen |
| Praktikum dan laboratorium | **Rendah**; belum ada unggah artefak/data, kelompok, checklist, atau rubric per prosedur |
| Pemrograman | **Rendah**; belum ada unggah kode, repositori wajib, test case, atau evaluasi otomatis |
| Studio, desain, arsitektur, seni | **Rendah**; artefak terutama berupa tautan, belum ada kumpulan file/media dan rubrik visual |
| Proyek kelompok dan kerja lapangan | **Rendah**; submission masih individual dan milestone belum mengatur penilaian per tahap |
| Presentasi/oral | **Sebagian** melalui tautan, tanpa alur unggah media atau rubrik performa khusus |

## Kesimpulan audit

Fitur saat ini lebih tepat digambarkan sebagai **lima template tugas di atas fondasi submission tulisan**, ditambah ujian pilihan ganda dasar. Ia belum menjadi sistem asesmen yang fleksibel untuk semua matakuliah. Batas utamanya bukan sekadar kurangnya pilihan template, melainkan karena **jenis tugas, bentuk jawaban, cara menilai, bobot nilai, dan aturan pengerjaan masih saling terikat atau belum tersambung**.

Untuk merancang ulang workflow, diskusi sebaiknya berangkat dari beberapa sumbu yang berdiri sendiri:

- **Tujuan dan pola aktivitas:** latihan, tugas individu, kuis, ujian, praktikum, proyek, atau milestone.
- **Bentuk jawaban:** teks, pilihan, angka, rumus, tabel/data, berkas, tautan, kode, audio/video, atau kombinasi.
- **Cara menilai:** kunci otomatis, skor per butir, rubrik, feedback naratif, penilaian bertahap, atau kombinasi.
- **Aturan pengerjaan:** individu/kelompok, draf, tenggat, terlambat, durasi ujian, kesempatan, revisi, dan akomodasi.
- **Peran nilai:** bobot tugas dalam matakuliah serta kategori seperti kuis, proyek, praktikum, dan ujian akhir.

Pemisahan ini akan membuat format tugas dapat disusun sesuai karakter matakuliah tanpa menjadikan seluruh matakuliah mengikuti pola esai atau ujian yang sama.

> Ya saya sepakat untuk mendesain ulang khususnya format tugas sesuai peruntukannya. Sekarang lakukan pencarian sumber referensi online untuk menjadi bahan pertimbangan penentuan format tugas dan bisa mengakomodir semua jenis mata kuliah dan berikan hasil rekomendasi dari hasil penelusuran tersebut

<details><summary>3 previous messages</summary>

> Context compaction
>
> Source: automatic
> Status: completed
>
> Saya akan menelusuri panduan resmi dan sumber pendidikan tinggi tentang ragam asesmen, desain tugas, dan dukungan berbagai disiplin. Setelah itu saya rangkum prinsip yang relevan dan rekomendasikan format tugas yang fleksibel untuk Academia Hub.
>
> <details><summary>Searched the web</summary>
>
> Searched the web for `site:teaching.cornell.edu assignment design assessment types higher education authentic assessment ...`
>
> Searched the web for `Carnegie Mellon Eberly Center designing assignments assessment types performance project oral presentation laboratory ...`
>
> </details>
</details>

## Hasil penelusuran

Sumber dari pusat pengajaran universitas menunjukkan bahwa format asesmen perlu dipilih berdasarkan capaian pembelajaran, disiplin ilmu, tingkat studi, karakter mahasiswa, konteks perkuliahan, dan sumber daya yang tersedia. Artinya, satu format tugas yang seragam tidak akan cocok untuk semua matakuliah. [UNSW — Assessment Methods](https://www.teaching.unsw.edu.au/assessment-methods)

Beberapa temuan yang paling relevan:

- **Tugas autentik** meminta mahasiswa menerapkan pengetahuan dalam konteks nyata atau mendekati situasi profesi. Tugas seperti ini dapat memperlihatkan kemampuan menerapkan dan menalar, tetapi cakupan, kriteria penilaian, dan beban kerjanya perlu dijelaskan dengan baik. [UNSW — Assessing Authentically](https://www.teaching.unsw.edu.au/authentic-assessment), [Cornell — Promoting Academic Integrity](https://teaching.cornell.edu/teaching-resources/assessing-student-learning/promoting-academic-integrity-your-course)
- **Proyek dan tugas bertahap** cocok untuk pekerjaan kompleks yang berkembang dari waktu ke waktu. Tahap seperti rancangan, draf, umpan balik, revisi, dan produk akhir dapat menjadi bagian dari proses belajar dan penilaian. [CMU — Assessments](https://www.cmu.edu/teaching/designteach/teach/instructionalstrategies/inclusiveexcellence/assessments/index.html), [University of Alberta — Choosing Assessment Formats](https://www.ualberta.ca/en/centre-for-teaching-and-learning/resources/course-design/choosing-assessment-formats.html)
- **Tugas kelompok** sebaiknya dapat menilai produk kelompok sekaligus proses dan kontribusi individu. Jika hanya produk akhir yang dinilai, kontribusi tiap mahasiswa mungkin tidak terlihat. [CMU — Assessing Group Work](https://www.cmu.edu/teaching/designteach/teach/instructionalstrategies/groupprojects/assess.html)
- **Rubrik yang dibagikan sebelum pengerjaan** membantu memperjelas kriteria dan standar, mendukung konsistensi penilaian, serta dapat dipakai untuk penilaian diri dan penilaian teman sebaya. [UC Berkeley — Assessment Rubrics](https://teaching.berkeley.edu/teaching-strategies/assessing-learning/assessment-rubrics)
- **Ragam format dan pilihan cara menunjukkan pembelajaran** dapat membuat asesmen lebih fleksibel. Namun, pilihan tersebut tetap perlu mengukur capaian belajar yang sama, bukan memberi mahasiswa keuntungan atau hambatan yang tidak terkait dengan kemampuan yang dinilai. [CMU — Inclusive Assessments](https://www.cmu.edu/teaching/designteach/teach/instructionalstrategies/inclusiveexcellence/assessments/index.html)
- **Praktikum** dapat dinilai melalui beberapa bentuk sekaligus, seperti laporan, kuis, rancangan prosedur, proyek, diskusi, atau presentasi. Ini menunjukkan bahwa satu aktivitas belajar tidak selalu harus dipaksa menjadi satu jenis kiriman. [Trinity College Dublin — Lab Assessment](https://www.tcd.ie/academicpractice/resources/assessment--feedback/types-of-digital-assessment/lab-assessments/)
- **Portofolio** berguna untuk memperlihatkan perkembangan, hasil kerja, dan refleksi mahasiswa dari waktu ke waktu; bentuk ini membutuhkan tahapan dan kriteria yang jelas. [KU Leuven — Portfolio](https://www.kuleuven.be/english/stuvo/education/leuvenlearninglab/support/assessment-feedback/assessment-methods/archief-evaluatievormen/portfolio)

## Rekomendasi untuk Academia Hub

Saya merekomendasikan **satu kerangka tugas yang dapat dikonfigurasi, dengan templat berdasarkan tujuan tugas**. Hindari membuat satu format tunggal yang menganggap semua tugas adalah tulisan, sekaligus hindari membuat daftar jenis yang kaku dan terus membesar setiap kali ada kebutuhan baru.

Kerangka ini perlu memisahkan tiga hal:

1. **Tujuan tugas** — kemampuan apa yang hendak diukur, misalnya pemahaman, penerapan, analisis, keterampilan praktik, atau refleksi.
2. **Bentuk aktivitas atau asesmen** — misalnya kuis, pemecahan masalah, praktikum, proyek, presentasi, penerjemahan, atau portofolio.
3. **Bentuk bukti/kiriman mahasiswa** — misalnya jawaban terstruktur, teks, angka atau rumus, berkas, tautan, rekaman audio/video, demonstrasi, atau kumpulan hasil kerja.

Pemisahan tersebut memungkinkan satu tugas menggabungkan beberapa bentuk. Contohnya, praktikum dapat meminta **lembar hasil pengamatan, tabel data, foto alat, dan laporan singkat**; proyek dapat meminta **proposal, produk, presentasi, dan refleksi individu**. Pengajar juga dapat memberi bobot yang berbeda pada tiap bagian.

### Keluarga format yang disarankan

| Keluarga tugas | Cocok untuk | Contoh bukti/kiriman |
|---|---|---|
| **Kuis dan ujian** | Pengetahuan, pemahaman, latihan, atau evaluasi terjadwal | Pilihan ganda, benar/salah, jawaban singkat/esai, soal hitungan, batas waktu dan percobaan |
| **Analisis dan tulisan** | Kajian, argumen, laporan, refleksi, atau jawaban panjang | Editor teks, unggahan dokumen, pertanyaan per bagian, sitasi |
| **Pemecahan masalah dan perhitungan** | Matematika, sains, ekonomi, teknik, dan bidang kuantitatif | Langkah pengerjaan, angka, rumus, satuan, tabel, grafik, jawaban akhir |
| **Praktik dan laboratorium** | Eksperimen, observasi, prosedur, dan keterampilan teknis | Logbook, tabel data, foto/berkas, laporan, daftar periksa keterampilan |
| **Proyek dan produk** | Desain, pemrograman, rekayasa, studi kasus, atau karya | Tautan atau berkas produk, dokumentasi, demo, tahapan proyek |
| **Presentasi dan performa** | Bahasa lisan, seni, mengajar, praktik profesional, atau demonstrasi | Jadwal/sesi, rekaman audio/video, slide, rubrik performa, penilaian lisan |
| **Terjemahan dan tugas bahasa** | Penerjemahan, menulis, menyimak, berbicara, atau analisis bahasa | Teks sumber dan target, anotasi, audio, segmen jawaban, komentar penerjemah |
| **Portofolio dan tugas bertahap** | Perkembangan kompetensi, studio, magang, penelitian, atau karya berulang | Kumpulan artefak, tahap kemajuan, umpan balik, revisi, refleksi akhir |

Keluarga ini adalah **templat awal**, bukan batas kemampuan sistem. Dosen perlu dapat memilih bagian yang relevan, menambahkan bagian yang sesuai dengan mata kuliahnya, dan mengatur cara penilaian untuk setiap bagian.

## Kebijakan tugas perlu diatur terpisah dari formatnya

Sesuai keputusan yang sudah disepakati, mahasiswa dapat menyimpan draf dan merevisi jawaban berdasarkan kebijakan dosen. Karena itu, opsi seperti **boleh revisi, tenggat, toleransi terlambat, jumlah percobaan, tugas individu/kelompok, dan publikasi umpan balik** sebaiknya menjadi pengaturan tugas yang berlaku lintas-format. Setiap perubahan status juga perlu jelas bagi mahasiswa: misalnya **draf**, **terkirim untuk dinilai**, **perlu revisi**, atau **dinilai**.

Untuk tugas kelompok, sistem perlu membedakan kiriman bersama dari bukti kontribusi individu. Untuk tugas bertahap, setiap tahap perlu memiliki tenggat, kiriman, dan umpan baliknya sendiri. Prinsip ini sejalan dengan panduan penilaian proyek kelompok dan proyek bertahap dari CMU. [CMU — Assessing Group Work](https://www.cmu.edu/teaching/designteach/teach/instructionalstrategies/groupprojects/assess.html), [CMU — Inclusive Assessments](https://www.cmu.edu/teaching/designteach/teach/instructionalstrategies/inclusiveexcellence/assessments/index.html)

## Arah keputusan desain

Rekomendasi inti saya adalah menjadikan **tugas sebagai wadah**, lalu dosen menyusun tugas dari bagian-bagian yang sesuai. Setiap bagian dapat memiliki instruksi, bentuk kiriman, kriteria atau kunci jawaban, bobot, dan cara pemberian nilai masing-masing. Tugas sederhana tetap dapat dibuat dengan cepat melalui templat, sementara matakuliah yang memerlukan format khusus bisa menggabungkan beberapa bagian.

Pendekatan ini lebih masuk akal untuk mengakomodasi perbedaan antar mata kuliah daripada mempertahankan lima format tetap seperti esai, ujian berbasis stimulus, hybrid, terjemahan, dan milestone sebagai satu-satunya pilihan. Format yang ada dapat menjadi templat awal, tetapi sebaiknya tidak membatasi bentuk tugas yang dapat dibuat dosen.

Satu batasan yang perlu disepakati dalam diskusi: **tidak ada daftar format terbatas yang dapat mencakup secara sempurna semua mata kuliah**. Sasaran realistisnya adalah sistem yang menyediakan bentuk umum yang cukup beragam, dapat dirangkai, dan memungkinkan bukti belajar baru ditambahkan tanpa mendesain ulang seluruh alur tugas.

> Baik. Saya berikan konteks matakuliah. Dosen Prodi Pendidikan Bahasa Inggris:
>
> dengan matakuliah:
>
> 1. Translation
> 2. Bahasa Inggris MKDU
> 3. Seminar in English Language Teaching
> 4. Listening for Academic Purposes
> 5. Writing for Academic Assignment
> 6. Cross-Cultural Understanding
> 7. Prose & Poetry
> 8. Sociolinguistis

<details><summary>1 previous message</summary>

> Konteks ini menunjukkan bahwa format tugas perlu mendukung setidaknya empat jenis bukti belajar: **teks, bahasa lisan/audio, analisis, dan karya kreatif**. Beberapa matakuliah juga dapat memerlukan lebih dari satu jenis bukti dalam satu tugas. Karena itu, daftar matakuliah lebih tepat digunakan untuk menyusun contoh templat daripada mengunci setiap matakuliah pada satu format.
>
> Berikut pemetaan awal untuk didiskusikan:
>
> | Matakuliah | Bentuk tugas yang mungkin relevan | Bukti yang dapat dikumpulkan |
> |---|---|---|
> | **Translation** | Terjemahan individu atau berpasangan, analisis pilihan terjemahan, revisi terjemahan | Teks sumber dan hasil terjemahan berdampingan, catatan keputusan, komentar atau revisi |
> | **Bahasa Inggris MKDU** | Latihan pemahaman, praktik bahasa, dialog, tulisan fungsional, presentasi singkat | Jawaban kuis, teks, audio/video, atau dokumen |
> | **Seminar in English Language Teaching** | Presentasi dan diskusi, ulasan artikel, proposal atau rancangan penelitian, tanggapan terhadap presentasi | Slide, tulisan, rekaman/presentasi, tanggapan, rubrik |
> | **Listening for Academic Purposes** | Menyimak ceramah atau diskusi, mencatat, merangkum, menjawab pertanyaan pemahaman | Audio/video stimulus, catatan mahasiswa, rangkuman, jawaban pilihan ganda atau esai |
> | **Writing for Academic Assignment** | Esai atau makalah bertahap, proposal, draf, umpan balik teman, revisi | Kerangka, draf, komentar, naskah akhir, refleksi revisi |
> | **Cross-Cultural Understanding** | Analisis kasus, perbandingan budaya, refleksi berbasis bacaan atau pengalaman, diskusi | Teks analisis, tanggapan terstruktur, presentasi atau rekaman |
> | **Prose & Poetry** | Analisis karya, respons kritis, interpretasi, pembacaan atau karya kreatif | Esai, anotasi teks, rekaman pembacaan, karya kreatif dan refleksi |
> | **Sociolinguistics** | Analisis data bahasa, studi kasus, observasi atau wawancara, laporan kecil | Transkrip/data, tabel atau temuan, analisis tertulis, presentasi |
>
> Pemetaan ini bersifat awal: nama matakuliah memberi petunjuk, tetapi bentuk tugas sesungguhnya tetap bergantung pada capaian pembelajaran dan tujuan tiap penugasan. Misalnya, **Listening for Academic Purposes** tidak selalu berarti tugas audio saja; dosen mungkin juga perlu menilai catatan, rangkuman, dan pemahaman mahasiswa. Begitu pula tugas **Translation** dapat menilai hasil akhir sekaligus alasan di balik pilihan terjemahan.
>
> ### Implikasi untuk rancangan Academia Hub
>
> Untuk konteks Prodi Pendidikan Bahasa Inggris ini, format tugas sebaiknya bisa menggabungkan beberapa bagian, seperti:
>
> - **Stimulus:** teks, audio, video, atau tautan bahan.
> - **Respons mahasiswa:** teks bebas, jawaban per pertanyaan, tabel, atau dokumen.
> - **Bukti lisan:** unggahan rekaman audio/video atau tautan presentasi.
> - **Tahapan kerja:** kerangka, draf, umpan balik, revisi, dan hasil akhir.
> - **Penilaian:** rubrik per kriteria, kunci jawaban untuk soal objektif, atau nilai per bagian.
> - **Pengaturan pengerjaan:** individu/kelompok, draf dan pengiriman final, revisi sesuai kebijakan dosen, tenggat dan jumlah percobaan.
>
> Hal yang perlu dijaga ialah agar setiap format menilai kemampuan yang memang menjadi tujuan tugas. Sebagai contoh, jika tugas mengukur kemampuan menyimak, penilaian sebaiknya berfokus pada pemahaman audio; kualitas tulisan dapat menjadi kriteria tambahan hanya bila memang ditargetkan. Prinsip penyelarasan tujuan, bentuk asesmen, dan kriteria penilaian ini sejalan dengan panduan [UNSW tentang pemilihan metode asesmen](https://www.teaching.unsw.edu.au/assessment-methods).
>
> Konteks ini cukup untuk menyusun **kumpulan templat awal** bagi delapan matakuliah tersebut. Tahap disk
</details>

Konteks ini memperjelas kebutuhan: Academia Hub perlu mendukung tugas bahasa yang beragam—mulai dari jawaban tertulis dan analisis, hingga audio, performa lisan, terjemahan, dan proyek bertahap. Berikut pemetaan awal berdasarkan karakter umum tiap matakuliah. Bentuk akhirnya tetap perlu disesuaikan dengan RPS dan capaian pembelajaran dosen.

| Matakuliah | Bentuk tugas yang perlu didukung | Bukti/kiriman yang mungkin dinilai |
|---|---|---|
| **Translation** | Terjemahan per teks atau segmen, analisis pilihan penerjemahan, revisi, perbandingan hasil | Teks sumber dan hasil terjemahan berdampingan; catatan strategi atau masalah; revisi; komentar dosen pada segmen tertentu |
| **Bahasa Inggris MKDU** | Latihan bahasa, kuis, dialog, presentasi, tulisan singkat, tugas terpadu | Jawaban otomatis untuk latihan objektif; teks; audio/video berbicara; rubrik untuk performa |
| **Seminar in English Language Teaching** | Proposal atau makalah, presentasi seminar, telaah artikel, diskusi, peer review | Naskah, slide, rekaman atau jadwal presentasi, tanggapan terhadap pertanyaan, ulasan teman sebaya |
| **Listening for Academic Purposes** | Menyimak kuliah/podcast, menangkap gagasan utama dan detail, membuat catatan atau ringkasan | Audio/video stimulus; soal objektif dan jawaban terbuka; catatan; ringkasan; transkrip atau referensi stimulus |
| **Writing for Academic Assignment** | Penulisan bertahap, argumentasi, parafrasa, sitasi, revisi, refleksi proses | Kerangka, draf, umpan balik, versi revisi, naskah akhir, daftar sumber dan refleksi |
| **Cross-Cultural Understanding** | Analisis kasus, perbandingan perspektif budaya, refleksi, diskusi atau presentasi | Jawaban berbasis kasus; teks atau media stimulus; refleksi; presentasi; kriteria analisis dan penggunaan bukti |
| **Prose & Poetry** | Analisis teks sastra, interpretasi, esai, pembacaan/performa, karya kreatif | Teks analisis; anotasi kutipan; rekaman pembacaan; karya kreatif; refleksi atau penjelasan pilihan artistik |
| **Sociolinguistics** | Analisis data bahasa, studi kasus, observasi, transkripsi, laporan mini-riset, presentasi | Data atau stimulus; transkrip; tabel/kategori analisis; laporan; rekaman atau presentasi; catatan metode |

## Kemampuan format yang tampak paling penting

Dari delapan matakuliah ini, beberapa kebutuhan muncul berulang:

- **Teks dan jawaban terbuka**, tetapi dengan struktur yang bisa berbeda: esai utuh, jawaban per pertanyaan, anotasi kutipan, ringkasan, atau refleksi.
- **Audio dan video**, baik sebagai stimulus untuk disimak maupun sebagai kiriman performa lisan.
- **Stimulus yang terhubung ke soal**, misalnya audio kuliah, teks sastra, kasus budaya, atau data bahasa.
- **Pengerjaan bertahap dan revisi**, terutama untuk writing, translation, seminar, dan proyek mini-riset.
- **Rubrik per kriteria**, agar aspek seperti isi, bahasa, strategi, penggunaan bukti, pelafalan, dan proses dapat dinilai secara terpisah.
- **Kiriman gabungan**, karena sebuah tugas dapat membutuhkan teks sekaligus audio, slide, sumber, atau refleksi.
- **Komentar yang menunjuk bagian tertentu**, terutama untuk naskah terjemahan, tulisan akademik, transkrip, dan analisis sastra.

Karena mahasiswa telah diputuskan dapat menyimpan draf dan merevisi sesuai kebijakan dosen, alur revisi sebaiknya menjadi kemampuan umum yang dapat diaktifkan per tugas. Dosen perlu bisa menentukan apakah mahasiswa merevisi kiriman yang sama, mengirim versi baru, atau menjalani beberapa tahap dengan tenggat dan umpan balik berbeda.

## Rekomendasi rancangan awal

Daripada membuat delapan format terpisah berdasarkan nama matakuliah, lebih fleksibel jika dosen menyusun tugas dari **templat aktivitas** berikut:

1. **Teks dan analisis** — esai, jawaban terbuka, refleksi, telaah, atau laporan.
2. **Menyimak dan memahami stimulus** — audio/video/teks yang diikuti soal pilihan ganda, jawaban singkat, ringkasan, atau analisis.
3. **Bahasa lisan dan performa** — rekaman, presentasi terjadwal, pembacaan, dialog, atau demonstrasi.
4. **Penerjemahan dan anotasi** — sumber dan target berdampingan, dengan komentar atau penilaian per segmen.
5. **Penulisan dan revisi bertahap** — kerangka, draf, umpan balik, revisi, dan hasil akhir.
6. **Analisis kasus atau data** — stimulus/kumpulan data, metode atau pertanyaan panduan, analisis, dan kesimpulan.
7. **Seminar, proyek, dan kerja kelompok** — tahap persiapan, produk kelompok, presentasi, refleksi, dan bukti kontribusi individu.
8. **Kuis dan latihan bahasa** — soal objektif atau jawaban singkat, dengan penilaian otomatis bila sesuai dan penilaian dosen bila memerlukan pertimbangan.

Templat ini bukan format yang wajib dipakai utuh. Dosen dapat menggabungkan beberapa aktivitas dalam satu tugas. Contohnya, tugas *Listening for Academic Purposes* dapat terdiri dari stimulus audio, kuis pemahaman, dan ringkasan tertulis. Tugas *Seminar in English Language Teaching* dapat berisi telaah artikel, presentasi kelompok, dan refleksi individu.

Implikasi utamanya: **jenis tugas sebaiknya ditentukan oleh aktivitas dan bukti belajar yang diminta, bukan hanya oleh matakuliahnya**. Matakuliah yang sama dapat menggunakan beberapa format; sebaliknya, satu templat dapat dipakai di beberapa matakuliah dengan stimulus, rubrik, dan kebijakan berbeda.

Untuk pembahasan berikutnya, pemetaan ini dapat kita gunakan untuk menetapkan daftar bagian yang wajib tersedia pada setiap tugas—misalnya instruksi, stimulus, jenis kiriman, rubrik, revisi, tenggat, dan penilaian kelompok—serta bagian mana yang opsional sesuai kebutuhan matakuliah.

> Ya saya sepakat. Intinya tugas yang didesain harus sesuai dengan tujuan pembelajaran mata kuliah (CPMK) yang telah ditetapkan. Sekarang, saatnya kita menentukan format tugas yang akan kita terapkan. Susun rekomendasi format tugas dan rencana implementasinya

## Rekomendasi format tugas

Format tugas sebaiknya mengikuti **CPMK yang dinilai**. Dosen memilih CPMK, menentukan bukti yang harus ditunjukkan mahasiswa, lalu menyusun aktivitas dan cara penilaiannya. Nama matakuliah dapat membantu memilih templat, tetapi tidak membatasi pilihan format.

### 1. Struktur umum tugas

Setiap tugas memiliki informasi dasar yang sama:

- judul, instruksi, matakuliah, dan tenggat;
- satu atau lebih CPMK yang dinilai;
- bobot tugas terhadap nilai matakuliah;
- format individu atau kelompok;
- cara menilai: kunci jawaban, rubrik, atau gabungan;
- kebijakan draf, revisi, percobaan, keterlambatan, dan umpan balik.

Di dalamnya, dosen menambahkan satu atau beberapa **bagian tugas**. Setiap bagian memiliki instruksi, bukti/kiriman yang diminta, kriteria penilaian, dan bobot. Dengan demikian, satu tugas dapat memadukan aktivitas, misalnya menyimak audio, menjawab soal, lalu menulis ringkasan.

### 2. Keluarga format yang diterapkan

| Format | Bukti belajar yang dapat dikumpulkan | Cocok untuk |
|---|---|---|
| **Kuis dan ujian** | Pilihan ganda, benar/salah, jawaban singkat/esai, batas waktu, beberapa percobaan | Latihan bahasa, pemahaman bacaan atau simakan, dan ujian |
| **Teks dan analisis** | Jawaban terstruktur, esai, laporan, refleksi, sitasi, unggahan dokumen | Writing, seminar, Cross-Cultural Understanding, Prose & Poetry |
| **Menyimak dengan stimulus** | Audio/video atau teks stimulus yang disertai soal, catatan, transkrip, atau ringkasan | Listening for Academic Purposes dan latihan Bahasa Inggris MKDU |
| **Berbicara dan performa** | Rekaman audio/video, presentasi terjadwal, pembacaan, dialog, demonstrasi | Bahasa Inggris MKDU, seminar, Prose & Poetry |
| **Terjemahan dan anotasi** | Teks sumber dan hasil terjemahan berdampingan, anotasi atau komentar per segmen, refleksi pilihan | Translation |
| **Analisis kasus atau data** | Stimulus/kumpulan data, pertanyaan panduan, tabel atau transkrip, analisis dan kesimpulan | Sociolinguistics dan Cross-Cultural Understanding |
| **Penulisan atau proyek bertahap** | Kerangka, draf, umpan balik, revisi, produk akhir, refleksi proses | Writing for Academic Assignment, seminar, penelitian mini |
| **Portofolio dan proyek kelompok** | Kumpulan artefak, tahapan proyek, produk bersama, presentasi, refleksi dan kontribusi individu | Seminar, Prose & Poetry, proyek lintas topik |

Daftar ini menjadi templat dan blok penyusun, bukan jenis tugas yang mengunci dosen pada alur tertentu. Dosen dapat menggabungkan beberapa format dan menyesuaikannya dengan CPMK.

## Rekomendasi penyelarasan CPMK

Setiap bagian tugas perlu dapat dikaitkan dengan CPMK yang diukurnya. Dosen kemudian menetapkan **kriteria dan bukti** untuk menunjukkan pencapaian CPMK tersebut. Contohnya:

- CPMK: mahasiswa mampu menganalisis pilihan penerjemahan.
- Bukti: hasil terjemahan dan anotasi alasan pada segmen tertentu.
- Kriteria: ketepatan makna, kewajaran bahasa sasaran, konsistensi istilah, dan alasan strategi.
- Penilaian: rubrik dengan bobot per kriteria.

Satu tugas boleh mengukur beberapa CPMK, dan satu CPMK boleh dinilai melalui beberapa tugas. Sistem sebaiknya membantu dosen melihat keterkaitan ini, tetapi keputusan akademik tetap pada dosen. Jangan menganggap satu nilai tugas otomatis mewakili seluruh CPMK jika tugas tersebut mengukur beberapa kemampuan dengan bobot berbeda.

## Rencana implementasi

### Tahap 1 — Tetapkan model dan kebijakan tugas

Sepakati definisi informasi inti tugas, bagian tugas, CPMK, bukti kiriman, rubrik/kunci, bobot, kelompok, draf, revisi, dan status pengumpulan. Pastikan istilah status mudah dipahami mahasiswa, misalnya **Draf**, **Terkirim**, **Perlu revisi**, dan **Dinilai**.

**Hasil tahap:** aturan tugas yang konsisten berlaku untuk seluruh matakuliah.

### Tahap 2 — Rancang templat dan bagian penyusun

Tentukan templat awal untuk delapan keluarga format di atas. Tetapkan jenis kiriman yang diperlukan—teks, jawaban soal, dokumen, tautan, audio/video, tabel data, serta bukti kelompok—dan kriteria penilaian yang dapat berbeda per bagian.

**Hasil tahap:** dosen dapat membuat tugas sederhana dari templat atau menyusun tugas gabungan.

### Tahap 3 — Prioritaskan kebutuhan bahasa Inggris

Untuk penerapan awal, kebutuhan yang paling jelas dari daftar matakuliah adalah:

1. teks, esai, dan tugas menulis bertahap;
2. stimulus audio/video yang terkait dengan soal dan ringkasan;
3. kiriman rekaman dan presentasi;
4. terjemahan berpasangan dengan anotasi;
5. analisis kasus, data, dan kutipan;
6. rubrik per kriteria, draf, revisi, dan umpan balik.

Urutan ini mencakup pola tugas utama di delapan matakuliah yang diberikan, tanpa membuat format khusus yang terpisah untuk setiap matakuliah.

### Tahap 4 — Uji desain dengan contoh tugas

Sebelum diterapkan luas, susun contoh tugas untuk masing-masing matakuliah: misalnya terjemahan dengan anotasi, kuis menyimak dan ringkasan, presentasi seminar dengan refleksi individu, analisis puisi berbasis kutipan, serta laporan analisis data sosiolinguistik.

Tinjau apakah dosen dapat mengatur CPMK, bagian tugas, bukti kiriman, rubrik/bobot, draf dan revisi tanpa memaksakan aktivitas ke format yang tidak sesuai.

### Tahap 5 — Evaluasi alur dosen dan mahasiswa

Evaluasi apakah mahasiswa memahami apa yang harus dikirim, kapan tugas dianggap terkirim, bagaimana revisi dilakukan, dan kriteria apa yang digunakan. Evaluasi juga apakah dosen dapat melihat seluruh kiriman, menilai per bagian, dan memberi umpan balik yang terkait dengan kriteria.

**Hasil tahap:** keputusan penyempurnaan desain berdasarkan contoh penggunaan, sebelum implementasi diperluas.

## Urutan prioritas yang saya rekomendasikan

Prioritas pertama adalah menyepakati **struktur universal tugas dan pengaitan CPMK**. Prioritas berikutnya adalah mendukung **teks/rubrik/revisi, stimulus audio, rekaman lisan, dan terjemahan beranotasi**. Format proyek kelompok dan portofolio dapat dilanjutkan setelah alur kiriman, penilaian per bagian, dan kontribusi individu dirumuskan dengan jelas.

Prinsip keputusan desainnya: **dosen menetapkan CPMK; format menyediakan cara yang sesuai untuk mengumpulkan bukti pencapaiannya**. Dengan dasar ini, tugas dapat berbeda antar matakuliah tanpa kehilangan alur yang konsisten bagi dosen dan mahasiswa.

> YA Sepekat. Dalam indentias mata kuliah CPMK sudah termuat sebagai bagian informasi dalam data mata kuliah
> misalnya di halaman detail: [https://academic-hub.test/courses/eng6549-kelas-a-2023](https://academic-hub.test/courses/eng6549-kelas-a-2023) sudah terdapat section Deskripsi & Capaian Pembelajaran (CPMK)

Baik, berarti data CPMK sudah tersedia pada matakuliah dan tidak perlu dimasukkan ulang saat membuat tugas. Rancangan tugas akan **mengacu pada CPMK yang sudah tercantum di detail matakuliah**.

Dalam alur yang direkomendasikan, dosen memilih satu atau lebih CPMK dari daftar tersebut untuk setiap tugas atau bagian tugas. Pilihan itu kemudian menghubungkan tujuan pembelajaran dengan bukti kiriman dan kriteria penilaiannya. Jika satu tugas mengukur beberapa CPMK, dosen dapat menentukan CPMK mana yang dinilai pada tiap bagian.

Dengan begitu, hubungan yang dijaga adalah:

**CPMK matakuliah → tugas/bagian tugas → bukti kiriman → kriteria penilaian → umpan balik dan nilai**

Ada satu keputusan desain yang perlu kita pertahankan: CPMK dipilih dari data matakuliah yang sudah ada; dosen tidak membuat salinan CPMK khusus di dalam tugas. Bila CPMK matakuliah diubah, tugas tetap perlu memiliki keterkaitan yang jelas dengan CPMK yang berlaku.

> ya betul

Baik, kita tetapkan sebagai prinsip rancangan: tugas memilih CPMK langsung dari data matakuliah, lalu menghubungkannya dengan bagian tugas, bukti kiriman, kriteria penilaian, nilai, dan umpan balik. CPMK tidak diduplikasi di dalam tugas.

> Sekarang implementasikan rancangannya ke existing fitur tugas

<details><summary>8 previous messages</summary>

> Saya akan menerapkan rancangan pada fitur tugas yang sudah ada: mengaitkan tugas dengan CPMK matakuliah, mendukung bagian tugas dengan format dan penilaian yang sesuai, serta menjaga alur draf, pengiriman, dan revisi yang sudah disepakati. Saya mulai dengan meninjau struktur implementasi dan perubahan yang sudah ada agar rancangan masuk ke alur saat ini tanpa menimpa pekerjaan sebelumnya.
>
> <details><summary>Ran 6 commands</summary>
>
> - Ran `git status --short; rg -n "CPMK|capaian|WritingAssignment|AssignmentQuestion|submission|assignment" app/Models app/Http/Controllers resources/views/courses resources/views/assignments database/migrations routes/web.php`
> - Ran `rg -n "class Course|cpmk|learning_outcome|outcome|capaian" app/Models app/Http/Controllers resources/views/courses database/migrations; rg -n "function (store|update|edit|grade)|assignment_type|config_json|mcq_weight|allow_revisions" app/Http/Controllers/CourseAssignmentController.php app/Models/WritingAssignment.php resources/views/courses/assignments/edit.blade.php resources/views/courses/show.blade.php;`
> - Ran `Get-Content app\Models\Course.php | Select-Object -First 230; Get-Content app\Http\Controllers\CourseAssignmentController.php | Select-Object -First 150; Get-Content app\Http\Controllers\CourseAssignmentController.php | Select-Object -Skip 224 -First 155; Get-Content app\Models\WritingAssignment.php | Select-Object -First 145;`
> - Ran `rg -n "function show|function update|function saveDraft|function submit|draft_body_text|artifact_url|revision" app/Http/Controllers/WritingAssignmentController.php app/Models/WritingSubmission.php resources/views/assignments/show.blade.php; Get-Content resources/views/courses/assignments/edit.blade.php | Select-Object -First 115; Get-Content resources/views/assignments/show.blade.php | Select-Object -Skip 130 -First 155;`
> - Ran `Get-Content app\Http\Controllers\WritingAssignmentController.php | Select-Object -Skip 138 -First 150; Get-Content app\Models\WritingRevision.php | Select-Object -First 100; Get-Content app\Models\WritingSubmission.php | Select-Object -First 80; Get-Content database\migrations\2026_10_27_120000_create_courses_tables.php | Select-Object -Skip 70 -First 40;`
> - Ran `Get-Content resources\views\courses\show.blade.php | Select-Object -Skip 1285 -First 120; Get-Content resources\views\courses\assignments\edit.blade.php | Select-Object -Skip 190 -First 220; Get-Content app\Http\Controllers\CourseAssignmentController.php | Select-Object -Skip 210 -First 30; rg -n "return redirect|courses.show" app\Http\Controllers\CourseAssignmentController.php`
>
> </details>
>
> Saya menemukan CPMK saat ini tersimpan sebagai daftar baris pada data silabus matakuliah, bukan sebagai record CPMK terpisah. Saya akan menautkan tugas ke butir yang dipilih sambil menyimpan teks CPMK sebagai snapshot agar hubungan tetap terbaca bila daftar matakuliah kelak disunting. Untuk format tugas, saya mulai mengubah konfigurasi khusus yang kaku menjadi bagian tugas yang bisa dirangkai dan menyimpan jawaban per bagian di draf maupun riwayat kiriman.
>
> <details><summary>Created a file, edited 9 files, ran 21 commands</summary>
>
> - Wrote `./app/Models/WritingRevision.php` (+2 -0)
> - Wrote `./app/Models/WritingSubmission.php` (+2 -0)
> - Wrote `./database/migrations/2026_11_05_120000_add_assignment_outcomes_and_sections.php` (+30 -0)
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+56 -0)
> - Ran `rg -n "selectedCpmk|normalizeSections|cpmk_outcomes|sections' =>" app\Http\Controllers\CourseAssignmentController.php; Get-Content app\Http\Controllers\CourseAssignmentController.php | Select-Object -Skip 205 -First 80;`
> - Wrote `./app/Http/Controllers/WritingAssignmentController.php` (+44 -2)
> - Wrote `./resources/views/courses/show.blade.php` (+35 -0)
> - Wrote `./resources/views/courses/assignments/edit.blade.php` (+42 -0)
> - Ran `Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 1 -First 35; Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 285 -First 95; rg -n "body_text|textarea" resources\views\assignments\show.blade.php`
> - Ran `Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 308 -First 52; Get-Content database\migrations\2026_10_23_100000_add_writewise_assignment_versions.php | Select-Object -First 45;`
> - Wrote `./resources/views/assignments/show.blade.php` (+36 -2)
> - Ran `rg -n "Schema::create\('writing_revisions'|body_text" database/migrations/*writing*; rg -n "section_answers|section_responses|draft_section_answers|cpmk_outcomes" app resources database/migrations/2026_11_05_120000_add_assignment_outcomes_and_sections.php;`
> - Ran `Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 120 -First 80; rg -l "Schema::create\('writing_revisions'" database/migrations`
> - Wrote `./resources/views/assignments/show.blade.php` (+0 -17)
> - Ran `Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 315 -First 45;`
> - Wrote `./resources/views/assignments/show.blade.php` (+17 -0)
> - Ran `rg -n "draftSectionAnswers|if\(!empty\(\$assignment->config_json\['sections'\]\)" resources\views\assignments\show.blade.php; Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 115 -First 18;`
> - Ran `Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 136 -First 34`
> - Wrote `./resources/views/assignments/show.blade.php` (+0 -16)
> - Ran `rg -n "@if\(!empty\(\$assignment->config_json\['sections'\]\)\)|@endif|draftSectionAnswers|@required" resources\views\assignments\show.blade.php | Select-Object -First 35;`
> - Wrote `./resources/views/assignments/show.blade.php` (+25 -10)
> - Ran `Get-Content database\migrations\2026_10_22_080100_create_writewise_tables.php | Select-Object -First 160; Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 133 -First 35; Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 315 -First 55;`
> - Wrote `./resources/views/assignments/show.blade.php` (+0 -1)
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+11 -0)
> - Wrote `./app/Http/Controllers/WritingAssignmentController.php` (+12 -0)
> - Ran `Get-Content app\Http\Controllers\WritingAssignmentController.php | Select-Object -Skip 155 -First 145;`
> - Ran `Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 462 -First 45;`
> - Ran `Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 507 -First 28`
> - Wrote `./resources/views/assignments/show.blade.php` (+10 -1)
> - Ran `rg -n "revision->section_answers|draftSectionAnswers|@if\(!empty\(\$assignment->config_json\['sections'\]\)\)|@endif" resources\views\assignments\show.blade.php | Select-Object -First 30; Get-Content resources\views\assignments\show.blade.php | Select-Object -Skip 512 -First 27;`
> - Ran `rg -n "revision->body_text|revision->section_answers" resources\views\assignments\show.blade.php`
> - Ran `rg -n "cpmk_indices|Bagian tugas dan bentuk|CPMK yang dinilai" resources\views\courses\show.blade.php resources\views\courses\assignments\edit.blade.php; rg -l "Schema::create\('writing_revisions'" database\migrations;`
> - Ran `Get-Content app\Http\Controllers\WritingAssignmentController.php | Select-Object -Skip 130 -First 20; rg -n "assignments.exam.take|isStimulusExam|isHybrid" resources\views\assignments\show.blade.php app\Http\Controllers\WritingAssignmentController.php; rg -n "body_text" database\migrations\2026_10_22_080100_create_writewise_tables.php`
> - Ran `Get-Content app\Http\Controllers\CourseAssignmentController.php | Select-Object -Skip 475 -First 90; rg -n "score|grade" resources\views\courses\show.blade.php | Select-Object -Last 15;`
> - Wrote `./app/Http/Controllers/WritingAssignmentController.php` (+5 -0)
> - Ran `rg -n "versions\(\)->create" app\Http\Controllers\CourseAssignmentController.php; rg -n "class WritingAssignmentVersion|fillable|casts" app\Models\WritingAssignmentVersion.php; Get-Content app\Models\WritingAssignmentVersion.php | Select-Object -First 80;`
> - Ran `Get-Content app\Http\Controllers\CourseAssignmentController.php | Select-Object -Skip 156 -First 24; Get-Content app\Http\Controllers\CourseAssignmentController.php | Select-Object -Skip 395 -First 24;`
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+2 -0)
> - Wrote `./app/Models/WritingAssignmentVersion.php` (+2 -2)
> - Wrote `./database/migrations/2026_11_05_120000_add_assignment_outcomes_and_sections.php` (+8 -0)
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+2 -2)
> - Ran `rg -n "created_by|edited_by|assessment_config" app\Http\Controllers\CourseAssignmentController.php | Select-Object -First 20;`
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+2 -2)
>
> </details>
>
> Fondasi rancangan sudah masuk: dosen dapat menautkan CPMK yang tersedia dan menyusun tugas dari beberapa bagian dengan bentuk jawaban berbeda; jawaban per bagian tersimpan pada draf dan setiap revisi, sehingga dapat dibaca dosen. Saya juga menambahkan snapshot konfigurasi asesmen pada versi tugas untuk menjaga konteks saat instruksi diperbarui. Berikutnya saya cek sintaks, jalankan migrasi lokal yang diperlukan, dan pastikan tampilan Blade dapat dikompilasi; saya tidak menjalankan test suite.
>
> <details><summary>Ran 2 commands</summary>
>
> - Ran `php -l app\Http\Controllers\CourseAssignmentController.php; php -l app\Http\Controllers\WritingAssignmentController.php; php -l app\Models\WritingSubmission.php; php -l app\Models\WritingRevision.php; php -l app\Models\WritingAssignmentVersion.php; php -l database\migrations\2026_11_05_120000_add_assignment_outcomes_and_sections.php; php artisan migrate; php artisan view:cache`
> - Ran `php artisan view:cache; git status --short; git diff --stat;`
>
> </details>
>
> Saya melihat satu hal penting agar keterkaitan CPMK benar-benar berguna: pemilihan CPMK perlu dapat dilakukan per bagian tugas, bukan hanya satu daftar umum untuk seluruh tugas. Saya lanjutkan penyempurnaan itu sekarang, sehingga dosen dapat mengaitkan, misalnya, bagian menyimak dengan CPMK listening dan bagian ringkasan dengan CPMK writing.
>
> <details><summary>Created a file, edited 6 files, ran 7 commands</summary>
>
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+8 -3)
> - Wrote `./resources/views/assignments/show.blade.php` (+1 -0)
> - Wrote `./resources/views/courses/assignments/edit.blade.php` (+6 -0)
> - Wrote `./resources/views/courses/show.blade.php` (+5 -0)
> - Wrote `./database/migrations/2026_11_05_120000_add_assignment_outcomes_and_sections.php` (+0 -7)
> - Wrote `./database/migrations/2026_11_05_130000_add_assessment_config_to_assignment_versions.php` (+22 -0)
> - Wrote `./resources/views/courses/assignments/edit.blade.php` (+1 -1)
> - Wrote `./resources/views/courses/show.blade.php` (+1 -1)
> - Ran `php -l app\Http\Controllers\CourseAssignmentController.php; php -l app\Http\Controllers\WritingAssignmentController.php; php -l app\Models\WritingAssignmentVersion.php; php -l database\migrations\2026_11_05_130000_add_assessment_config_to_assignment_versions.php; php artisan migrate; php artisan view:cache;`
> - Wrote `./database/migrations/2026_11_05_130000_add_assessment_config_to_assignment_versions.php` (+5 -3)
> - Ran `php artisan migrate; php artisan view:cache;`
> - Ran `git diff -- app/Http/Controllers/CourseAssignmentController.php app/Http/Controllers/WritingAssignmentController.php app/Models/WritingAssignmentVersion.php app/Models/WritingRevision.php app/Models/WritingSubmission.php resources/views/courses/assignments/edit.blade.php resources/views/assignments/show.blade.php database/migrations/2026_11_05_120000_add_assignment_outcomes_and_sections.php database/migrations/2026_11_05_130000_add_assessment_config_to_assignment_versions.php`
> - Wrote `./app/Http/Controllers/CourseAssignmentController.php` (+1 -1)
> - Ran `php -l app\Http\Controllers\CourseAssignmentController.php; php -l database\migrations\2026_11_05_120000_add_assignment_outcomes_and_sections.php; php -l database\migrations\2026_11_05_130000_add_assessment_config_to_assignment_versions.php; php artisan view:cache; php artisan migrate:status`
> - Ran `Get-Content resources\views\courses\show.blade.php | Select-Object -Skip 1510 -First 75; Get-Content resources\views\courses\show.blade.php | Select-Object -Skip 2705 -First 85;`
> - Wrote `./resources/views/assignments/show.blade.php` (+1 -1)
> - Wrote `./resources/views/courses/assignments/edit.blade.php` (+1 -1)
> - Wrote `./resources/views/courses/show.blade.php` (+1 -1)
> - Wrote `./resources/views/courses/show.blade.php` (+1 -1)
> - Ran `php -l app\Http\Controllers\CourseAssignmentController.php; php -l app\Http\Controllers\WritingAssignmentController.php; php -l app\Models\WritingAssignmentVersion.php; php -l app\Models\WritingRevision.php; php -l app\Models\WritingSubmission.php; php -l database\migrations\2026_11_05_120000_add_assignment_outcomes_and_sections.php; php -l database\migrations\2026_11_05_130000_add_assessment_config_to_assignment_versions.php; php artisan view:cache;`
> - Ran `rg -n "CPMK yang dinilai|Bagian tugas dan bentuk jawaban|normalizeSections|validatedSectionAnswers|section_answers|assessment_config|draft_section_answers" app\Http\Controllers\CourseAssignmentController.php app\Http\Controllers\WritingAssignmentController.php app\Models\WritingAssignmentVersion.php app\Models\WritingRevision.php app\Models\WritingSubmission.php resources\views\courses\show.blade.php resources\views\courses\assignments\edit.blade.php resources\views\assignments\show.blade.php database\migrations\2026_11_05_120000_add_assignment_outcomes_and_sections.php database\migrations\2026_11_05_130000_add_assessment_config_to_assignment_versions.php; git status --short`
>
> </details>
</details>

Implementasi awal rancangan sudah diterapkan pada fitur tugas matakuliah.

- Dosen dapat menautkan tugas ke CPMK matakuliah, dan memilih CPMK **per bagian tugas**. CPMK diambil dari data matakuliah; teksnya disimpan sebagai snapshot pada konfigurasi versi tugas. Form pembuatan dan penyuntingan ada di [detail matakuliah](/C:/Users/satuh/Herd/academic-hub/resources/views/courses/show.blade.php:1395) dan [sunting tugas](/C:/Users/satuh/Herd/academic-hub/resources/views/courses/assignments/edit.blade.php:120).
- Tugas dapat dirangkai dari beberapa bagian dengan jawaban berupa teks, tautan, tautan audio/video, atau angka. Dosen dapat memberi instruksi, menandai bagian wajib, dan mencatat bobot acuan.
- Mahasiswa melihat CPMK dan bagian tugas, lalu dapat menyimpan jawaban per bagian sebagai draf atau mengirim revisi. Kiriman per bagian tersimpan dalam draf dan riwayat revisi serta terlihat pada tampilan mahasiswa dan dosen. Pengaturan dan validasinya ada di [CourseAssignmentController.php](/C:/Users/satuh/Herd/academic-hub/app/Http/Controllers/CourseAssignmentController.php:445) dan [WritingAssignmentController.php](/C:/Users/satuh/Herd/academic-hub/app/Http/Controllers/WritingAssignmentController.php:305).
- Struktur penyimpanan ditambahkan melalui dua migrasi. Keduanya sudah diterapkan pada database lokal.

Pemeriksaan yang dilakukan: sintaks PHP lolos, dan templat Blade berhasil dikompilasi. Test suite tidak dijalankan.

**Batas tahap ini:** bobot bagian masih menjadi acuan yang ditampilkan; nilai akhir tetap dimasukkan dosen secara keseluruhan. Penilaian otomatis per bagian atau rekap capaian per CPMK belum diterapkan. Bentuk jawaban saat ini juga belum menyediakan unggahan berkas terpisah untuk setiap bagian. Perubahan berada di workspace lokal dan belum diterapkan ke hosting.

> &#x20;[https://academic-hub.test/courses/fia105mpk2-kelas-b](https://academic-hub.test/courses/fia105mpk2-kelas-b)
> resources\views\courses\show\.blade.php:1752\
> \
> syntax error, unexpected token "endforeach", expecting end of file

<details><summary>2 previous messages</summary>

> Saya akan menelusuri blok Blade di sekitar baris 1752, mencocokkan pasangan `@foreach`/`@endforeach` dan kondisi di sekitarnya, lalu memperbaiki kesalahan sintaks tanpa mengubah alur tugas lainnya.
>
> <details><summary>Edited a file, ran 13 commands</summary>
>
> - Ran `$p='resources\views\courses\show.blade.php'; $c=Get-Content $p; for($i=1720;$i -le 1770;$i++){ '{0}: {1}' -f ($i+1),$c[$i] };`
> - Ran `$p='resources\views\courses\show.blade.php'; $c=Get-Content $p; for($i=1435;$i -le 1755;$i++){ if($i -lt 1570 -or $i -gt 1680){ '{0}: {1}' -f ($i+1),$c[$i] } };`
> - Ran `$p='resources\views\courses\show.blade.php'; $c=Get-Content $p; for($i=1370;$i -le 1448;$i++){ '{0}: {1}' -f ($i+1),$c[$i] };`
> - Ran `$p='resources\views\courses\show.blade.php'; $c=Get-Content $p; for($i=1555;$i -le 1710;$i++){ '{0}: {1}' -f ($i+1),$c[$i] };`
> - Ran `rg -l "assignments as \$assignment" storage\framework\views;`
> - Ran `Get-ChildItem storage\framework\views -File | Select-String -Pattern '\$assignments as \$assignment' | Select-Object -First 3 Path,LineNumber,Line`
> - Ran `$p='storage\framework\views\94e25c02d15f823418d217dfc7b5b06a.php'; $c=Get-Content $p; for($i=1478;$i -le 1495;$i++){ '{0}: {1}' -f ($i+1),$c[$i] };`
> - Ran `Get-Content storage\framework\views\94e25c02d15f823418d217dfc7b5b06a.php -TotalCount 4; rg -n "foreach\(\$assignments|endforeach|courseCpmks" storage\framework\views\94e25c02d15f823418d217dfc7b5b06a.php | Select-Object -Last 15;`
> - Ran `$p='storage\framework\views\94e25c02d15f823418d217dfc7b5b06a.php'; $c=Get-Content $p; for($i=1405;$i -le 1458;$i++){ '{0}: {1}' -f ($i+1),$c[$i] };`
> - Ran `Select-String -Path storage\framework\views\*.php -Pattern '<\?php\(\$sectionKey|<\?php\(\$courseCpmks' | Select-Object -First 10 Path,LineNumber,Line`
> - Wrote `./resources/views/courses/show.blade.php` (+3 -1)
> - Ran `php artisan view:clear; php artisan view:cache;`
> - Ran `$f=Get-ChildItem storage\framework\views -File | Select-String -Pattern 'Bagian tugas dan bentuk jawaban' | Select-Object -First 1 -ExpandProperty Path; php -l $f; Select-String -Path $f -Pattern '\$assignments as \$assignment|foreach\(\$assignments|endif;.*foreach' | Select-Object -First 4 LineNumber,Line`
> - Ran `$f='storage\framework\views\94e25c02d15f823418d217dfc7b5b06a.php'; Select-String -Path $f -Pattern '\$courseCpmks|@endforeach|foreach \(\$assignments' | Select-Object -Last 8 LineNumber,Line;`
>
> </details>
</details>

Kesalahan sintaks di [show.blade.php](/C:/Users/satuh/Herd/academic-hub/resources/views/courses/show.blade.php:1393) sudah diperbaiki. Pemanggilan CPMK menggunakan sintaks `@php` yang membuat Blade menghasilkan PHP tidak valid; saya ubah ke blok `@php … @endphp`.

Saya bersihkan dan kompilasi ulang tampilan, lalu memeriksa berkas hasil kompilasi dengan `php -l`. Semuanya berhasil.

> ok. sekarang sudah aman.
> luar biasa baris kodenya sampa 1982 baris. Apakah ini tidak bisa dipisahkan halaman blade khusus untuk html sementara script maupun css dipisahkan agar kode lebih bersih. Fokus diskusi dan jangan sentuh kode

Bisa. File Blade sepanjang itu sebaiknya dipecah agar lebih mudah dibaca dan dirawat. **`courses/show.blade.php` dapat menjadi kerangka halaman**, sementara isi tiap bagian dipindahkan ke view khusus.

Pemisahan yang cocok untuk halaman detail matakuliah:

- **Bagian HTML per fitur:** tab materi, sesi, tugas, peserta, dan nilai. Bagian tugas bisa dipecah lagi menjadi formulir tugas, daftar tugas, daftar submisi, dan modal penilaian.
- **Komponen yang digunakan berulang:** misalnya kartu CPMK, label status, atau ringkasan tenggat. Komponen Blade cocok untuk bagian kecil yang berulang; `@include` cocok untuk potongan halaman yang lebih besar dan khusus pada satu halaman.
- **JavaScript:** pindahkan fungsi interaksi—seperti modal submisi, form AI, dan pengelolaan tugas—ke berkas JavaScript terpisah. Kode Alpine tetap bisa dipakai; yang berubah adalah lokasinya.
- **CSS khusus:** pindahkan aturan CSS buatan sendiri ke berkas stylesheet. Kelas Tailwind yang melekat pada elemen tidak harus dipisahkan secara paksa, karena kelas tersebut memang mendeskripsikan tampilan elemen itu.

Salah satu susunan yang mungkin:

```text
resources/views/courses/
  show.blade.php
  show/
    tabs/
      materials.blade.php
      sessions.blade.php
      assignments.blade.php
      students.blade.php
      grades.blade.php
    assignments/
      create-form.blade.php
      list.blade.php
      submissions.blade.php
      grading-modal.blade.php
```

Pemisahan perlu dilakukan **bertahap**, dimulai dari tab tugas yang paling panjang dan memiliki alur sendiri. Data yang dibutuhkan setiap potongan sebaiknya tetap jelas, dan perilaku halaman—termasuk Alpine, status submisi, serta izin dosen/mahasiswa—perlu dipertahankan saat pemindahan.

Jadi, baris kode yang banyak bukan masalah karena jumlahnya semata; masalahnya adalah banyak tanggung jawab berbeda menumpuk di satu berkas. Struktur yang lebih modular akan membuat perubahan pada fitur tugas lebih terfokus dan mengurangi risiko mengganggu tab lain. Saya belum mengubah kode, sesuai arahan.

> kalau begitu periska semua file blade view tidak hanya show.blad.php halaman tugas. Jadi semua blade view perlu kita rapikan

<details><summary>2 previous messages</summary>

> Saya akan melakukan inventarisasi seluruh view Blade, mencari berkas yang paling besar serta pola HTML, JavaScript, dan CSS yang tercampur. Saya akan menyusun prioritas dan usulan pemisahan berdasarkan temuan; tetap hanya pemeriksaan, tanpa mengubah kode.
>
> <details><summary>Ran 4 commands</summary>
>
> - Ran ``$views=Get-ChildItem resources\views -Recurse -File -Filter *.blade.php; $views | ForEach-Object { $text=Get-Content -LiteralPath $_.FullName -Raw; [pscustomobject]@{Path=$_.FullName; Lines=(($text -split "`n").Count); Scripts=([regex]::Matches($text,'(?is)<script\b').Count); Styles=([regex]::Matches($text,'(?is)<style\b').Count); InlineEvents=([regex]::Matches($text,'(?i)\s(onclick|onchange|onsubmit|oninput)=|\s@(?:click|change|submit|input)=|\sx-on:').Count)} } | Sort-Object Lines -Descending | Select-Object -First 35 | Format-Table -AutoSize; "TOTAL_BLADE_FILES=$($views.Count)";``
> - Ran ``$views=Get-ChildItem resources\views -Recurse -File -Filter *.blade.php; $views | ForEach-Object { $text=Get-Content -LiteralPath $_.FullName -Raw; [pscustomobject]@{View=$_.FullName.Substring((Get-Location).Path.Length+1); Lines=(($text -split "`n").Count); ScriptTags=([regex]::Matches($text,'(?is)<script\b').Count); StyleTags=([regex]::Matches($text,'(?is)<style\b').Count); Includes=([regex]::Matches($text,'@include|<x-[a-z]')).Count} } | Sort-Object View | Format-Table -AutoSize; Get-ChildItem resources\js,resources\css -Recurse -File | Select-Object FullName,Length | Format-Table -AutoSize;``
> - Ran `rg -n "@include|<x-[a-z]|@push\('scripts'|@push\('styles'|@stack\('scripts'|@stack\('styles'|<script|<style" resources\views resources\js resources\css;`
> - Ran ``$views=Get-ChildItem resources\views -Recurse -File -Filter *.blade.php; $rows=$views | ForEach-Object {$t=Get-Content -Raw -LiteralPath $_.FullName; [pscustomobject]@{Path=$_.FullName.Substring((Get-Location).Path.Length+1); Lines=(($t -split "`n").Count)}}; foreach($n in @(200,300,400,500,1000)){"OVER_${n}=$(@($rows | Where-Object Lines -gt $n).Count)"}; "TOTAL_LINES=$((($rows | Measure-Object Lines -Sum).Sum))";``
>
> </details>
</details>

Saya sudah menginventarisasi **semua 48 file Blade** di `resources/views`. Pemeriksaan ini hanya membaca berkas; tidak ada kode yang diubah.

## Temuan utama

- Di workspace saat ini, [courses/show.blade.php](/C:/Users/satuh/Herd/academic-hub/resources/views/courses/show.blade.php) berisi sekitar **2.983 baris**. Satu view menangani beberapa tab matakuliah, form dan daftar tugas, submisi, penilaian, modal, serta skrip halaman. Ini prioritas tertinggi untuk dirapikan.
- **11 view** panjangnya lebih dari 300 baris; **21 view** lebih dari 200 baris.
- View tugas lain yang layak dipecah adalah [assignments/show.blade.php](/C:/Users/satuh/Herd/academic-hub/resources/views/assignments/show.blade.php) (600 baris), [assignments/exam.blade.php](/C:/Users/satuh/Herd/academic-hub/resources/views/assignments/exam.blade.php) (422), [assignments/questions.blade.php](/C:/Users/satuh/Herd/academic-hub/resources/views/assignments/questions.blade.php) (281), dan [courses/assignments/edit.blade.php](/C:/Users/satuh/Herd/academic-hub/resources/views/courses/assignments/edit.blade.php) (331).
- View panjang lainnya mencakup struktur akademik, ekspor BKD, cetak nilai dan sesi, daftar mahasiswa, dashboard, layout, serta pengelolaan pengguna.
- Pola pemakaian ulang masih terbatas. Saya menemukan partial untuk pesan flash, form CorpusLab, form GuruBahasa, dan konten ekspor. Sejumlah bagian besar halaman belum dipisah menjadi view khusus.
- JavaScript sehalaman paling menonjol berada di halaman detail matakuliah; JavaScript global ada di `resources/js/app.js`. CSS global ada di `resources/css/app.css`. Beberapa view ekspor/cetak memiliki CSS sendiri—ini bisa memang diperlukan agar dokumen cetak tetap mandiri.

## Rencana perapian yang disarankan

1. **Mulai dari halaman detail matakuliah.** Pecah per fitur: tab materi, sesi, tugas, mahasiswa, dan nilai. Untuk tab tugas, pisahkan form pembuatan, daftar tugas, daftar submisi, dan modal penilaian.
2. **Pisahkan perilaku halaman dari markup.** Pindahkan logika interaksi halaman—terutama modal dan pengelolaan submisi—ke modul JavaScript khusus, sementara Blade menerima data dan merender HTML.
3. **Rapikan rangkaian halaman tugas.** Pisahkan tampilan pengerjaan, ujian, dan pengelolaan soal menjadi bagian yang lebih kecil sesuai fungsi, dengan pola komponen yang konsisten.
4. **Lanjutkan ke view panjang lain secara bertahap.** Prioritaskan halaman operasional di atas 300 baris; view cetak/ekspor ditangani tersendiri agar hasil cetaknya tidak berubah.
5. **Konsolidasikan CSS sesuai kebutuhan.** CSS umum masuk stylesheet aplikasi. CSS ekspor/cetak dapat memakai stylesheet bersama jika format ekspor tetap bekerja mandiri.
6. **Jaga perilaku saat pemecahan.** Tiap pemindahan mempertahankan izin, data yang dikirim controller, validasi, status tugas, dan interaksi mahasiswa/dosen.

Sasaran yang sehat bukan memaksa setiap view menjadi pendek, melainkan membuat setiap file punya satu tanggung jawab yang jelas. Urutan paling aman adalah memecah per fitur dan memastikan hasil render tetap sama pada setiap tahap sebelum beralih ke kelompok view berikutnya.

> YA SEPAKAT> EKSEKUSI SEKARANG

<details><summary>6 previous messages</summary>

> Saya mulai merapikan view dengan pendekatan bertahap per fitur, dimulai dari halaman detail matakuliah dan halaman tugas. Saya akan menjaga perubahan perilaku tetap minimal: pemindahan markup dan skrip ke berkas khusus, lalu memastikan view hasil pemisahan bisa dikompilasi sebelum lanjut ke kelompok halaman berikutnya.
>
> <details><summary>Edited 2 files, ran 31 commands</summary>
>
> - Ran `Get-ChildItem -Path resources -Filter AGENTS.md -Recurse -File | Select-Object -ExpandProperty FullName; rg -n "^\s*\{?\{--|^\s*{{--|^\s*<script|^\s*<style|^\s*@section|^\s*@push" resources\views\courses\show.blade.php resources\views\assignments resources\views\academic-structure\index.blade.php resources\views\students\index.blade.php resources\views\dashboard.blade.php resources\views\users\*.blade.php resources\views\backups\index.blade.php resources\views\corpuslab\index.blade.php resources\views\outputs\index.blade.php resources\views\bkd\export.blade.php`
> - Ran `Get-ChildItem -Path . -Filter AGENTS.md -Recurse -File | Select-Object -ExpandProperty FullName; rg -n "TAB |TAB[0-9]|<script|<style|@section|@push" resources\views\courses\show.blade.php resources\views\assignments resources\views\academic-structure\index.blade.php resources\views\students\index.blade.php resources\views\dashboard.blade.php resources\views\users resources\views\backups\index.blade.php resources\views\corpuslab\index.blade.php resources\views\outputs\index.blade.php resources\views\bkd\export.blade.php`
> - Ran `rg -n "{{--|@if|@foreach|<script|<style|Modal|MODAL|Toast|Dashboard" resources\views\courses\show.blade.php | Select-Object -Last 80;`
> - Ran `rg -n "TAB|Modal|Toast|<script|@section|@endsection|submissionModal|aiModal|@endif" resources\views\courses\show.blade.php | Select-Object -Last 100;`
> - Ran `Get-Content resources\js\app.js; Get-Content resources\views\layouts\hub.blade.php | Select-Object -First 50; Get-Content vite.config.js; Get-Content resources\views\courses\show.blade.php | Select-Object -Skip 2040 -First 17; Get-Content resources\views\courses\show.blade.php | Select-Object -Skip 2775 -First 208;`
> - Ran `$p='resources\views\courses\show.blade.php'; $c=Get-Content $p; for($i=0;$i -lt 435;$i++){ if($i -lt 70 -or ($i -ge 320 -and $i -le 435)){ '{0}: {1}' -f ($i+1),$c[$i] } };`
> - Ran `$p='resources\views\courses\show.blade.php'; $c=Get-Content $p; for($i=2005;$i -le 2065;$i++){ '{0}: {1}' -f ($i+1),$c[$i] }; for($i=2760;$i -le 2786;$i++){ '{0}: {1}' -f ($i+1),$c[$i] };`
> - Ran `rg -n "courseDetailDashboard|x-data" resources\views\courses\show.blade.php | Select-Object -First 20;`
> - Ran ``$sourcePath='resources\views\courses\show.blade.php'; $lines=[System.IO.File]::ReadAllLines((Join-Path (Get-Location) $sourcePath)); $markers=@('        {{-- TAB 1: BAHAN AJAR MULTI-FORMAT --}}','        {{-- TAB 2: JURNAL PERKULIAHAN (16 PERTEMUAN) & PRESENSI --}}','        {{-- TAB 3: TUGAS MAHASISWA & PENILAIAN --}}','        {{-- TAB 4: PESERTA KELAS (MAHASISWA) --}}','        {{-- MODAL PRATINJAU DOKUMEN GOOGLE DOCS (Global Viewer Modal) --}}','<script>'); $idx=@(); foreach($m in $markers){ $hit=[Array]::IndexOf($lines,$m); if($hit -lt 0){ throw "Marker tidak ditemukan: $m" }; $idx+= $hit }; $scriptEnd=[Array]::IndexOf($lines,'</script>'); if($scriptEnd -lt 0){throw 'Penutup script tidak ditemukan'}; $base=Join-Path (Get-Location) 'resources\views\courses\show'; [System.IO.Directory]::CreateDirectory($base) | Out-Null; $names=@('materials','sessions','assignments','students','modals'); for($i=0;$i -lt 4;$i++){ $part=[string]::Join("`n",$lines[$idx[$i]..($idx[$i+1]-1)])+"`n"; [System.IO.File]::WriteAllText((Join-Path $base ($names[$i]+'.blade.php')),$part,[System.Text.UTF8Encoding]::new($false)) }; $modalEnd=$idx[5]-3; $part=[string]::Join("`n",$lines[$idx[4]..($modalEnd-1)])+"`n"; [System.IO.File]::WriteAllText((Join-Path $base 'modals.blade.php'),$part,[System.Text.UTF8Encoding]::new($false)); $script=[string]::Join("`n",$lines[($idx[5]+1)..($scriptEnd-1)]); $script=$script.Replace('function courseDetailDashboard() {','window.courseDetailDashboard = function (config = {}) {'); $script=[regex]::Replace($script,"tab: '.*?request\('tab'\).*?',", "tab: config.initialTab || 'materials',", [System.Text.RegularExpressions.RegexOptions]::Singleline); $script=[regex]::Replace($script,'sessionsData: @js\(.*?\]\)\),', 'sessionsData: config.sessionsData || [],', [System.Text.RegularExpressions.RegexOptions]::Singleline); $script=[regex]::Replace($script,"aiProvider: '.*?config\('ai.assignment_provider'.*?',", "aiProvider: config.aiProvider || 'deepseek',", [System.Text.RegularExpressions.RegexOptions]::Singleline); $script=$script.Replace("'{{ route('courses.assignments.ai-generate', $course) }}'",'config.aiGenerateUrl').Replace("'{{ csrf_token() }}'",'config.csrfToken'); [System.IO.Directory]::CreateDirectory((Join-Path (Get-Location) 'resources\js\pages')) | Out-Null; [System.IO.File]::WriteAllText((Join-Path (Get-Location) 'resources\js\pages\course-detail.js'),$script+"`n",[System.Text.UTF8Encoding]::new($false)); $root=[System.Collections.Generic.List[string]]::new(); for($i=0;$i -lt $idx[0];$i++){ $root.Add($lines[$i]) }; $root.Add("        @include('courses.show.materials')"); $root.Add("        @include('courses.show.sessions')"); $root.Add("        @include('courses.show.assignments')"); $root.Add("        @include('courses.show.students')"); $root.Add("        @include('courses.show.modals')"); for($i=$modalEnd;$i -lt $idx[5];$i++){ $root.Add($lines[$i]) }; for($i=$scriptEnd+1;$i -lt $lines.Length;$i++){ $root.Add($lines[$i]) }; $rootText=[string]::Join("`n",$root)+"`n"; $rootText=$rootText.Replace('x-data="courseDetailDashboard()"','x-data="courseDetailDashboard(@js([''initialTab'' => in_array(request(''tab''), [''materials'', ''sessions'', ''assignments'', ''students''], true) ? request(''tab'') : ''materials'', ''sessionsData'' => $sessions->map(fn($s) => [''id'' => $s->id, ''session_number'' => $s->session_number, ''topic'' => $s->topic, ''teaching_method'' => $s->teaching_method, ''notes'' => $s->notes, ''date_formatted'' => $s->date?->format(''d/m/Y'')])->values(), ''aiProvider'' => config(''ai.assignment_provider'', ''deepseek''), ''aiGenerateUrl'' => route(''courses.assignments.ai-generate'', $course), ''csrfToken'' => csrf_token()]))"'); [System.IO.File]::WriteAllText((Join-Path (Get-Location) $sourcePath),$rootText,[System.Text.UTF8Encoding]::new($false)); "show root and 5 partials created; JS extracted";``
> - Ran `Get-Content resources\views\courses\show.blade.php; Get-Content resources\js\pages\course-detail.js | Select-Object -First 110; rg -n "@js|\{\{|@endphp|@foreach|@if|function courseDetailDashboard|config\." resources\js\pages\course-detail.js`
> - Ran `rg -n "courseDetailDashboard|@include\('courses.show|@js|<script|@endsection" resources\views\courses\show.blade.php;`
> - Wrote `./resources/js/pages/course-detail.js` (+2 -2)
> - Wrote `./resources/js/app.js` (+2 -0)
> - Ran `php artisan view:clear; php artisan view:cache; npm run build;`
> - Ran `npm run build`
> - Ran `Get-ChildItem resources\views\courses\show -File | ForEach-Object { [pscustomobject]@{File=$_.Name;Lines=(Get-Content -LiteralPath $_.FullName).Count} } | Sort-Object Lines -Descending | Format-Table -AutoSize; rg -n "MODAL|MODAL|modal|-->|@if|@else|@endif" resources\views\courses\show\modals.blade.php | Select-Object -First 30;`
> - Ran `Select-String -Path resources\views\courses\show\assignments.blade.php -Pattern 'TAB 3|FORM|Daftar Tugas|{{-- SISI|Rekap|Terkirim|foreach\(\$assignments|@foreach\(\$assignments' | Select-Object LineNumber,Line; Select-String -Path resources\views\courses\show\materials.blade.php -Pattern '^\s*{{--|@if\(auth|@else' | Select-Object -First 35 LineNumber,Line;`
> - Ran `$p='resources\views\courses\show\assignments.blade.php'; $c=Get-Content $p; for($i=0;$i -lt 40;$i++){ '{0}: {1}' -f ($i+1),$c[$i] }; for($i=185;$i -le 225;$i++){ '{0}: {1}' -f ($i+1),$c[$i] };`
> - Ran `Get-Content resources\views\courses\show\assignments.blade.php | Select-Object -Last 12; Get-Content resources\views\courses\show\modals.blade.php | Select-Object -Last 22`
> - Ran ``$enc=[System.Text.UTF8Encoding]::new($false); $base=Join-Path (Get-Location) 'resources\views\courses\show'; $modalPath=Join-Path $base 'modals.blade.php'; $ml=[System.IO.File]::ReadAllLines($modalPath); $marks=@('        {{-- MODAL PRATINJAU DOKUMEN GOOGLE DOCS (Global Viewer Modal) --}}','        {{-- MODAL EDIT BAHAN AJAR / DOKUMEN --}}','        {{-- MODAL AI GENERATOR PENUGASAN (DeepSeek, Gemini, ChatGPT) --}}','        {{-- MODAL PERIKSA JAWABAN & SUBMISI MAHASISWA --}}','        {{-- Toast Notifikasi Sukses Terapkan AI --}}'); $mi=@(); foreach($m in $marks){$n=[Array]::IndexOf($ml,$m);if($n -lt 0){throw "Marker modal hilang $m"};$mi+=$n}; $mn=@('document-preview','material-editor','assignment-ai','submission-review','toast'); for($i=0;$i -lt 4;$i++){ $end=if($i -lt 3){$mi[$i+1]}else{$mi[4]}; $t=[string]::Join("`n",$ml[$mi[$i]..($end-1)])+"`n"; [System.IO.File]::WriteAllText((Join-Path $base ($mn[$i]+'.blade.php')),$t,$enc) }; $toast=[string]::Join("`n",$ml[$mi[4]..($ml.Length-1)])+"`n"; [System.IO.File]::WriteAllText((Join-Path $base 'toast.blade.php'),$toast,$enc); $orchestrator="@include('courses.show.document-preview')`n@include('courses.show.material-editor')`n@include('courses.show.assignment-ai')`n@include('courses.show.submission-review')`n@include('courses.show.toast')`n"; [System.IO.File]::WriteAllText($modalPath,$orchestrator,$enc); $aPath=Join-Path $base 'assignments.blade.php'; $al=[System.IO.File]::ReadAllLines($aPath); $aMark='            {{-- Daftar Tugas Kelas --}}'; $ai=[Array]::IndexOf($al,$aMark); if($ai -lt 0){throw 'Marker daftar tugas hilang'}; $head=[string]::Join("`n",$al[0..($ai-1)])+"`n"; $list=[string]::Join("`n",$al[$ai..($al.Length-1)])+"`n"; [System.IO.File]::WriteAllText((Join-Path $base 'assignments-create.blade.php'),$head,$enc); [System.IO.File]::WriteAllText((Join-Path $base 'assignments-list.blade.php'),$list,$enc); [System.IO.File]::WriteAllText($aPath,"@include('courses.show.assignments-create')`n@include('courses.show.assignments-list')`n",$enc); Get-ChildItem $base -File | ForEach-Object { [pscustomobject]@{File=$_.Name;Lines=(Get-Content -LiteralPath $_.FullName).Count} } | Sort-Object Lines -Descending | Format-Table -AutoSize;``
> - Ran `Get-Content resources\views\courses\grades-print.blade.php -TotalCount 24; Get-Content resources\views\courses\sessions-print.blade.php -TotalCount 20; Get-Content resources\views\bkd\export.blade.php -TotalCount 20; Get-Content resources\views\outputs\export.blade.php -TotalCount 4; Get-Content resources\views\gurubahasa\export.blade.php -TotalCount 4;`
> - Ran `Select-String -Path resources\views\courses\show\sessions.blade.php -Pattern '{{--' | Select-Object LineNumber,Line; Select-String -Path resources\views\courses\show\materials.blade.php -Pattern '{{--' | Select-Object LineNumber,Line;`
> - Ran ``$enc=[System.Text.UTF8Encoding]::new($false); $base=Join-Path (Get-Location) 'resources\views\courses\show'; foreach($spec in @(@{Name='materials';Marker='            {{-- Interactive Filter & Search Bar untuk Bahan Ajar --}}';A='upload';B='library'},@{Name='sessions';Marker='            {{-- 3. REKAPITULASI TABEL 16 PERTEMUAN (DENGAN SLIDE-OVER ATTENDEE MODAL) --}}';A='attendance-controls';B='session-register'})){ $path=Join-Path $base ($spec.Name+'.blade.php'); $l=[System.IO.File]::ReadAllLines($path); $cut=[Array]::IndexOf($l,$spec.Marker); if($cut -lt 0){throw "Marker $($spec.Marker) hilang"}; $prefix=[string]::Join("`n",$l[0..2])+"`n"; $partA=[string]::Join("`n",$l[3..($cut-1)])+"`n"; $partB=[string]::Join("`n",$l[$cut..($l.Length-1)])+"`n"; [System.IO.File]::WriteAllText((Join-Path $base ($spec.Name+'-'+$spec.A+'.blade.php')),$partA,$enc); [System.IO.File]::WriteAllText((Join-Path $base ($spec.Name+'-'+$spec.B+'.blade.php')),$partB,$enc); [System.IO.File]::WriteAllText($path,$prefix+"@include('courses.show.$($spec.Name)-$($spec.A)')`n@include('courses.show.$($spec.Name)-$($spec.B)')`n",$enc) }; Get-ChildItem $base -File | ForEach-Object { [pscustomobject]@{File=$_.Name;Lines=(Get-Content -LiteralPath $_.FullName).Count} } | Sort-Object Lines -Descending | Format-Table -AutoSize``
> - Ran `Select-String -Path resources\views\assignments\show.blade.php -Pattern '{{--|<section|<form|@if\(auth\(\)->user\(\)->role' | Select-Object -First 60 LineNumber,Line; Select-String -Path resources\views\assignments\exam.blade.php -Pattern '{{--|<section|<form|<div class="' | Select-Object -First 50 LineNumber,Line;`
> - Ran `$p='resources\views\assignments\show.blade.php'; $c=Get-Content $p; for($i=458;$i -lt 490;$i++){ '{0}: {1}' -f ($i+1),$c[$i] }; Get-Content resources\views\assignments\exam.blade.php | Select-Object -Last 25;`
> - Ran `Get-Content resources\views\assignments\show.blade.php | Select-Object -Last 20;`
> - Ran ``$enc=[System.Text.UTF8Encoding]::new($false); $path=Join-Path (Get-Location) 'resources\views\assignments\show.blade.php'; $l=[System.IO.File]::ReadAllLines($path); $studentIf=[Array]::FindIndex($l,[Predicate[string]]{param($x)$x.Trim() -eq "@if(auth()->user()->role === 'student')"}); $branchElse=[Array]::FindIndex($l,[Predicate[string]]{param($x)$x.Trim() -eq '@else'},$studentIf); $lastIf=[Array]::FindLastIndex($l,[Predicate[string]]{param($x)$x.Trim() -eq '@endif'}); if($studentIf -lt 0 -or $branchElse -lt 0 -or $lastIf -lt 0){throw 'Batas student/staff tidak ditemukan'}; $student=[string]::Join("`n",$l[($studentIf+1)..($branchElse-1)])+"`n"; $staff=[string]::Join("`n",$l[($branchElse+1)..($lastIf-1)])+"`n"; [System.IO.File]::WriteAllText((Join-Path (Get-Location) 'resources\views\assignments\show-student.blade.php'),$student,$enc); [System.IO.File]::WriteAllText((Join-Path (Get-Location) 'resources\views\assignments\show-staff.blade.php'),$staff,$enc); $root=[System.Collections.Generic.List[string]]::new(); for($i=0;$i -lt $studentIf;$i++){$root.Add($l[$i])}; $root.Add("@if(auth()->user()->role === 'student')");$root.Add("    @include('assignments.show-student')");$root.Add('@else');$root.Add("    @include('assignments.show-staff')");$root.Add('@endif');$root.Add('@endsection');[System.IO.File]::WriteAllText($path,[string]::Join("`n",$root)+"`n",$enc); Get-ChildItem resources\views\assignments\show*.blade.php | ForEach-Object { [pscustomobject]@{File=$_.Name;Lines=(Get-Content -LiteralPath $_.FullName).Count} } | Format-Table -AutoSize;``
> - Ran ``$enc=[System.Text.UTF8Encoding]::new($false); $path=Join-Path (Get-Location) 'resources\views\assignments\show.blade.php'; $l=[System.IO.File]::ReadAllLines($path); $studentIf=[Array]::FindIndex($l,[Predicate[string]]{param($x)$x.Trim() -eq "@if(auth()->user()->role === 'student')"}); $branchElse=-1; for($i=$studentIf+1;$i -lt $l.Length;$i++){if($l[$i].Trim() -eq '@else'){$branchElse=$i;break}}; $endSection=[Array]::FindLastIndex($l,[Predicate[string]]{param($x)$x.Trim() -eq '@endsection'}); $lastIf=-1; for($i=$endSection-1;$i -gt $branchElse;$i--){if($l[$i].Trim() -eq '@endif'){$lastIf=$i;break}}; if($studentIf -lt 0 -or $branchElse -lt 0 -or $lastIf -lt 0){throw 'Batas student/staff tidak ditemukan'}; $student=[string]::Join("`n",$l[($studentIf+1)..($branchElse-1)])+"`n"; $staff=[string]::Join("`n",$l[($branchElse+1)..($lastIf-1)])+"`n"; [System.IO.File]::WriteAllText((Join-Path (Get-Location) 'resources\views\assignments\show-student.blade.php'),$student,$enc); [System.IO.File]::WriteAllText((Join-Path (Get-Location) 'resources\views\assignments\show-staff.blade.php'),$staff,$enc); $root=[System.Collections.Generic.List[string]]::new(); for($i=0;$i -lt $studentIf;$i++){$root.Add($l[$i])}; $root.Add("@if(auth()->user()->role === 'student')");$root.Add("    @include('assignments.show-student')");$root.Add('@else');$root.Add("    @include('assignments.show-staff')");$root.Add('@endif');$root.Add('@endsection');[System.IO.File]::WriteAllText($path,[string]::Join("`n",$root)+"`n",$enc); Get-ChildItem resources\views\assignments\show*.blade.php | ForEach-Object { [pscustomobject]@{File=$_.Name;Lines=(Get-Content -LiteralPath $_.FullName).Count} } | Format-Table -AutoSize;``
> - Ran ``$views=Get-ChildItem resources\views -Recurse -File -Filter *.blade.php; $views | ForEach-Object { $text=Get-Content -LiteralPath $_.FullName -Raw; [pscustomobject]@{Path=$_.FullName.Substring((Get-Location).Path.Length+1);Lines=(($text -split "`n").Count)} } | Sort-Object Lines -Descending | Select-Object -First 25 | Format-Table -AutoSize;``
> - Ran `php artisan view:clear; php artisan view:cache; php -l resources\js\pages\course-detail.js`
> - Ran `Get-Content resources\css\app.css -Tail 35; Get-Content resources\views\academic-structure\index.blade.php | Select-Object -First 80; Get-Content resources\views\dashboard.blade.php | Select-Object -First 30;`
> - Ran ``$enc=[System.Text.UTF8Encoding]::new($false); $root=(Get-Location).Path; $targets=@(@{View='resources\views\bkd\export.blade.php';Css='bkd-export.css'},@{View='resources\views\courses\grades-print.blade.php';Css='course-grades-print.css'},@{View='resources\views\courses\sessions-print.blade.php';Css='course-sessions-print.css'},@{View='resources\views\gurubahasa\export.blade.php';Css='gurubahasa-export.css'},@{View='resources\views\outputs\export.blade.php';Css='outputs-export.css'},@{View='resources\views\welcome.blade.php';Css='welcome.css'}); $cssDir=Join-Path $root 'public\css'; [System.IO.Directory]::CreateDirectory($cssDir)|Out-Null; foreach($target in $targets){$path=Join-Path $root $target.View;$html=[System.IO.File]::ReadAllText($path);$m=[regex]::Match($html,'(?is)<style\s*>(.*?)</style>');if(!$m.Success){throw "Style tag not found $($target.View)"};[System.IO.File]::WriteAllText((Join-Path $cssDir $target.Css),$m.Groups[1].Value.Trim()+"`n",$enc);$replacement='<link rel="stylesheet" href="{{ asset(''css/'+$target.Css+''') }}">';$html=$html.Substring(0,$m.Index)+$replacement+$html.Substring($m.Index+$m.Length);[System.IO.File]::WriteAllText($path,$html,$enc)}; $layoutPath=Join-Path $root 'resources\views\layouts\hub.blade.php';$layout=[System.IO.File]::ReadAllText($layoutPath);$layout=$layout.Replace('    <style>[x-cloak] { display: none !important; }</style>'+'`n','');[System.IO.File]::WriteAllText($layoutPath,$layout,$enc); Add-Content -Path resources\css\app.css -Value "`n[x-cloak] { display: none !important; }"; Get-ChildItem $cssDir -File | Select-Object Name,Length | Format-Table -AutoSize``
> - Ran `rg -n "<style|stylesheet href|x-cloak" resources\views\layouts\hub.blade.php resources\views\welcome.blade.php resources\views\bkd\export.blade.php resources\views\courses\grades-print.blade.php resources\views\courses\sessions-print.blade.php resources\views\gurubahasa\export.blade.php resources\views\outputs\export.blade.php;`
>
> </details>
>
> Stream error
>
> Reconnecting 5/5
> stream disconnected before completion: No such host is known. (os error 11001)
>
> Stream error
>
> Reconnecting... waiting for network
> Connection failed: error sending request
>
> Stream error
>
> Reconnecting... waiting for network
> Connection failed: error sending request
>
> <details><summary>Edited a file, ran 6 commands</summary>
>
> - Wrote `./resources/views/layouts/hub.blade.php` (+0 -1)
> - Ran `Get-Content resources\views\outputs\export.blade.php -TotalCount 3; Get-Content resources\views\gurubahasa\export.blade.php -TotalCount 2; Get-Content resources\views\bkd\export.blade.php -TotalCount 12; Get-Content resources\css\app.css -Tail 5;`
> - Ran `Select-String -Path resources\views\academic-structure\index.blade.php -Pattern '{{--' | Select-Object LineNumber,Line; Select-String -Path resources\views\students\index.blade.php -Pattern '{{--' | Select-Object LineNumber,Line; Select-String -Path resources\views\dashboard.blade.php -Pattern '{{--|^<section|^<div' | Select-Object LineNumber,Line; Select-String -Path resources\views\layouts\hub.blade.php -Pattern '<header|<main|<aside|@include|<footer|@yield' | Select-Object LineNumber,Line;`
> - Ran `$p='resources\views\dashboard.blade.php';$c=Get-Content $p;for($i=68;$i -le 90;$i++){'{0}: {1}' -f ($i+1),$c[$i]};for($i=200;$i -le 220;$i++){'{0}: {1}' -f ($i+1),$c[$i]};for($i=350;$i -lt $c.Count;$i++){'{0}: {1}' -f ($i+1),$c[$i]}`
> - Ran `Get-Content resources\views\academic-structure\index.blade.php | Select-Object -Last 12; Get-Content resources\views\students\index.blade.php | Select-Object -Last 12;`
> - Ran `$c=Get-Content resources\views\academic-structure\index.blade.php;for($i=318;$i -le 342;$i++){'{0}: {1}' -f ($i+1),$c[$i]};$c=Get-Content resources\views\students\index.blade.php;for($i=68;$i -le 85;$i++){'{0}: {1}' -f ($i+1),$c[$i]};`
> - Ran `Get-Content resources\views\students\index.blade.php -TotalCount 8; Get-Content resources\views\students\index.blade.php | Select-Object -Last 5;`
>
> </details>
</details>
