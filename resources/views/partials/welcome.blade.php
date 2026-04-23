@php
    $isLive = false;

    if($nextMatch) {
        $start = \Carbon\Carbon::parse($nextMatch->match_date);
        $end = $start->copy()->addHours(2);

        $now = now();

        $timeLive = $now->between($start, $end);
        $manualLive = !empty($nextMatch->stream_link);

        $isLive = $timeLive || $manualLive;
    }
@endphp

@if($nextMatch)

    <section class="relative h-[60vh] flex items-center">

        <!-- TŁO -->
        <div class="absolute inset-0">
            <img src="{{ $nextMatch->image ? asset('storage/'.$nextMatch->image) : '/images/default.jpg' }}"
                 class="w-full h-full object-cover opacity-40">
        </div>

        <!-- TREŚĆ -->
        <div class="relative z-10 max-w-7xl mx-auto px-6 text-white">

            <!-- LIVE -->
            @if($isLive)
                <div class="mb-2 flex items-center gap-2 text-red-500 font-bold">
                    <span class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                    LIVE
                </div>
            @endif

            <!-- TYPO -->
            <div class="mb-4 text-yellow-400 uppercase tracking-widest font-semibold">
                Najbliższy mecz
                ({{ $nextMatch->is_home ? 'DOM' : 'WYJAZD' }})
            </div>

            <!-- MECZ -->
            <h1 class="text-4xl md:text-6xl font-extrabold mb-4">
                ETB <span class="text-yellow-400">vs</span>
                {{ $nextMatch->opponent }}
            </h1>

            <!-- INFO -->
            <div class="text-lg mb-4 text-gray-300">
                {{ \Carbon\Carbon::parse($nextMatch->match_date)->format('d.m.Y H:i') }}
                • {{ $nextMatch->location }}
            </div>

            <!-- COUNTDOWN -->
            <div id="countdown"
                 data-date="{{ $nextMatch->match_date }}"
                 class="text-xl font-bold text-yellow-400 mb-6">
            </div>

            <!-- BUTTONY -->
            <div class="flex gap-4">

                @if($isLive && $nextMatch->stream_link)
                    <a href="{{ $nextMatch->stream_link }}" target="_blank"
                       class="bg-red-600 text-white px-6 py-3 font-bold rounded hover:bg-red-500 transition">
                        🔴 Oglądaj mecz
                    </a>
                @else
                    <a href="{{ route('tickets') }}" class="ajax-link bg-yellow-400 text-black px-6 py-3 font-bold rounded hover:bg-yellow-300 transition">Kup bilety</a>
                @endif

                <a href="{{ route('schedule') }}" class="ajax-link border border-white px-6 py-3 rounded hover:bg-yellow-400 hover:text-black transition">Zobacz więcej</a>

            </div>

        </div>
    </section>

@else

    <div class="h-[60vh] flex items-center justify-center text-white text-2xl">
        Brak zaplanowanych meczów
    </div>

@endif
