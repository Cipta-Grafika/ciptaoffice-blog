<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Inquiry::with('product')->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->ajax()) {
            return view('cms.inquiries.partials.table', ['inquiries' => $query->paginate(20)->withQueryString()]);
        }

        return view('cms.inquiries.index', ['inquiries' => $query->paginate(20)->withQueryString()]);
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
