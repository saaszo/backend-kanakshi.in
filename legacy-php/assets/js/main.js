/**
 * MyShop — Main JavaScript
 * No jQuery, Vanilla JS only
 * Bootstrap 5 already loaded via CDN
 */

'use strict';

/* ── Constants ────────────────────────────────────────── */
const BASE_URL = document.querySelector('meta[name="base-url"]')?.content ?? '';

/* ── Document Ready ───────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    initNavbarScroll();
    initScrollToTop();
    initCartActions();
    initWishlistActions();
    initLiveSearch();
    initVariantSelector();
    initImageGallery();
    initPincodeCheck();
    initQuantityControls();
    initCouponApply();
    initTooltips();
    initNewsletter();
    initScrollReveal();
});

/* ── 1. Navbar scroll class ───────────────────────────── */
function initNavbarScroll() {
    const navbar = document.getElementById('mainNavbar');
    if (!navbar) return;
    const handler = () => navbar.classList.toggle('scrolled', window.scrollY > 50);
    window.addEventListener('scroll', handler, { passive: true });
    handler();
}

/* ── 2. Scroll to top ─────────────────────────────────── */
function initScrollToTop() {
    const btn = document.getElementById('scrollToTop');
    if (!btn) return;
    window.addEventListener('scroll', () => {
        btn.classList.toggle('show', window.scrollY > 400);
    }, { passive: true });
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* ── 3. Cart Actions (Add / Update / Remove) ──────────── */
function initCartActions() {
    // Add to cart buttons (product listing + detail page)
    const addToCartBtns = document.querySelectorAll('[data-add-cart]');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', async function() {
            const pid = this.dataset.addCart;
            const vid = this.dataset.variantId || '';
            const qty = this.dataset.qty || 1;
            
            this.disabled = true;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
            
            const res = await postAjax(BASE_URL + '/ajax/cart.php', {
                action: 'add', product_id: pid, variant_id: vid, quantity: qty
            });
            
            this.disabled = false;
            this.innerHTML = originalHtml;
            
            if (res.success) {
                updateCartBadge(res.cart_count ?? res.data?.cart_count ?? 0);
                showToast('success', res.message);
                
                // Show offcanvas logic maybe here
            } else {
                showToast('error', res.message);
            }
        });
    });
}

function updateCartBadge(count) {
    const badge = document.getElementById('cartCount');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('d-none', count === 0);
        badge.classList.add('animate__bounceIn');
        setTimeout(() => badge.classList.remove('animate__bounceIn'), 600);
    }

    const mobileBadge = document.getElementById('mobileCartCount');
    if (mobileBadge) {
        mobileBadge.textContent = count;
        mobileBadge.style.display = count > 0 ? 'flex' : 'none';
    }
}

/* ── 4. Wishlist ──────────────────────────────────────── */
function initWishlistActions() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-wishlist]');
        if (!btn) return;
        e.preventDefault();

        const productId = btn.dataset.wishlist;
        btn.disabled = true;

        const res = await postAjax(BASE_URL + '/ajax/add-to-wishlist.php', { product_id: productId });

        btn.disabled = false;
        if (res.success) {
            btn.classList.toggle('active', res.added);
            const icon = btn.querySelector('i');
            if (icon) icon.className = res.added ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
            showToast('success', res.message);
        } else {
            showToast('error', res.message || 'Please login to use wishlist.');
        }
    });
}

/* ── 5. Live Search ───────────────────────────────────── */
function initLiveSearch() {
    const input = document.getElementById('searchInput');
    const box   = document.getElementById('searchSuggestions');
    if (!input || !box) return;

    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) { box.classList.add('d-none'); box.innerHTML = ''; return; }
        timer = setTimeout(async () => {
            const res = await fetchJSON(BASE_URL + `/ajax/live-search.php?q=${encodeURIComponent(q)}`);
            if (!res || !res.results?.length) { box.classList.add('d-none'); return; }
            box.innerHTML = res.results.map(p => `
                <a href="${BASE_URL}/product.php?slug=${p.slug}" class="suggestion-item text-decoration-none text-dark">
                    <img src="${p.thumb}" alt="" class="suggestion-thumb">
                    <div>
                        <div class="fw-500">${p.name}</div>
                        <div class="text-primary small fw-600">${p.price}</div>
                    </div>
                </a>
            `).join('');
            box.classList.remove('d-none');
        }, 300);
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !box.contains(e.target)) {
            box.classList.add('d-none');
        }
    });
}

