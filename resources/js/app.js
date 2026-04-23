import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

Alpine.start();

/*
|--------------------------------------------------------------------------
| LUCIDE ICONS
|--------------------------------------------------------------------------
*/
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

/*
|--------------------------------------------------------------------------
| COUNTDOWN
|--------------------------------------------------------------------------
*/
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
            el.innerHTML = "Mecz trwa 🔥";
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((diff / (1000 * 60)) % 60);
        const seconds = Math.floor((diff / 1000) % 60);

        el.innerHTML = `
            <span>${days} dni</span> :
            <span>${hours} godz</span> :
            <span>${minutes} min</span> :
            <span>${seconds} sek</span>
        `;
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

window.etbSearch = function etbSearch() {
    const input = document.getElementById('etb-search');
    if (!input) return;

    const query = input.value.trim().toLowerCase();
    if (!query) return;

    const main = document.getElementById('app-main');
    if (!main) return;

    const text = main.innerText.toLowerCase();
    if (!text.includes(query)) {
        alert('Brak wyników dla podanej frazy.');
        return;
    }

    window.find(input.value);
};
