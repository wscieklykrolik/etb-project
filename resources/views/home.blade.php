@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $articleImage = function ($item) {
        $path = $item->main_image_path ?: $item->images->first()?->path;

        return $path ? \App\Support\MediaStorage::url($path) : null;
    };
@endphp

@section('content')
<div class="bg-black text-white">
    <section
        class="relative min-h-[560px] overflow-hidden bg-zinc-950 text-white"
        x-data="{ active: 0, total: {{ max($heroNews->count(), 1) }} }"
        x-init="setInterval(() => active = (active + 1) % total, 5000)"
    >
        @forelse($heroNews as $item)
            @php($image = $articleImage($item))
            <a
                href="{{ route('news.show', $item) }}"
                class="absolute inset-0 transition-opacity duration-700"
                x-show="active === {{ $loop->index }}"
                x-transition.opacity
            >
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $item->title }}" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full bg-zinc-950">
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,#facc15_0%,transparent_40%),radial-gradient(circle_at_bottom_left,#facc15_0%,transparent_30%)] opacity-20"></div>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 mx-auto max-w-7xl px-6 pb-16">
                    <span class="inline-block rounded-full bg-yellow-400 px-3 py-1 text-xs font-black uppercase tracking-[0.2em] text-black mb-3">{{ $item->publish_at?->format('d.m.Y') ?? $item->created_at?->format('d.m.Y') }}</span>
                    <h1 class="mt-2 max-w-4xl text-4xl font-black uppercase leading-tight md:text-6xl">{{ $item->title }}</h1>
                    <p class="mt-4 max-w-2xl text-base font-medium text-zinc-300 md:text-lg">{{ $item->excerpt ?: Str::limit(strip_tags($item->content), 150) }}</p>
                </div>
            </a>
        @empty
            <div class="absolute inset-0 bg-zinc-950">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,#facc15_0%,transparent_40%),radial-gradient(circle_at_bottom_left,#facc15_0%,transparent_30%)] opacity-20"></div>
            </div>
            <div class="relative mx-auto flex min-h-[560px] max-w-7xl items-end px-6 pb-16">
                <div>
                    <span class="inline-block rounded-full bg-yellow-400 px-3 py-1 text-xs font-black uppercase tracking-[0.2em] text-black">ETB Łódź</span>
                    <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-tight md:text-6xl">Oficjalna strona klubu</h1>
                    <p class="mt-5 max-w-2xl text-base font-semibold text-zinc-200 md:text-lg">Wkrótce pojawią się tutaj najnowsze informacje, galerie i zapowiedzi.</p>
                </div>
            </div>
        @endforelse

        @if($heroNews->count() > 1)
            <div class="absolute bottom-8 right-6 flex gap-3 md:right-20">
                @foreach($heroNews as $item)
                    <button type="button" class="h-1.5 w-12 rounded-full bg-white/25 transition-all hover:bg-white/50" :class="{ 'bg-yellow-400 w-16': active === {{ $loop->index }} }" @click="active = {{ $loop->index }}">
                        <span class="sr-only">{{ $item->title }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    <section class="bg-zinc-950/50 py-16 border-y border-zinc-800/50">
        <div class="mx-auto max-w-7xl px-6">
            <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-400">Mecze pierwszej drużyny</p>
            <h2 class="mt-2 text-4xl font-black uppercase italic text-white">Terminarz</h2>
            <div class="mt-8 grid items-center gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.12fr)_minmax(0,1fr)]">
                @include('pages.partials.match-public-card', ['match' => $lastFinishedMatch, 'label' => 'Ostatni mecz'])

                @foreach($upcomingMatches as $match)
                    @include('pages.partials.match-public-card', ['match' => $match, 'label' => $loop->first ? 'Najbliższy mecz' : 'Kolejny mecz', 'featured' => $loop->first])
                @endforeach

                @if(! $lastFinishedMatch && $upcomingMatches->isEmpty())
                    <div class="rounded-xl border border-dashed border-zinc-700 bg-zinc-900/50 p-8 text-zinc-500 text-center lg:col-span-3">Terminarz zostanie uzupełniony po dodaniu meczów w panelu.</div>
                @endif
            </div>
        </div>
    </section>

    <section class="bg-black py-16">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-400">Aktualności</p>
                    <h2 class="mt-2 text-4xl font-black uppercase">Z życia drużyny</h2>
                </div>
                <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-yellow-400 px-6 py-3 text-sm font-black uppercase text-black hover:bg-white transition-all shadow-lg shadow-yellow-400/20">Wszystkie aktualności <span aria-hidden="true">→</span></a>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                @foreach($featuredArticles as $item)
                    @php($image = $articleImage($item))
                    <a href="{{ route('news.show', $item) }}" class="group overflow-hidden rounded-xl bg-zinc-900 border border-zinc-800 transition-all hover:-translate-y-1 hover:border-yellow-400/50 hover:shadow-xl hover:shadow-yellow-400/5">
                        <div class="aspect-[16/9] bg-zinc-800 overflow-hidden">
                            @if($image)
                                <img src="{{ $image }}" alt="{{ $item->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="p-6">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-yellow-400">{{ $item->publish_at?->format('d.m.Y') ?? $item->created_at?->format('d.m.Y') }}</p>
                            <h3 class="mt-3 text-2xl font-black">{{ $item->title }}</h3>
                            <p class="mt-3 text-sm text-zinc-400 leading-relaxed">{{ $item->excerpt ?: Str::limit(strip_tags($item->content), 130) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @foreach($moreArticles as $item)
                    <a href="{{ route('news.show', $item) }}" class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 transition-all hover:-translate-y-1 hover:border-yellow-400/50 hover:bg-zinc-900 hover:shadow-lg hover:shadow-yellow-400/5">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-zinc-500">{{ $item->publish_at?->format('d.m.Y') ?? $item->created_at?->format('d.m.Y') }}</p>
                        <h3 class="mt-3 text-lg font-black text-white">{{ $item->title }}</h3>
                        <p class="mt-3 text-sm text-zinc-500 leading-relaxed">{{ $item->excerpt ?: Str::limit(strip_tags($item->content), 95) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-zinc-950 py-16 text-white border-t border-zinc-800/50">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex items-center gap-4 mb-2">
                <div class="h-px flex-1 bg-zinc-800"></div>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-400">Pierwsza piątka</p>
                <div class="h-px flex-1 bg-zinc-800"></div>
            </div>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 class="text-4xl font-black uppercase">Skład ETB</h2>
                <a href="{{ route('team.players') }}" class="inline-flex items-center gap-2 rounded-lg bg-yellow-400 px-6 py-3 text-sm font-black uppercase text-black hover:bg-white transition-all shadow-lg shadow-yellow-400/20">Pełny skład <span aria-hidden="true">→</span></a>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
                @forelse($startingFive as $player)
                    <a href="{{ route('team.players.show', $player) }}" class="group overflow-hidden rounded-xl bg-zinc-900 border border-zinc-800 transition-all hover:-translate-y-1 hover:border-yellow-400/50 hover:shadow-xl hover:shadow-yellow-400/5">
                        <div class="aspect-[4/5] bg-zinc-800 overflow-hidden">
                            @if($player->photo_path)
                                <img src="{{ \App\Support\MediaStorage::url($player->photo_path) }}" alt="{{ $player->full_name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="p-5">
                            <p class="text-sm font-black text-yellow-400">#{{ $player->number }}</p>
                            <h3 class="mt-1 text-xl font-black">{{ $player->full_name }}</h3>
                            <p class="mt-1 text-sm text-zinc-500">{{ $player->positionLabel() }}</p>
                        </div>
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-700 bg-zinc-900/50 p-8 text-zinc-500 text-center sm:col-span-2 lg:col-span-5">Zaznacz zawodników jako pierwszą piątkę w panelu admina, a pojawią się tutaj.</div>
                @endforelse
            </div>
        </div>
    </section>

    @if($shopProducts->isNotEmpty())
        <section class="bg-black py-16 border-t border-zinc-800/50">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-400">Sklep</p>
                        <h2 class="mt-2 text-4xl font-black uppercase">Kupuj ETB</h2>
                    </div>
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-yellow-400 px-6 py-3 text-sm font-black uppercase text-black hover:bg-white transition-all shadow-lg shadow-yellow-400/20">Zobacz wszystkie <span aria-hidden="true">→</span></a>
                </div>
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($shopProducts as $product)
                        <a href="{{ route('shop.show', $product) }}" class="group overflow-hidden rounded-xl bg-zinc-900 border border-zinc-800 transition-all hover:-translate-y-1 hover:border-yellow-400/50 hover:shadow-xl hover:shadow-yellow-400/5">
                            <div class="aspect-square bg-zinc-800 overflow-hidden flex items-center justify-center">
                                @if($product->images && $img = $product->images[0] ?? null)
                                    <img src="{{ \App\Support\MediaStorage::url($img) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <span class="text-4xl font-black text-zinc-700">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div class="p-5">
                                <p class="text-xs font-black uppercase tracking-wider text-yellow-400">{{ $product->category?->name }}</p>
                                <h3 class="mt-2 text-lg font-black">{{ $product->name }}</h3>
                                <p class="mt-4 text-xl font-black text-yellow-400">{{ $product->displayPrice() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($sponsors->isNotEmpty())
        <section class="bg-zinc-950 py-12 border-t border-zinc-800/50">
            <div class="mx-auto max-w-7xl px-6">
                <p class="text-center text-xs font-black uppercase tracking-[0.28em] text-yellow-400 mb-8">Partnerzy i sponsorzy</p>
                <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-6">
                    @foreach($sponsors as $sponsor)
                        <a href="{{ $sponsor->url ?: '#' }}" target="_blank" rel="noopener noreferrer" class="group flex min-h-24 min-w-32 items-center justify-center rounded-lg border border-white/5 bg-white/[0.03] px-5 py-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-yellow-400/70 hover:bg-yellow-400/10 hover:shadow-lg hover:shadow-yellow-400/20 focus:outline-none focus:ring-2 focus:ring-yellow-400" title="{{ $sponsor->name }}">
                            @if($sponsor->logo_path)
                                <img src="{{ \App\Support\MediaStorage::url($sponsor->logo_path) }}" alt="{{ $sponsor->name }}" class="max-h-20 w-full object-contain opacity-65 grayscale transition-all duration-300 group-hover:opacity-100 group-hover:grayscale-0 group-hover:drop-shadow-[0_0_18px_rgba(250,204,21,0.55)]">
                            @else
                                <span class="text-sm font-bold text-zinc-500 transition-colors group-hover:text-yellow-400">{{ $sponsor->name }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="relative overflow-hidden bg-gradient-to-br from-yellow-400 to-yellow-500 py-16 text-black">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.3)_0%,transparent_50%)]"></div>
        <a href="{{ route('academy') }}" class="academy-cta-link group relative mx-auto flex max-w-7xl flex-col gap-6 px-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-black/60">Akademia ETB</p>
                <h2 class="mt-2 text-4xl font-black uppercase md:text-5xl">Kochasz koszykówkę? Dołącz do nas</h2>
            </div>
            <span class="academy-cta-arrow flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-black text-3xl font-black text-yellow-400 transition-[transform,background-color,color] duration-300 group-hover:scale-110 group-hover:bg-white group-hover:text-black group-focus-visible:scale-110 group-focus-visible:bg-white group-focus-visible:text-black" aria-hidden="true">
                <svg class="academy-cta-arrow-icon h-10 w-10" viewBox="0 0 24 24" fill="none">
                    <path d="M4 12h14" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="m13 7 5 5-5 5" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </a>
    </section>

    @if($faqQuestions->isNotEmpty())
        <section class="bg-black py-16 text-white border-t border-zinc-800/50">
            <div class="mx-auto max-w-7xl px-6">
                <div class="max-w-3xl">
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-400">Pytania i odpowiedzi</p>
                    <h2 class="mt-2 text-4xl font-black uppercase">Najczęstsze pytania</h2>
                    <p class="mt-4 text-sm leading-relaxed text-zinc-400">Odpowiedzi na pytania, które najczęściej pojawiają się przed kontaktem z klubem, akademią i pierwszą drużyną ETB.</p>
                </div>

                <div class="mt-8 divide-y divide-zinc-800 rounded-xl border border-zinc-800 bg-zinc-950/70">
                    @foreach($faqQuestions as $question)
                        <details class="group p-6" {{ $loop->first ? 'open' : '' }}>
                            <summary class="flex cursor-pointer list-none items-start justify-between gap-4 text-left">
                                <h3 class="text-lg font-black text-white">{{ $question->question }}</h3>
                                <span class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-yellow-400 text-xl font-black text-black transition group-open:rotate-45">+</span>
                            </summary>
                            <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-zinc-300">{{ $question->answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
        <script type="application/ld+json">{!! $faqSchemaJson !!}</script>
    @endif
</div>
@endsection

