<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role', 'customer');
        $search = $request->query('q');

        $users = User::query()
            ->whereIn('role', [UserRole::Customer->value, UserRole::Partner->value])
            ->when(in_array($role, [UserRole::Customer->value, UserRole::Partner->value], true), fn ($query) => $query->where('role', $role))
            ->when($search, function ($query, $keyword) {
                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%"));
            })
            ->with('store')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'role' => $role,
            'search' => $search,
            'roles' => [UserRole::Customer, UserRole::Partner],
        ]);
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);
        abort_if($user->id === $request->user()->id, 403);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active
            ? "Akun {$user->name} diaktifkan."
            : "Akun {$user->name} dinonaktifkan.");
    }
}
