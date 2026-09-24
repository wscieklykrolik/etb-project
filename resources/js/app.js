import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

// Keep wide tables inside their own scroll area, including editorial content.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#app-main table').forEach((table) => {
        let wrapper = table.parentElement;
        if (!wrapper.classList.contains('overflow-x-auto') && !wrapper.classList.contains('etb-table-scroll')) {
            wrapper = document.createElement('div');
            table.before(wrapper);
            wrapper.append(table);
        }
        wrapper.classList.add('etb-table-scroll');
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', 'Tabela — przewiń w poziomie, aby zobaczyć wszystkie kolumny');
    });
    document.querySelectorAll('[data-faq-scroll]').forEach((list) => {
        const items = Array.from(list.children).slice(0, 5);
        const summaries = items.map((item) => item.querySelector('summary'));
        const updateHeight = () => {
            const listStyle = getComputedStyle(list);
            const height = items.reduce((total, item, index) => {
                const style = getComputedStyle(item);
                return total + summaries[index].getBoundingClientRect().height
                    + parseFloat(style.paddingTop) + parseFloat(style.paddingBottom)
                    + parseFloat(style.borderTopWidth) + parseFloat(style.borderBottomWidth);
            }, parseFloat(listStyle.borderTopWidth) + parseFloat(listStyle.borderBottomWidth));
            list.style.setProperty('--faq-list-height', `${Math.ceil(height)}px`);
        };
        updateHeight();
        const observer = new ResizeObserver(updateHeight);
        summaries.forEach((summary) => observer.observe(summary));
    });
    const wideScreen = window.matchMedia('(min-width: 1024px)');
    document.querySelectorAll('[data-responsive-details]').forEach((details) => {
        const sync = () => { details.open = wideScreen.matches; };
        sync();
        wideScreen.addEventListener('change', sync);
    });
});

window.adminUserSearch = function adminUserSearch(searchUrl, filters = {}) {
    return {
        query: '',
        results: [],
        role: filters.role || 'all',
        marketingConsent: filters.marketingConsent || 'all',
        highlightedId: null,
        async search() {
            if (this.query.trim().length < 2) {
                this.results = [];
                return;
            }

            const params = new URLSearchParams({
                q: this.query,
                role: this.role,
                marketing_consent: this.marketingConsent,
            });
            const response = await fetch(`${searchUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });

            this.results = await response.json();
        },
        focusUser(user) {
            this.results = [];
            this.query = user.name;
            this.highlightedId = user.id;

            const element = document.getElementById(`managed-user-${user.id}`);
            if (!element) {
                const params = new URLSearchParams({
                    section: 'users',
                    page: user.page,
                    focus_user: user.id,
                    user_role: this.role,
                    marketing_consent: this.marketingConsent,
                });
                window.location.href = `${window.location.pathname}?${params.toString()}#managed-user-${user.id}`;
                return;
            }

            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            element.classList.add('admin-highlight');

            setTimeout(() => {
                element.classList.remove('admin-highlight');
            }, 4500);
        },
    };
};

document.addEventListener('DOMContentLoaded', () => {
    const focusUser = new URLSearchParams(window.location.search).get('focus_user');
    if (!focusUser) return;

    const element = document.getElementById(`managed-user-${focusUser}`);
    if (!element) return;

    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    element.classList.add('admin-highlight');

    setTimeout(() => {
        element.classList.remove('admin-highlight');
    }, 4500);
});

window.matchForm = function matchForm(config) {
    return {
        status: config.status,
        includeInLzkosz: Boolean(config.includeInLzkosz ?? false),
        isTicketed: Boolean(config.isTicketed ?? false),
        locations: [],
        opponents: [],
        opponentLogo: config.opponentLogo,
        async loadLocations(query) {
            const response = await fetch(`${config.locationsUrl}?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
            });
            this.locations = await response.json();
        },
        async loadOpponents(query) {
            const response = await fetch(`${config.opponentsUrl}?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
            });
            this.opponents = await response.json();
        },
        selectLocation(location) {
            this.$root.querySelector('[name="location"]').value = location.name;
            this.locations = [];
        },
        selectOpponent(opponent) {
            this.$root.querySelector('[name="opponent_name"]').value = opponent.name;
            this.opponentLogo = opponent.logo_url || null;
            this.opponents = [];
        },
        syncTime(value) {
            const input = this.$root.querySelector('[name="match_date"]');
            if (!input || !value) return;

            const date = input.value ? input.value.slice(0, 10) : new Date().toISOString().slice(0, 10);
            input.value = `${date}T${value}`;
        },
    };
};

