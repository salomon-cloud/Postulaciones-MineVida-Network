<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DiscordSystemLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount(['applications', 'reviewedApplications'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->toString().'%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('discord_username', 'like', $search)
                        ->orWhere('discord_global_name', 'like', $search)
                        ->orWhere('discord_id', 'like', $search);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roleCounts = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $stats = [
            'total' => User::query()->count(),
            'owners' => (int) ($roleCounts[UserRole::Owner->value] ?? 0),
            'admins' => (int) ($roleCounts[UserRole::Admin->value] ?? 0),
            'reviewers' => (int) ($roleCounts[UserRole::Reviewer->value] ?? 0),
            'users' => (int) ($roleCounts[UserRole::User->value] ?? 0),
            'recent' => User::query()->where('last_login_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'q' => $request->string('q')->toString(),
            'stats' => $stats,
        ]);
    }

    public function updateRole(Request $request, User $user, DiscordSystemLogService $systemLogs): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(array_map(fn (UserRole $role) => $role->value, UserRole::cases()))],
        ]);

        if ($request->user()->is($user) && $validated['role'] !== UserRole::Owner->value) {
            return back()->with('error', 'No puedes quitarte el rol Owner a ti mismo.');
        }

        $oldRole = $user->role;
        $user->update(['role' => UserRole::from($validated['role'])]);

        $systemLogs->queue(
            'users',
            'Rol de usuario actualizado',
            'Se cambio el rol de un usuario dentro del sistema.',
            [
                'Usuario' => $user->name.' ('.$user->discord_id.')',
                'Cambio' => $oldRole->label().' -> '.$user->role->label(),
            ],
            'warning',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Rol actualizado.');
    }
}
