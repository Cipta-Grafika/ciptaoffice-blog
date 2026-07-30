<dialog class="cms-confirm-modal" data-cms-confirm-modal aria-labelledby="cmsConfirmTitle"
    aria-describedby="cmsConfirmMessage">
    <div class="cms-confirm-modal__content">
        <div class="cms-confirm-modal__accent" aria-hidden="true"></div>
        <button class="cms-confirm-modal__close" type="button" data-cms-confirm-close
            aria-label="Tutup konfirmasi">
            <x-cms-icon name="x" />
        </button>

        <div class="cms-confirm-modal__body">
            <span class="cms-confirm-modal__icon" aria-hidden="true">
                <x-cms-icon name="warning" />
            </span>
            <div class="cms-confirm-modal__copy">
                <p class="cms-confirm-modal__eyebrow">Konfirmasi tindakan</p>
                <h2 id="cmsConfirmTitle" data-cms-confirm-title>Konfirmasi perubahan</h2>
                <p id="cmsConfirmMessage" data-cms-confirm-message>
                    Pastikan Anda ingin melanjutkan tindakan ini.
                </p>
            </div>
        </div>

        <div class="cms-confirm-modal__actions">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-cms-confirm-close>
                Batal
            </button>
            <button class="btn btn-sm cms-confirm-modal__submit" type="button" data-cms-confirm-submit>
                <span data-cms-confirm-submit-label>Lanjutkan</span>
            </button>
        </div>
    </div>
</dialog>
