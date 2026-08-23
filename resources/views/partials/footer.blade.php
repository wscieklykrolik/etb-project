<footer class="mt-16">
    @php
        $footerSponsorCategories = $footerSponsorCategories ?? collect();
    @endphp

    @if ($footerSponsorCategories->isNotEmpty())
        <section id="partners" class="bg-zinc-950 py-12 text-white">
            <div class="mx-auto max-w-7xl px-6">
                <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-400">Partnerzy</p>
                <h3 class="mt-2 text-3xl font-black uppercase">ETB Wspierają</h3>

                <div class="mt-8 divide-y divide-white/15 border-y border-white/15">
                    @foreach ($footerSponsorCategories as $category)
                        <div class="grid gap-5 py-7 lg:grid-cols-[16rem_1fr] lg:items-center">
                            <h4 class="text-lg font-black uppercase italic text-white">{{ $category->name }}</h4>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6">
                                @foreach ($category->sponsors as $sponsor)
                                    <a href="{{ $sponsor->url }}" target="_blank" rel="noopener noreferrer" class="group flex min-h-28 items-center justify-center rounded-lg border border-white/10 bg-white p-5 shadow-sm transition duration-200 hover:z-10 hover:scale-105 hover:border-yellow-400 hover:shadow-xl hover:shadow-yellow-400/20 focus:outline-none focus:ring-2 focus:ring-yellow-400" aria-label="{{ $sponsor->name }}">
                                        <img src="{{ \App\Support\MediaStorage::url($sponsor->logo_path) }}" alt="{{ $sponsor->name }}" class="max-h-20 w-full object-contain transition duration-200 group-hover:scale-105">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="bg-zinc-900 text-zinc-200 py-10 border-t border-zinc-700">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-5 gap-6 text-sm">
            <div><h4 class="font-bold text-white mb-3">ETB Łódź</h4><p>Oficjalna strona klubu ETB Łódź.</p></div>
            <div><h4 class="font-bold text-white mb-3">Biuro</h4><p>biuro@etb-lodz.pl</p></div>
            <div><h4 class="font-bold text-white mb-3">Marketing i media</h4><p>media@etb-lodz.pl</p></div>
            <div><h4 class="font-bold text-white mb-3">Bilety i Akademia</h4><p><a href="{{ route('tickets') }}" class="hover:text-yellow-400">Bilety</a><br><a href="{{ route('academy') }}" class="hover:text-yellow-400">Akademia</a></p></div>
            <div>
                <h4 class="font-bold text-white mb-3">Ważne linki</h4>
                <ul class="space-y-1">
                    <li><a href="#" class="hover:text-yellow-400">Polityka prywatności</a></li>
                    <li><a href="#" class="hover:text-yellow-400">Regulamin sklepu</a></li>
                    <li><a href="#" class="hover:text-yellow-400">Biuro prasowe</a></li>
                    <li><a href="#" class="hover:text-yellow-400">Regulamin zwrotów</a></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="bg-black text-center text-zinc-400 text-xs py-3 border-t border-zinc-800">
        © {{ now()->year }} ETB Łódź. Wszelkie prawa zastrzeżone.
    </section>
</footer>

