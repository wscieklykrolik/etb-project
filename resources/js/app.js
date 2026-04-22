import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function () {

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