/* ── 6. Variant Selector (Product Detail) ─────────────── */
function initVariantSelector() {
    const container = document.getElementById('variantContainer');
    if (!container) return;

    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.variant-btn');
        if (!btn || btn.classList.contains('out-of-stock')) return;

        // Deselect siblings in same group
        const group = btn.closest('[data-variant-group]');
        if (group) {
            group.querySelectorAll('.variant-btn').forEach(b => b.classList.remove('selected'));
        }
        btn.classList.add('selected');

        // Collect selected variants
        const size  = container.querySelector('.variant-btn[data-size].selected')?.dataset.size ?? '';
        const color = container.querySelector('.variant-btn[data-color].selected')?.dataset.color ?? '';
        const productId = container.dataset.productId;

        if (!productId) return;
        const res = await fetchJSON(BASE_URL + `/ajax/get-variants.php?product_id=${productId}&size=${encodeURIComponent(size)}&color=${encodeURIComponent(color)}`);

        if (res) {
            // Update price
            const priceEl = document.getElementById('productPrice');
            if (priceEl && res.price) priceEl.textContent = res.price;

            // Update stock
            const stockEl = document.getElementById('productStock');
            if (stockEl) {
                stockEl.textContent = res.stock > 0 ? `In Stock (${res.stock} left)` : 'Out of Stock';
                stockEl.className   = res.stock > 0 ? 'text-success fw-600' : 'text-danger fw-600';
            }

            // Update hidden variant id for cart button
            const cartBtn = document.getElementById('addToCartBtn');
            if (cartBtn) cartBtn.dataset.variantId = res.variant_id ?? '';

            // Toggle cart button
            if (cartBtn) cartBtn.disabled = res.stock <= 0;
        }
    });
}

/* ── 7. Image Gallery (Product Detail) ───────────────── */
function initImageGallery() {
    const mainImg = document.getElementById('galleryMain');
    if (!mainImg) return;

    document.addEventListener('click', (e) => {
        const thumb = e.target.closest('.gallery-thumb');
        if (!thumb) return;
        mainImg.src = thumb.src;
        document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    });
}

/* ── 8. Pincode Delivery Check ───────────────────────── */
function initPincodeCheck() {
    const btn = document.getElementById('checkPincodeBtn');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const input  = document.getElementById('pincodeInput');
        const result = document.getElementById('pincodeResult');
        if (!input || !result) return;

        const pincode = input.value.trim();
        if (!/^\d{6}$/.test(pincode)) {
            result.textContent = 'Enter a valid 6-digit pincode.';
            result.className   = 'pincode-result error';
            return;
        }

        btn.disabled = true;
        const res = await fetchJSON(BASE_URL + `/ajax/check-pincode.php?pincode=${pincode}`);
        btn.disabled = false;

        if (res?.available) {
            result.className   = 'pincode-result success';
            result.innerHTML   = `<i class="fa-solid fa-circle-check me-1"></i>Delivery available! Expected in ${res.min_days}–${res.max_days} days. Shipping: ${res.cost}`;
        } else {
            result.className   = 'pincode-result error';
            result.innerHTML   = `<i class="fa-solid fa-circle-xmark me-1"></i>${res?.message ?? 'Delivery not available for this pincode.'}`;
        }
    });
}

/* ── 9. Quantity Controls (Cart Page) ─────────────────── */
function initQuantityControls() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-qty-action]');
        if (!btn) return;

        const action  = btn.dataset.qtyAction;   // 'inc' | 'dec'
        const cartId  = btn.dataset.cartId;
        const input   = document.querySelector(`input[data-cart-id="${cartId}"]`);
        if (!input) return;

        let qty = parseInt(input.value, 10);
        if (action === 'inc') qty++;
        if (action === 'dec') qty--;
        qty = Math.max(1, qty);
        input.value = qty;

        const res = await postAjax(BASE_URL + '/ajax/update-cart.php', { cart_id: cartId, quantity: qty });
        if (res.success) {
            // Update item line total
            const lineEl = document.querySelector(`[data-line-total="${cartId}"]`);
            if (lineEl && res.line_total) lineEl.textContent = res.line_total;
            // Update cart totals
            refreshCartTotals(res);
            updateCartBadge(res.cart_count);
        } else {
            showToast('error', res.message || 'Could not update cart.');
        }
    });

    // Remove item
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-remove-cart]');
        if (!btn) return;

        const cartId = btn.dataset.removeCart;
        const res    = await postAjax(BASE_URL + '/ajax/remove-cart.php', { cart_id: cartId });
        if (res.success) {
            const row = document.querySelector(`[data-cart-row="${cartId}"]`);
            if (row) row.remove();
            refreshCartTotals(res);
            updateCartBadge(res.cart_count);
            if (res.cart_count === 0) location.reload();
        } else {
            showToast('error', res.message || 'Could not remove item.');
        }
    });
}

function refreshCartTotals(res) {
    const map = {
        'cartSubtotal': res.subtotal,
        'cartDiscount': res.discount,
        'cartShipping': res.shipping,
        'cartTax':      res.tax,
        'cartTotal':    res.total,
    };
    for (const [id, val] of Object.entries(map)) {
        const el = document.getElementById(id);
        if (el && val !== undefined) el.textContent = val;
    }
}

