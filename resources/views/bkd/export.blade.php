<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan BKD - {{ $lecturer->name }} - {{ $period->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/bkd-export.css') }}">
</head>
<body>

    <div class="toolbar no-print">
        <a href="{{ route('outputs.index', ['academic_period_id' => $period->id]) }}" class="btn btn-outline">
            &larr; Kembali ke Bukti Kinerja
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Cetak / Simpan PDF (Ctrl+P)
        </button>
    </div>

    <div class="page-container">
        <!-- Kop Surat Institusi -->
        <div class="kop-surat">
            <div class="kop-instansi">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
            <div class="kop-fakultas">{{ $profile?->studyProgram?->faculty?->name ?? 'FAKULTAS KEGURUAN DAN ILMU PENDIDIKAN' }}</div>
            <div class="kop-prodi">PROGRAM STUDI {{ strtoupper($profile?->studyProgram?->name ?? 'PENDIDIKAN BAHASA INGGRIS') }} ({{ strtoupper($profile?->studyProgram?->level ?? 'S1') }})</div>
            <div class="kop-alamat">Sistem Informasi Manajemen Kinerja Tri Dharma Perguruan Tinggi · Academic Hub</div>
        </div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h1>LEMBAR HASIL EVALUASI BEBAN KERJA DOSEN (BKD)</h1>
            <p>SEMESTER {{ strtoupper($period->name) }} · TAHUN AKADEMIK {{ $period->academic_year }}</p>
        </div>

        <!-- Bagian I: Identitas Dosen -->
        <div class="section-header">I. IDENTITAS DOSEN</div>
        <table class="info-table">
            <tr>
                <td style="width: 25%;"><strong>Nama Lengkap</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 40%;">{{ $lecturer->name }}</td>
                <td style="width: 15%;"><strong>Fakultas</strong></td>
                <td style="width: 2%;">:</td>
                <td>{{ $profile?->studyProgram?->faculty?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>NIDN / NIP</strong></td>
                <td>:</td>
                <td>{{ $profile?->nidn ?? '-' }} / {{ $profile?->nip ?? '-' }}</td>
                <td><strong>Program Studi</strong></td>
                <td>:</td>
                <td>{{ $profile?->studyProgram?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Jabatan Fungsional</strong></td>
                <td>:</td>
                <td>{{ $profile ? $profile->functionalRankLabel() : 'Tenaga Pengajar' }}</td>
                <td><strong>Sertifikasi Dosen</strong></td>
                <td>:</td>
                <td>{{ ($profile?->is_certified) ? 'Sudah Tersertifikasi' : 'Belum Tersertifikasi' }}</td>
            </tr>
            <tr>
                <td><strong>Pendidikan Tertinggi</strong></td>
                <td>:</td>
                <td>{{ $profile?->highest_education ?? 'S2' }}</td>
                <td><strong>Target Beban</strong></td>
                <td>:</td>
                <td>{{ $summary['target_sks'] }} SKS / Semester</td>
            </tr>
        </table>

        <!-- Bagian II: Rekapitulasi Capaian SKS -->
        <div class="section-header">II. REKAPITULASI KINERJA TRI DHARMA (PO BKD DIKTI)</div>
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="text-align: left;">Bidang Kinerja Tri Dharma</th>
                    <th style="width: 18%;">Ambang Batas Min.</th>
                    <th style="width: 18%;">Capaian Kinerja</th>
                    <th style="width: 18%;">Status Ketercapaian</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td><strong>Bidang Pendidikan & Pengajaran</strong></td>
                    <td style="text-align: center;">3.00 SKS</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($summary['teaching_sks'], 2) }} SKS</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $summary['teaching_met'] ? 'badge-success' : 'badge-danger' }}">
                            {{ $summary['teaching_met'] ? 'MEMENUHI' : 'BELUM MEMENUHI' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">2</td>
                    <td><strong>Bidang Penelitian & Pengembangan</strong></td>
                    <td style="text-align: center;">1.00 SKS</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($summary['research_sks'], 2) }} SKS</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $summary['research_met'] ? 'badge-success' : 'badge-danger' }}">
                            {{ $summary['research_met'] ? 'MEMENUHI' : 'BELUM MEMENUHI' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">3</td>
                    <td><strong>Bidang Pengabdian kepada Masyarakat</strong></td>
                    <td style="text-align: center;">0.50 SKS</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($summary['community_sks'], 2) }} SKS</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $summary['community_met'] ? 'badge-success' : 'badge-danger' }}">
                            {{ $summary['community_met'] ? 'MEMENUHI' : 'BELUM MEMENUHI' }}
                        </span>
                    </td>
                </tr>
                <tr style="background-color: #f8fafc; font-weight: bold;">
                    <td colspan="2" style="text-align: right; padding-right: 12px;">TOTAL KINERJA TERAKUMULASI :</td>
                    <td style="text-align: center;">12.00 - 16.00 SKS</td>
                    <td style="text-align: center; font-size: 11pt; color: #0f766e;">{{ number_format($summary['total_sks'], 2) }} SKS</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $summary['is_eligible'] ? 'badge-success' : 'badge-danger' }}" style="font-size: 9pt;">
                            {{ $summary['status_label'] }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Bagian III: Rincian Bukti Kinerja -->
        <div class="section-header">III. RINCIAN BUKTI KINERJA TERVALIDASI</div>
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Bidang</th>
                    <th style="text-align: left;">Uraian Bukti Kinerja & Proyek</th>
                    <th style="width: 20%;">Modul Sumber</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 10%;">Bobot</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evidenceItems as $idx => $item)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="text-align: center; text-transform: capitalize;">{{ $item['pillar'] }}</td>
                        <td>
                            <strong>{{ $item['title'] }}</strong>
                            <div style="font-size: 8.5pt; color: #4b5563;">Proyek: {{ $item['project_name'] }}</div>
                        </td>
                        <td style="font-size: 9pt;">{{ $item['type_label'] }}</td>
                        <td style="text-align: center; text-transform: capitalize; font-size: 8.5pt;">{{ $item['status'] }}</td>
                        <td style="text-align: center; font-weight: bold; color: #0f766e;">+{{ number_format($item['sks'], 1) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6b7280; padding: 1.5rem;">
                            Belum ada bukti kinerja terverifikasi pada semester ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Bagian IV: Catatan dan Keputusan Asesor BKD -->
        <div class="section-header">IV. KEPUTUSAN DAN CATATAN ASESOR BKD</div>
        <table class="info-table" style="border: 1px solid #cbd5e1; background-color: #f8fafc; padding: 8px;">
            <tr>
                <td style="width: 25%;"><strong>Status Penilaian</strong></td>
                <td style="width: 2%;">:</td>
                <td>
                    <span class="badge {{ $assessment?->status === 'evaluated' ? 'badge-success' : 'badge-warning' }}">
                        {{ $assessment?->statusLabel() ?? 'Draf Portofolio (Belum Diajukan)' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Keputusan Asesor</strong></td>
                <td>:</td>
                <td><strong>{{ $assessment?->verdictLabel() ?? 'Menunggu Penilaian' }}</strong></td>
            </tr>
            <tr>
                <td><strong>Nama Asesor</strong></td>
                <td>:</td>
                <td>{{ $assessment?->assessor?->name ?? 'Belum Ditugaskan' }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Evaluasi</strong></td>
                <td>:</td>
                <td>{{ $assessment?->evaluated_at?->translatedFormat('d F Y H:i') ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Catatan Rekomendasi</strong></td>
                <td>:</td>
                <td><em>{{ $assessment?->notes ?? 'Tidak ada catatan rekomendasi khusus dari asesor.' }}</em></td>
            </tr>
        </table>

        <!-- Bagian V: Lembar Pengesahan -->
        <div style="margin-top: 1.5rem; text-align: right; font-size: 10pt;">
            Ditetapkan pada tanggal: {{ now()->translatedFormat('d F Y') }}
        </div>
        <div class="ttd-container">
            <div class="ttd-box">
                <div>Dosen Yang Dinilai,</div>
                <div>
                    <div class="ttd-name">{{ $lecturer->name }}</div>
                    <div>NIDN. {{ $profile?->nidn ?? '..............................' }}</div>
                </div>
            </div>
            <div class="ttd-box">
                <div>Asesor Penilai BKD,</div>
                <div>
                    <div class="ttd-name">{{ $assessment?->assessor?->name ?? '.........................................' }}</div>
                    <div>NIDN. {{ $assessment?->assessor?->lecturerProfile?->nidn ?? '..............................' }}</div>
                </div>
            </div>
            <div class="ttd-box">
                <div>Mengetahui,<br>Ketua Program Studi,</div>
                <div>
                    <div class="ttd-name">.........................................</div>
                    <div>NIP. ..............................</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
