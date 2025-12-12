<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\UserWarning;
use Illuminate\Http\Request;

class CommentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new report for a comment.
     */
    public function store(Request $request, Comment $comment)
    {
        // Prevent users from reporting their own comments
        if ($comment->user_id === auth()->id()) {
            return back()->with('error', 'You cannot report your own comment.');
        }

        // Check if user already reported this comment
        $existingReport = CommentReport::where('comment_id', $comment->id)
            ->where('reported_by_user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if ($existingReport) {
            return back()->with('error', 'You have already reported this comment.');
        }

        $validated = $request->validate([
            'reason' => 'required|in:Spam,Harassment,Inappropriate Content,Misinformation,Other',
            'description' => 'nullable|string|max:1000',
        ]);

        CommentReport::create([
            'comment_id' => $comment->id,
            'reported_by_user_id' => auth()->id(),
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        // Mark comment as reported
        $comment->update(['is_reported' => true]);

        return back()->with('success', 'Comment reported successfully. Our moderators will review it.');
    }

    /**
     * Show all pending reports (admin only).
     */
    public function index()
    {
        $this->authorize('viewAdmin');

        $reports = CommentReport::with(['comment.user', 'comment.route', 'reportedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Approve a report and take action (admin only).
     */
    public function approve(Request $request, CommentReport $report)
    {
        $this->authorize('viewAdmin');

        $validated = $request->validate([
            'action' => 'required|in:delete_comment,warn_user,no_action',
        ]);

        // Mark report as resolved
        $report->update([
            'status' => 'resolved',
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Take action based on admin decision
        if ($validated['action'] === 'delete_comment') {
            $report->comment->delete();
            $message = 'Comment deleted and report resolved.';
        } elseif ($validated['action'] === 'warn_user') {
            // Create warning for the user
            UserWarning::create([
                'user_id' => $report->comment->user_id,
                'warned_by_user_id' => auth()->id(),
                'comment_report_id' => $report->id,
                'reason' => $report->reason,
                'message' => "Your comment has been reported and reviewed. Reason: {$report->reason}. " .
                             ($report->description ? "Details: {$report->description}. " : '') .
                             "Please review our community guidelines to avoid future warnings.",
            ]);
            $message = 'User warned and report resolved.';
        } else {
            // No action needed
            $report->comment->update(['is_reported' => false]);
            $message = 'Report dismissed with no action taken.';
        }

        return back()->with('success', $message);
    }

    /**
     * Dismiss a report (admin only).
     */
    public function dismiss(CommentReport $report)
    {
        $this->authorize('viewAdmin');

        $report->update([
            'status' => 'dismissed',
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $report->comment->update(['is_reported' => false]);

        return back()->with('success', 'Report dismissed.');
    }
}
