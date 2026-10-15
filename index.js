// ================= TOAST FUNCTION ================= //
function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}


// ================= PRODUCT-INFO PAGE ================= //
// -- Quantity -- //
function pipChangeQty(delta) {
    const el  = document.getElementById('pip-qty-display');
    const val = parseInt(el.textContent) + delta;
    if (val >= 1) el.textContent = val;
}

// -- Tabs -- //
if (document.querySelector('.pip-tab')) {
    document.querySelectorAll('.pip-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.pip-tab').forEach(t => t.classList.remove('pip-tab--active'));
            document.querySelectorAll('.pip-tab-panel').forEach(p => p.classList.remove('pip-tab-panel--active'));
            tab.classList.add('pip-tab--active');
            document.getElementById(tab.dataset.tab).classList.add('pip-tab-panel--active');
        });
    });
}

// -- Wishlist Heart Button (product-info page) -- //
if (document.getElementById('pip-product-id')) {
    const productId   = document.getElementById('pip-product-id').value;
    const currentPage = document.getElementById('pip-current-page').value;

    document.querySelectorAll('.js-pip-wishlist').forEach(btn => {
        btn.addEventListener('click', function () {
            fetch('wishlist-handler.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    `product_id=${productId}&current_page=${encodeURIComponent(currentPage)}`
            })
            .then(async res => JSON.parse(await res.text()))
            .then(data => {
                document.querySelectorAll('.js-pip-wishlist i').forEach(icon => {
                    if (data.status === 'added') {
                        icon.classList.replace('fa-regular', 'fa-solid');
                        icon.classList.add('pip-liked');
                    } else if (data.status === 'removed') {
                        icon.classList.replace('fa-solid', 'fa-regular');
                        icon.classList.remove('pip-liked');
                    }
                });
                showToast(data.status === 'added' ? 'Added to wishlist ❤️' : 'Removed from wishlist');
            })
            .catch(err => console.log('Wishlist error:', err));
        });
    });

    // -- Add to Cart (product-info page) -- //
    const pipCartBtn = document.querySelector('.pip-cart-btn');
    if (pipCartBtn) {
        pipCartBtn.addEventListener('click', function () {
            const qty   = parseInt(document.getElementById('pip-qty-display').textContent);
            const btn   = this;
            const label = btn.querySelector('span');

            btn.disabled      = true;
            label.textContent = 'Adding...';

            const requests = [];
            for (let i = 0; i < qty; i++) {
                requests.push(
                    fetch('cart/cart-handler.php', {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body:    `product_id=${productId}`
                    }).then(res => res.json())
                );
            }

            Promise.all(requests)
                .then(results => {
                    const last = results[results.length - 1];
                    if (last.success) {
                        showToast('Added to cart 🛒');
                        updateCartIcon(last.cart_count);
                    } else {
                        showToast('Could not add to cart');
                    }
                })
                .catch(err => {
                    console.error('Cart error:', err);
                    showToast('Something went wrong, please try again.');
                })
                .finally(() => {
                    btn.disabled      = false;
                    label.textContent = 'Add to Cart';
                });
        });
    }
}

// ================= CART ICON COUNT ================= //
function updateCartIcon(newCount) {
    const badge = document.querySelector('.cart-count');
    if (!badge) return;
    badge.textContent = newCount;
    badge.style.display = newCount > 0 ? '' : 'none';
}

// ================= WISHLIST BUTTON (product grid pages) ================= //
document.querySelectorAll('.js-wishlist-btn').forEach(button => {
    button.addEventListener('click', function () {
        const card        = this.closest('.product-shop-product-card');
        const productId   = card.querySelector('.product-id').value;
        const currentPage = card.querySelector('.current-page').value;

        fetch('wishlist-handler.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    `product_id=${productId}&current_page=${encodeURIComponent(currentPage)}`
        })
        .then(async res => JSON.parse(await res.text()))
        .then(data => {
            if (data.status === 'added') {
                this.innerHTML = '<i class="fa-solid fa-heart"></i>';
                showToast('Added to wishlist ❤️');
            } else if (data.status === 'removed') {
                this.innerHTML = '<i class="fa-regular fa-heart"></i>';
                showToast('Removed from wishlist');
            }
        })
        .catch(err => {
            console.error('Wishlist error:', err);
            showToast('Something went wrong, please try again.');
        });
    });
});

// ================= WISHLIST TO CART ================= //
document.querySelectorAll('.js-wishlist-add-to-cart').forEach(btn => {
    btn.addEventListener('click', async function () {
        const productId = this.dataset.id;
        const formData  = new FormData();
        formData.append('product_id', productId);

        try {
            const res  = await fetch('cart/cart-handler.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                updateCartIcon(data.cart_count);
                this.textContent = 'ADDED!';
                this.disabled    = true;
                setTimeout(() => {
                    this.textContent = 'ADD TO BAG';
                    this.disabled    = false;
                }, 1500);
            }
        } catch (err) {
            console.error('Cart error:', err);
        }
    });
});

