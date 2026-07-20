<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_inquiry_is_stored(): void
    {
        $this->post(route('contact.store'), ['name' => 'Budi', 'phone' => '08123456789', 'email' => 'budi@example.com', 'message' => 'Butuh sepuluh kursi kantor.', 'website' => ''])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('inquiries', ['name' => 'Budi', 'status' => 'new']);
    }

    public function test_honeypot_rejects_spam(): void
    {
        $this->post(route('contact.store'), ['name' => 'Bot', 'phone' => '123', 'message' => 'Spam', 'website' => 'https://spam.test'])->assertSessionHasErrors('website');
        $this->assertSame(0, Inquiry::count());
    }
}
