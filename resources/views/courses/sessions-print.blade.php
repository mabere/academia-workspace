<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jurnal Perkuliahan - {{ $course->code }} {{ $course->name }} ({{ $course->class_group }})</title>
    <link rel="stylesheet" href="{{ asset('css/course-sessions-print.css') }}">
</head>
<body>

    <div class="toolbar no-print">
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline">
            &larr; Kembali ke Ruang Mata Kuliah
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            Cetak / Simpan PDF (Ctrl+P)
        </button>
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
            <h1>LAPORAN JURNAL PERKULIAHAN & PRESENSI KELAS</h1>
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
                <td>{{ $course->credit_hours }} SKS / {{ $course->class_group }} (Semester {{ $course->semester_level }})</td>
                <td><strong>NIDN / NIP</strong></td>
                <td>:</td>
                <td>{{ $profile?->nidn ?? '-' }} / {{ $profile?->nip ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Jadwal & Ruang</strong></td>
                <td>:</td>
                <td>{{ $course->scheduleLabel() }}</td>
                <td><strong>Total Mahasiswa</strong></td>
                <td>:</td>
                <td>{{ $course->students()->count() }} Mahasiswa Terdaftar</td>
            </tr>
        </table>

        <!-- Tabel 16 Pertemuan -->
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 5%;">Sesi</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="text-align: left;">Pokok Bahasan / Materi Perkuliahan</th>
                    <th style="width: 18%;">Metode Pembelajaran</th>
                    <th style="width: 14%;">Hadir / Total (%)</th>
                    <th style="width: 15%;">Catatan / Refleksi</th>
                </tr>
            </thead>
            <tbody>
                @for($s = 1; $s <= 16; $s++)
                    @php
                        $session = $sessions->firstWhere('session_number', $s);
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $s }}</td>
                        <td style="text-align: center;">{{ $session?->date?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            @if($session)
                                <strong>{{ $session->topic }}</strong>
                            @else
                                <span style="color: #9ca3af;">(Belum terlaksana)</span>
                            @endif
                        </td>
                        <td>{{ $session?->teaching_method ?? '-' }}</td>
                        <td style="text-align: center;">
                            @if($session)
                                {{ $session->attendance_count }}/{{ $session->total_students }} ({{ $session->attendancePercentage() }}%)
                            @else
                                -
                            @endif
                        </td>
                        <td style="font-size: 8.5pt;">{{ $session?->notes ?? '-' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- Lembar Pengesahan -->
        <div style="margin-top: 1.5rem; text-align: right; font-size: 9.5pt;">
            Ditetapkan pada: {{ now()->translatedFormat('d F Y') }}
        </div>
        <div class="ttd-container">
            <div class="ttd-box">
                <div>Mengetahui,<br>Ketua Program Studi,</div>
                <div>
                    <div class="ttd-name">...................................................</div>
                    <div>NIP. .................................................</div>
                </div>
            </div>
            <div class="ttd-box">
                <div>Dosen Pengampu Mata Kuliah,</div>
                <div>
                    <div class="ttd-name">{{ $lecturer->name }}</div>
                    <div>NIDN. {{ $profile?->nidn ?? '.................................................' }}</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
