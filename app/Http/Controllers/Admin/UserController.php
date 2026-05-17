<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('manager')->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $managers = User::where('role', UserRole::Manager)->orderBy('name')->get();
        $departments = User::whereNotNull('department')->distinct()->pluck('department');

        return view('admin.users.index', compact('users', 'managers', 'departments'));
    }

    public function create(): View
    {
        $managers = User::where('role', UserRole::Manager)->orderBy('name')->get();

        return view('admin.users.create', compact('managers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        if ($validated['role'] !== UserRole::Employee->value) {
            $validated['manager_id'] = null;
        }

        User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $managers = User::where('role', UserRole::Manager)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'managers'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($validated['role'] !== UserRole::Employee->value) {
            $validated['manager_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    protected function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'department' => ['nullable', 'string', 'max:100'],
            'manager_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', UserRole::Manager->value),
                Rule::requiredIf($request->role === UserRole::Employee->value),
            ],
        ]);
    }
}
