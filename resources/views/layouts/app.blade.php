<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-black text-white">

@include('partials.navbar')

<main id="app-main">
    @yield('content')
</main>

<!-- Simple AJAX navigation: ładuje tylko zawartość main, zachowuje statyczny navbar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicjalizuje handlery dla linków z klasą .ajax-link
    function initAjaxLinks() {
        document.querySelectorAll('a.ajax-link').forEach(link => {
            // unikaj wielokrotnego przypięcia
            if (link.dataset.ajaxAttached) return;
            link.dataset.ajaxAttached = '1';

            link.addEventListener('click', async function(e) {
                const href = link.getAttribute('href');
                // pozwól normalne działanie dla zewnętrznych lub pusta href
                if (!href || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('#')) return;
                // jeśli link prowadzi do innego hosta, zostaw normalne przeładowanie
                try {
                    const url = new URL(href, location.origin);
                    if (url.origin !== location.origin) return;
                } catch (err) {
                    // nieprawidłowy URL — fallback
                    return;
                }

                e.preventDefault();

                try {
                    const res = await fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('Network response was not ok');

                    const text = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(text, 'text/html');

                    // szukamy nowej zawartości main (najpierw #app-main, potem main)
                    const newMain = doc.querySelector('#app-main') || doc.querySelector('main');
                    const curMain = document.querySelector('#app-main') || document.querySelector('main');

                    if (newMain && curMain) {
                        curMain.innerHTML = newMain.innerHTML;
                        // zaktualizuj URL i historię
                        history.pushState({ ajax: true }, '', href);
                        window.scrollTo(0,0);
                        // ponownie przypnij handlery (np. linki wewnątrz nowo załadowanej zawartości)
                        initAjaxLinks();
                    } else {
                        // fallback — pełne przeładowanie
                        location.href = href;
                    }
                } catch (err) {
                    console.error('AJAX navigation failed:', err);
                    location.href = href;
                }
            });
        });
    }

    initAjaxLinks();

    // Obsługa back/forward
    window.addEventListener('popstate', function(e) {
        const href = location.href;
        fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => {
                if (!r.ok) throw new Error('Network response not ok');
                return r.text();
            })
            .then(text => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                const newMain = doc.querySelector('#app-main') || doc.querySelector('main');
                const curMain = document.querySelector('#app-main') || document.querySelector('main');
                if (newMain && curMain) {
                    curMain.innerHTML = newMain.innerHTML;
                    initAjaxLinks();
                } else {
                    location.reload();
                }
            })
            .catch(() => { location.reload(); });
    });
});
</script>

</body>
</html>
