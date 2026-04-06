<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagedUserRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $request->user()->can('users') || abort(403);

        return $this->renderPage($request);
    }

    public function show(Request $request, User $user): View
    {
        $request->user()->can('users') || abort(403);

        abort_unless($request->user()->isAdmin() || $request->user()->is($user), 403);

        $mode = $request->query('mode', 'view');
        $mode = in_array($mode, ['view', 'edit'], true) ? $mode : 'view';

        return $this->renderPage($request, $user, $mode);
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        $managedUser = User::query()->create($request->validated());

        return redirect()
            ->route('master-data.users.show', ['user' => $managedUser, 'mode' => 'view'])
            ->with('success', 'Data user berhasil ditambahkan.');
    }

    public function update(UpdateManagedUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validated();

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('master-data.users.show', ['user' => $user, 'mode' => 'view'])
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($request->user()->is($user), 422, 'Akun yang sedang dipakai tidak dapat dihapus.');

        $user->delete();

        return redirect()
            ->route('master-data.users.index')
            ->with('success', 'Data user berhasil dihapus.');
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $user->update(['status' => 'active']);

        return redirect()
            ->route('master-data.users.show', ['user' => $user, 'mode' => 'view'])
            ->with('success', 'User berhasil di-approve.');
    }

    private function renderPage(Request $request, ?User $managedUser = null, string $mode = 'create'): View
    {
        $users = User::query()
            ->when(! $request->user()->isAdmin(), fn($query) => $query->whereKey($request->user()->id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master-data.users.index', [
            'users' => $users,
            'managedUser' => $managedUser,
            'mode' => $managedUser ? $mode : 'create',
            'storeUrl' => route('master-data.users.store'),
            'updateUrl' => $managedUser ? route('master-data.users.update', $managedUser) : null,
            'deleteUrl' => $managedUser ? route('master-data.users.destroy', $managedUser) : null,
            'canManageUsers' => $request->user()->isAdmin(),
            'canDeleteManagedUser' => $managedUser ? ! $request->user()->is($managedUser) : false,
        ]);
    }
}
