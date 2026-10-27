<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jurnal Perkuliahan - {{ $course->code }} {{ $course->name }} ({{ $course->class_group }})</title>
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
            font-size: 10.5pt;
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
            font-size: 9.5pt;
        }
        table.bordered th, table.bordered td {
            border: 1px solid #4b5563;
            padding: 5px 6px;
            vertical-align: top;
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
        }
    </style>
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
