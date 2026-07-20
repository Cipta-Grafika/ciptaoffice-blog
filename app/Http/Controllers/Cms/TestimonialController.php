<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('cms.testimonials.index', ['testimonials' => Testimonial::orderBy('sort_order')->paginate(15)]);
    }

    public function create(): View
    {
        return view('cms.testimonials.form', ['testimonial' => new Testimonial]);
    }

    public function store(TestimonialRequest $request): RedirectResponse
    {
        $data = $this->data($request);
        Testimonial::create($data);

        return redirect()->route('cms.testimonials.index')->with('success', 'Testimonial ditambahkan.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('cms.testimonials.form', compact('testimonial'));
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->data($request, $testimonial));

        return redirect()->route('cms.testimonials.index')->with('success', 'Testimonial diperbarui.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        if ($testimonial->avatar_path) {
            Storage::disk('public')->delete($testimonial->avatar_path);
        } $testimonial->delete();

        return back()->with('success', 'Testimonial dihapus.');
    }

    private function data(TestimonialRequest $request, ?Testimonial $testimonial = null): array
    {
        $data = $request->safe()->except('avatar');
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('avatar')) {
            if ($testimonial?->avatar_path) {
                Storage::disk('public')->delete($testimonial->avatar_path);
            } $data['avatar_path'] = $request->file('avatar')->store('testimonials', 'public');
        }

        return $data;
    }
}
