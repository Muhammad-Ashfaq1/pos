/**
 * Customer portal client. Talks to /api/v1/customer/* with a Sanctum Bearer
 * token kept in localStorage — the exact same API the Flutter app consumes.
 */
(function () {
    'use strict';

    const TOKEN_KEY = 'portal_token';
    const api = axios.create({ baseURL: window.PORTAL.apiBase, headers: { Accept: 'application/json' } });

    const token = () => localStorage.getItem(TOKEN_KEY);
    const setToken = (t) => localStorage.setItem(TOKEN_KEY, t);
    const clearToken = () => localStorage.removeItem(TOKEN_KEY);

    api.interceptors.request.use((config) => {
        const t = token();
        if (t) config.headers.Authorization = 'Bearer ' + t;
        return config;
    });

    const notify = (type, msg) => {
        if (window.Notiflix && Notiflix.Notify) Notiflix.Notify[type === 'error' ? 'failure' : type](msg);
        else alert(msg);
    };

    const firstError = (error, fallback) => {
        const data = error.response && error.response.data;
        if (data && data.errors) return Object.values(data.errors)[0][0];
        if (data && data.message) return data.message;
        return fallback;
    };

    const formData = (form) => {
        const obj = {};
        new FormData(form).forEach((v, k) => { obj[k] = v; });
        return obj;
    };

    const submitting = (form, on) => {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = on; btn.dataset.label = btn.dataset.label || btn.innerHTML; btn.innerHTML = on ? 'Please wait…' : btn.dataset.label; }
    };

    // ---- AUTH PAGES -------------------------------------------------------
    function initLoginPage() {
        const loginForm = document.getElementById('login-form');
        if (!loginForm) return false;

        if (token()) { window.location.href = window.PORTAL.dashboardUrl; return true; }

        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            submitting(loginForm, true);
            api.post('/login', formData(loginForm))
                .then((res) => { setToken(res.data.token); window.location.href = window.PORTAL.dashboardUrl; })
                .catch((err) => { notify('error', firstError(err, 'Sign in failed.')); submitting(loginForm, false); });
        });

        const registerForm = document.getElementById('register-form');
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            submitting(registerForm, true);
            api.post('/register', formData(registerForm))
                .then((res) => { setToken(res.data.token); window.location.href = window.PORTAL.dashboardUrl; })
                .catch((err) => { notify('error', firstError(err, 'Registration failed.')); submitting(registerForm, false); });
        });

        const forgotLink = document.getElementById('forgot-link');
        const forgotModalEl = document.getElementById('forgotModal');
        const forgotModal = forgotModalEl ? new bootstrap.Modal(forgotModalEl) : null;
        forgotLink.addEventListener('click', (e) => { e.preventDefault(); forgotModal && forgotModal.show(); });

        const forgotForm = document.getElementById('forgot-form');
        forgotForm.addEventListener('submit', (e) => {
            e.preventDefault();
            submitting(forgotForm, true);
            api.post('/forgot-password', formData(forgotForm))
                .then((res) => { notify('success', res.data.message); forgotModal && forgotModal.hide(); forgotForm.reset(); })
                .catch((err) => { notify('error', firstError(err, 'Request failed.')); })
                .finally(() => submitting(forgotForm, false));
        });
        return true;
    }

    function initResetPage() {
        const form = document.getElementById('reset-form');
        if (!form) return false;
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitting(form, true);
            api.post('/reset-password', formData(form))
                .then((res) => { notify('success', res.data.message); setTimeout(() => window.location.href = window.PORTAL.loginUrl, 1200); })
                .catch((err) => { notify('error', firstError(err, 'Reset failed.')); submitting(form, false); });
        });
        return true;
    }

    // ---- APP SHELL --------------------------------------------------------
    function initAppShell() {
        const shell = document.querySelector('[data-app-shell]');
        if (!shell) return false;

        if (!token()) { window.location.href = window.PORTAL.loginUrl; return true; }

        const ordersState = { page: 0, lastPage: 1 };

        function logout() {
            api.post('/logout').catch(() => {}).finally(() => { clearToken(); window.location.href = window.PORTAL.loginUrl; });
        }
        document.getElementById('logout-btn').addEventListener('click', logout);

        // Section switching
        document.querySelectorAll('[data-section]').forEach((btn) => {
            btn.addEventListener('click', () => showSection(btn.dataset.section));
        });
        function showSection(name) {
            document.querySelectorAll('[data-pane]').forEach((p) => { p.hidden = p.dataset.pane !== name; });
            document.querySelectorAll('.nav-link[data-section]').forEach((l) => l.classList.toggle('active', l.dataset.section === name));
            if (name === 'orders' && ordersState.page === 0) loadOrders();
            if (name === 'credits') loadCredits();
        }

        function money(v) { return v; }

        // Profile + dashboard stats
        function loadProfile() {
            return api.get('/me').then((res) => {
                const c = res.data.data;
                document.getElementById('nav-customer-name').textContent = c.name;
                document.getElementById('hero-balance').textContent = c.credit_balance_label;
                document.getElementById('hero-shop').textContent = c.shop && c.shop.name ? ('at ' + c.shop.name) : '';
                document.getElementById('stat-visits').textContent = c.total_visits;
                document.getElementById('stat-lifetime').textContent = c.lifetime_value_label;
                document.getElementById('credits-balance').textContent = c.credit_balance_label;
                const f = document.getElementById('profile-form');
                f.name.value = c.name || '';
                f.phone.value = c.phone || '';
                document.getElementById('profile-email').value = c.email || '';
            });
        }

        const profileForm = document.getElementById('profile-form');
        profileForm.addEventListener('submit', (e) => {
            e.preventDefault();
            submitting(profileForm, true);
            api.patch('/me', formData(profileForm))
                .then((res) => { notify('success', 'Profile updated.'); loadProfile(); })
                .catch((err) => notify('error', firstError(err, 'Update failed.')))
                .finally(() => submitting(profileForm, false));
        });

        const statusBadge = (s) => {
            const map = { paid: 'success', partially_paid: 'warning', pending: 'secondary', estimate: 'info' };
            return '<span class="badge bg-label-' + (map[s] || 'secondary') + ' text-capitalize">' + s.replace('_', ' ') + '</span>';
        };

        const orderRow = (o) => `
            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-order="${o.id}">
                <div class="text-start">
                    <div class="fw-semibold">${o.order_number}</div>
                    <div class="small text-muted">${o.created_at_label || ''}${o.service_summary ? ' · ' + o.service_summary : ''}</div>
                    ${o.credit_earned > 0 ? `<div class="small text-success">+${o.credit_earned} credit earned</div>` : ''}
                </div>
                <div class="text-end">
                    <div class="fw-bold">${o.total_amount_label}</div>
                    ${statusBadge(o.status)}
                </div>
            </button>`;

        function loadDashboardRecent() {
            api.get('/orders', { params: { per_page: 5 } }).then((res) => {
                const wrap = document.getElementById('recent-orders');
                const items = res.data.data;
                wrap.innerHTML = items.length ? items.map(orderRow).join('') : '<div class="list-group-item text-muted text-center py-4">No visits yet.</div>';
                bindOrderRows(wrap);
            });
        }

        function loadOrders() {
            ordersState.page += 1;
            api.get('/orders', { params: { page: ordersState.page, per_page: 15 } }).then((res) => {
                const wrap = document.getElementById('orders-list');
                if (ordersState.page === 1) wrap.innerHTML = '';
                const items = res.data.data;
                ordersState.lastPage = res.data.meta ? res.data.meta.last_page : 1;
                if (!items.length && ordersState.page === 1) {
                    wrap.innerHTML = '<div class="list-group-item text-muted text-center py-4">No visits yet.</div>';
                } else {
                    wrap.insertAdjacentHTML('beforeend', items.map(orderRow).join(''));
                    bindOrderRows(wrap);
                }
                document.getElementById('orders-more-wrap').classList.toggle('d-none', ordersState.page >= ordersState.lastPage);
            });
        }
        document.getElementById('orders-more').addEventListener('click', loadOrders);

        function bindOrderRows(wrap) {
            wrap.querySelectorAll('[data-order]').forEach((b) => {
                if (b.dataset.bound) return;
                b.dataset.bound = '1';
                b.addEventListener('click', () => openOrder(b.dataset.order));
            });
        }

        const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
        function openOrder(id) {
            api.get('/orders/' + id).then((res) => {
                const o = res.data.data;
                document.getElementById('order-modal-title').textContent = 'Order ' + o.order_number;
                const items = (o.items || []).map((it) => `<tr><td>${it.product_name}</td><td class="text-end">${it.quantity_label}</td><td class="text-end">${it.line_total_label}</td></tr>`).join('');
                const payments = (o.payment_history || []).map((p) => `<tr><td>${p.created_at_label}</td><td>${p.payment_method_label}</td><td class="text-end">${p.amount_label}</td></tr>`).join('');
                document.getElementById('order-modal-body').innerHTML = `
                    <div class="d-flex justify-content-between mb-3">${statusBadge(o.status)}<span class="text-muted small">${o.created_at_label}</span></div>
                    <table class="table table-sm"><thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Total</th></tr></thead><tbody>${items}</tbody></table>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong>${o.subtotal_amount_label}</strong></div>
                    ${o.discount_amount > 0 ? `<div class="d-flex justify-content-between text-danger"><span>Discount</span><strong>-${o.discount_amount_label}</strong></div>` : ''}
                    ${o.tax_amount > 0 ? `<div class="d-flex justify-content-between"><span>Tax</span><strong>${o.tax_amount_label}</strong></div>` : ''}
                    ${o.credit_applied > 0 ? `<div class="d-flex justify-content-between text-primary"><span>Store credit used</span><strong>-${o.credit_applied_label}</strong></div>` : ''}
                    <div class="d-flex justify-content-between border-top pt-2 mt-2"><span class="fw-bold">Total</span><strong>${o.total_amount_label}</strong></div>
                    ${o.credit_earned > 0 ? `<div class="alert alert-success mt-3 mb-0 py-2"><i class="ti tabler-coin me-1"></i>You earned ${o.credit_earned_label} in store credit on this visit.</div>` : ''}
                    ${payments ? `<h6 class="mt-4">Payments</h6><table class="table table-sm"><tbody>${payments}</tbody></table>` : ''}`;
                orderModal.show();
            }).catch((err) => notify('error', firstError(err, 'Could not load order.')));
        }

        function loadCredits() {
            api.get('/credits', { params: { per_page: 30 } }).then((res) => {
                document.getElementById('credits-balance').textContent = res.data.meta.balance_label;
                const wrap = document.getElementById('credits-list');
                const items = res.data.data;
                wrap.innerHTML = items.length ? items.map((t) => `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold text-capitalize">${t.type}${t.order_number ? ' · ' + t.order_number : ''}</div>
                            <div class="small text-muted">${t.created_at_label}${t.description ? ' · ' + t.description : ''}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold ${t.direction === 'credit' ? 'text-success' : 'text-danger'}">${t.amount_label}</div>
                            <div class="small text-muted">Bal: ${t.balance_after_label}</div>
                        </div>
                    </div>`).join('') : '<div class="list-group-item text-muted text-center py-4">No credit activity yet.</div>';
            });
        }

        // Initial load — gate the shell on a valid token.
        loadProfile()
            .then(() => { shell.hidden = false; loadDashboardRecent(); })
            .catch((err) => {
                if (err.response && err.response.status === 401) { clearToken(); window.location.href = window.PORTAL.loginUrl; }
                else notify('error', 'Could not load your account.');
            });

        return true;
    }

    document.addEventListener('DOMContentLoaded', function () {
        initLoginPage() || initResetPage() || initAppShell();
    });
})();
