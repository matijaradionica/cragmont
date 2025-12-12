<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Support\Facades\Storage;

class RouteTopoController extends Controller
{
    public function show(Route $route)
    {
        $this->authorize('view', $route);

        if (!$route->topo_url || !Storage::disk('public')->exists($route->topo_url)) {
            abort(404);
        }

        return Storage::disk('public')->response($route->topo_url);
    }
}

