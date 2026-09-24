<div
    x-data="cookieConsentManager()"
    x-init="init()"
    @etb:open-cookie-settings.window="reopenBanner()"
    @keydown.escape.window="closePreferences()"
>
    <section
        x-cloak
        x-show="initialized && showBanner"
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="translate-y-6 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-6 opacity-0"
        class="fixed inset-x-0 bottom-0 z-[80] p-3 sm:p-5"
        aria-label="Ustawienia plików cookies"
    >
        <div class="etb-cookie-banner mx-auto max-w-6xl rounded-2xl border border-white/15 bg-zinc-950/95 shadow-2xl shadow-black/60 backdrop-blur-xl">
            <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                <div class="flex items-start gap-4">
                    <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-yellow-400 text-black sm:flex" aria-hidden="true">
                        <i data-lucide="cookie" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <h2 x-ref="bannerHeading" tabindex="-1" class="text-xl font-black text-white outline-none">Dbamy o Twoją prywatność</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-300">
                            Niezbędne cookies zapewniają prawidłowe i bezpieczne działanie strony. Za Twoją zgodą możemy też używać cookies funkcjonalnych, analitycznych i marketingowych. Wybór możesz zmienić w każdej chwili.
                        </p>
                        <a href="{{ route('cookies.policy') }}" class="mt-2 inline-flex text-sm font-bold text-yellow-400 underline decoration-yellow-400/40 underline-offset-4 hover:text-yellow-300">
                            Dowiedz się więcej w polityce cookies
                        </a>
                    </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-3 lg:w-[31rem]">
                    <button type="button" @click="rejectOptional()" class="min-h-11 rounded-xl border border-white bg-white px-4 py-2.5 text-sm font-black text-black transition hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-zinc-950">
                        Odrzuć opcjonalne
                    </button>
                    <button type="button" @click="openPreferences()" class="min-h-11 rounded-xl border border-zinc-500 bg-zinc-900 px-4 py-2.5 text-sm font-black text-white transition hover:border-yellow-400 hover:text-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-zinc-950">
                        Dostosuj
                    </button>
                    <button type="button" @click="acceptAll()" class="min-h-11 rounded-xl border border-yellow-400 bg-yellow-400 px-4 py-2.5 text-sm font-black text-black transition hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 focus:ring-offset-zinc-950">
                        Akceptuję wszystkie
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div
        x-cloak
        x-show="showPreferences"
        x-transition.opacity
        class="fixed inset-0 z-[90] flex items-end justify-center bg-black/75 p-0 backdrop-blur-sm sm:items-center sm:p-5"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cookie-preferences-title"
        @click.self="closePreferences()"
    >
        <div class="etb-cookie-dialog max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-t-2xl border border-zinc-700 bg-zinc-950 shadow-2xl sm:rounded-2xl">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-zinc-800 bg-zinc-950/95 p-5 backdrop-blur sm:p-6">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-400">Twoja prywatność</p>
                    <h2 id="cookie-preferences-title" x-ref="preferencesHeading" tabindex="-1" class="mt-1 text-2xl font-black text-white outline-none">Ustawienia cookies</h2>
                    <p class="mt-2 text-sm leading-6 text-zinc-400">Wybierz kategorie, na które wyrażasz zgodę. Cookies niezbędnych nie można wyłączyć.</p>
                </div>
                <button type="button" @click="closePreferences()" class="rounded-lg p-2 text-zinc-300 transition hover:bg-zinc-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-yellow-400" aria-label="Zamknij ustawienia cookies">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <div class="space-y-3 p-5 sm:p-6">
                <div class="rounded-xl border border-emerald-400/30 bg-emerald-400/5 p-4">
                    <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                        <div>
                            <h3 class="font-black text-white">Niezbędne</h3>
                            <p class="mt-1 text-sm leading-5 text-zinc-400">Sesja, bezpieczeństwo, koszyk i zapamiętanie ustawień cookies.</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-black text-emerald-300">Zawsze aktywne</span>
                    </div>
                </div>

                <label class="block cursor-pointer rounded-xl border border-zinc-700 bg-zinc-900/70 p-4 transition hover:border-zinc-500">
                    <span class="flex items-center justify-between gap-4">
                        <span>
                            <span class="block font-black text-white">Funkcjonalne</span>
                            <span class="mt-1 block text-sm leading-5 text-zinc-400">Zapamiętują dodatkowe ustawienia i ułatwiają korzystanie ze strony.</span>
                        </span>
                        <input type="checkbox" x-model="preferences.functional" class="h-5 w-5 shrink-0 rounded border-zinc-500 bg-zinc-800 text-yellow-400 focus:ring-yellow-400 focus:ring-offset-zinc-900">
                    </span>
                </label>

                <label class="block cursor-pointer rounded-xl border border-zinc-700 bg-zinc-900/70 p-4 transition hover:border-zinc-500">
                    <span class="flex items-center justify-between gap-4">
                        <span>
                            <span class="block font-black text-white">Analityczne</span>
                            <span class="mt-1 block text-sm leading-5 text-zinc-400">Pomagają anonimowo mierzyć odwiedziny i poprawiać działanie serwisu.</span>
                        </span>
                        <input type="checkbox" x-model="preferences.analytics" class="h-5 w-5 shrink-0 rounded border-zinc-500 bg-zinc-800 text-yellow-400 focus:ring-yellow-400 focus:ring-offset-zinc-900">
                    </span>
                </label>

                <label class="block cursor-pointer rounded-xl border border-zinc-700 bg-zinc-900/70 p-4 transition hover:border-zinc-500">
                    <span class="flex items-center justify-between gap-4">
                        <span>
                            <span class="block font-black text-white">Marketingowe i multimedia zewnętrzne</span>
                            <span class="mt-1 block text-sm leading-5 text-zinc-400">Pozwalają wyświetlać materiały YouTube oraz mierzyć skuteczność przyszłych działań promocyjnych.</span>
                        </span>
                        <input type="checkbox" x-model="preferences.marketing" class="h-5 w-5 shrink-0 rounded border-zinc-500 bg-zinc-800 text-yellow-400 focus:ring-yellow-400 focus:ring-offset-zinc-900">
                    </span>
                </label>
            </div>

            <div class="sticky bottom-0 grid gap-2 border-t border-zinc-800 bg-zinc-950/95 p-5 backdrop-blur sm:grid-cols-3 sm:p-6">
                <button type="button" @click="rejectOptional()" class="min-h-11 rounded-xl border border-white bg-white px-4 py-2.5 text-sm font-black text-black transition hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-white">
                    Odrzuć opcjonalne
                </button>
                <button type="button" @click="savePreferences()" class="min-h-11 rounded-xl border border-yellow-400 px-4 py-2.5 text-sm font-black text-yellow-300 transition hover:bg-yellow-400/10 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    Zapisz wybrane
                </button>
                <button type="button" @click="acceptAll()" class="min-h-11 rounded-xl border border-yellow-400 bg-yellow-400 px-4 py-2.5 text-sm font-black text-black transition hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                    Akceptuję wszystkie
                </button>
            </div>
        </div>
    </div>

    <div x-cloak x-show="savedNotice" x-transition class="w-max max-w-[calc(100%-2rem)] fixed bottom-5 left-1/2 z-[100] -translate-x-1/2 rounded-xl border border-emerald-400/30 bg-zinc-950 px-4 py-3 text-sm font-bold text-white shadow-2xl" role="status" aria-live="polite">
        Ustawienia cookies zostały zapisane.
    </div>

    <noscript>
        <div class="fixed inset-x-0 bottom-0 z-[80] border-t border-yellow-400 bg-zinc-950 p-4 text-center text-sm text-white">
            Ta strona używa wyłącznie niezbędnych cookies. Włącz JavaScript, aby zarządzać zgodami na opcjonalne kategorie.
        </div>
    </noscript>
</div>
