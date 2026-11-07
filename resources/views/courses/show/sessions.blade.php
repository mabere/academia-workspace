        {{-- TAB 2: JURNAL PERKULIAHAN (16 PERTEMUAN) & PRESENSI --}}
        {{-- ======================================================== --}}
        <div x-show="tab === 'sessions'" x-cloak class="space-y-4">
@include('courses.show.sessions-attendance-controls')
@include('courses.show.sessions-session-register')
