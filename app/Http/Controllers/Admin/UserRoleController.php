<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    /**
     * Display a listing of users with their roles.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('role')
            ->orderBy('name')
            ->paginate(20);

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Update the specified user's role.
     */
    public function updateRole(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        // Prevent self-demotion from Admin role
        if ($user->id === auth()->id() && $user->isAdmin()) {
            $newRoleId = $request->input('role_id');
            $adminRole = Role::where('name', 'Admin')->first();

            if ($newRoleId != $adminRole->id) {
                return back()->with('error', 'You cannot change your own admin role.');
            }
        }

        $user->update([
            'role_id' => $request->input('role_id'),
        ]);

        return back()->with('success', 'User role updated successfully.');
    }
}
