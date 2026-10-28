<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseSession;
use App\Models\CourseStudent;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $lecturer = User::whereIn('role', ['lecturer', 'admin', 'superadmin'])->first()
            ?? User::factory()->create([
                'name' => 'Dr. Budi Santoso, M.Pd.',
                'email' => 'dosen@academic-hub.test',
                'role' => 'lecturer',
            ]);

        $activePeriod = AcademicPeriod::where('is_active', true)->first()
            ?? AcademicPeriod::firstOrCreate(
                ['academic_year' => '2026/2027', 'semester' => 'odd'],
                ['name' => '2026/2027 Ganjil', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31', 'is_active' => true]
            );

        $faculty = Faculty::first() ?? Faculty::create([
            'name' => 'Fakultas Keguruan dan Ilmu Pendidikan',
            'code' => 'FKIP',
            'description' => 'Fakultas penyelenggara pendidikan akademik dan profesi keguruan.',
        ]);

        $studyProgram = StudyProgram::where('code', 'PBI-S1')->first()
            ?? StudyProgram::first()
            ?? StudyProgram::create([
                'faculty_id' => $faculty->id,
                'name' => 'Pendidikan Bahasa Inggris',
                'code' => 'PBI-S1',
                'level' => 'S1',
            ]);

        // Buat atau ambil 5 mahasiswa contoh
        $students = collect();
        $studentData = [
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad.fauzi@student.test', 'nim' => '230105001', 'cohort_year' => 2023],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti.nur@student.test', 'nim' => '230105002', 'cohort_year' => 2023],
            ['name' => 'Bima Prasetyo', 'email' => 'bima.pra@student.test', 'nim' => '230105003', 'cohort_year' => 2023],
            ['name' => 'Dewi Anggraini', 'email' => 'dewi.ang@student.test', 'nim' => '230105004', 'cohort_year' => 2023],
            ['name' => 'Rizky Pratama', 'email' => 'rizky.pra@student.test', 'nim' => '230105005', 'cohort_year' => 2023],
        ];

        foreach ($studentData as $data) {
            $students->push(User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'role' => 'student',
                    'nim' => $data['nim'],
                    'study_program_id' => $studyProgram->id,
                    'cohort_year' => $data['cohort_year'],
                ]
            ));
        }

        // 5 Mata Kuliah Realistis (Total 12 SKS = Standar PO BKD Dikti)
        $coursesPayload = [
            [
                'code' => 'ENG201',
                'name' => 'Academic Writing',
                'credit_hours' => 3,
                'class_group' => 'Kelas A',
                'schedule_day' => 'Senin',
                'schedule_time' => '08:00 - 10:30 WIB',
                'room' => 'Gedung B R.302',
                'semester_level' => 3,
                'description' => 'Mata kuliah ini membekali mahasiswa dengan keterampilan menulis esai akademik, perumusan thesis statement, sitasi ilmiah standar APA 7th, dan sintesis literatur.',
                'syllabus' => 'CPMK 1: Mahasiswa mampu menyusun kerangka esai argumentatif. CPMK 2: Mahasiswa mampu menerapkan parafrasa dan sitasi yang akurat tanpa plagiasi.',
            ],
            [
                'code' => 'ENG204',
                'name' => 'Applied Linguistics',
                'credit_hours' => 2,
                'class_group' => 'Kelas B',
                'schedule_day' => 'Selasa',
                'schedule_time' => '10:00 - 11:40 WIB',
                'room' => 'Lab Bahasa R.101',
                'semester_level' => 3,
                'description' => 'Kajian penerapan teori linguistik dalam pengajaran bahasa kedua (SLA), analisis kesalahan berbahasa (error analysis), dan sosiolinguistik terapan.',
                'syllabus' => 'CPMK: Mengidentifikasi variasi bahasa dan fenomena transfer bahasa dalam pembelajaran bahasa Inggris.',
            ],
            [
                'code' => 'ENG305',
                'name' => 'Curriculum and Materials Development',
                'credit_hours' => 3,
                'class_group' => 'Kelas A',
                'schedule_day' => 'Rabu',
                'schedule_time' => '13:00 - 15:30 WIB',
                'room' => 'Gedung C R.204',
                'semester_level' => 5,
                'description' => 'Perancangan kurikulum bahasa Inggris, analisis kebutuhan belajar (needs analysis), dan pengembangan materi ajar berbasis capaian CEFR dan Kurikulum Merdeka.',
                'syllabus' => 'CPMK: Mahasiswa mampu merancang buku teks mini (coursebook unit) yang terintegrasi 4 keterampilan bahasa.',
            ],
            [
                'code' => 'ENG308',
                'name' => 'Discourse Analysis',
                'credit_hours' => 2,
                'class_group' => 'Kelas C',
                'schedule_day' => 'Kamis',
                'schedule_time' => '08:00 - 09:40 WIB',
                'room' => 'Gedung B R.201',
                'semester_level' => 5,
                'description' => 'Analisis struktur wacana teks tulis dan lisan, kohesi, koherensi, serta Critical Discourse Analysis (CDA) dalam wacana akademik dan media kontemporer.',
                'syllabus' => 'CPMK: Mahasiswa mampu menganalisis pola retorika dan ideologi dalam teks berita internasional.',
            ],
            [
                'code' => 'ENG410',
                'name' => 'Educational Technology in ELT',
                'credit_hours' => 2,
                'class_group' => 'Kelas B',
                'schedule_day' => 'Jumat',
                'schedule_time' => '09:00 - 10:40 WIB',
                'room' => 'Multimedia Lab R.405',
                'semester_level' => 7,
                'description' => 'Pemanfaatan teknologi digital, korpus bahasa, AI terkelola, dan platform multimedia dalam inovasi pembelajaran bahasa Inggris abad 21.',
                'syllabus' => 'CPMK: Mampu mengintegrasikan CALL (Computer-Assisted Language Learning) dan AI pedagogy dalam rencana pembelajaran.',
            ],
        ];

        foreach ($coursesPayload as $cData) {
            $course = Course::updateOrCreate(
                [
                    'academic_period_id' => $activePeriod->id,
                    'code' => $cData['code'],
                    'class_group' => $cData['class_group'],
                ],
                array_merge($cData, [
                    'study_program_id' => $studyProgram->id,
                    'lecturer_id' => $lecturer->id,
                    'status' => 'active',
                ])
            );

            // Daftarkan mahasiswa ke kelas
            foreach ($students as $idx => $student) {
                CourseStudent::firstOrCreate(
                    ['course_id' => $course->id, 'student_id' => $student->id],
                    [
                        'nim' => $studentData[$idx]['nim'],
                        'attendance_rate' => 100.00,
                        'final_grade' => rand(80, 95),
                        'letter_grade' => 'A',
                    ]
                );
            }

            // Seed Bahan Ajar Multi-Format
            $materials = [
                [
                    'session_number' => null, // Silabus / RPS
                    'title' => 'Rencana Pembelajaran Semester (RPS) & Kontrak Kuliah',
                    'material_type' => 'document',
                    'file_name' => 'RPS_'.$course->code.'.pdf',
                    'file_size' => 1024 * 512, // 512 KB
                    'mime_type' => 'application/pdf',
                    'description' => 'Pedoman perkuliahan 16 pertemuan, rubrik penilaian, dan referensi pustaka utama.',
                ],
                [
                    'session_number' => 1,
                    'title' => 'Slide Kuliah Sesi 1: Orientation & Foundations of '.$course->name,
                    'material_type' => 'document',
                    'file_name' => 'Pertemuan_1_Pengantar.pptx',
                    'file_size' => 1024 * 1024 * 2, // 2 MB
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'description' => 'Presentasi materi pengantar dan pemetaan konsep kunci.',
                ],
                [
                    'session_number' => 2,
                    'title' => 'Audio Listening & Lecture Podcast: Native Academic Discussion',
                    'material_type' => 'audio',
                    'file_name' => 'Audio_Lecture_02.mp3',
                    'file_size' => 1024 * 1024 * 4, // 4 MB
                    'mime_type' => 'audio/mpeg',
                    'description' => 'Rekaman audio diskusi ahli untuk melatih pemahaman menyimak (listening comprehension).',
                ],
                [
                    'session_number' => 3,
                    'title' => 'Video Penjelasan: Video Kuliah Model & Analisis Komparatif',
                    'material_type' => 'link',
                    'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'description' => 'Video pembelajaran visual yang mengupas studi kasus mendalam materi sesi ini.',
                ],
                [
                    'session_number' => 4,
                    'title' => 'Catatan Dosen: Panduan Teknis & Checklist Evaluasi Mandiri',
                    'material_type' => 'text',
                    'content_text' => "### Rangkuman & Pedoman Teknis\n\n1. Pastikan perumusan tesis spesifik dan dapat diperdebatkan (*arguable*).\n2. Setiap paragraf tubuh harus memuat satu ide pengendali (*topic sentence*).\n3. Gunakan penanda kohesi (transisi) yang bervariasi untuk mengalirkan gagasan secara alami.",
                    'description' => 'Catatan ringkas instruksional dosen yang dapat langsung dibaca mahasiswa di portal.',
                ],
            ];

            foreach ($materials as $mData) {
                CourseMaterial::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'title' => $mData['title'],
                    ],
                    array_merge($mData, [
                        'uploaded_by' => $lecturer->id,
                        'is_published' => true,
                    ])
                );
            }

            // Seed Jurnal Perkuliahan (Pertemuan 1 s/d 4 yang sudah terlaksana)
            $sessions = [
                [
                    'session_number' => 1,
                    'date' => now()->subWeeks(3)->format('Y-m-d'),
                    'topic' => 'Kontrak Perkuliahan, Silabus, dan Pengantar Ruang Lingkup '.$course->name,
                    'teaching_method' => 'Ceramah Interaktif & Diskusi Kelas',
                    'attendance_count' => 5,
                    'total_students' => 5,
                    'notes' => 'Seluruh mahasiswa hadir. Pembagian kelompok tugas berjalan lancar.',
                ],
                [
                    'session_number' => 2,
                    'date' => now()->subWeeks(2)->format('Y-m-d'),
                    'topic' => 'Teori Dasar, Terminologi Kunci, dan Analisis Contoh Kasus',
                    'teaching_method' => 'Problem-Based Learning & Small Group Discussion',
                    'attendance_count' => 5,
                    'total_students' => 5,
                    'notes' => 'Diskusi aktif. Mahasiswa mampu mengidentifikasi problem utama.',
                ],
                [
                    'session_number' => 3,
                    'date' => now()->subWeeks(1)->format('Y-m-d'),
                    'topic' => 'Studi Komparatif Model Teks dan Praktik Mandiri Terbimbing',
                    'teaching_method' => 'Workshop & Peer Review',
                    'attendance_count' => 5,
                    'total_students' => 5,
                    'notes' => 'Praktik di kelas menunjukkan kemajuan pemahaman teknik penulisan.',
                ],
                [
                    'session_number' => 4,
                    'date' => now()->format('Y-m-d'),
                    'topic' => 'Evaluasi Formatif, Refleksi Pembelajaran, dan Pengarahan Tugas Tengah Semester',
                    'teaching_method' => 'Diskusi Pleno & Konsultasi Draf',
                    'attendance_count' => 5,
                    'total_students' => 5,
                    'notes' => 'Semua kelompok menyampaikan progres dan menerima umpan balik.',
                ],
            ];

            foreach ($sessions as $sData) {
                CourseSession::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'session_number' => $sData['session_number'],
                    ],
                    array_merge($sData, ['status' => 'completed'])
                );
            }
        }
    }
}
