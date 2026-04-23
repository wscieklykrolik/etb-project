<footer class="mt-16">
    <section class="bg-yellow-400 text-black py-8">
        <div class="max-w-7xl mx-auto px-6">
            <h3 class="text-2xl font-extrabold mb-4">Partnerzy</h3>
            <div class="grid md:grid-cols-3 gap-4 text-sm font-semibold">
                <div class="bg-yellow-300 p-4 rounded">Partner strategiczny</div>
                <div class="bg-yellow-300 p-4 rounded">Partnerzy klubu</div>
                <div class="bg-yellow-300 p-4 rounded">Sponsorzy</div>
            </div>
        </div>
    </section>

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
