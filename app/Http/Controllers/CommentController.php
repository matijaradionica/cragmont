<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request, Route $route)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'route_id' => $route->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Comment posted successfully!');
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Comment $comment)
    {
        if (!$comment->canBeEditedBy(auth()->user())) {
            abort(403, 'You can only edit your own comments within 30 minutes of posting.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment->update([
            'content' => $validated['content'],
            'edited_at' => now(),
        ]);

        return back()->with('success', 'Comment updated successfully!');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized to delete this comment.');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Vote on a comment.
     */
    public function vote(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'vote_type' => 'required|in:upvote,downvote,helpful',
        ]);

        $voteType = $validated['vote_type'];

        // Check if user already voted this way
        $existingVote = CommentVote::where('user_id', auth()->id())
            ->where('comment_id', $comment->id)
            ->where('vote_type', $voteType)
            ->first();

        DB::transaction(function () use ($existingVote, $comment, $voteType) {
            if ($existingVote) {
                // Remove vote
                $existingVote->delete();

                // Decrement counter
                $column = $voteType . '_count';
                $comment->decrement($column);
            } else {
                // Add vote
                CommentVote::create([
                    'user_id' => auth()->id(),
                    'comment_id' => $comment->id,
                    'vote_type' => $voteType,
                ]);

                // Increment counter
                $column = $voteType . '_count';
                $comment->increment($column);
            }
        });

        return back();
    }
}
