import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

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

window.adminPanel = function adminPanel(config) {
    return {
        openModal: null,
        matchFilter: 'all',
        newsFilter: 'all',
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

