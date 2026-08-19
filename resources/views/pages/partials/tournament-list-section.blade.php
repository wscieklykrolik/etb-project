@php
    $sectionId = $sectionId ?? null;
    $headingLevel = $headingLevel ?? 1;
@endphp

<section @if($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-28">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">{{ $pageEyebrow ?? 'ETB 3x3' }}</p>
        @if ($headingLevel === 1)
            <h1 class="mt-2 text-4xl font-black text-white">{{ $pageTitle ?? 'Terminarz turniejów 3x3' }}</h1>
        @else
            <h2 class="mt-2 text-3xl font-black text-white">{{ $pageTitle ?? 'Terminarz turniejów 3x3' }}</h2>
        @endif
    </div>

    @foreach ([['title' => 'Nadchodzące turnieje', 'items' => $upcomingTournaments], ['title' => 'Zakończone turnieje', 'items' => $finishedTournaments]] as $group)
        <section class="mb-12">
            <h3 class="mb-5 border-l-4 border-yellow-400 pl-4 text-2xl font-black text-white">{{ $group['title'] }}</h3>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($group['items'] as $tournament)
                    <a href="{{ route('three-x-three.tournaments.show', $tournament) }}" class="group overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl transition hover:-translate-y-1 hover:border-yellow-400/70">
                        <div class="aspect-[16/10] bg-zinc-900">
                            @if ($tournament->image_path)
                                <img src="{{ \App\Support\MediaStorage::url($tournament->image_path) }}" alt="{{ $tournament->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center text-sm font-bold uppercase tracking-widest text-zinc-600">3x3</div>
                            @endif
                        </div>
                        <div class="p-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-yellow-400">{{ $tournament->date?->format('d.m.Y') }} / {{ $tournament->location }}</p>
                            <h4 class="mt-2 text-xl font-black text-white">{{ $tournament->name }}</h4>
                            @if ($tournament->description)
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-400">{{ $tournament->description }}</p>
                            @endif
                            @if ($tournament->categories->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($tournament->categories as $category)
                                        <span class="rounded-full bg-yellow-400/10 px-2.5 py-1 text-xs font-black text-yellow-300 ring-1 ring-yellow-400/30">{{ $category->category->label() }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="rounded border border-dashed border-zinc-700 p-6 text-zinc-400">{{ $emptyMessage ?? 'Brak pozycji w tej sekcji.' }}</p>
                @endforelse
            </div>
        </section>
    @endforeach
</section>

