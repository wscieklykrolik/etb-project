import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
Alpine.start();

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

window.adjustFontSize = function adjustFontSize(change) {
    const root = document.documentElement;
    const current = parseFloat(getComputedStyle(root).fontSize);
    const next = Math.min(22, Math.max(14, current + change * 16));
    root.style.fontSize = `${next}px`;
};

const searchIndex = [
    { label: 'Aktualności', url: '/news', keywords: ['aktualnosci', 'news'] },
    { label: 'Klub', url: '/club', keywords: ['klub'] },
    { label: 'Rozgrywki', url: '/schedule', keywords: ['rozgrywki', 'liga', 'terminarz'] },
    { label: 'Kontakt', url: '/contact', keywords: ['kontakt', 'email', 'telefon'] },
    { label: 'Drużyna', url: '/team', keywords: ['druzyna', 'zawodnicy'] },
    { label: 'Zawodnicy 3x3', url: '/team-3x3/players', keywords: ['3x3', 'trzy na trzy', 'zawodnicy 3x3', 'druzyna 3x3'] },
    { label: 'Tabela', url: '/schedule/table', keywords: ['tabela'] },
    { label: 'Terminarz EŁZKosz', url: '/schedule/lzkosz', keywords: ['łzkosz', 'lzkosz', 'terminarz łzkosz'] },
    { label: 'III liga mężczyzn ŁZKosz', url: '/schedule/third-league', keywords: ['iii liga', '3 liga', 'elkosz', 'lzkosz'] },
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
