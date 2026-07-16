<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        return view('cms.inquiries.index', ['inquiries' => Inquiry::with('product')->latest()->paginate(20)]);
    }

    public function show(Inquiry $inquiry): View
    {
        return view('cms.inquiries.show', compact('inquiry'));
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:new,contacted,closed']]);
        $data['contacted_at'] = $data['status'] === 'contacted' ? now() : $inquiry->contacted_at;
        $inquiry->update($data);

        return back()->with('success', 'Status inquiry diperbarui.');
    }
}
