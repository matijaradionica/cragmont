<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Route;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get statistics
        $stats = [
            'total_routes' => Route::count(),
            'approved_routes' => Route::where('is_approved', true)->count(),
            'pending_routes' => Route::where('is_approved', false)->count(),
            'total_locations' => Location::count(),
            'total_users' => User::count(),
        ];

        // Get pending routes with relationships
        $pendingRoutes = Route::pending()
            ->with(['location', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.dashboard', compact('stats', 'pendingRoutes'));
    }

    /**
     * Bulk approve multiple routes.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'route_ids' => ['required', 'array'],
            'route_ids.*' => ['exists:routes,id'],
        ]);

        $routeIds = $request->input('route_ids');
        $approvedCount = 0;

        foreach ($routeIds as $routeId) {
            $route = Route::find($routeId);

            if ($route && !$route->is_approved) {
                $route->approve(auth()->user());
                $approvedCount++;
            }
        }

        return back()->with('success', "Successfully approved {$approvedCount} route(s).");
    }
}
