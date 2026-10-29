<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif (in_array($filter, ['assignment', 'grade', 'attendance'], true)) {
            $query->where('data->category', $filter);
        }

        $notifications = $query->latest()->paginate(15)->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'currentFilter' => $filter,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $targetUrl = $request->query('redirect_to')
            ?? ($notification->data['action_url'] ?? route('notifications.index'));

        return redirect()->to($targetUrl);
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Seluruh notifikasi telah ditandai sudah dibaca.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->delete();

        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }

    public function clearAll(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return back()->with('status', 'Semua riwayat notifikasi berhasil dibersihkan.');
    }
}
