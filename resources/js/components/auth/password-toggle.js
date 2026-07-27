export function initPasswordToggles(root = document) {
    root.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = root.querySelector(button.dataset.passwordToggle);
        if (!input) return;

        button.addEventListener('click', () => {
            const shouldShow = input.type === 'password';
            const icon = button.querySelector('i');

            input.type = shouldShow ? 'text' : 'password';
            button.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            button.setAttribute('aria-pressed', String(shouldShow));
            icon?.classList.toggle('bi-eye', !shouldShow);
            icon?.classList.toggle('bi-eye-slash', shouldShow);
            input.focus({ preventScroll: true });
        });
    });
}
