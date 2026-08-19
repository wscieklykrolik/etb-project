@extends('layouts.app')

@section('content')
<section
    class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8"
    x-data="newsLightbox(@js($news->images->map(fn ($image) => \App\Support\MediaStorage::url($image->path))->values()))"
    @keydown.arrow-left.window="previous()"
    @keydown.arrow-right.window="next()"
    @keydown.escape.window="close()"
>
    <a href="{{ route('news.index') }}" class="text-sm font-semibold text-yellow-400 hover:text-yellow-300">← Wróć do aktualności</a>

    @if ($isPreview ?? false)
        <div class="mt-6 rounded border border-yellow-400/50 bg-yellow-400/10 p-4 text-sm font-semibold text-yellow-200">
            Podgląd administratora. Ten materiał nie musi być jeszcze widoczny publicznie.
        </div>
    @endif

    <article class="mt-8 overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-2xl">
        @if ($news->type === \App\Models\News::TYPE_VIDEO && $news->youtubeEmbedUrl())
            <div class="aspect-video bg-black">
                <iframe
                    src="{{ $news->youtubeEmbedUrl() }}"
                    title="{{ $news->title }}"
                    class="h-full w-full"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen></iframe>
            </div>
        @elseif ($news->type === \App\Models\News::TYPE_ARTICLE && $news->main_image_path)
            <img src="{{ \App\Support\MediaStorage::url($news->main_image_path) }}" alt="{{ $news->title }}" class="aspect-[16/7] w-full object-cover">
        @endif

        <div class="p-6 sm:p-10">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">{{ $news->typeLabel() }} · {{ ($news->publish_at ?? $news->created_at)?->format('d.m.Y H:i') }}</p>
            <h1 class="mt-3 text-4xl font-black text-white">{{ $news->title }}</h1>

            @if ($news->excerpt)
                <p class="mt-5 text-lg leading-8 text-zinc-300">{{ $news->excerpt }}</p>
            @endif

            @if ($news->type === \App\Models\News::TYPE_ARTICLE)
                <div class="prose prose-invert mt-8 max-w-none text-zinc-300">{!! nl2br(e($news->content)) !!}</div>
            @endif

            @if ($news->images->isNotEmpty())
                <div class="mt-10">
                    <h2 class="mb-4 text-2xl font-black text-white">Galeria</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($news->images as $image)
                            <button type="button" class="relative aspect-square overflow-hidden rounded border border-zinc-800 transition hover:border-yellow-400 focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/60" @click="openGallery({{ $loop->index }})">
                                <img src="{{ \App\Support\MediaStorage::url($image->path) }}" alt="Zdjęcie galerii" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (($news->type === \App\Models\News::TYPE_ARTICLE && $news->article_author) || (($news->type === \App\Models\News::TYPE_ARTICLE || $news->type === \App\Models\News::TYPE_GALLERY) && $news->photo_author))
                <div class="mt-10 border-t border-zinc-800 pt-5 text-sm font-semibold text-zinc-400 sm:text-right">
                    @if ($news->type === \App\Models\News::TYPE_ARTICLE && $news->article_author)
                        <p>Autor artykułu: {{ $news->article_author }}</p>
                    @endif

                    @if (($news->type === \App\Models\News::TYPE_ARTICLE || $news->type === \App\Models\News::TYPE_GALLERY) && $news->photo_author)
                        <p class="mt-1">Autor zdjęć: {{ $news->photo_author }}</p>
                    @endif
                </div>
            @endif
        </div>
    </article>

    <div x-show="image" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 sm:p-8" @click="close()">
        <div class="relative w-full max-w-5xl rounded-lg border border-zinc-700 bg-zinc-950 p-3 shadow-2xl sm:p-4" @click.stop>
            <button type="button" class="absolute right-3 top-3 z-10 rounded-full bg-black/70 p-2 text-white transition hover:bg-yellow-400 hover:text-black" @click="close()" aria-label="Zamknij podgląd">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>

            <div class="flex items-center gap-3">
                <button type="button" x-show="hasMultipleImages" class="hidden rounded-full bg-white/10 p-3 text-white transition hover:bg-yellow-400 hover:text-black sm:inline-flex" @click="previous()" aria-label="Poprzednie zdjęcie">
                    <i data-lucide="chevron-left" class="h-7 w-7"></i>
                </button>

                <div class="flex min-h-[18rem] flex-1 items-center justify-center rounded bg-black sm:min-h-[28rem]">
                    <img :src="image" alt="Powiększone zdjęcie galerii" class="max-h-[72vh] max-w-full rounded object-contain">
                </div>

                <button type="button" x-show="hasMultipleImages" class="hidden rounded-full bg-white/10 p-3 text-white transition hover:bg-yellow-400 hover:text-black sm:inline-flex" @click="next()" aria-label="Następne zdjęcie">
                    <i data-lucide="chevron-right" class="h-7 w-7"></i>
                </button>
            </div>

            <div class="mt-3 flex items-center justify-between gap-3 text-sm text-zinc-300">
                <button type="button" x-show="hasMultipleImages" class="inline-flex items-center gap-2 rounded border border-zinc-700 px-3 py-2 font-semibold hover:border-yellow-400 hover:text-yellow-300 sm:hidden" @click="previous()">
                    <i data-lucide="chevron-left" class="h-4 w-4"></i>
                    Poprzednie
                </button>
                <span class="mx-auto" x-show="activeIndex !== null">
                    <span x-text="activeIndex + 1"></span> / <span x-text="images.length"></span>
                </span>
                <button type="button" x-show="hasMultipleImages" class="inline-flex items-center gap-2 rounded border border-zinc-700 px-3 py-2 font-semibold hover:border-yellow-400 hover:text-yellow-300 sm:hidden" @click="next()">
                    Następne
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                </button>
            </div>
        </div>
    </div>
</section>
@endsection

