<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $fkip = Faculty::firstOrCreate(
            ['code' => 'FKIP'],
            ['name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'description' => 'Membina calon pendidik dan tenaga kependidikan.']
        );

        $fbs = Faculty::firstOrCreate(
            ['code' => 'FBS'],
            ['name' => 'Fakultas Bahasa dan Seni', 'description' => 'Membina studi bahasa, sastra, dan seni budaya.']
        );

        $pbiS1 = StudyProgram::firstOrCreate(
            ['code' => 'PBI-S1'],
            ['faculty_id' => $fkip->id, 'name' => 'Pendidikan Bahasa Inggris', 'level' => 'S1']
        );

        StudyProgram::firstOrCreate(
            ['code' => 'PBI-S2'],
            ['faculty_id' => $fkip->id, 'name' => 'Magister Pendidikan Bahasa Inggris', 'level' => 'S2']
        );

        StudyProgram::firstOrCreate(
            ['code' => 'SI-S1'],
            ['faculty_id' => $fbs->id, 'name' => 'Sastra Inggris', 'level' => 'S1']
        );

        $currentYear = now()->year;
        $nextYear = $currentYear + 1;
        $pastYear = $currentYear - 1;

        $activePeriod = AcademicPeriod::firstOrCreate(
            ['academic_year' => "{$currentYear}/{$nextYear}", 'semester' => 'odd'],
            [
                'name' => "{$currentYear}/{$nextYear} Ganjil",
                'start_date' => "{$currentYear}-09-01",
                'end_date' => "{$nextYear}-01-31",
                'is_active' => true,
            ]
        );

        AcademicPeriod::firstOrCreate(
            ['academic_year' => "{$pastYear}/{$currentYear}", 'semester' => 'even'],
            [
                'name' => "{$pastYear}/{$currentYear} Genap",
                'start_date' => "{$currentYear}-02-01",
                'end_date' => "{$currentYear}-06-30",
                'is_active' => false,
            ]
        );

        // Pasangkan profil dosen untuk akun dosen yang sudah ada
        $lecturers = User::query()->where('role', 'lecturer')->get();
        foreach ($lecturers as $lecturer) {
            LecturerProfile::firstOrCreate(
                ['user_id' => $lecturer->id],
                [
                    'study_program_id' => $pbiS1->id,
                    'functional_rank' => 'lektor',
                    'highest_education' => 'S3',
                    'is_certified' => true,
                    'target_sks' => 12,
                ]
            );
        }
    }
}