// ================= ADD TO CART (product grid + home page) ================= //
// Handles both .product-shop-cart-form (category pages) and .home-cart-form (index page)
document.addEventListener('submit', function (e) {
    // e.target IS the form on a submit event
    const form = e.target;
    if (!form.matches('.product-shop-cart-form, .home-cart-form')) return;

    e.preventDefault();

    const productId   = form.querySelector('input[name="product_id"]').value;
    const currentPage = form.querySelector('input[name="current_page"]')?.value || '';

    // .product-shop-cart-button on category pages, .home-product-card__cart on index
    const submitBtn = form.querySelector('.product-shop-cart-button, .home-product-card__cart');
    if (submitBtn) submitBtn.disabled = true;

    fetch('cart/cart-handler.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    `product_id=${encodeURIComponent(productId)}&current_page=${encodeURIComponent(currentPage)}`
    })
    .then(async res => JSON.parse(await res.text()))
    .then(data => {
        if (data.success) {
            showToast('Added to cart 🛒');
            updateCartIcon(data.cart_count);
        } else {
            showToast('Could not add to cart. Try again.');
        }
    })
    .catch(err => {
        console.error('Cart error:', err);
        showToast('Something went wrong, please try again.');
    })
    .finally(() => {
        if (submitBtn) submitBtn.disabled = false;
    });
});

// ================= CART PAGE ================= //
(function () {
    const SHIPPING = 100;

    function fmt(n) {
        return 'R' + parseFloat(n).toFixed(2);
    }

    function flash(el) {
        el.classList.remove('flash');
        void el.offsetWidth;
        el.classList.add('flash');
    }

    function recalcSummary() {
        let subtotal   = 0;
        let totalItems = 0;

        document.querySelectorAll('.cart-item').forEach(function (row) {
            const price  = parseFloat(row.dataset.price) || 0;
            const qty    = parseInt(row.querySelector('.qty-value').textContent, 10) || 0;
            subtotal    += price * qty;
            totalItems  += qty;
        });

        const shipping = subtotal > 0 ? SHIPPING : 0;
        const total    = subtotal + shipping;

        document.getElementById('summary-item-count').textContent = totalItems;

        const subtotalEl = document.getElementById('summary-subtotal');
        const totalEl    = document.getElementById('summary-total');

        subtotalEl.textContent = fmt(subtotal);
        document.getElementById('summary-shipping').textContent = fmt(shipping);
        totalEl.textContent = fmt(total);

        flash(subtotalEl);
        flash(totalEl);

        updateCartIcon(totalItems);
    }

    async function updateCart(productId, action) {
        try {
            const body = new URLSearchParams({ product_id: productId, action: action });
            const res  = await fetch('cart/update-cart.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    body.toString()
            });
            if (!res.ok) throw new Error('Server error ' + res.status);
            return await res.json();
        } catch (err) {
            console.error('Cart update failed:', err);
            return null;
        }
    }

    async function removeFromCart(productId) {
        try {
            const body = new URLSearchParams({ product_id: productId });
            const res  = await fetch('cart/remove-cart-item.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    body.toString()
            });
            if (!res.ok) throw new Error('Server error ' + res.status);
            return true;
        } catch (err) {
            console.error('Remove failed:', err);
            return false;
        }
    }

    document.addEventListener('click', async function (e) {

        // -- INCREASE -- //
        const incBtn = e.target.closest('.qty-increase');
        if (incBtn) {
            const id     = incBtn.dataset.id;
            const row    = incBtn.closest('.cart-item');
            const qtyEl  = row.querySelector('.qty-value');
            const decBtn = row.querySelector('.qty-decrease');

            incBtn.classList.add('loading');
            const result = await updateCart(id, 'increase');
            incBtn.classList.remove('loading');

            if (result && result.success) {
                const newQty = parseInt(result.quantity, 10);
                qtyEl.textContent = newQty;

                const price   = parseFloat(row.dataset.price);
                const totalEl = row.querySelector('.item-total-value');
                totalEl.textContent = (price * newQty).toFixed(2);
                flash(totalEl.parentElement);

                decBtn.disabled = newQty <= 1;
                recalcSummary();
            }
            return;
        }

        // -- DECREASE -- //
        const decBtn = e.target.closest('.qty-decrease');
        if (decBtn) {
            const id     = decBtn.dataset.id;
            const row    = decBtn.closest('.cart-item');
            const qtyEl  = row.querySelector('.qty-value');
            const curQty = parseInt(qtyEl.textContent, 10);

            if (curQty <= 1) return;

            decBtn.classList.add('loading');
            const result = await updateCart(id, 'decrease');
            decBtn.classList.remove('loading');

            if (result && result.success) {
                const newQty = parseInt(result.quantity, 10);
                qtyEl.textContent = newQty;

                const price   = parseFloat(row.dataset.price);
                const totalEl = row.querySelector('.item-total-value');
                totalEl.textContent = (price * newQty).toFixed(2);
                flash(totalEl.parentElement);

                decBtn.disabled = newQty <= 1;
                recalcSummary();
            }
            return;
        }

        // -- REMOVE -- //
        const removeBtn = e.target.closest('.remove-btn');
        if (removeBtn) {
            const id  = removeBtn.dataset.id;
            const row = removeBtn.closest('.cart-item');

            removeBtn.classList.add('loading');
            const ok = await removeFromCart(id);

            if (ok) {
                row.classList.add('removing');
                row.addEventListener('transitionend', function () {
                    row.remove();
                    recalcSummary();

                    if (!document.querySelector('.cart-item')) {
                        const section  = document.querySelector('section.cart-page');
                        const emptyMsg = document.createElement('h2');
                        emptyMsg.className   = 'empty-cart';
                        emptyMsg.textContent = 'Your cart is empty';
                        section.insertBefore(emptyMsg, section.querySelector('.cart-actions'));
                    }
                }, { once: true });
            } else {
                removeBtn.classList.remove('loading');
            }
        }

    });

})();


