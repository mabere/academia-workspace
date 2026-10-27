<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'superadmin', 403, 'Akses terbatas untuk superadmin.');

        $counts = [
            'all' => User::count(),
            'superadmin' => User::where('role', 'superadmin')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'lecturer' => User::where('role', 'lecturer')->count(),
            'student' => User::where('role', 'student')->count(),
            'public' => User::where('role', 'public')->count(),
        ];

        $query = User::query()->with(['lecturerProfile.studyProgram']);

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->filled('study_program_id')) {
            $query->whereHas('lecturerProfile', fn ($q) => $q->where('study_program_id', $request->study_program_id));
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('lecturerProfile', function ($sub) use ($search) {
                        $sub->where('nidn', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $studyPrograms = StudyProgram::orderBy('name')->get();

        return view('users.index', [
            'users' => $users,
            'counts' => $counts,
            'studyPrograms' => $studyPrograms,
            'selectedRole' => $request->input('role', 'all'),
            'searchQuery' => $request->input('q', ''),
            'selectedStudyProgramId' => $request->input('study_program_id'),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->role === 'superadmin', 403, 'Akses terbatas untuk superadmin.');

        $studyPrograms = StudyProgram::orderBy('name')->get();

        return view('users.create', [
            'studyPrograms' => $studyPrograms,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            if ($data['role'] === 'lecturer') {
                $user->lecturerProfile()->create([
                    'study_program_id' => $data['study_program_id'] ?? null,
                    'nidn' => $data['nidn'] ?? null,
                    'nip' => $data['nip'] ?? null,
                    'functional_rank' => $data['functional_rank'] ?? 'tenaga_pengajar',
                    'highest_education' => $data['highest_education'] ?? 'S2',
                    'is_certified' => (bool) ($data['is_certified'] ?? false),
                    'target_sks' => $data['target_sks'] ?? 12,
                ]);
            }

            return $user;
        });

        return redirect()->route('users.index')->with('status', "Pengguna {$user->name} ({$user->email}) berhasil ditambahkan sebagai {$user->roleLabel()}.");
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($request->user()->role === 'superadmin', 403, 'Akses terbatas untuk superadmin.');

        $user->load('lecturerProfile.studyProgram');
        $studyPrograms = StudyProgram::orderBy('name')->get();

        return view('users.edit', [
            'user' => $user,
            'studyPrograms' => $studyPrograms,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Perlindungan akun sendiri: superadmin tidak boleh men-demote akunnya sendiri
        if ($user->id === $request->user()->id && $data['role'] !== 'superadmin') {
            return back()->withErrors(['role' => 'Anda tidak dapat menurunkan peran (demote) akun Anda sendiri dari superadmin.'])->withInput();
        }

        DB::transaction(function () use ($user, $data) {
            $userUpdate = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
            ];

            if (! empty($data['password'])) {
                $userUpdate['password'] = Hash::make($data['password']);
            }

            $user->update($userUpdate);

            if ($data['role'] === 'lecturer') {
                $user->lecturerProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'study_program_id' => $data['study_program_id'] ?? null,
                        'nidn' => $data['nidn'] ?? null,
                        'nip' => $data['nip'] ?? null,
                        'functional_rank' => $data['functional_rank'] ?? 'tenaga_pengajar',
                        'highest_education' => $data['highest_education'] ?? 'S2',
                        'is_certified' => (bool) ($data['is_certified'] ?? false),
                        'target_sks' => $data['target_sks'] ?? 12,
                    ]
                );
            }
        });

        return redirect()->route('users.index')->with('status', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->role === 'superadmin', 403, 'Akses terbatas untuk superadmin.');

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('status', "Kata sandi untuk pengguna {$user->name} berhasil direset.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->role === 'superadmin', 403, 'Akses terbatas untuk superadmin.');

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('status', "Pengguna {$userName} berhasil dihapus dari sistem.");
    }
}
