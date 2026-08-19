@php
    $sectionId = $sectionId ?? null;
    $headingLevel = $headingLevel ?? 1;
@endphp

<section @if($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-28">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">ETB 3x3</p>
        @if ($headingLevel === 1)
            <h1 class="mt-2 text-4xl font-black text-white">Drużyna 3x3</h1>
        @else
            <h2 class="mt-2 text-3xl font-black text-white">Drużyna 3x3</h2>
        @endif
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @forelse ($members as $member)
            <article class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl transition hover:-translate-y-1 hover:border-yellow-400/70">
                <div class="aspect-[4/5] bg-zinc-900">
                    @if ($member->photo_path)
                        <img src="{{ \App\Support\MediaStorage::url($member->photo_path) }}" alt="{{ $member->name }}" class="h-full w-full object-cover object-top">
                    @else
                        <div class="flex h-full items-center justify-center text-sm font-bold uppercase tracking-widest text-zinc-600">3x3</div>
                    @endif
                </div>
                <div class="p-5">
                    @if ($member->is_coach)
                        <span class="rounded bg-yellow-400 px-2 py-1 text-xs font-black uppercase text-black">Trener</span>
                    @endif
                    <h3 class="mt-3 text-xl font-black text-white">{{ $member->name }}</h3>
                    <p class="mt-1 text-sm font-bold uppercase tracking-wide text-yellow-400">{{ $member->role }}</p>
                    @if ($member->description)
                        <p class="mt-4 text-sm leading-6 text-zinc-400">{{ $member->description }}</p>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded border border-dashed border-zinc-700 p-6 text-zinc-400">Drużyna 3x3 zostanie opublikowana wkrótce.</p>
        @endforelse
    </div>
</section>

