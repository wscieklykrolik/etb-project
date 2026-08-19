@php
    $sectionId = $sectionId ?? null;
    $headingLevel = $headingLevel ?? 1;
@endphp

<section @if($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-28">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">Drużyna ETB</p>
        @if ($headingLevel === 1)
            <h1 class="mt-2 text-4xl font-black text-white">Zawodnicy</h1>
        @else
            <h2 class="mt-2 text-3xl font-black text-white">Zawodnicy</h2>
        @endif
    </div>

    <div class="space-y-12">
        @foreach ($positions as $position)
            @php($players = $playersByPosition->get($position->value, collect()))
            @continue($players->isEmpty())

            <section>
                <h3 class="mb-5 border-l-4 border-yellow-400 pl-4 text-2xl font-black text-white">{{ $position->label() }}</h3>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    @foreach ($players as $player)
                        @php($cardClasses = 'group overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl transition hover:-translate-y-1 hover:border-yellow-400/70')
                        @if ($player->publish_description)
                            <a href="{{ route('team.players.show', $player) }}" class="{{ $cardClasses }}">
                        @else
                            <article class="{{ $cardClasses }} opacity-95">
                        @endif
                                <div class="aspect-[4/5] bg-zinc-900">
                                    @if ($player->photo_path)
                                        <img src="{{ \App\Support\MediaStorage::url($player->photo_path) }}" alt="{{ $player->full_name }}" class="h-full w-full object-cover object-top transition duration-300 group-hover:scale-105">
                                    @else
                                        <div class="flex h-full items-center justify-center text-sm font-bold uppercase tracking-widest text-zinc-600">ETB</div>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <p class="text-4xl font-black text-yellow-400">#{{ $player->number }}</p>
                                    <h4 class="mt-2 text-xl font-black text-white">{{ $player->full_name }}</h4>
                                    <p class="mt-1 text-sm font-semibold uppercase tracking-wide text-zinc-400">{{ $player->positionLabel() }}</p>
                                </div>
                        @if ($player->publish_description)
                            </a>
                        @else
                            </article>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</section>

