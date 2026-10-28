<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseMaterialRequest;
use App\Models\Course;
use App\Models\CourseMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseMaterialController extends Controller
{
    public function store(StoreCourseMaterialRequest $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

        $data = $request->validated();
        $data['course_id'] = $course->id;
        $data['uploaded_by'] = $request->user()->id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('materials/'.$course->id, 'local');

            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }

        CourseMaterial::create($data);

        return back()->with('status', 'Bahan ajar "'.$data['title'].'" berhasil diunggah.');
    }

    public function download(Request $request, Course $course, CourseMaterial $material): BinaryFileResponse|StreamedResponse|RedirectResponse
    {
        $user = $request->user();
        if (! $user->isStaff()) {
            abort_unless($course->students()->where('student_id', $user->id)->exists(), 403);
        }

        if ($material->external_url) {
            return redirect()->away($material->external_url);
        }

        if (! $material->file_path || ! Storage::disk('local')->exists($material->file_path)) {
            abort(404, 'Berkas bahan ajar tidak ditemukan.');
        }

        $material->increment('download_count');

        return Storage::disk('local')->download($material->file_path, $material->file_name);
    }

    public function stream(Request $request, Course $course, CourseMaterial $material): BinaryFileResponse|StreamedResponse
    {
        $user = $request->user();
        if (! $user->isStaff()) {
            abort_unless($course->students()->where('student_id', $user->id)->exists(), 403);
        }

        if (! $material->file_path || ! Storage::disk('local')->exists($material->file_path)) {
            abort(404, 'Media bahan ajar tidak ditemukan.');
        }

        $fullPath = Storage::disk('local')->path($material->file_path);
        $mime = $material->mime_type ?: mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$material->file_name.'"',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public function publicPreview(Request $request, Course $course, CourseMaterial $material): BinaryFileResponse
    {
        abort_unless($request->hasValidSignature(), 403, 'Tautan pratinjau kedaluwarsa atau tidak valid.');
        abort_unless($material->course_id === $course->id, 404);

        if (! $material->file_path || ! Storage::disk('local')->exists($material->file_path)) {
            abort(404, 'Berkas bahan ajar tidak ditemukan.');
        }

        $fullPath = Storage::disk('local')->path($material->file_path);
        $mime = $material->mime_type ?: mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$material->file_name.'"',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public function destroy(Request $request, Course $course, CourseMaterial $material): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

        if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
            Storage::disk('local')->delete($material->file_path);
        }

        $title = $material->title;
        $material->delete();

        return back()->with('status', 'Bahan ajar "'.$title.'" berhasil dihapus.');
    }
}
