<?php

namespace App\Http\Controllers\Cms;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('cms.users.index', ['users' => User::orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return view('cms.users.form', ['user' => new User, 'roles' => UserRole::cases()]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        User::create($data);

        return redirect()->route('cms.users.index')->with('success', 'Pengguna ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('cms.users.form', ['user' => $user, 'roles' => UserRole::cases()]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } $data['is_active'] = $request->boolean('is_active');
        if ($user->is(auth()->user()) && ! $data['is_active']) {
            return back()->withErrors(['is_active' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }
        $user->update($data);

        return redirect()->route('cms.users.index')->with('success', 'Pengguna diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'Anda tidak dapat menghapus akun sendiri.');
        $user->delete();

        return back()->with('success', 'Pengguna dihapus.');
    }
}
