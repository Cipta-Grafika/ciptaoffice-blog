<?php

namespace App\Http\Controllers\Cms;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = request()->user();

        return view('cms.profile.edit', [
            'user' => $user,
            'articleCount' => $user->posts()->count(),
            'publishedArticleCount' => $user->posts()->where('status', PostStatus::Published)->count(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
