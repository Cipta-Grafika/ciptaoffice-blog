<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_unknown_public_page_uses_standalone_branded_404(): void
    {
        $this->get('/halaman-yang-tidak-tersedia')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan.')
            ->assertSee('data-error-page-back', false)
            ->assertSee('ciptaoffice-brand.svg', false)
            ->assertSee('noindex, nofollow', false)
            ->assertDontSee('site-header', false)
            ->assertDontSee('cms-sidebar', false);
    }

    public function test_unknown_cms_path_also_uses_global_standalone_404(): void
    {
        $this->get('/cms/modul-yang-tidak-tersedia')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan.')
            ->assertDontSee('cms-topbar', false)
            ->assertDontSee('cms-sidebar', false);
    }
}
