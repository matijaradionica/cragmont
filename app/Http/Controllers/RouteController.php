<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Models\Location;
use App\Models\Photo;
use App\Models\Route;
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

        // Set default values for optional fields when admin doesn't provide them
        if (auth()->user()->isAdmin()) {
            $validated['location_id'] = $validated['location_id'] ?? null;
            $validated['grade_type'] = $validated['grade_type'] ?? 'UIAA';
            $validated['grade_value'] = $validated['grade_value'] ?? 'N/A';
            $validated['route_type'] = $validated['route_type'] ?? 'Sport';
            $validated['risk_rating'] = $validated['risk_rating'] ?? 'None';
            $validated['pitch_count'] = $validated['pitch_count'] ?? 1;
            $validated['status'] = $validated['status'] ?? 'New';

            // Admins always get auto-approved routes
            $validated['is_approved'] = true;
            $validated['approved_by_user_id'] = auth()->id();
            $validated['approved_at'] = now();
        } elseif (auth()->user()->canAutoApproveRoutes()) {
            // Auto-approve for Club/Equipper users
            $validated['is_approved'] = true;
            $validated['approved_by_user_id'] = auth()->id();
            $validated['approved_at'] = now();
        }

        $route = Route::create($validated);

        $uploadedPhotos = $request->file('photos', []);
        if (count($uploadedPhotos) > 10) {
            return back()
                ->withInput()
                ->withErrors(['photos' => 'You can upload a maximum of 10 photos per route.']);
        }

        foreach (array_values($uploadedPhotos) as $index => $file) {
            $path = $file->store('photos', 'public');
            Photo::create([
                'route_id' => $route->id,
                'user_id' => auth()->id(),
                'path' => $path,
                'is_topo' => false,
                'order' => $index,
            ]);
        }

        $message = $route->is_approved
            ? 'Route created and approved successfully!'
            : 'Route submitted for approval.';

        // Disable Livewire navigation to force fresh data load
        return redirect()->route('routes.show', $route)
            ->with('success', $message)
            ->header('X-Livewire-Navigate', 'false');
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

        if (empty($validated['topo_url']) && ! $route->topo_url) {
            $validated['topo_data'] = null;
        }

        // Trigger re-moderation if edited by non-admin/moderator
        if (! auth()->user()->isAdmin() && ! auth()->user()->isModerator()) {
            $validated['is_approved'] = false;
            $validated['approved_by_user_id'] = null;
            $validated['approved_at'] = null;
        }

        $route->update($validated);

        $uploadedPhotos = $request->file('photos', []);
        if (! empty($uploadedPhotos)) {
            $existingCount = $route->photos()->where('is_topo', false)->count();
            $incomingCount = count($uploadedPhotos);
            if (($existingCount + $incomingCount) > 10) {
                return back()
                    ->withInput()
                    ->withErrors(['photos' => "You can upload a maximum of 10 photos per route. This route already has {$existingCount}."]);
            }

            $nextOrder = (int) ($route->photos()->where('is_topo', false)->max('order') ?? -1) + 1;
            foreach ($uploadedPhotos as $file) {
                $path = $file->store('photos', 'public');
                Photo::create([
                    'route_id' => $route->id,
                    'user_id' => auth()->id(),
                    'path' => $path,
                    'is_topo' => false,
                    'order' => $nextOrder++,
                ]);
            }
        }

        $message = ! $route->is_approved
            ? 'Changes saved. Route will need re-approval.'
            : 'Route updated successfully!';

        // Disable Livewire navigation to force fresh data load
        return redirect()->route('routes.show', $route)
            ->with('success', $message)
            ->header('X-Livewire-Navigate', 'false');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Route $route)
    {
        $this->authorize('delete', $route);

        $route->delete();

        // Disable Livewire navigation to force fresh data load
        return redirect()->route('routes.index')
            ->with('success', 'Route deleted successfully.')
            ->header('X-Livewire-Navigate', 'false');
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