window.newsLightbox = function newsLightbox(images = []) {
    return {
        sectionQuery: '',
        images,
        activeIndex: null,
        image: null,
        get hasMultipleImages() {
            return this.images.length > 1;
        },
        open(pathOrIndex) {
            if (Number.isInteger(pathOrIndex)) {
                this.openGallery(pathOrIndex);
                return;
            }

            const index = this.images.indexOf(pathOrIndex);
            this.activeIndex = index >= 0 ? index : null;
            this.image = pathOrIndex;
        },
        openGallery(index) {
            if (!this.images[index]) return;

            this.activeIndex = index;
            this.image = this.images[index];
        },
        previous() {
            if (this.activeIndex === null || !this.hasMultipleImages) return;

            this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length;
            this.image = this.images[this.activeIndex];
        },
        next() {
            if (this.activeIndex === null || !this.hasMultipleImages) return;

            this.activeIndex = (this.activeIndex + 1) % this.images.length;
            this.image = this.images[this.activeIndex];
        },
        close() {
            this.image = null;
            this.activeIndex = null;
        },
    };
};

window.newsEditor = function newsEditor(config = {}) {
    return {
        submitting: false,
        errors: [],
        initialValues: '',
        init() {
            this.$nextTick(() => { this.initialValues = this.signature(); });
        },
        signature() {
            return JSON.stringify([...new FormData(this.$refs.editorForm).entries()]
                .filter(([key]) => !['_token', 'save_as_draft'].includes(key))
                .map(([key, value]) => [key, value instanceof File ? (value.size ? [value.name, value.size, value.lastModified] : null) : value]));
        },
        hasContent() {
            const data = new FormData(this.$refs.editorForm);
            return ['title', 'content', 'excerpt', 'video_url', 'article_author', 'photo_author']
                .some((key) => String(data.get(key) || '').trim() !== '')
                || [...data.values()].some((value) => value instanceof File && value.size > 0);
        },
        closeEditor() {
            if (this.submitting) return;
            if (config.saveOnClose && (config.existing ? this.signature() !== this.initialValues : this.hasContent())) {
                this.submitEditor(true);
                return;
            }
            if (config.returnUrl) {
                window.location.assign(config.returnUrl);
            } else {
                this.openModal = null;
            }
        },
        async submitEditor(draft = false) {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = [];
            const form = this.$refs.editorForm;
            const data = new FormData(form);
            data.set('save_as_draft', draft ? '1' : '0');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json' },
                    body: data,
                });
                if (!response.ok) {
                    const result = await response.json().catch(() => ({}));
                    this.errors = response.status === 422
                        ? Object.values(result.errors || {}).flat()
                        : ['Nie udało się zapisać wpisu. Spróbuj ponownie.'];
                    if (!this.errors.length) this.errors = ['Nie udało się zapisać wpisu. Spróbuj ponownie.'];
                    this.submitting = false;
                    return;
                }
                const result = await response.json();
                window.location.assign(result.redirect);
            } catch {
                this.errors = ['Nie udało się zapisać wpisu. Sprawdź połączenie i spróbuj ponownie.'];
                this.submitting = false;
            }
        },
    };
};

window.newsGallery = function newsGallery() {
    return {
        selectedImages: [],
        addFiles(event) {
            for (const file of event.target.files) {
                this.selectedImages.push({ file, url: URL.createObjectURL(file) });
            }
            this.syncFiles();
        },
        removeFile(index) {
            URL.revokeObjectURL(this.selectedImages[index].url);
            this.selectedImages.splice(index, 1);
            this.syncFiles();
        },
        syncFiles() {
            const transfer = new DataTransfer();
            this.selectedImages.forEach((image) => transfer.items.add(image.file));
            this.$refs.galleryInput.files = transfer.files;
        },
        destroy() {
            this.selectedImages.forEach((image) => URL.revokeObjectURL(image.url));
        },
    };
};

