@php
    $footerPhone = config('ciptaoffice.contact_phone');
    $footerEmail = config('ciptaoffice.contact_email');
    $footerPhoneHref = preg_replace('/[^\d+]/', '', $footerPhone);
    $footerWhatsappHref = preg_replace('/\D/', '', config('ciptaoffice.whatsapp_number'));
    $footerAddress = config('ciptaoffice.contact_address');
@endphp

<footer id="siteFooter" class="site-footer site-footer--editorial" aria-labelledby="siteFooterTitle">
    <div class="container">
        <h2 class="visually-hidden" id="siteFooterTitle">Informasi CiptaOffice</h2>

        <div class="site-footer-grid">
            <div class="site-footer-brand">
                <a href="{{ route('home') }}" aria-label="CiptaOffice — Beranda">
                    <img src="{{ asset('images/logos/ciptaoffice-logo.svg') }}" width="800" height="800"
                        alt="CiptaOffice">
                </a>
                <p>Partner kebutuhan kantor dengan pilihan yang tepat guna dan mutu setara.</p>
            </div>

            <div class="site-footer-block site-footer-contact">
                <p class="site-footer-label">Kontak</p>
                <a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>
                <a href="tel:{{ $footerPhoneHref }}">{{ $footerPhone }}</a>
            </div>

            <address class="site-footer-block site-footer-address">
                <p class="site-footer-label">Alamat</p>
                @foreach ($footerAddress as $addressLine)
                    <span>{{ $addressLine }}</span>
                @endforeach
            </address>

            <div class="site-footer-connect">
                <p class="site-footer-label">Terhubung</p>
                <div class="site-footer-socials">
                    <a href="https://wa.me/{{ $footerWhatsappHref }}" target="_blank" rel="noopener noreferrer"
                        aria-label="Hubungi CiptaOffice melalui WhatsApp">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i>
                    </a>
                    <a href="mailto:{{ $footerEmail }}" aria-label="Kirim email kepada CiptaOffice">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="site-footer-meta">
            <p>&copy; {{ date('Y') }} CiptaOffice. Seluruh hak dilindungi.</p>
            <span>Solusi kebutuhan kantor</span>
        </div>
    </div>
</footer>
