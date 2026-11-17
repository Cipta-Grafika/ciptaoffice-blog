<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Inquiry;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Menangani halaman formulir kontak publik dan penyimpanan inquiry baru.
 */
class ContactController extends Controller
{
    /**
     * Menampilkan halaman kontak dengan daftar pilihan produk aktif dari cache array ter-hydrate.
     *
     * @return View Halaman formulir kontak.
     */
    public function create(): View
    {
        $rows = Cache::remember('contact.products', now()->addHour(), fn (): array =>
            Product::active()->orderBy('name')->get(['id', 'name'])->toArray()
        );
        return view('pages.contact', [
            'products' => Product::hydrate($rows),
        ]);
    }

    /**
     * Memvalidasi dan menyimpan inquiry dari formulir kontak serta mengirim notifikasi email.
     *
     * @param  ContactRequest  $request  Request kontak yang sudah tervalidasi.
     * @return RedirectResponse Redirect kembali ke formulir dengan pesan sukses.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('website');
        $data['source_ip'] = $request->ip();
        $inquiry = Inquiry::create($data);
        if ($to = config('ciptaoffice.notification_email')) {
            try {
                Mail::raw("Inquiry baru dari {$inquiry->name}\nTelepon: {$inquiry->phone}\n\n{$inquiry->message}", fn ($mail) => $mail->to($to)->subject('Inquiry baru CiptaOffice'));
            } catch (\Throwable $exception) {
                Log::warning('Inquiry tersimpan tetapi notifikasi email gagal.', ['inquiry_id' => $inquiry->id, 'error' => $exception->getMessage()]);
            }
        }

        return back()->with('success', 'Terima kasih. Tim CiptaOffice akan menghubungi Anda segera.');
    }
}
