<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Models\Location;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Route::class);

        // This view will use Livewire RouteSearch component for filtering
        return view('routes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Route::class);

        $locations = Location::orderBy('level')->orderBy('name')->get();

        return view('routes.create', compact('locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRouteRequest $request)
    {
        $validated = $request->validated();

        if (isset($validated['topo_data'])) {
            if ($validated['topo_data'] === 'null' || $validated['topo_data'] === null || $validated['topo_data'] === '') {
                $validated['topo_data'] = null;
            } elseif (is_string($validated['topo_data'])) {
                $decoded = json_decode($validated['topo_data'], true);
                $validated['topo_data'] = is_array($decoded) ? $decoded : null;
            }
        }

        // Set the creator
        $validated['created_by_user_id'] = auth()->id();

        // Handle topo file upload
        if ($request->hasFile('topo')) {
            $path = $request->file('topo')->store('topos', 'public');
            $validated['topo_url'] = $path;
        } else {
            $validated['topo_data'] = null;
        }

        if (empty($validated['topo_url'])) {
            $validated['topo_data'] = null;
        }

        // Auto-approve for Admin and Club/Equipper users
        if (auth()->user()->canAutoApproveRoutes()) {
            $validated['is_approved'] = true;
            $validated['approved_by_user_id'] = auth()->id();
            $validated['approved_at'] = now();
        }

        $route = Route::create($validated);

        $message = $route->is_approved
            ? 'Route created and approved successfully!'
            : 'Route submitted for approval.';

        return redirect()->route('routes.show', $route)
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Route $route)
    {
        $this->authorize('view', $route);

        $route->load(['location', 'creator', 'approver', 'photos', 'ratings.user']);

        // Load top-level comments with replies
        $comments = $route->comments()
            ->with(['user', 'replies.user', 'replies.replies.user'])
            ->topLevel()
            ->mostHelpful()
            ->get();

        // Get user's rating if authenticated
        $userRating = auth()->check() ? $route->getUserRating(auth()->user()) : null;

        // Check if user has logged ascent (required for rating)
        $userHasAscent = auth()->check() ? $route->ascents()->where('user_id', auth()->id())->exists() : false;

        return view('routes.show', compact('route', 'comments', 'userRating', 'userHasAscent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Route $route)
    {
        $this->authorize('update', $route);

        $locations = Location::orderBy('level')->orderBy('name')->get();

        return view('routes.edit', compact('route', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRouteRequest $request, Route $route)
    {
        $validated = $request->validated();

        if (isset($validated['topo_data'])) {
            if ($validated['topo_data'] === 'null' || $validated['topo_data'] === null || $validated['topo_data'] === '') {
                $validated['topo_data'] = null;
            } elseif (is_string($validated['topo_data'])) {
                $decoded = json_decode($validated['topo_data'], true);
                $validated['topo_data'] = is_array($decoded) ? $decoded : null;
            }
        }

        // Handle topo file upload
        if ($request->hasFile('topo')) {
            // Delete old topo if it exists
            if ($route->topo_url) {
                Storage::disk('public')->delete($route->topo_url);
            }

            $path = $request->file('topo')->store('topos', 'public');
            $validated['topo_url'] = $path;
        }

        if ($request->hasFile('topo') && empty($validated['topo_data'])) {
            $validated['topo_data'] = null;
        }

        if (empty($validated['topo_url']) && !$route->topo_url) {
            $validated['topo_data'] = null;
        }

        // Trigger re-moderation if edited by non-admin/moderator
        if (!auth()->user()->isAdmin() && !auth()->user()->isModerator()) {
            $validated['is_approved'] = false;
            $validated['approved_by_user_id'] = null;
            $validated['approved_at'] = null;
        }

        $route->update($validated);

        $message = !$route->is_approved
            ? 'Changes saved. Route will need re-approval.'
            : 'Route updated successfully!';

        return redirect()->route('routes.show', $route)
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Route $route)
    {
        $this->authorize('delete', $route);

        $route->delete();

        return redirect()->route('routes.index')
            ->with('success', 'Route deleted successfully.');
    }

    /**
     * Approve a pending route.
     */
    public function approve(Route $route)
    {
        $this->authorize('approve', $route);

        $route->approve(auth()->user());

        return back()->with('success', 'Route approved successfully!');
    }

    /**
     * Reject a pending route.
     */
    public function reject(Route $route)
    {
        $this->authorize('approve', $route);

        // For now, just delete the route
        // In the future, you might want to add a rejection reason or soft delete
        $route->delete();

        return back()->with('success', 'Route rejected and removed.');
    }
}
