<?php

namespace App\Http\Controllers;

use App\Models\Ascent;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AscentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the user's ascents (logbook).
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id', auth()->id());

        // Users can only view their own logbook unless they're admin
        if ($userId != auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized to view this logbook.');
        }

        $ascents = Ascent::with(['route.location', 'user'])
            ->where('user_id', $userId)
            ->orderBy('ascent_date', 'desc')
            ->paginate(20);

        $successfulAscents = Ascent::query()
            ->where('ascents.user_id', $userId)
            ->where('ascents.status', 'Success');

        $successfulCount = (int) $successfulAscents->clone()->count();

        $uniqueRouteCount = (int) Ascent::query()
            ->where('ascents.user_id', $userId)
            ->distinct('route_id')
            ->count('route_id');

        $totalVerticalM = (int) $successfulAscents
            ->clone()
            ->join('routes', 'ascents.route_id', '=', 'routes.id')
            ->whereNotNull('routes.length_m')
            ->sum('routes.length_m');

        $totalVerticalFt = (int) round($totalVerticalM * 3.28084);

        $yearStart = now()->startOfYear()->toDateString();
        $yearEnd = now()->endOfYear()->toDateString();

        $activityRows = Ascent::query()
            ->where('user_id', $userId)
            ->whereBetween('ascent_date', [$yearStart, $yearEnd])
            ->selectRaw('MONTH(ascent_date) as m, COUNT(*) as c')
            ->groupBy('m')
            ->pluck('c', 'm')
            ->all();

        $activityByMonth = collect(range(1, 12))
            ->map(function (int $month) use ($activityRows) {
                return [
                    'month' => $month,
                    'count' => (int) ($activityRows[$month] ?? 0),
                ];
            })
            ->all();

        $maxMonthlyCount = (int) collect($activityByMonth)->max('count');

        $gradeRows = Ascent::query()
            ->where('ascents.user_id', $userId)
            ->where('ascents.status', 'Success')
            ->join('routes', 'ascents.route_id', '=', 'routes.id')
            ->select([
                'routes.grade_type',
                'routes.grade_value',
                DB::raw('COUNT(*) as c'),
            ])
            ->groupBy('routes.grade_type', 'routes.grade_value')
            ->orderByDesc('c')
            ->limit(12)
            ->get();

        $ascentsByGrade = $gradeRows
            ->map(function ($row) {
                return [
                    'label' => "{$row->grade_type} {$row->grade_value}",
                    'count' => (int) $row->c,
                ];
            })
            ->all();

        $maxGradeCount = (int) collect($ascentsByGrade)->max('count');

        return view('ascents.index', compact(
            'ascents',
            'successfulCount',
            'uniqueRouteCount',
            'totalVerticalM',
            'totalVerticalFt',
            'activityByMonth',
            'maxMonthlyCount',
            'ascentsByGrade',
            'maxGradeCount',
        ));
    }

    /**
     * Show the form for creating a new ascent.
     */
    public function create(Request $request)
    {
        $routeId = $request->query('route_id');
        $route = $routeId ? Route::findOrFail($routeId) : null;

        $routes = Route::approved()
            ->with('location')
            ->orderBy('name')
            ->get();

        return view('ascents.create', compact('route', 'routes'));
    }

    /**
     * Store a newly created ascent in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'ascent_date' => 'required|date|before_or_equal:today',
            'partners' => 'nullable|string|max:255',
            'status' => 'required|in:Success,Failed,Attempt',
            'notes' => 'nullable|string|max:2000',
        ]);

        $ascent = Ascent::create([
            'user_id' => auth()->id(),
            'route_id' => $validated['route_id'],
            'ascent_date' => $validated['ascent_date'],
            'partners' => $validated['partners'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('ascents.show', $ascent)
            ->with('success', 'Ascent logged successfully!');
    }

    /**
     * Display the specified ascent.
     */
    public function show(Ascent $ascent)
    {
        Gate::authorize('view', $ascent);

        $ascent->load(['route.location', 'user']);

        return view('ascents.show', compact('ascent'));
    }

    /**
     * Show the form for editing the specified ascent.
     */
    public function edit(Ascent $ascent)
    {
        Gate::authorize('update', $ascent);

        $routes = Route::approved()
            ->with('location')
            ->orderBy('name')
            ->get();

        return view('ascents.edit', compact('ascent', 'routes'));
    }

    /**
     * Update the specified ascent in storage.
     */
    public function update(Request $request, Ascent $ascent)
    {
        Gate::authorize('update', $ascent);

        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'ascent_date' => 'required|date|before_or_equal:today',
            'partners' => 'nullable|string|max:255',
            'status' => 'required|in:Success,Failed,Attempt',
            'notes' => 'nullable|string|max:2000',
        ]);

        $ascent->update($validated);

        return redirect()->route('ascents.show', $ascent)
            ->with('success', 'Ascent updated successfully!');
    }

    /**
     * Remove the specified ascent from storage.
     */
    public function destroy(Ascent $ascent)
    {
        Gate::authorize('delete', $ascent);

        $ascent->delete();

        return redirect()->route('ascents.index')
            ->with('success', 'Ascent deleted successfully.');
    }
}
