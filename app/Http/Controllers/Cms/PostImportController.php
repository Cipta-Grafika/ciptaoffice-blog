<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportPostsRequest;
use App\Services\PostImportService;
use Illuminate\Http\RedirectResponse;

class PostImportController extends Controller
{
    public function __invoke(ImportPostsRequest $request, PostImportService $importer): RedirectResponse
    {
        $total = $importer->import(
            $request->file('import_file'),
            $request->user(),
        );

        return redirect()
            ->route('cms.posts.index')
            ->with('success', "{$total} artikel berhasil diimpor sebagai draft.");
    }
}
