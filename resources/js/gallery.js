function initRouteGalleries() {
    const galleries = document.querySelectorAll('[data-route-gallery]');
    galleries.forEach((gallery) => {
        if (gallery.dataset.initialized === 'true') return;
        gallery.dataset.initialized = 'true';

        const items = Array.from(gallery.querySelectorAll('[data-route-gallery-item]'));
        const lightbox = gallery.querySelector('[data-route-gallery-lightbox]');
        const lightboxImg = gallery.querySelector('[data-route-gallery-lightbox-img]');
        const closeButtons = gallery.querySelectorAll('[data-route-gallery-close]');
        const backdrop = gallery.querySelector('[data-route-gallery-backdrop]');
        const prevButton = gallery.querySelector('[data-route-gallery-prev]');
        const nextButton = gallery.querySelector('[data-route-gallery-next]');

        if (!lightbox || !lightboxImg) return;

        let currentIndex = 0;

        const open = (index) => {
            currentIndex = index;
            lightboxImg.src = items[currentIndex].dataset.fullsrc;
            lightbox.classList.remove('hidden');
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
        };

        const close = () => {
            lightbox.classList.add('hidden');
            lightboxImg.src = '';
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        };

        const show = (delta) => {
            if (!items.length) return;
            currentIndex = (currentIndex + delta + items.length) % items.length;
            lightboxImg.src = items[currentIndex].dataset.fullsrc;
        };

        items.forEach((item, index) => {
            item.addEventListener('click', () => open(index));
        });

        closeButtons.forEach((btn) => btn.addEventListener('click', () => close()));
        backdrop?.addEventListener('click', () => close());
        prevButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            show(-1);
        });
        nextButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            show(1);
        });

        document.addEventListener('keydown', (e) => {
            if (lightbox.classList.contains('hidden')) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') show(-1);
            if (e.key === 'ArrowRight') show(1);
        });
    });
}

document.addEventListener('DOMContentLoaded', initRouteGalleries);
document.addEventListener('livewire:navigated', initRouteGalleries);

