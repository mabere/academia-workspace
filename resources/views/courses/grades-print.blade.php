<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Nilai Akhir - {{ $course->code }} {{ $course->name }} ({{ $course->class_group }})</title>
    <link rel="stylesheet" href="{{ asset('css/course-grades-print.css') }}">
</head>
<body>

    <div class="toolbar no-print">
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline">
            &larr; Kembali ke Ruang Mata Kuliah
        </a>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            @if(auth()->user()->isStaff())
                <a href="{{ route('courses.grades.export', $course) }}" class="btn btn-outline" style="border-color: #059669; color: #065f46; background: #ecfdf5;">
                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Unduh CSV / Excel
                </a>
            @endif
            <button onclick="window.print()" class="btn btn-primary">
                Cetak / Simpan PDF (Ctrl+P)
            </button>
        </div>
    </div>

    <div class="page-container">
        <!-- Kop Surat Institusi -->
        <div class="kop-surat">
            <div class="kop-instansi">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
            <div class="kop-fakultas">{{ $course->studyProgram->faculty->name ?? 'FAKULTAS KEGURUAN DAN ILMU PENDIDIKAN' }}</div>
            <div class="kop-prodi">PROGRAM STUDI {{ strtoupper($course->studyProgram->name) }} ({{ strtoupper($course->studyProgram->level) }})</div>
        </div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h1>DAFTAR NILAI AKHIR MAHASISWA & REKAPITULASI PENILAIAN KELAS</h1>
            <p>SEMESTER {{ strtoupper($course->academicPeriod->name) }} · TAHUN AKADEMIK {{ $course->academicPeriod->academic_year }}</p>
        </div>

        <!-- Identitas Mata Kuliah & Dosen -->
        <table class="info-table" style="margin-bottom: 0.8rem;">
            <tr>
                <td style="width: 20%;"><strong>Mata Kuliah</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 38%;">{{ $course->name }} ({{ $course->code }})</td>
                <td style="width: 18%;"><strong>Dosen Pengampu</strong></td>
                <td style="width: 2%;">:</td>
                <td>{{ $lecturer->name }}</td>
            </tr>
            <tr>
                <td><strong>Bobot SKS / Kelas</strong></td>
                <td>:</td>
                <td>{{ $course->credit_hours }} SKS / Kelas {{ $course->class_group }} (Smt {{ $course->semester_level }})</td>
                <td><strong>NIDN / NIP</strong></td>
                <td>:</td>
                <td>{{ $profile->nidn ?? '-' }} / {{ $profile->nip ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Program Studi</strong></td>
                <td>:</td>
                <td>{{ $course->studyProgram->name }} ({{ strtoupper($course->studyProgram->level) }})</td>
                <td><strong>Total Mahasiswa</strong></td>
                <td>:</td>
                <td>{{ $students->count() }} Orang Terdaftar</td>
            </tr>
            <tr>
                <td><strong>Jadwal & Ruang</strong></td>
                <td>:</td>
                <td>{{ $course->schedule_day ? $course->schedule_day.', '.$course->schedule_time : '-' }} ({{ $course->room ?: 'Daring' }})</td>
                <td><strong>Tanggal Cetak</strong></td>
                <td>:</td>
                <td>{{ now()->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <!-- Ringkasan Statistik Kelas -->
        @php
            $gradedStudents = $students->filter(fn($s) => $s->final_grade !== null);
            $avgGrade = $gradedStudents->isNotEmpty() ? round($gradedStudents->avg('final_grade'), 1) : 0;
            $maxGrade = $gradedStudents->isNotEmpty() ? $gradedStudents->max('final_grade') : 0;
            $minGrade = $gradedStudents->isNotEmpty() ? $gradedStudents->min('final_grade') : 0;
            $passCount = $gradedStudents->filter(fn($s) => $s->final_grade >= 55.0)->count();
            $passRate = $gradedStudents->isNotEmpty() ? round(($passCount / $gradedStudents->count()) * 100, 1) : 0;
        @endphp

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-val">{{ $avgGrade ?: '-' }}</div>
                <div class="stat-lbl">Rata-Rata Kelas</div>
            </div>
            <div class="stat-card">
                <div class="stat-val">{{ $maxGrade ?: '-' }} / {{ $minGrade ?: '-' }}</div>
                <div class="stat-lbl">Tertinggi / Terendah</div>
            </div>
            <div class="stat-card">
                <div class="stat-val">{{ $passCount }}/{{ $students->count() }}</div>
                <div class="stat-lbl">Jumlah Mahasiswa Lulus</div>
            </div>
            <div class="stat-card">
                <div class="stat-val">{{ $passRate }}%</div>
                <div class="stat-lbl">Tingkat Kelulusan</div>
            </div>
        </div>

        <!-- Tabel Rekapitulasi Nilai Mahasiswa -->
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 28px;">No</th>
                    <th style="width: 80px;">NIM</th>
                    <th>Nama Mahasiswa</th>
                    @foreach($assignments as $idx => $assignment)
                        <th style="width: 55px;" title="{{ $assignment->title }}">
                            T{{ $idx + 1 }}
                        </th>
                    @endforeach
                    <th style="width: 55px;">Hadir (%)</th>
                    <th style="width: 55px;">Nilai Akhir</th>
                    <th style="width: 45px;">Mutu</th>
                    <th style="width: 65px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $cs)
                    @php
                        $isPassed = $cs->final_grade !== null && $cs->final_grade >= 55.0;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="text-align: center; font-family: Arial, Helvetica, sans-serif; font-weight: bold;">
                            {{ $cs->nim ?: '-' }}
                        </td>
                        <td>
                            <strong>{{ $cs->student->name }}</strong>
                        </td>
                        @foreach($assignments as $assignment)
                            @php
                                $sub = $assignment->submissions->firstWhere('student_id', $cs->student_id);
                            @endphp
                            <td style="text-align: center;">
                                @if($sub && $sub->score !== null)
                                    {{ number_format($sub->score, 1) }}
                                @elseif($sub)
                                    <span style="font-size: 7.5pt; color: #b45309;">Draft</span>
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td style="text-align: center;">
                            {{ number_format($cs->attendance_rate, 0) }}%
                        </td>
                        <td style="text-align: center; font-weight: bold; background-color: #f8fafc;">
                            {{ $cs->final_grade !== null ? number_format($cs->final_grade, 1) : '-' }}
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ $cs->letter_grade ?: '-' }}
                        </td>
                        <td style="text-align: center; font-size: 8pt; font-weight: bold;">
                            @if($cs->final_grade !== null)
                                <span style="color: {{ $isPassed ? '#0f766e' : '#b91c1c' }};">
                                    {{ $isPassed ? 'LULUS' : 'TL' }}
                                </span>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 6 + $assignments->count() }}" style="text-align: center; padding: 1.5rem; color: #6b7280;">
                            Belum ada mahasiswa yang terdaftar di kelas perkuliahan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Keterangan Tugas & Skala Penilaian -->
        @if($assignments->isNotEmpty())
            <div style="font-size: 8pt; margin-bottom: 0.5rem; color: #374151;">
                <strong>Keterangan Tugas:</strong>
                @foreach($assignments as $idx => $assignment)
                    <span><strong>T{{ $idx + 1 }}</strong>: {{ $assignment->title }}@if(!$loop->last), @endif</span>
                @endforeach
            </div>
        @endif

        <div class="scale-note">
            * Rentang Konversi Nilai Standar Dikti: A (85.0 - 100), A- (80.0 - 84.9), B+ (75.0 - 79.9), B (70.0 - 74.9), B- (65.0 - 69.9), C+ (60.0 - 64.9), C (55.0 - 59.9), D (40.0 - 54.9), E (< 40.0). Ambang Batas Kelulusan: C (55.0).
        </div>

        <!-- Kolom Tanda Tangan Resmi -->
        <div class="ttd-container">
            <div class="ttd-box">
                <div>
                    Mengetahui,<br>
                    Ketua Program Studi {{ $course->studyProgram->name }}
                </div>
                <div>
                    <div class="ttd-name">(...................................................)</div>
                    <div>NIP. .........................................</div>
                </div>
            </div>
            <div class="ttd-box">
                <div>
                    {{ $course->studyProgram->faculty->university_city ?? 'Kota Institusi' }}, {{ now()->translatedFormat('d F Y') }}<br>
                    Dosen Pengampu Mata Kuliah
                </div>
                <div>
                    <div class="ttd-name">{{ $lecturer->name }}</div>
                    <div>NIDN. {{ $profile->nidn ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
