<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $request->user()->can('users') || abort(403);

        $users = User::query()
            ->when(! $request->user()->isAdmin(), fn($query) => $query->whereKey($request->user()->id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master-data.users.index', [
            'users' => $users,
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $request->user()->can('users') || abort(403);

        abort_unless($request->user()->isAdmin() || $request->user()->is($user), 403);

        return view('master-data.users.show', [
            'managedUser' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:user,admin'],
            'status' => ['required', 'in:pending,active,inactive'],
        ]);

        $user->update($validated);

        return redirect()
            ->route('master-data.users.show', $user)
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $user->update(['status' => 'active']);

        return redirect()
            ->route('master-data.users.show', $user)
            ->with('success', 'User berhasil di-approve.');
    }
}
