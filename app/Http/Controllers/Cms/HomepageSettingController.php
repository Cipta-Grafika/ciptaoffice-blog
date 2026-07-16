<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomepageSettingRequest;
use App\Models\HomepageSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomepageSettingController extends Controller
{
    public function edit(): View
    {
        return view('cms.homepage.edit', ['settings' => HomepageSetting::current()]);
    }

    public function update(HomepageSettingRequest $request): RedirectResponse
    {
        $settings = HomepageSetting::current();
        $data = $request->safe()->except('hero_image');
        if ($request->hasFile('hero_image')) {
            if ($settings->hero_image_path) {
                Storage::disk('public')->delete($settings->hero_image_path);
            } $data['hero_image_path'] = $request->file('hero_image')->store('homepage', 'public');
        }
        $settings->update($data);

        return back()->with('success', 'Homepage berhasil diperbarui.');
    }
}
