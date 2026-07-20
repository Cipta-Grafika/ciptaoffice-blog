<?php

namespace App\Http\Controllers\Cms;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Testimonial;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $query = Post::query();
        if (! auth()->user()->isAdmin()) {
            $query->where('author_id', auth()->id());
        }
        $counts = (clone $query)->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

        return view('cms.dashboard', ['counts' => $counts, 'statuses' => PostStatus::cases(), 'activeTestimonials' => Testimonial::active()->count(), 'newInquiries' => Inquiry::where('status', 'new')->count()]);
    }
}