window.adminPanel = function adminPanel(config) {
    return {
        openModal: config.initialModal || null,
        matchFilter: 'all',
        newsFilter: 'all',
        academyTrainingScope: 'single',
        publishAction: null,
        panelSearch: '',
        notificationsOpen: false,
        accountOpen: false,
        previewNotification: null,
        unreadCount: Number(config.unreadCount || 0),
        currentAccount: config.currentAccount,
        savedAccounts: [],
        get notificationBadge() {
            return this.unreadCount > 99 ? '+99' : String(this.unreadCount);
        },
        init() {
            this.savedAccounts = this.readSavedAccounts();
        },
        closeModal() {
            if (this.openModal === 'news-create' || this.openModal?.startsWith('news-edit-')) {
                window.dispatchEvent(new CustomEvent('close-news-editor'));
            } else {
                this.openModal = null;
            }
        },
        readSavedAccounts() {
            try {
                return JSON.parse(localStorage.getItem('etb.admin.accounts') || '[]');
            } catch {
                return [];
            }
        },
        persistSavedAccounts() {
            localStorage.setItem('etb.admin.accounts', JSON.stringify(this.savedAccounts.slice(0, 6)));
        },
        saveCurrentAccount() {
            this.savedAccounts = [
                this.currentAccount,
                ...this.savedAccounts.filter((account) => account.email !== this.currentAccount.email),
            ].slice(0, 6);
            this.persistSavedAccounts();
        },
        switchAccount(account) {
            sessionStorage.setItem('etb.login.email', account.email);
            window.location.href = '/login';
        },
        searchPanel() {
            const query = this.panelSearch.trim().toLowerCase();
            document.querySelectorAll('[data-admin-search]').forEach((element) => {
                if (!query) {
                    element.classList.remove('admin-highlight');
                    return;
                }

                const text = element.textContent.toLowerCase();
                if (text.includes(query)) {
                    element.classList.add('admin-highlight');
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    element.classList.remove('admin-highlight');
                }
            });
        },
    };
};

