@extends('layouts.app')

@section('content')
    <div class="bg-gray-100 py-10" x-data="newsLightbox()">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <article class="rounded border border-gray-200 bg-white p-6 shadow-sm">
                @if ($news->main_image_path)
                    <img src="{{ \App\Support\MediaStorage::url($news->main_image_path) }}" alt="{{ $news->title }}" class="mb-6 h-80 w-full rounded object-cover">
                @endif

                <h1 class="text-3xl font-bold">{{ $news->title }}</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Autor: {{ $news->author->name ?? 'Nieznany autor' }} ·
                    {{ $news->publish_at?->format('d.m.Y H:i') ?? $news->created_at?->format('d.m.Y H:i') }}
                </p>

                <div class="mt-6 whitespace-pre-line text-gray-800">{{ $news->content }}</div>

                @if ($news->images->isNotEmpty())
                    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-5">
                        @foreach ($news->images->take(5) as $image)
                            <button type="button" class="relative" @click="open(@js(\App\Support\MediaStorage::url($image->path)))">
                                <img src="{{ \App\Support\MediaStorage::url($image->path) }}" alt="Zdjęcie z galerii" class="h-28 w-full rounded object-cover">
                                @if ($loop->last && $news->images->count() > 5)
                                    <span class="absolute inset-0 flex items-center justify-center rounded bg-black/60 text-xl font-bold text-white">+{{ $news->images->count() - 5 }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </article>
        </div>

        <div x-show="image" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click="close()">
            <img :src="image" alt="Zdjęcie z galerii" class="max-h-[90vh] max-w-full rounded bg-white object-contain">
        </div>
    </div>
@endsection

