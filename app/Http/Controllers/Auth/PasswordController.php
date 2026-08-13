<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handle password update requests for authenticated users.
 */
class PasswordController extends Controller
{
    /**
     * Display the change password view.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit(): RedirectResponse
    {
        return redirect()->route('cms.profile.edit');
    }

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'min:10', 'max:255'],
        ]);
        $request->user()->update(['password' => $validated['password']]);

        return redirect()->route('cms.profile.edit')->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
