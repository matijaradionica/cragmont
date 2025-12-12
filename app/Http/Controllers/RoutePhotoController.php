<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Route;
use Illuminate\Support\Facades\Storage;

class RoutePhotoController extends Controller
{
    public function show(Route $route, Photo $photo)
    {
        $this->authorize('view', $route);

        if ($photo->route_id !== $route->id) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($photo->path)) {
            abort(404);
        }

        return Storage::disk('public')->response($photo->path);
    }
}