// ================= CHECKOUT PAGE ================= //
// ============= CHECKOUT ============= //
(function () {

    const form = document.getElementById('lux-checkout-form');

    // Only run on checkout page
    if (!form) return;

    const subtotal = window.checkoutData ? parseFloat(window.checkoutData.subtotal) : 0;

    // ================= SHIPPING TOTAL UPDATE ================= //
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    const shippingCostEl = document.getElementById('lux-shipping-cost');
    const orderTotalEl   = document.getElementById('lux-order-total');

    shippingRadios.forEach(radio => {
        radio.addEventListener('change', function () {

            const cost  = parseFloat(this.dataset.cost);
            const total = subtotal + cost;

            shippingCostEl.textContent =
            cost === 0 ? 'FREE' : 'R' + cost.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            orderTotalEl.textContent =
                'R' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        });
    });

    // ================= FORM VALIDATION ================= //
    const rules = {
        full_name: {
            label: 'Full name',
            test: v => v.trim().length >= 2
        },

        email: {
            label: 'Email',
            test: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)
        },

        phone: {
            label: 'Phone',
            test: v => /^\+?[\d\s\-()]{7,15}$/.test(v)
        },

        address: {
            label: 'Address',
            test: v => v.trim().length >= 5
        },

        city: {
            label: 'City',
            test: v => v.trim().length >= 2
        },

        province: {
            label: 'Province',
            test: v => v !== ''
        },

        postal_code: {
            label: 'Postal code',
            test: v => /^\d{4}$/.test(v)
        }
    };

    function validateField(id, input) {

        const errEl = document.getElementById('err-' + id);

        if (!errEl) return true;

        const rule = rules[id];

        if (!rule.test(input.value)) {

            errEl.textContent =
                rule.label +
                ' is required' +
                (id === 'postal_code' ? ' (4 digits)' : '') +
                '.';

            input.classList.add('lux-input-error');

            return false;
        }

        errEl.textContent = '';
        input.classList.remove('lux-input-error');

        return true;
    }

    // Validate on blur
    Object.keys(rules).forEach(id => {

        const input = document.getElementById(id);

        if (!input) return;

        input.addEventListener('blur', () => {
            validateField(id, input);
        });

    });

    // Validate on submit
    form.addEventListener('submit', function (e) {

        let valid = true;

        Object.keys(rules).forEach(id => {

            const input = document.getElementById(id);

            if (input && !validateField(id, input)) {
                valid = false;
            }

        });

        if (!valid) {

            e.preventDefault();

            const firstErr = form.querySelector('.lux-input-error');

            if (firstErr) {
                firstErr.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
    });

})();


// ================= CHECKOUT LOGIN PROMPT ================= //
const checkoutBtn = document.getElementById('checkout-btn');
if (checkoutBtn) {
    checkoutBtn.addEventListener('click', function () {
        const loggedIn = this.dataset.loggedIn === 'true';

        if (loggedIn) {
            window.location.href = 'checkout.php';
            return;
        }

        // Not logged in — show modal with countdown redirect
        const overlay   = document.getElementById('login-prompt-overlay');
        const bar       = document.getElementById('login-prompt-bar');
        const cancelBtn = document.getElementById('login-prompt-cancel');

        overlay.style.display = 'flex';

        // Animate progress bar over 3 seconds then redirect
        bar.style.transition = 'none';
        bar.style.width      = '0%';
        void bar.offsetWidth; // force reflow
        bar.style.transition = 'width 10s linear';
        bar.style.width      = '100%';

        const redirectTimer = setTimeout(() => {
            window.location.href = 'login.php';
        }, 10000);

        // CANCEL stops the redirect and closes modal
        cancelBtn.addEventListener('click', function () {
            clearTimeout(redirectTimer);
            bar.style.transition  = 'none';
            bar.style.width       = '0%';
            overlay.style.display = 'none';
        }, { once: true });

        // Click outside modal box to cancel
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                clearTimeout(redirectTimer);
                bar.style.transition  = 'none';
                bar.style.width       = '0%';
                overlay.style.display = 'none';
            }
        }, { once: true });
    });
}