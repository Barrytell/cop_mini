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

document.addEventListener('alpine:init', () => {
    Alpine.data('unitQuote', () => ({
        amount: '',
        currency: 'USD',
        price: '0.01',
        priceLabelText: '0.01',
        units: 0,
        chargeLabel: '',
        sending: false,
        quoteUrl: '',
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
                    if (!ok) {
                        this.chargeLabel = body.message || 'Flutterwave could not price this currency.';
                        return;
                    }

                    this.units = body.units;
                    this.chargeLabel = `Flutterwave will charge ${body.charge_amount} ${body.charge_currency}.`;
                })
                .catch(() => {
                    this.chargeLabel = 'Flutterwave did not respond. You can still submit, and the rate will be checked again.';
                });
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();
