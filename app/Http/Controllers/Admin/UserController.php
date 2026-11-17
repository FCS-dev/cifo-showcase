<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\PasswordResetLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('users.view');

        $request->validate([
            'sort' => 'in:id,name,email,created_at',
            'direction' => 'in:asc,desc',
            'per_page' => 'in:5,10,25',
        ]);

        $search = $request->query('search');
        $roleName = $request->query('role');
        $sort = $request->query('sort', 'id');
        $direction = $request->query('direction', 'asc');
        $perPage = (int) $request->query('per_page', 10);

        $users = User::query()
            ->with('roles')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleName, function ($q) use ($roleName) {
                $q->whereHas('roles', fn ($qr) => $qr->where('name', $roleName));
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create(): View
    {
        Gate::authorize('users.create');

        $roles = Role::where('name', '!=', 'Admin')->orderBy('name')->get();
        $canGenerateLinks = auth()->user()->isAdmin();

        return view('admin.users.create', compact('roles', 'canGenerateLinks'));
    }

    public function store(StoreUserRequest $request, PasswordResetLinkService $links): RedirectResponse|Response|JsonResponse
    {
        Gate::authorize('users.create');

        $mode = $request->input('mode', 'password');

        if ($mode === 'activation') {
            abort_unless($request->user()->isAdmin(), 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $mode === 'activation' ? Str::password(40) : Hash::make($request->password),
            'activated_at' => $mode === 'activation' ? null : now(),
        ]);

        if ($request->has('roles')) {
            Gate::authorize('users.assign-roles');

            $roles = collect($request->input('roles', []))
                ->reject(fn (string $role) => $role === 'Admin')
                ->values()
                ->all();

            if ($roles !== []) {
                $user->assignRole($roles);
            }
        }

        if ($mode === 'activation') {
            $link = $links->createFor($user);

            session()->flash('activation_url', $link['url']);
            session()->flash('activation_expires_at', $link['expires_at']);
        }

        session()->flash('success', 'Usuario creado correctamente.');

        $redirect = $mode === 'activation'
            ? route('users.show', $user)
            : route('users.index');

        if ($request->wantsJson()) {
            return response()->json(['redirect' => $redirect]);
        }

        return redirect($redirect);
    }

    /**
     * Muestra el detalle de un usuario específico.
     */
    public function show(User $user): View
    {
        Gate::authorize('users.view');

        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     */
    public function edit(User $user): View
    {
        Gate::authorize('users.update');

        $roles = $user->isAdmin()
            ? Role::orderBy('name')->get()
            : Role::where('name', '!=', 'Admin')->orderBy('name')->get();

        $userRoleNames = $user->roles->pluck('name')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoleNames'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse|Response
    {
        Gate::authorize('users.update');

        $user->update($request->only('name'));

        if ($request->filled('password')) {
            $attributes = ['password' => Hash::make($request->password)];

            if ($user->activated_at === null) {
                $attributes['activated_at'] = now();
            }

            $user->update($attributes);
        }

        if ($request->has('roles')) {
            Gate::authorize('users.assign-roles');

            $roles = collect($request->input('roles', []))
                ->reject(fn (string $role) => $role === 'Admin')
                ->values();

            if ($user->isAdmin()) {
                $roles->push('Admin');
            }

            $user->syncRoles($roles->unique()->values()->all());
        }

        session()->flash('success', 'Usuario actualizado correctamente.');

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->route('users.index');
    }

    public function accessLink(Request $request, User $user, PasswordResetLinkService $links): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $link = $links->createFor($user);

        session()->flash('activation_url', $link['url']);
        session()->flash('activation_expires_at', $link['expires_at']);

        return redirect()->route('users.show', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('users.delete');

        // El rol Admin no puede eliminarse ni transferirse
        if ($user->isAdmin()) {
            return back()->with('error', 'No se puede eliminar a un usuario administrador.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
