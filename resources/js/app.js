import './bootstrap';

import Alpine from 'alpinejs';

Alpine.directive('confirm', (el, { expression }, { evaluate }) => {
    el.addEventListener('submit', (event) => {
        if (el.dataset.confirmBypass === '1') {
            delete el.dataset.confirmBypass;

            return;
        }

        event.preventDefault();

        const options = expression ? evaluate(expression) : {};

        window.dispatchEvent(new CustomEvent('confirm-action', {
            detail: { form: el, ...options },
        }));
    });
});

document.addEventListener('alpine:init', () => {
    Alpine.data('notificationBell', (initialCount = 0) => ({
        open: false,
        count: initialCount,
        items: [],
        loading: false,

        init() {
            this.load();
            window.setInterval(() => this.load(), 30000);
        },

        async load() {
            if (this.loading || !this.$el.dataset.url) {
                return;
            }

            this.loading = true;

            try {
                const { data } = await window.axios.get(this.$el.dataset.url);
                this.count = data.count;
                this.items = data.notifications;
            } catch (error) {
                // Diamkan kegagalan polling; coba lagi pada interval berikutnya.
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('productGallery', (images = []) => ({
        active: 0,
        images,

        select(index) {
            this.active = index;
        },

        get current() {
            return this.images[this.active] ?? this.images[0] ?? '';
        },
    }));
});

window.Alpine = Alpine;

Alpine.start();