window.academyTrainerForm = function academyTrainerForm(config) {
    return {
        name: config.initialName || '',
        phone: config.initialPhone || '',
        email: config.initialEmail || '',
        role: config.initialRole || '',
        suggestions: [],
        suggestionsOpen: false,
        noticeOpen: false,
        noticeProgress: 100,
        noticeTimer: null,
        progressTimer: null,
        async searchTrainers() {
            const query = this.name.trim();
            if (query.length < 2) {
                this.suggestions = [];
                this.suggestionsOpen = false;
                return;
            }

            const params = new URLSearchParams({ q: query });
            const response = await fetch(`${config.searchUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                this.suggestions = [];
                this.suggestionsOpen = false;
                return;
            }

            this.suggestions = await response.json();
            this.suggestionsOpen = this.suggestions.length > 0;
        },
        selectTrainer(trainer) {
            this.name = trainer.name || '';
            this.phone = trainer.phone || '';
            this.email = trainer.email || '';
            this.role = trainer.role || this.role;
            this.suggestions = [];
            this.suggestionsOpen = false;
            this.showNotice();
        },
        showNotice() {
            this.clearNoticeTimers();
            this.noticeOpen = true;
            this.noticeProgress = 100;
            const startedAt = Date.now();

            this.progressTimer = setInterval(() => {
                const elapsed = Date.now() - startedAt;
                this.noticeProgress = Math.max(0, 100 - (elapsed / 5000) * 100);
            }, 50);

            this.noticeTimer = setTimeout(() => {
                this.closeNotice();
            }, 5000);
        },
        closeNotice() {
            this.noticeOpen = false;
            this.noticeProgress = 0;
            this.clearNoticeTimers();
        },
        closeNoticeOnKey(event) {
            if (!this.noticeOpen) return;
            event.preventDefault();
            this.closeNotice();
        },
        clearNoticeTimers() {
            if (this.noticeTimer) {
                clearTimeout(this.noticeTimer);
                this.noticeTimer = null;
            }
            if (this.progressTimer) {
                clearInterval(this.progressTimer);
                this.progressTimer = null;
            }
        },
    };
};

const COOKIE_CONSENT_CONFIG = {
    name: 'etb_cookie_consent',
    version: 1,
    maxAge: 60 * 60 * 24 * 180,
    categories: ['functional', 'analytics', 'marketing'],
};

const optionalCookiePatterns = {
    functional: [/^etb_preferences$/],
    analytics: [/^_ga/, /^_gid$/, /^_gat/, /^_gcl_au$/, /^_hj/, /^AMP_TOKEN$/, /^_pk_/],
    marketing: [/^_fbp$/, /^fr$/, /^IDE$/, /^NID$/, /^ANONCHK$/, /^MUID$/, /^VISITOR_INFO1_LIVE$/, /^YSC$/, /^PREF$/],
};

const necessaryOnlyConsent = () => ({
    necessary: true,
    functional: false,
    analytics: false,
    marketing: false,
});

let activeCookieConsent = necessaryOnlyConsent();

function readCookieConsent() {
    const prefix = `${COOKIE_CONSENT_CONFIG.name}=`;
    const rawValue = document.cookie
        .split(';')
        .map((part) => part.trim())
        .find((part) => part.startsWith(prefix))
        ?.slice(prefix.length);

    if (!rawValue) return null;

    try {
        const stored = JSON.parse(decodeURIComponent(rawValue));
        if (stored.version !== COOKIE_CONSENT_CONFIG.version || typeof stored.categories !== 'object') {
            return null;
        }

        return {
            version: stored.version,
            acceptedAt: stored.acceptedAt,
            categories: {
                necessary: true,
                functional: stored.categories.functional === true,
                analytics: stored.categories.analytics === true,
                marketing: stored.categories.marketing === true,
            },
        };
    } catch {
        return null;
    }
}

function writeCookieConsent(categories) {
    const value = encodeURIComponent(JSON.stringify({
        version: COOKIE_CONSENT_CONFIG.version,
        acceptedAt: new Date().toISOString(),
        categories,
    }));
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';

    document.cookie = `${COOKIE_CONSENT_CONFIG.name}=${value}; Max-Age=${COOKIE_CONSENT_CONFIG.maxAge}; Path=/; SameSite=Lax${secure}`;
}

function removeCookie(name) {
    const encodedName = encodeURIComponent(name);
    const hostname = window.location.hostname;
    const domains = hostname && hostname !== 'localhost'
        ? ['', `; Domain=${hostname}`, `; Domain=.${hostname}`]
        : [''];

    domains.forEach((domain) => {
        document.cookie = `${encodedName}=; Max-Age=0; Path=/; SameSite=Lax${domain}`;
    });
}

function clearCookiesForDisabledCategories(categories) {
    const disabledPatterns = COOKIE_CONSENT_CONFIG.categories
        .filter((category) => !categories[category])
        .flatMap((category) => optionalCookiePatterns[category] || []);

    if (disabledPatterns.length === 0) return;

    document.cookie.split(';').forEach((part) => {
        const name = decodeURIComponent(part.split('=')[0].trim());
        if (disabledPatterns.some((pattern) => pattern.test(name))) {
            removeCookie(name);
        }
    });
}

function activateConsentScripts(categories) {
    document.querySelectorAll('script[type="text/plain"][data-cookie-category]').forEach((blockedScript) => {
        const category = blockedScript.dataset.cookieCategory;
        if (!categories[category] || blockedScript.dataset.cookieActivated === 'true') return;

        const script = document.createElement('script');
        Array.from(blockedScript.attributes).forEach((attribute) => {
            if (['type', 'data-cookie-category', 'data-cookie-type', 'data-cookie-src', 'data-cookie-activated'].includes(attribute.name)) return;
            script.setAttribute(attribute.name, attribute.value);
        });
        script.type = blockedScript.dataset.cookieType || 'text/javascript';
        if (blockedScript.dataset.cookieSrc) script.src = blockedScript.dataset.cookieSrc;
        script.textContent = blockedScript.textContent;
        blockedScript.dataset.cookieActivated = 'true';
        blockedScript.after(script);
    });
}

function updateConsentEmbeds(categories) {
    document.querySelectorAll('[data-cookie-embed][data-cookie-category]').forEach((container) => {
        const allowed = categories[container.dataset.cookieCategory] === true;
        const loadedContent = container.querySelector('[data-cookie-loaded]');
        const placeholder = container.querySelector('[data-cookie-placeholder]');

        if (allowed && !loadedContent) {
            placeholder?.remove();
            const template = container.querySelector('template[data-cookie-embed-content]');
            if (!template) return;

            const content = template.content.cloneNode(true);
            content.querySelectorAll('[data-cookie-src]').forEach((element) => {
                element.setAttribute('src', element.dataset.cookieSrc);
                element.setAttribute('data-cookie-loaded', 'true');
                element.removeAttribute('data-cookie-src');
            });
            container.appendChild(content);
            return;
        }

        if (!allowed && loadedContent) {
            loadedContent.remove();
        }

        if (!allowed && !container.querySelector('[data-cookie-placeholder]')) {
            const template = container.querySelector('template[data-cookie-placeholder-content]');
            if (template) container.appendChild(template.content.cloneNode(true));
        }
    });

    window.reinitializeUi?.();
}

function applyCookieConsent(categories) {
    activeCookieConsent = { ...necessaryOnlyConsent(), ...categories, necessary: true };
    clearCookiesForDisabledCategories(activeCookieConsent);
    activateConsentScripts(activeCookieConsent);
    updateConsentEmbeds(activeCookieConsent);
    window.dispatchEvent(new CustomEvent('etb:cookie-consent-changed', {
        detail: { ...activeCookieConsent },
    }));
}

window.etbCookieConsent = {
    has(category) {
        return category === 'necessary' || activeCookieConsent[category] === true;
    },
};

window.etbAnalytics = {
    track(eventName, parameters = {}) {
        if (!window.etbCookieConsent.has('analytics') || typeof window.gtag !== 'function') return;
        window.gtag('event', eventName, parameters);
    },
};

function analyticsParametersFrom(element) {
    try {
        return JSON.parse(element.dataset.analyticsParameters || '{}');
    } catch {
        return {};
    }
}

document.addEventListener('click', (event) => {
    const trackedElement = event.target.closest('[data-analytics-event]');
    if (!trackedElement || trackedElement.tagName === 'FORM') return;

    window.etbAnalytics.track(
        trackedElement.dataset.analyticsEvent,
        analyticsParametersFrom(trackedElement),
    );
});

document.addEventListener('submit', (event) => {
    const trackedForm = event.target.closest('form[data-analytics-event]');
    if (!trackedForm) return;

    const parameters = analyticsParametersFrom(trackedForm);
    const quantityField = trackedForm.dataset.analyticsQuantityField;
    const shippingField = trackedForm.dataset.analyticsShippingField;

    if (quantityField && Array.isArray(parameters.items) && parameters.items[0]) {
        const quantity = Math.max(1, Number(trackedForm.elements[quantityField]?.value || 1));
        parameters.items[0].quantity = quantity;
        if (typeof parameters.items[0].price === 'number') {
            parameters.value = Number((parameters.items[0].price * quantity).toFixed(2));
        }
    }

    if (shippingField) {
        parameters.shipping_tier = trackedForm.querySelector(`[name="${shippingField}"]:checked`)?.value || undefined;
    }

    window.etbAnalytics.track(trackedForm.dataset.analyticsEvent, parameters);
});

window.cookieConsentManager = function cookieConsentManager() {
    return {
        initialized: false,
        hasChoice: false,
        showBanner: false,
        showPreferences: false,
        savedNotice: false,
        noticeTimer: null,
        preferences: necessaryOnlyConsent(),
        init() {
            const stored = readCookieConsent();
            this.hasChoice = stored !== null;
            this.preferences = stored ? { ...stored.categories } : necessaryOnlyConsent();
            this.showBanner = !stored;
            this.initialized = true;
            applyCookieConsent(this.preferences);
        },
        reopenBanner() {
            this.showPreferences = false;
            this.savedNotice = false;
            this.showBanner = true;
            this.$nextTick(() => this.$refs.bannerHeading?.focus());
        },
        openPreferences() {
            this.preferences = { ...activeCookieConsent };
            this.showPreferences = true;
            this.showBanner = false;
            this.$nextTick(() => this.$refs.preferencesHeading?.focus());
        },
        closePreferences() {
            if (!this.showPreferences) return;
            this.showPreferences = false;
            this.showBanner = !this.hasChoice;
        },
        acceptAll() {
            this.persist({
                necessary: true,
                functional: true,
                analytics: true,
                marketing: true,
            });
        },
        rejectOptional() {
            this.persist(necessaryOnlyConsent());
        },
        savePreferences() {
            this.persist({
                necessary: true,
                functional: this.preferences.functional === true,
                analytics: this.preferences.analytics === true,
                marketing: this.preferences.marketing === true,
            });
        },
        persist(categories) {
            const revoked = COOKIE_CONSENT_CONFIG.categories
                .some((category) => activeCookieConsent[category] && !categories[category]);

            writeCookieConsent(categories);
            this.preferences = { ...categories };
            this.hasChoice = true;
            this.showBanner = false;
            this.showPreferences = false;

            if (revoked) {
                clearCookiesForDisabledCategories(categories);
                window.location.reload();
                return;
            }

            applyCookieConsent(categories);
            this.showSavedNotice();
        },
        showSavedNotice() {
            if (this.noticeTimer) window.clearTimeout(this.noticeTimer);
            this.savedNotice = true;
            this.noticeTimer = window.setTimeout(() => {
                this.savedNotice = false;
            }, 3500);
        },
    };
};

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const loginEmail = sessionStorage.getItem('etb.login.email');
    if (!loginEmail) return;

    const input = document.querySelector('input[name="email"][autocomplete="username"]');
    if (input && !input.value) {
        input.value = loginEmail;
    }

    sessionStorage.removeItem('etb.login.email');
});

window.reinitializeUi = function reinitializeUi() {
    createIcons({ icons });
};

document.addEventListener('DOMContentLoaded', () => {
    window.reinitializeUi();
});

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('countdown');
    if (!el) return;

    const matchDate = el.dataset.date;
    if (!matchDate) return;

    const target = new Date(matchDate).getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const diff = target - now;

        if (diff <= 0) {
            el.innerHTML = 'Mecz trwa 🔥';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((diff / (1000 * 60)) % 60);
        const seconds = Math.floor((diff / 1000) % 60);

        el.innerHTML = `<span>${days} dni</span> : <span>${hours} godz</span> : <span>${minutes} min</span> : <span>${seconds} sek</span>`;
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});

const DEFAULT_FONT_SIZE = 16;
const MIN_FONT_SIZE = 14;
const MAX_FONT_SIZE = 22;

function setRootFontSize(size) {
    const root = document.documentElement;
    const next = Math.min(MAX_FONT_SIZE, Math.max(MIN_FONT_SIZE, size));
    root.style.fontSize = `${next}px`;
}

window.adjustFontSize = function adjustFontSize(change) {
    const current = parseFloat(getComputedStyle(document.documentElement).fontSize) || DEFAULT_FONT_SIZE;
    setRootFontSize(current + change);
};

const legacySearchIndex = [
    { label: 'Aktualności', url: '/news', keywords: ['aktualnosci', 'news'] },
    { label: 'Klub', url: '/club', keywords: ['klub'] },
    { label: 'Rozgrywki', url: '/schedule', keywords: ['rozgrywki', 'liga', 'terminarz'] },
    { label: 'Kontakt', url: '/contact', keywords: ['kontakt', 'email', 'telefon'] },
    { label: 'Marketing', url: '/contact#marketing', keywords: ['marketing', 'media', 'współpraca medialna'] },
    { label: 'Drużyna', url: '/team', keywords: ['drużyna', 'zawodnicy'] },
    { label: 'Drużyna 3x3', url: '/team/3x3', keywords: ['3x3', 'trzy na trzy', 'drużyna 3x3'] },
    { label: 'Tabela', url: '/schedule/table', keywords: ['tabela'] },
    { label: 'Terminarz ŁZKosz', url: '/schedule/lzkosz', keywords: ['łzkosz', 'lzkosz', 'terminarz łzkosz'] },
    { label: 'III liga mężczyzn ŁZKosz', url: '/schedule/third-league', keywords: ['iii liga', '3 liga', 'elkosz', 'lzkosz'] },
    { label: 'Partnerzy', url: '/#partners', keywords: ['sponsorzy', 'sponsor', 'partner', 'partnerzy', 'partner strategiczny', 'partner technologiczny'] },
    { label: 'Akademia', url: '/academy', keywords: ['akademia', 'treningi', 'grupy', 'u15', 'u17', 'u19'] },
];

function populateSearchSuggestions() {
    const datalist = document.getElementById('etb-search-suggestions');
    if (!datalist) return;

    datalist.innerHTML = searchIndex
        .map((item) => `<option value="${item.label}"></option>`)
        .join('');
}

document.addEventListener('DOMContentLoaded', populateSearchSuggestions);

window.etbSearch = function etbSearch() {
    const input = document.getElementById('etb-search');
    if (!input) return;

    const query = input.value.trim().toLowerCase();
    if (!query) return;

    const directMatch = searchIndex.find((item) => item.label.toLowerCase() === query);
    const partialMatch = searchIndex.find((item) => item.label.toLowerCase().includes(query)
        || item.keywords.some((keyword) => keyword.includes(query) || query.includes(keyword)));

    const result = directMatch || partialMatch;

    if (result) {
        window.location.href = result.url;
        return;
    }

    const main = document.getElementById('app-main');
    if (main && main.innerText.toLowerCase().includes(query)) {
        window.find(input.value);
        return;
    }

    alert('Brak wyników dla podanej frazy.');
};

const searchIndex = [
    { label: 'Strona główna', url: '/', keywords: ['home', 'start', 'glowna', 'główna', 'etb'] },
    { label: 'Aktualności', url: '/news', keywords: ['aktualnosci', 'aktualności', 'news', 'artykuly', 'artykuły', 'wieści'] },
    { label: 'Klub', url: '/club', keywords: ['klub', 'o klubie', 'eat the ball'] },
    { label: 'Historia', url: '/club/history', keywords: ['historia', 'dzieje klubu'] },
    { label: 'Władze klubu', url: '/club/board', keywords: ['wladze', 'władze', 'zarzad', 'zarząd'] },
    { label: 'Obiekt', url: '/club/venue', keywords: ['obiekt', 'hala', 'arena', 'miejsce'] },
    { label: 'Oferta biznesowa', url: '/club/business', keywords: ['biznes', 'oferta', 'wspolpraca', 'współpraca'] },
    { label: 'Sukcesy', url: '/club/success', keywords: ['sukcesy', 'osiagniecia', 'osiągnięcia'] },
    { label: 'Sponsorzy', url: '/club/sponsors', keywords: ['sponsorzy', 'sponsor', 'partnerzy', 'partner', 'partner strategiczny', 'partner technologiczny'] },
    { label: 'Kontakt', url: '/contact', keywords: ['kontakt', 'email', 'telefon', 'biuro'] },
    { label: 'Marketing', url: '/contact#marketing', keywords: ['marketing', 'media', 'współpraca medialna', 'promocja'] },
    { label: 'Rozgrywki', url: '/schedule', keywords: ['rozgrywki', 'liga', 'mecze'] },
    { label: 'Terminarz', url: '/schedule', keywords: ['terminarz', 'kalendarz', 'najblizszy mecz', 'najbliższy mecz', 'mecz'] },
    { label: 'III liga mężczyzn ŁZKosz', url: '/schedule/third-league', keywords: ['iii liga', '3 liga', 'trzecia liga', 'lzkosz', 'łzkosz'] },
    { label: 'Terminarz ŁZKosz', url: '/schedule/lzkosz', keywords: ['lzkosz', 'łzkosz', 'terminarz lzkosz', 'terminarz łzkosz'] },
    { label: 'Tabela', url: '/schedule/table', keywords: ['tabela', 'ranking', 'pozycja'] },
    { label: 'Terminarz 3x3', url: '/schedule/3x3', keywords: ['3x3', 'trzy na trzy', 'koszykówka 3x3'] },
    { label: 'Turnieje 3x3', url: '/schedule/3x3', keywords: ['turnieje 3x3', 'turniej 3x3', 'zawody 3x3'] },
    { label: 'Drużyna 3x3', url: '/team/3x3', keywords: ['zespół 3x3', 'drużyna 3x3', 'team 3x3'] },
    { label: 'Drużyna', url: '/team', keywords: ['drużyna', 'team', 'skład'] },
    { label: 'Zawodnicy', url: '/team/players', keywords: ['zawodnicy', 'koszykarze', 'gracze', 'pierwsza piątka'] },
    { label: 'Sztab szkoleniowy', url: '/team/staff', keywords: ['sztab', 'trenerzy', 'trener', 'szkoleniowy'] },
    { label: 'Zawodnicy 3x3', url: '/team/3x3', keywords: ['zawodnicy 3x3', 'gracze 3x3'] },
    { label: 'Bilety', url: '/tickets', keywords: ['bilety', 'ticket', 'wejsciowki', 'wejściówki'] },
    { label: 'Sklep', url: '/shop', keywords: ['sklep', 'shop', 'merch', 'koszulki'] },
    { label: 'Akademia', url: '/academy', keywords: ['akademia', 'treningi', 'grupy', 'u15', 'u17', 'u19', 'dzieci', 'młodzież'] },
];

const normalizeSearchText = (value) => value
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/ł/g, 'l')
    .trim();

function escapeHtml(value) {
    return value.replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[character]));
}

function scoreSearchItem(item, normalizedQuery) {
    const normalizedLabel = normalizeSearchText(item.label);
    const normalizedKeywords = item.keywords.map(normalizeSearchText);

    if (normalizedLabel === normalizedQuery) return 100;
    if (normalizedLabel.startsWith(normalizedQuery)) return 90;
    if (normalizedKeywords.some((keyword) => keyword === normalizedQuery)) return 80;
    if (normalizedKeywords.some((keyword) => keyword.startsWith(normalizedQuery))) return 70;
    if (normalizedLabel.includes(normalizedQuery)) return 60;
    if (normalizedKeywords.some((keyword) => keyword.includes(normalizedQuery))) return 50;

    return 0;
}

function getSearchMatches(query) {
    const normalizedQuery = normalizeSearchText(query);
    if (!normalizedQuery) return [];

    const seen = new Set();

    return searchIndex
        .map((item) => ({ ...item, score: scoreSearchItem(item, normalizedQuery) }))
        .filter((item) => item.score > 0)
        .sort((a, b) => b.score - a.score || a.label.localeCompare(b.label, 'pl'))
        .filter((item) => {
            if (seen.has(item.label)) return false;
            seen.add(item.label);
            return true;
        })
        .slice(0, 7);
}

function getInlineCompletion(query, matches) {
    if (!query || matches.length === 0) return '';

    const typed = normalizeSearchText(query);
    const match = matches.find((item) => normalizeSearchText(item.label).startsWith(typed));
    return match ? match.label : '';
}

function renderSearchGhost(input, ghost, matches) {
    const completion = getInlineCompletion(input.value, matches);

    if (!completion || normalizeSearchText(completion) === normalizeSearchText(input.value)) {
        ghost.innerHTML = '';
        return;
    }

    ghost.innerHTML = `<span class="text-transparent">${escapeHtml(input.value)}</span><span>${escapeHtml(completion.slice(input.value.length))}</span>`;
}

function renderSearchPanel(input, panel, matches, activeIndex = -1) {
    input.setAttribute('aria-expanded', matches.length > 0 ? 'true' : 'false');

    if (matches.length === 0) {
        panel.classList.add('hidden');
        panel.innerHTML = '';
        return;
    }

    panel.classList.remove('hidden');
    panel.innerHTML = matches
        .map((item, index) => `
            <button
                type="button"
                class="etb-search-option flex w-full items-center justify-between gap-3 border-b border-white/10 px-4 py-3 text-left font-semibold transition last:border-b-0 hover:bg-yellow-400 hover:text-black ${index === activeIndex ? 'bg-yellow-400 text-black' : 'text-white'}"
                role="option"
                aria-selected="${index === activeIndex ? 'true' : 'false'}"
                data-search-url="${escapeHtml(item.url)}"
                data-search-label="${escapeHtml(item.label)}"
            >
                <span>${escapeHtml(item.label)}</span>
            </button>
        `)
        .join('');
}

function initializeSiteSearch() {
    const input = document.getElementById('etb-search');
    const panel = document.getElementById('etb-search-panel');
    const ghost = document.getElementById('etb-search-ghost');
    if (!input || !panel || !ghost || input.dataset.etbSearchReady === 'true') return;

    input.dataset.etbSearchReady = 'true';
    let matches = [];
    let activeIndex = -1;

    const sync = () => {
        matches = getSearchMatches(input.value);
        activeIndex = -1;
        renderSearchGhost(input, ghost, matches);
        renderSearchPanel(input, panel, matches, activeIndex);
    };

    const goTo = (item) => {
        if (!item) return;
        input.value = item.label;
        window.etbAnalytics.track('search');
        window.location.href = item.url;
    };

    input.addEventListener('input', sync);
    input.addEventListener('focus', sync);

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            panel.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
            return;
        }

        if ((event.key === 'Tab' || event.key === 'ArrowRight') && ghost.textContent.trim()) {
            event.preventDefault();
            input.value = getInlineCompletion(input.value, matches);
            sync();
            return;
        }

        if (event.key === 'ArrowDown' && matches.length > 0) {
            event.preventDefault();
            activeIndex = (activeIndex + 1) % matches.length;
            renderSearchPanel(input, panel, matches, activeIndex);
            return;
        }

        if (event.key === 'ArrowUp' && matches.length > 0) {
            event.preventDefault();
            activeIndex = (activeIndex - 1 + matches.length) % matches.length;
            renderSearchPanel(input, panel, matches, activeIndex);
            return;
        }

        if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            goTo(matches[activeIndex]);
        }
    });

    panel.addEventListener('mousedown', (event) => {
        const option = event.target.closest('[data-search-url]');
        if (!option) return;

        event.preventDefault();
        goTo({
            label: option.dataset.searchLabel,
            url: option.dataset.searchUrl,
        });
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('#etb-site-search')) return;
        panel.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
    });
}

document.addEventListener('DOMContentLoaded', initializeSiteSearch);

window.etbSearch = function etbSearch() {
    const input = document.getElementById('etb-search');
    if (!input) return;

    const query = input.value.trim();
    if (!query) return;

    window.etbAnalytics.track('search');

    const result = getSearchMatches(query)[0];

    if (result) {
        window.location.href = result.url;
        return;
    }

    const main = document.getElementById('app-main');
    if (main && normalizeSearchText(main.innerText).includes(normalizeSearchText(query))) {
        window.find(input.value);
        return;
    }

    alert('Brak wyników dla podanej frazy.');
};

window.materialsCarousel = function materialsCarousel(items) {
    return {
        items,
        page: 0,
        timer: null,
        get chunks() {
            const result = [];
            for (let i = 0; i < this.items.length; i += 4) {
                result.push(this.items.slice(i, i + 4));
            }
            return result;
        },
        get visibleItems() {
            return this.chunks[this.page] ?? [];
        },
        goTo(index) {
            this.page = index;
        },
        start() {
            if (this.chunks.length <= 1) return;
            this.timer = setInterval(() => {
                this.page = (this.page + 1) % this.chunks.length;
            }, 5000);
        },
    };
};

