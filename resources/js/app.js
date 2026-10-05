import './bootstrap';
import Alpine from 'alpinejs';

function scaled(value, scale) {
    const trimmed = String(value ?? '').trim();

    if (!/^\d+(\.\d+)?$/.test(trimmed)) {
        return null;
    }

    const [whole, fraction = ''] = trimmed.split('.');
    const digits = (fraction + '0'.repeat(scale)).slice(0, scale);

    return BigInt(whole + digits);
}

function unitsFrom(amount, price) {
    const left = scaled(amount, 6);
    const right = scaled(price, 6);

    if (left === null || right === null || right <= 0n) {
        return 0;
    }

    const units = left / right;

    return units > BigInt(Number.MAX_SAFE_INTEGER) ? 0 : Number(units);
}

document.documentElement.classList.add('js');

document.addEventListener('alpine:init', () => {
    Alpine.data('publicShell', () => ({
        navOpen: false,
        showTop: false,
        init() {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce || !('IntersectionObserver' in window)) {
                return;
            }

            const nodes = this.$root.querySelectorAll('[data-reveal]');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.16 });

            nodes.forEach((node) => observer.observe(node));
        },
        onScroll() {
            this.showTop = window.scrollY > 480;
        },
        toTop() {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
        },
    }));

    Alpine.data('statCount', (target) => ({
        value: Number(target) || 0,
        init() {
            const goal = Number(target) || 0;
            this.value = goal;
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const start = () => {
                if (reduce || goal === 0) {
                    this.value = goal;
                    return;
                }

                this.value = 0;

                const step = Math.max(1, Math.round(goal / 36));
                const tick = () => {
                    this.value = Math.min(goal, this.value + step);
                    if (this.value < goal) {
                        requestAnimationFrame(tick);
                    }
                };
                tick();
            };

            if (!('IntersectionObserver' in window)) {
                start();
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    start();
                    observer.disconnect();
                }
            });
            observer.observe(this.$el);
        },
        get label() {
            return new Intl.NumberFormat('en-US').format(this.value);
        },
    }));

    Alpine.data('testimonialSlider', (count) => ({
        index: 0,
        count,
        paused: false,
        timer: null,
        touchX: 0,
        init() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || this.count < 2) {
                return;
            }

            this.timer = setInterval(() => {
                if (!this.paused) {
                    this.next();
                }
            }, 7000);
        },
        destroy() {
            clearInterval(this.timer);
        },
        next() {
            this.index = (this.index + 1) % this.count;
        },
        prev() {
            this.index = (this.index - 1 + this.count) % this.count;
        },
        onTouchStart(event) {
            this.paused = true;
            this.touchX = event.changedTouches[0].clientX;
        },
        onTouchEnd(event) {
            const delta = event.changedTouches[0].clientX - this.touchX;
            if (delta > 48) {
                this.prev();
            } else if (delta < -48) {
                this.next();
            }
            this.paused = false;
        },
    }));

    Alpine.data('cookieNotice', () => ({
        open: false,
        init() {
            try {
                this.open = localStorage.getItem('minimini.cookie') !== '1';
            } catch (error) {
                this.open = true;
            }
        },
        accept() {
            try {
                localStorage.setItem('minimini.cookie', '1');
            } catch (error) {
                // The notice can close even if storage is blocked.
            }
            this.open = false;
        },
    }));

    Alpine.data('faqList', () => ({
        open: null,
        toggle(id) {
            this.open = this.open === id ? null : id;
        },
    }));

    Alpine.data('bannerSlider', (count) => ({
        index: 0,
        count,
        paused: false,
        running: false,
        reduced: false,
        timer: null,
        touchX: 0,
        init() {
            this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (this.reduced || this.count < 2) {
                return;
            }

            this.running = true;
            this.arm();
        },
        arm() {
            if (this.timer !== null) {
                return;
            }

            this.timer = setInterval(() => {
                if (!this.paused && this.running) {
                    this.next();
                }
            }, 5000);
        },
        destroy() {
            clearInterval(this.timer);
        },
        next() {
            this.index = (this.index + 1) % this.count;
        },
        prev() {
            this.index = (this.index - 1 + this.count) % this.count;
        },
        go(nextIndex) {
            this.index = nextIndex;
        },
        pause() {
            this.paused = true;
        },
        resume() {
            this.paused = false;
        },
        toggle() {
            if (this.reduced) {
                return;
            }

            this.running = !this.running;

            if (this.running) {
                this.arm();
            }
        },
        onTouchStart(event) {
            this.paused = true;
            this.touchX = event.changedTouches[0].clientX;
        },
        onTouchEnd(event) {
            const delta = event.changedTouches[0].clientX - this.touchX;

            if (delta > 48) {
                this.prev();
            } else if (delta < -48) {
                this.next();
            }

            this.paused = false;
        },
    }));

    Alpine.data('copyText', (value) => ({
        copied: false,
        async copy() {
            try {
                await navigator.clipboard.writeText(value);
            } catch (error) {
                const field = document.createElement('textarea');
                field.value = value;
                document.body.appendChild(field);
                field.select();
                document.execCommand('copy');
                field.remove();
            }

            this.copied = true;
            setTimeout(() => {
                this.copied = false;
            }, 2000);
        },
    }));

    Alpine.data('unitQuote', () => ({
        amount: '',
        currency: 'USD',
        price: '0.01',
        priceLabelText: '0.01',
        units: 0,
        chargeLabel: '',
        sending: false,
        quoteUrl: '',
        quoteRequest: 0,
        init() {
            this.price = this.$el.dataset.unitPrice || '0.01';
            this.priceLabelText = this.$el.dataset.priceLabel || this.price;
            this.amount = this.$el.dataset.minimum || '';
            this.quoteUrl = this.$el.dataset.quoteUrl || '';
            this.currency = this.$el.dataset.currency || 'USD';
            this.calculate();
        },
        get unitsLabel() {
            return new Intl.NumberFormat('en-US').format(this.units);
        },
        get priceLabel() {
            return this.priceLabelText;
        },
        calculate() {
            const requestId = ++this.quoteRequest;
            this.units = unitsFrom(this.amount, this.price);
            this.chargeLabel = '';

            if (this.currency === 'USD' || this.quoteUrl === '') {
                return;
            }

            const params = new URLSearchParams({
                amount_usd: this.amount,
                currency: this.currency,
            });

            fetch(`${this.quoteUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            })
                .then((response) => response.json().then((body) => ({ ok: response.ok, body })))
                .then(({ ok, body }) => {
                    if (requestId !== this.quoteRequest) {
                        return;
                    }

                    if (!ok) {
                        this.chargeLabel = body.message || 'Flutterwave could not price this currency.';
                        return;
                    }

                    this.units = body.units;
                    this.chargeLabel = `Flutterwave will charge ${body.charge_amount} ${body.charge_currency}.`;
                })
                .catch(() => {
                    if (requestId !== this.quoteRequest) {
                        return;
                    }

                    this.chargeLabel = 'Flutterwave did not respond. You can still submit, and the rate will be checked again.';
                });
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();
