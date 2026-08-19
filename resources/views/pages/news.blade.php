@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">ETB</p>
        <h1 class="mt-2 text-4xl font-black text-white">Aktualności</h1>
    </div>

    @if ($featuredNews->isNotEmpty())
        <div class="mb-12 overflow-x-auto pb-4">
            <div class="flex gap-5">
                @foreach ($featuredNews as $item)
                    <a href="{{ route('news.show', $item) }}" class="group min-w-[18rem] overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl transition hover:-translate-y-1 hover:border-yellow-400/70 sm:min-w-[26rem]">
                        @php($previewImage = $item->previewImagePath())
                        <div class="aspect-[16/9] bg-zinc-900">
                            @if ($previewImage)
                                <img src="{{ \App\Support\MediaStorage::url($previewImage) }}" alt="{{ $item->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center text-sm font-bold uppercase tracking-widest text-zinc-600">ETB Aktualności</div>
                            @endif
                        </div>
                        <div class="p-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-yellow-400">{{ $item->typeLabel() }} · {{ ($item->publish_at ?? $item->created_at)?->format('d.m.Y H:i') }}</p>
                            <h2 class="mt-2 text-xl font-black text-white">{{ $item->title }}</h2>
                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-400">{{ $item->excerpt ?: str($item->content)->stripTags()->limit(150) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($newsItems as $item)
            <a href="{{ route('news.show', $item) }}" class="group overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl transition hover:-translate-y-1 hover:border-yellow-400/70">
                @php($previewImage = $item->previewImagePath())
                <div class="aspect-[16/10] bg-zinc-900">
                    @if ($previewImage)
                        <img src="{{ \App\Support\MediaStorage::url($previewImage) }}" alt="{{ $item->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    @else
                        <div class="flex h-full items-center justify-center text-sm font-bold uppercase tracking-widest text-zinc-600">{{ $item->type === \App\Models\News::TYPE_VIDEO ? 'ETB Wideo' : 'ETB Aktualności' }}</div>
                    @endif
                </div>
                <div class="p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-yellow-400">{{ $item->typeLabel() }} · {{ ($item->publish_at ?? $item->created_at)?->format('d.m.Y H:i') }}</p>
                    <h2 class="mt-2 text-xl font-black text-white">{{ $item->title }}</h2>
                    <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-400">{{ $item->excerpt ?: str($item->content)->stripTags()->limit(150) }}</p>
                </div>
            </a>
        @empty
            <p class="rounded border border-dashed border-zinc-700 p-6 text-zinc-400">Brak opublikowanych aktualności.</p>
        @endforelse
    </div>
</section>
@endsection

