<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Faculty;
use App\Models\StudyProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicStructureController extends Controller
{
    private function ensureSuperadmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->isSuperadmin(), 403, 'Akses pengelolaan unit akademik hanya untuk Superadmin.');
    }

    public function index(): View
    {
        $this->ensureSuperadmin();

        $faculties = Faculty::query()
            ->withCount('studyPrograms')
            ->orderBy('code')
            ->get();

        $studyPrograms = StudyProgram::query()
            ->with('faculty')
            ->withCount(['courses', 'students'])
            ->orderBy('faculty_id')
            ->orderBy('name')
            ->get();

        $academicPeriods = AcademicPeriod::query()
            ->withCount(['courses', 'projects'])
            ->orderByDesc('start_date')
            ->get();

        return view('academic-structure.index', [
            'faculties' => $faculties,
            'studyPrograms' => $studyPrograms,
            'academicPeriods' => $academicPeriods,
        ]);
    }

    public function storeFaculty(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:faculties,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['code'] = Str::upper(trim($validated['code']));

        $faculty = Faculty::create($validated);

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Fakultas {$faculty->name} ({$faculty->code}) berhasil ditambahkan.");
    }

    public function updateFaculty(Request $request, Faculty $faculty): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('faculties', 'code')->ignore($faculty->id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['code'] = Str::upper(trim($validated['code']));

        $faculty->update($validated);

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Data fakultas {$faculty->name} berhasil diperbarui.");
    }

    public function destroyFaculty(Faculty $faculty): RedirectResponse
    {
        $this->ensureSuperadmin();

        if ($faculty->studyPrograms()->exists()) {
            return redirect()
                ->route('academic-structure.index')
                ->withErrors(['faculty' => "Fakultas {$faculty->name} tidak dapat dihapus karena masih menaungi Program Studi aktif."]);
        }

        $name = $faculty->name;
        $faculty->delete();

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Fakultas {$name} berhasil dihapus.");
    }

    public function storeStudyProgram(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'code' => ['required', 'string', 'max:20', 'unique:study_programs,code'],
            'name' => ['required', 'string', 'max:150'],
            'level' => ['required', 'string', Rule::in(['D3', 'D4', 'S1', 'S2', 'S3', 'Profesi'])],
        ]);

        $validated['code'] = Str::upper(trim($validated['code']));

        $prodi = StudyProgram::create($validated);

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Program Studi {$prodi->name} ({$prodi->level}) berhasil ditambahkan.");
    }

    public function updateStudyProgram(Request $request, StudyProgram $studyProgram): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'code' => ['required', 'string', 'max:20', Rule::unique('study_programs', 'code')->ignore($studyProgram->id)],
            'name' => ['required', 'string', 'max:150'],
            'level' => ['required', 'string', Rule::in(['D3', 'D4', 'S1', 'S2', 'S3', 'Profesi'])],
        ]);

        $validated['code'] = Str::upper(trim($validated['code']));

        $studyProgram->update($validated);

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Program Studi {$studyProgram->name} berhasil diperbarui.");
    }

    public function destroyStudyProgram(StudyProgram $studyProgram): RedirectResponse
    {
        $this->ensureSuperadmin();

        if ($studyProgram->courses()->exists()) {
            return redirect()
                ->route('academic-structure.index')
                ->withErrors(['study_program' => "Program Studi {$studyProgram->name} tidak dapat dihapus karena sudah memiliki Mata Kuliah terkait."]);
        }

        $name = $studyProgram->name;
        $studyProgram->delete();

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Program Studi {$name} berhasil dihapus.");
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'academic_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', Rule::in(['odd', 'even', 'short'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $makeActive = $request->boolean('is_active') || ! AcademicPeriod::where('is_active', true)->exists();

        if ($makeActive) {
            AcademicPeriod::query()->where('is_active', true)->update(['is_active' => false]);
        }

        $period = AcademicPeriod::create([
            'name' => $validated['name'],
            'academic_year' => $validated['academic_year'],
            'semester' => $validated['semester'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $makeActive,
        ]);

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Periode akademik {$period->name} berhasil ditambahkan.");
    }

    public function activatePeriod(AcademicPeriod $period): RedirectResponse
    {
        $this->ensureSuperadmin();

        AcademicPeriod::query()->where('is_active', true)->update(['is_active' => false]);
        $period->update(['is_active' => true]);

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Periode {$period->name} ditetapkan sebagai semester aktif.");
    }

    public function destroyPeriod(AcademicPeriod $period): RedirectResponse
    {
        $this->ensureSuperadmin();

        if ($period->courses()->exists() || $period->projects()->exists()) {
            return redirect()
                ->route('academic-structure.index')
                ->withErrors(['period' => "Periode {$period->name} tidak dapat dihapus karena masih memiliki Mata Kuliah atau Proyek terkait."]);
        }

        $name = $period->name;
        $period->delete();

        return redirect()
            ->route('academic-structure.index')
            ->with('status', "Periode akademik {$name} berhasil dihapus.");
    }
}
