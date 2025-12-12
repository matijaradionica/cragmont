<?php

namespace App\Http\Controllers;

use App\Models\UserWarning;
use Illuminate\Http\Request;

class WarningController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user's warnings.
     */
    public function index()
    {
        $warnings = auth()->user()->warnings()
            ->with(['warnedBy', 'commentReport.comment'])
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('warnings.index', compact('warnings'));
    }

    /**
     * Mark a warning as read.
     */
    public function markAsRead(UserWarning $warning)
    {
        // Ensure user can only mark their own warnings as read
        if ($warning->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $warning->markAsRead();

        return back()->with('success', 'Warning marked as read.');
    }

    /**
     * Mark all warnings as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->warnings()
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'All warnings marked as read.');
    }
}
