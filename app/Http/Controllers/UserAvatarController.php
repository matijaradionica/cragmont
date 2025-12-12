<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserAvatarController extends Controller
{
    public function show(User $user)
    {
        if (!auth()->check()) {
            abort(403);
        }

        if (!$user->avatar_path || !Storage::disk('public')->exists($user->avatar_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($user->avatar_path);
    }
}

