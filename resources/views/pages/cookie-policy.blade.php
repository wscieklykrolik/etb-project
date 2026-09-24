@extends('layouts.app')

@section('content')
<article class="mx-auto max-w-5xl px-4 py-12 text-white sm:px-6 lg:px-8">
    <header class="max-w-3xl">
        <p class="text-sm font-black uppercase tracking-[0.25em] text-yellow-400">Prywatność</p>
        <h1 class="mt-2 text-4xl font-black sm:text-5xl">Polityka cookies</h1>
        <p class="mt-4 text-lg leading-8 text-zinc-300">Tutaj wyjaśniamy, czym są cookies, których używa ETB Łódź, po co są potrzebne i jak możesz nimi zarządzać.</p>
        <p class="mt-3 text-sm text-zinc-500">Ostatnia aktualizacja: 14 września 2026 r.</p>
    </header>

    <div class="mt-10 rounded-2xl border border-yellow-400/25 bg-yellow-400/10 p-5 sm:p-6">
        <h2 class="text-xl font-black text-white">Zarządzaj swoją zgodą</h2>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-300">Możesz w każdej chwili zmienić albo wycofać zgodę. Wycofanie zgody jest równie łatwe jak jej udzielenie i nie wpływa na dostęp do podstawowej treści strony.</p>
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('etb:open-cookie-settings'))" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-yellow-400 px-5 py-3 text-sm font-black text-black transition hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 focus:ring-offset-zinc-950">
            <i data-lucide="settings-2" class="h-4 w-4"></i>
            Otwórz ustawienia cookies
        </button>
    </div>

    <div class="mt-12 space-y-10 text-zinc-300">
        <section>
            <h2 class="text-2xl font-black text-white">1. Czym są cookies?</h2>
            <p class="mt-3 leading-7">Cookies to niewielkie informacje zapisywane w przeglądarce lub odczytywane z urządzenia podczas korzystania ze strony. Mogą być potrzebne do utrzymania sesji i koszyka, zapamiętania ustawień, pomiaru odwiedzin albo wyświetlenia materiału z zewnętrznego serwisu.</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">2. Kto odpowiada za cookies?</h2>
            <p class="mt-3 leading-7">Administratorem serwisu jest ETB Łódź. W sprawach dotyczących prywatności i cookies możesz napisać na adres <a href="mailto:etb.3x3@gmail.com" class="font-bold text-yellow-400 underline underline-offset-4">etb.3x3@gmail.com</a>.</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">3. Podstawa korzystania z cookies</h2>
            <p class="mt-3 leading-7">Cookies niezbędne są używane tylko w zakresie koniecznym do transmisji danych, bezpieczeństwa i dostarczenia funkcji wyraźnie żądanej przez użytkownika. Pozostałe kategorie uruchamiamy dopiero po dobrowolnej, konkretnej i świadomej zgodzie. Podstawą są w szczególności art. 399–400 ustawy Prawo komunikacji elektronicznej oraz — gdy dochodzi do przetwarzania danych osobowych — art. 6 ust. 1 lit. a i art. 7 RODO.</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">4. Kategorie cookies</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-emerald-400/25 bg-emerald-400/5 p-5">
                    <h3 class="font-black text-white">Niezbędne</h3>
                    <p class="mt-2 text-sm leading-6">Obsługują sesję, logowanie, zabezpieczenia formularzy, koszyk i zapis Twojego wyboru. Nie wymagają zgody i nie można ich wyłączyć w panelu.</p>
                </div>
                <div class="rounded-xl border border-zinc-700 bg-zinc-900 p-5">
                    <h3 class="font-black text-white">Funkcjonalne</h3>
                    <p class="mt-2 text-sm leading-6">Zapamiętują dodatkowe preferencje poprawiające wygodę korzystania. Obecnie serwis nie uruchamia odrębnych narzędzi tej kategorii.</p>
                </div>
                <div class="rounded-xl border border-zinc-700 bg-zinc-900 p-5">
                    <h3 class="font-black text-white">Analityczne</h3>
                    <p class="mt-2 text-sm leading-6">Google Analytics 4 mierzy odsłony, przewijanie, kliknięcia wychodzące, użycie wyszukiwarki i zagregowane etapy zakupowe. Jest uruchamiany wyłącznie po zgodzie analitycznej.</p>
                </div>
                <div class="rounded-xl border border-zinc-700 bg-zinc-900 p-5">
                    <h3 class="font-black text-white">Marketingowe i multimedia zewnętrzne</h3>
                    <p class="mt-2 text-sm leading-6">Pozwalają wyświetlać filmy YouTube. Dostawca może przetwarzać informacje o urządzeniu i aktywności zgodnie ze swoimi zasadami.</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">5. Wykaz używanych cookies i technologii</h2>
            <div class="mt-5 overflow-x-auto rounded-xl border border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-700 text-left text-sm">
                    <thead class="bg-zinc-900 text-white">
                        <tr>
                            <th class="px-4 py-3 font-black">Nazwa lub usługa</th>
                            <th class="px-4 py-3 font-black">Dostawca</th>
                            <th class="px-4 py-3 font-black">Cel</th>
                            <th class="px-4 py-3 font-black">Kategoria i czas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800 bg-zinc-950">
                        <tr>
                            <td class="px-4 py-4 font-mono text-xs text-yellow-300">{{ config('session.cookie') }}</td>
                            <td class="px-4 py-4">ETB Łódź</td>
                            <td class="px-4 py-4">Utrzymanie bezpiecznej sesji, logowania i koszyka.</td>
                            <td class="px-4 py-4">Niezbędne; do {{ config('session.lifetime') }} minut bezczynności.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 font-mono text-xs text-yellow-300">XSRF-TOKEN</td>
                            <td class="px-4 py-4">ETB Łódź</td>
                            <td class="px-4 py-4">Ochrona formularzy i żądań przed podszyciem się pod użytkownika, jeśli token jest używany przez aplikację.</td>
                            <td class="px-4 py-4">Niezbędne; czas sesji.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 font-mono text-xs text-yellow-300">etb_cookie_consent</td>
                            <td class="px-4 py-4">ETB Łódź</td>
                            <td class="px-4 py-4">Zapamiętanie wybranych kategorii i daty decyzji.</td>
                            <td class="px-4 py-4">Niezbędne; 6 miesięcy.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 font-mono text-xs text-yellow-300">_ga oraz _ga_*</td>
                            <td class="px-4 py-4">Google Ireland Limited</td>
                            <td class="px-4 py-4">Rozróżnianie anonimowych instancji przeglądarki, pomiar odsłon i interakcji oraz tworzenie statystyk serwisu. ETB nie wysyła treści wyszukiwania, danych kontaktowych, adresów ani numerów zamówień.</td>
                            <td class="px-4 py-4">Analityczne; maksymalnie 6 miesięcy.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 font-semibold text-white">YouTube i youtube-nocookie.com</td>
                            <td class="px-4 py-4">Google Ireland Limited</td>
                            <td class="px-4 py-4">Odtwarzanie materiałów wideo. Po wyrażeniu zgody dostawca może używać własnych identyfikatorów, cookies lub pamięci przeglądarki.</td>
                            <td class="px-4 py-4">Marketingowe i multimedia zewnętrzne; okres zależny od dostawcy.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-sm leading-6 text-zinc-400">Lista może ulec zmianie po dodaniu nowych funkcji. W takim przypadku zaktualizujemy politykę i — gdy będzie to wymagane — ponownie poprosimy o zgodę.</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">6. Google Analytics, YouTube i odbiorcy danych</h2>
            <p class="mt-3 leading-7">Po zgodzie analitycznej strona łączy się z Google Analytics 4. Sygnały reklamowe, personalizacja reklam i magazyny reklamowe pozostają wyłączone. Materiały YouTube są domyślnie zablokowane i po osobnej zgodzie marketingowej ładujemy je w trybie zwiększonej prywatności z domeny youtube-nocookie.com. Korzystanie z tych usług może powodować przetwarzanie danych przez Google Ireland Limited. Szczegóły opisuje <a href="https://policies.google.com/privacy?hl=pl" target="_blank" rel="noopener noreferrer" class="font-bold text-yellow-400 underline underline-offset-4">polityka prywatności Google</a>.</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">7. Jak zmienić lub wycofać zgodę?</h2>
            <p class="mt-3 leading-7">Użyj przycisku „Ustawienia cookies” w stopce albo przycisku na początku tej strony. Po wyłączeniu wcześniej aktywnej kategorii strona zostanie odświeżona, opcjonalne osadzenia przestaną działać, a dostępne dla ETB cookies tej kategorii zostaną usunięte. Cookies podmiotów trzecich mogą wymagać usunięcia w ustawieniach przeglądarki.</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">8. Ustawienia przeglądarki</h2>
            <p class="mt-3 leading-7">Możesz blokować lub usuwać cookies również w ustawieniach swojej przeglądarki. Zablokowanie cookies niezbędnych może uniemożliwić logowanie, utrzymanie koszyka albo prawidłową obsługę formularzy.</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">9. Twoje prawa</h2>
            <p class="mt-3 leading-7">Jeżeli dane z cookies są danymi osobowymi, możesz żądać dostępu do nich, sprostowania, usunięcia, ograniczenia przetwarzania lub przeniesienia, a także wycofać zgodę w dowolnym momencie. Masz również prawo złożyć skargę do Prezesa Urzędu Ochrony Danych Osobowych.</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white">10. Zmiany polityki</h2>
            <p class="mt-3 leading-7">Politykę możemy aktualizować po zmianie funkcji strony, dostawców lub przepisów. Istotna zmiana celów albo zakresu opcjonalnych cookies spowoduje ponowne wyświetlenie panelu zgody.</p>
        </section>
    </div>
</article>
@endsection
