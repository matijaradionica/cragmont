<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Route;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store or update a rating for a route.
     */
    public function store(Request $request, Route $route)
    {
        // Check if user has logged an ascent
        $hasAscent = $route->ascents()
            ->where('user_id', auth()->id())
            ->exists();

        if (!$hasAscent) {
            return back()->with('error', 'You must log an ascent before rating this route.');
        }

        // Check if user already rated
        $existingRating = $route->ratings()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingRating) {
            return back()->with('error', 'You have already rated this route. Ratings cannot be changed.');
        }

        $validated = $request->validate([
            'is_positive' => 'required|boolean',
        ]);

        Rating::create([
            'user_id' => auth()->id(),
            'route_id' => $route->id,
            'is_positive' => $validated['is_positive'],
        ]);

        return back()->with('success', 'Rating submitted successfully!');
    }
}
