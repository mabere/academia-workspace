<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Nilai Akhir - {{ $course->code }} {{ $course->name }} ({{ $course->class_group }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, Georgia, serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #111827;
            background-color: #f8fafc;
            margin: 0;
            padding: 2rem 1rem;
        }
        .page-container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            padding: 2.5rem 3rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }
        .toolbar {
            max-width: 820px;
            margin: 0 auto 1.5rem auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: ui-sans-serif, system-ui, sans-serif;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-primary {
            background-color: #0f766e;
            color: #ffffff;
        }
        .btn-outline {
            background-color: #ffffff;
            color: #374151;
            border-color: #d1d5db;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #1f2937;
            padding-bottom: 10px;
            margin-bottom: 16px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .kop-instansi {
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-fakultas {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-prodi {
            font-size: 10pt;
            font-weight: 500;
        }
        .doc-title {
            text-align: center;
            margin: 1.25rem 0 1rem 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        .doc-title h1 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .doc-title p {
            font-size: 10pt;
            margin: 3px 0 0 0;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            font-size: 9pt;
        }
        table.bordered th, table.bordered td {
            border: 1px solid #4b5563;
            padding: 5px 6px;
            vertical-align: middle;
        }
        table.bordered th {
            background-color: #f1f5f9;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bold;
        }
        table.info-table td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            font-family: Arial, Helvetica, sans-serif;
        }
        .stat-card {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 0.5rem;
            text-align: center;
            background: #fafafa;
        }
        .stat-val {
            font-size: 13pt;
            font-weight: bold;
            color: #0f766e;
        }
        .stat-lbl {
            font-size: 7.5pt;
            text-transform: uppercase;
            color: #4b5563;
            letter-spacing: 0.5px;
        }
        .scale-note {
            font-size: 8pt;
            color: #4b5563;
            margin-bottom: 1.5rem;
            font-style: italic;
        }
        .ttd-container {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            text-align: center;
            page-break-inside: avoid;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
        }
        .ttd-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .page-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            table.bordered th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .stat-card {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
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
