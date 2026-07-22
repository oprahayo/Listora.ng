import Alpine from 'alpinejs';

const SAVED_KEY = 'listora.guest-saved-properties.v1';
const STATIC_PREVIEW = window.location.hostname === 'oprahayo.github.io' && window.location.pathname.startsWith('/Listora.ng');
const APP_BASE = STATIC_PREVIEW ? '/Listora.ng' : '';
const appPath = path => `${APP_BASE}${path}`;

Alpine.data('listoraApp', () => ({
    online: navigator.onLine,
    loginOpen: false,
    loginRole: 'tenant',
    loginMode: 'password',
    loginLoading: false,
    loginErrors: {},
    loginMessage: '',
    mobileMenuOpen: false,
    filterOpen: false,
    sortOpen: false,
    infoOpen: false,
    savedIds: [],
    savedProperties: [],
    savedLoading: false,
    viewMode: sessionStorage.getItem('listora.view-mode') || 'grid',
    toast: '',
    previousFocus: null,
    toastTimer: null,

    init() {
        this.savedIds = this.readSavedIds();
        window.addEventListener('online', () => { this.online = true; });
        window.addEventListener('offline', () => { this.online = false; });

        if ('serviceWorker' in navigator && import.meta.env.PROD) {
            window.addEventListener('load', () => navigator.serviceWorker.register(appPath('/service-worker.js')).catch(() => {}));
        }
    },

    readSavedIds() {
        try {
            const value = JSON.parse(localStorage.getItem(SAVED_KEY) || '[]');
            return Array.isArray(value) ? value.map(Number).filter(Number.isInteger) : [];
        } catch {
            return [];
        }
    },

    isSaved(id) {
        return this.savedIds.includes(Number(id));
    },

    toggleSaved(id) {
        id = Number(id);
        this.savedIds = this.isSaved(id)
            ? this.savedIds.filter(savedId => savedId !== id)
            : [...this.savedIds, id];
        localStorage.setItem(SAVED_KEY, JSON.stringify(this.savedIds));
        this.showToast(this.isSaved(id) ? 'Property saved on this device.' : 'Property removed from saved.');
        this.savedProperties = this.savedProperties.filter(property => this.savedIds.includes(property.id));
    },

    async loadSaved() {
        this.savedLoading = true;
        if (!this.savedIds.length) {
            this.savedLoading = false;
            return;
        }

        try {
            const params = new URLSearchParams();
            this.savedIds.forEach(id => params.append('ids[]', id));
            const endpoint = STATIC_PREVIEW
                ? appPath('/data/properties.json')
                : appPath(`/saved/property-summaries?${params}`);
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Unable to load saved properties.');
            const data = await response.json();
            const properties = STATIC_PREVIEW
                ? this.savedIds.map(id => data.properties.find(property => property.id === id)).filter(Boolean)
                : data.properties;
            this.savedProperties = properties;
            this.savedIds = (STATIC_PREVIEW ? properties.map(property => property.id) : data.valid_ids).map(Number);
            localStorage.setItem(SAVED_KEY, JSON.stringify(this.savedIds));
        } catch {
            this.showToast(this.online ? 'Saved properties could not be refreshed.' : 'Reconnect to refresh saved properties.');
        } finally {
            this.savedLoading = false;
        }
    },

    openLogin(role = 'tenant') {
        this.previousFocus = document.activeElement;
        this.loginRole = ['agent', 'landlord', 'tenant'].includes(role) ? role : 'tenant';
        this.loginErrors = {};
        this.loginMessage = '';
        this.loginOpen = true;
        this.$nextTick(() => this.$refs.loginIdentifier?.focus());
        if (this.online) this.refreshCsrfToken();
    },

    async refreshCsrfToken() {
        if (STATIC_PREVIEW) return;

        try {
            const response = await fetch(appPath('/auth/csrf-token'), { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const { token } = await response.json();
            document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', token);
            document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = token; });
        } catch {
            // The existing page token remains valid for ordinary, non-cached visits.
        }
    },

    closeLogin() {
        if (this.loginLoading) return;
        this.loginOpen = false;
        this.$nextTick(() => this.previousFocus?.focus?.());
    },

    openFilter() {
        this.previousFocus = document.activeElement;
        this.filterOpen = true;
        this.$nextTick(() => this.$refs.filterClose?.focus());
    },

    closeFilter() {
        this.filterOpen = false;
        this.$nextTick(() => this.previousFocus?.focus?.());
    },

    openSort() {
        this.previousFocus = document.activeElement;
        this.sortOpen = true;
        this.$nextTick(() => this.$refs.sortClose?.focus());
    },

    closeSort() {
        this.sortOpen = false;
        this.$nextTick(() => this.previousFocus?.focus?.());
    },

    trapFocus(event, container) {
        const focusable = [...container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])')]
            .filter(element => element.offsetParent !== null);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    },

    async submitLogin(form) {
        if (!this.online) return;
        if (STATIC_PREVIEW) {
            this.loginErrors = { identifier: 'Sign in is currently unavailable. Please try again later.' };
            return;
        }
        this.loginLoading = true;
        this.loginErrors = {};
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();
            if (!response.ok) {
                this.loginErrors = Object.fromEntries(Object.entries(data.errors || { identifier: [data.message || 'Unable to sign in.'] }).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value]));
                return;
            }
            await this.notifyAuthState();
            window.location.assign(data.redirect || '/');
        } catch {
            this.loginErrors = { identifier: 'Unable to reach Listora. Check your connection and try again.' };
        } finally {
            this.loginLoading = false;
        }
    },

    notifyAuthState() {
        return new Promise(resolve => {
            if (!navigator.serviceWorker?.controller) return resolve();
            const channel = new MessageChannel();
            const timeout = setTimeout(resolve, 500);
            channel.port1.onmessage = () => { clearTimeout(timeout); resolve(); };
            navigator.serviceWorker.controller.postMessage({ type: 'AUTHENTICATED' }, [channel.port2]);
        });
    },

    async requestOtp(form) {
        if (!this.online) return;
        if (STATIC_PREVIEW) {
            this.loginErrors = { identifier: 'OTP sign-in is currently unavailable.' };
            return;
        }
        this.loginLoading = true;
        this.loginErrors = {};
        this.loginMessage = '';
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();
            if (!response.ok) {
                this.loginErrors = Object.fromEntries(Object.entries(data.errors || {}).map(([key, value]) => [key, value[0]]));
                return;
            }
            this.loginMessage = data.message;
        } catch {
            this.loginErrors = { identifier: 'Unable to request an OTP. Check your connection.' };
        } finally {
            this.loginLoading = false;
        }
    },

    setView(mode) {
        this.viewMode = mode;
        sessionStorage.setItem('listora.view-mode', mode);
    },

    async shareProperty(data) {
        try {
            if (navigator.share) {
                await navigator.share(data);
                return;
            }
            await navigator.clipboard.writeText(data.url);
            this.showToast('Property link copied.');
        } catch (error) {
            if (error?.name !== 'AbortError') this.showToast('Could not share this property.');
        }
    },

    showToast(message) {
        this.toast = message;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => { this.toast = ''; }, 2600);
    },
}));

window.Alpine = Alpine;
Alpine.start();