/* ── 10. Coupon Apply ─────────────────────────────────── */
function initCouponApply() {
    const btn = document.getElementById('applyCouponBtn');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const input = document.getElementById('couponInput');
        const msg   = document.getElementById('couponMessage');
        if (!input) return;

        const code = input.value.trim();
        if (!code) return;

        btn.disabled = true;
        btn.textContent = 'Checking…';

        const subtotal = parseFloat(document.getElementById('cartSubtotalRaw')?.value ?? 0);
        const res = await postAjax(BASE_URL + '/ajax/apply-coupon.php', { code, subtotal });

        btn.disabled = false;
        btn.textContent = 'Apply';

        if (msg) {
            msg.textContent = res.message;
            msg.className   = 'small mt-1 ' + (res.success ? 'text-success' : 'text-danger');
        }

        if (res.success) {
            refreshCartTotals(res);
            input.readOnly = true;
            btn.textContent = 'Applied ✓';
            btn.disabled = true;
            btn.classList.replace('btn-outline-primary', 'btn-success');
        }
    });
}

/* ── 11. Bootstrap Tooltips ───────────────────────────── */
function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
}

/* ── 12. Newsletter ───────────────────────────────────── */
function initNewsletter() {
    const form = document.getElementById('newsletterForm');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const emailInput = form.querySelector('input[type="email"]');
        const email = emailInput?.value.trim();
        if (!email) return;

        const res = await postAjax(BASE_URL + '/ajax/subscribe.php', { email });
        showToast(res.success ? 'success' : 'error', res.message);
        if (res.success) {
            form.reset();
        }
    });
}

/* ── Utils ────────────────────────────────────────────── */

/** POST JSON to an AJAX endpoint */
async function postAjax(endpoint, data = {}) {
    try {
        const form = new FormData();
        // Append CSRF token
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) form.append('csrf_token', csrf);
        for (const [k, v] of Object.entries(data)) form.append(k, v);

        const res = await fetch(endpoint, { method: 'POST', body: form });
        return await res.json();
    } catch (err) {
        console.error('[AJAX]', endpoint, err);
        return { success: false, message: 'Network error. Please try again.' };
    }
}

/** GET JSON from endpoint */
async function fetchJSON(endpoint) {
    try {
        const res = await fetch(endpoint);
        return await res.json();
    } catch (err) {
        console.error('[FETCH]', endpoint, err);
        return null;
    }
}

/** Show a Bootstrap toast notification */
function showToast(type, message) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;max-width:320px;';
        document.body.appendChild(container);
    }

    const colors = { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#0891b2' };
    const icons  = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };

    const toast = document.createElement('div');
    toast.className = 'toast show align-items-center border-0 text-white';
    toast.style.cssText = `background:${colors[type] ?? colors.info};border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.2);min-width:240px;`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex align-items-center gap-2 p-3">
            <i class="fa-solid ${icons[type] ?? icons.info} fa-lg flex-shrink-0"></i>
            <span class="flex-grow-1" style="font-size:.88rem;font-weight:500;">${message}</span>
            <button type="button" class="btn-close btn-close-white ms-1 flex-shrink-0" style="font-size:.7rem;"></button>
        </div>`;

    container.appendChild(toast);
    
    // Force a reflow and then show
    toast.offsetHeight;
    toast.classList.add('show');
    toast.style.opacity = '1';

    const dismiss = () => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s ease'; 
        setTimeout(() => { if(toast.parentNode) toast.remove(); }, 400);
    };

    toast.querySelector('.btn-close').addEventListener('click', dismiss);
    setTimeout(dismiss, 4000);
}

/** Save original button HTML before AJAX modifies it */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-add-cart]');
    if (btn && !btn.dataset.originalHtml) {
        btn.dataset.originalHtml = btn.innerHTML;
    }
});
/** Toggle Mobile Search Overlay */
function toggleMobileSearch() {
    const overlay = document.getElementById('mobileSearch');
    if (!overlay) return;
    
    const isActive = overlay.classList.toggle('active');
    document.body.style.overflow = isActive ? 'hidden' : '';
    
    if (isActive) {
        const input = overlay.querySelector('input');
        if (input) setTimeout(() => input.focus(), 300);
    }
}
/* ── 13. Scroll Reveal Animation ──────────────────────── */
function initScrollReveal() {
    const revealElements = document.querySelectorAll('.reveal');
    if (!revealElements.length) return;

    // Use GSAP ScrollTrigger for ultra-smooth luxury reveal
    revealElements.forEach((el, index) => {
        gsap.to(el, {
            scrollTrigger: {
                trigger: el,
                start: "top 90%",
                toggleActions: "play none none none",
                once: true
            },
            y: 0,
            opacity: 1,
            duration: 1.2,
            ease: "power2.out",
            delay: (index % 4) * 0.1 // Stagger items in a row
        });
    });
}
