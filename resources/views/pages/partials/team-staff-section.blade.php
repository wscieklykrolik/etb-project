@php
    $sectionId = $sectionId ?? null;
    $headingLevel = $headingLevel ?? 1;
@endphp

<section @if($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-28">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">Drużyna ETB</p>
        @if ($headingLevel === 1)
            <h1 class="mt-2 text-4xl font-black text-white">Sztab szkoleniowy</h1>
        @else
            <h2 class="mt-2 text-3xl font-black text-white">Sztab szkoleniowy</h2>
        @endif
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @forelse ($staff as $person)
            <article class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl transition hover:-translate-y-1 hover:border-yellow-400/70">
                <div class="aspect-[4/5] bg-zinc-900">
                    @if ($person->photo_path)
                        <img src="{{ \App\Support\MediaStorage::url($person->photo_path) }}" alt="{{ $person->name }}" class="h-full w-full object-cover object-top">
                    @else
                        <div class="flex h-full items-center justify-center text-sm font-bold uppercase tracking-widest text-zinc-600">ETB</div>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="text-xl font-black text-white">{{ $person->name }}</h3>
                    <p class="mt-1 text-sm font-bold uppercase tracking-wide text-yellow-400">{{ $person->role }}</p>
                    @if ($person->description)
                        <p class="mt-4 text-sm leading-6 text-zinc-400">{{ $person->description }}</p>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded border border-dashed border-zinc-700 p-6 text-zinc-400">Sztab zostanie opublikowany wkrótce.</p>
        @endforelse
    </div>
</section>

