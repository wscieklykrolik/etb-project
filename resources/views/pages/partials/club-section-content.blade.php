@php
    $sectionId = $sectionId ?? $clubSection->slug;
@endphp

<section id="{{ $sectionId }}" class="scroll-mt-28">
    <div class="mb-6">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">Klub</p>
        <h2 class="mt-2 text-3xl font-black text-white">{{ $clubSection->title }}</h2>
    </div>

    <div class="{{ $clubSection->images->isNotEmpty() ? 'grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-start' : 'max-w-4xl' }}">
        @if (filled($clubSection->body))
            <div class="whitespace-pre-line text-base leading-8 text-zinc-100 sm:text-lg">{{ $clubSection->body }}</div>
        @endif

        @if ($clubSection->images->isNotEmpty())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                @foreach ($clubSection->images as $image)
                    <figure>
                        <img src="{{ \App\Support\MediaStorage::url($image->image_path) }}" alt="{{ $image->alt ?: $clubSection->title }}" class="aspect-[16/10] w-full rounded object-cover">
                        @if (filled($image->caption))
                            <figcaption class="mt-2 text-xs leading-5 text-zinc-400">{{ $image->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</section>

