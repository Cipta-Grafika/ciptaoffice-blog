<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handle password update requests for authenticated users.
 */
class PasswordController extends Controller
{
    /**
     * Display the change password view.
     *
     * @return \Illuminate\View\View
     */
    public function edit(): View
    {
        return view('auth.change-password');
    }

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'confirmed', 'min:10']]);
        $request->user()->update(['password' => $validated['password']]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
