export function initRevealElements(root = document) {
    const observer = 'IntersectionObserver' in window
        ? new IntersectionObserver(
            (entries) => entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            }),
            { threshold: .12 },
        )
        : null;

    root.querySelectorAll('.reveal').forEach((element) => {
        if (observer) observer.observe(element);
        else element.classList.add('is-visible');
    });
}
