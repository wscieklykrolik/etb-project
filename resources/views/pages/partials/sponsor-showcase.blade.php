@php
    $sponsorCategories = $sponsorCategories ?? collect();
    $hasSponsors = $sponsorCategories->isNotEmpty();
    $sponsorDisplayMode = $sponsorDisplayMode ?? 'grid';
    $sponsorIntro = filled($clubSection?->body)
        ? $clubSection->body
        : 'Partnerzy ETB wyeksponowani tak, jak powinny pracować logotypy: spokojnie, czytelnie i na jasnym tle.';
@endphp

<section id="sponsors" class="scroll-mt-28">
    <div class="mb-6">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">Klub</p>
        <h2 class="mt-2 text-3xl font-black text-white">Sponsorzy</h2>
        <p class="mt-3 max-w-4xl whitespace-pre-line text-base leading-7 text-zinc-300">{{ $sponsorIntro }}</p>
    </div>

    <div class="rounded-lg bg-white p-5 text-slate-950 shadow-xl sm:p-7 lg:p-8">
        @if ($hasSponsors)
            <div class="space-y-9">
                @foreach ($sponsorCategories as $category)
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-500">{{ $category->name }}</h3>
                        @if ($sponsorDisplayMode === 'detailed')
                            <div class="mt-4 space-y-4">
                                @foreach ($category->sponsors as $sponsor)
                                    <a href="{{ $sponsor->url }}" target="_blank" rel="noopener noreferrer" class="group grid gap-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-yellow-400 hover:shadow-lg hover:shadow-yellow-400/20 focus:outline-none focus:ring-2 focus:ring-yellow-400 sm:grid-cols-[minmax(12rem,0.8fr)_minmax(0,1.7fr)] sm:items-center" aria-label="{{ $sponsor->name }}">
                                        <div class="flex min-h-36 items-center justify-center rounded-lg bg-slate-50 p-5">
                                            <img src="{{ \App\Support\MediaStorage::url($sponsor->logo_path) }}" alt="{{ $sponsor->name }}" class="max-h-28 w-full object-contain transition group-hover:scale-105">
                                        </div>
                                        <div>
                                            <h4 class="text-xl font-black text-slate-950">{{ $sponsor->name }}</h4>
                                            @if (filled($sponsor->description))
                                                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $sponsor->description }}</p>
                                            @endif
                                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-black text-yellow-700">Odwiedź stronę sponsora <span aria-hidden="true">→</span></span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($category->sponsors as $sponsor)
                                    <a href="{{ $sponsor->url }}" target="_blank" rel="noopener noreferrer" class="group flex min-h-44 items-center justify-center rounded-lg border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-0.5 hover:border-yellow-400 hover:shadow-lg hover:shadow-yellow-400/20 focus:outline-none focus:ring-2 focus:ring-yellow-400" aria-label="{{ $sponsor->name }}">
                                        <img src="{{ \App\Support\MediaStorage::url($sponsor->logo_path) }}" alt="{{ $sponsor->name }}" class="max-h-28 w-full object-contain transition group-hover:scale-105">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="rounded-lg border border-dashed border-slate-300 p-6 text-sm text-slate-500">Sponsorzy pojawią się tutaj po dodaniu ich w panelu admina.</p>
        @endif
    </div>
</section>

