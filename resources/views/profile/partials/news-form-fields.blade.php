@php
    $item = $item ?? null;
    $selectedType = old('type', $item?->type ?? \App\Models\News::TYPE_ARTICLE);
@endphp

<div class="space-y-4" x-data="{ type: @js($selectedType) }">
    <div>
        <p class="text-sm font-black text-gray-800">Typ aktualności</p>
        <div class="mt-2 grid gap-3 md:grid-cols-3">
            @foreach ([
                \App\Models\News::TYPE_ARTICLE => ['icon' => 'newspaper', 'title' => 'Artykuł', 'hint' => 'Pełny artykuł, zdjęcie główne i treść.'],
                \App\Models\News::TYPE_GALLERY => ['icon' => 'images', 'title' => 'Galeria zdjęć', 'hint' => 'Tytuł, krótki opis i do 100 zdjęć.'],
                \App\Models\News::TYPE_VIDEO => ['icon' => 'youtube', 'title' => 'Film YouTube', 'hint' => 'Tytuł, opis i osadzony film.'],
            ] as $type => $option)
                <label class="flex min-h-28 cursor-pointer rounded-lg border p-4 transition hover:border-yellow-400" :class="type === '{{ $type }}' ? 'border-yellow-400 bg-yellow-50 ring-2 ring-yellow-200' : 'border-gray-200 bg-white'">
                    <input type="radio" name="type" value="{{ $type }}" x-model="type" class="sr-only">
                    <span class="grid w-full grid-cols-[2.5rem_1fr] items-start gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-950 text-yellow-400">
                            @if ($type === \App\Models\News::TYPE_VIDEO)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M23.5 6.2s-.2-1.7-.9-2.4c-.9-.9-1.9-.9-2.3-1C17.2 2.5 12 2.5 12 2.5h0s-5.2 0-8.3.3c-.4.1-1.4.1-2.3 1C.7 4.5.5 6.2.5 6.2S.2 8.1.2 10v2c0 1.9.3 3.8.3 3.8s.2 1.7.9 2.4c.9.9 2.1.9 2.6 1 1.9.2 8 .3 8 .3s5.2 0 8.3-.3c.4-.1 1.4-.1 2.3-1 .7-.7.9-2.4.9-2.4s.3-1.9.3-3.8v-2c0-1.9-.3-3.8-.3-3.8zM9.5 14.5v-5l5 2.5-5 2.5z"/>
                                </svg>
                            @else
                                <i data-lucide="{{ $option['icon'] }}" class="h-5 w-5"></i>
                            @endif
                        </span>
                        <span class="pt-0.5">
                            <span class="block min-h-5 font-black leading-5 text-gray-950">{{ $option['title'] }}</span>
                            <span class="mt-2 block text-xs leading-5 text-gray-600">{{ $option['hint'] }}</span>
                        </span>
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Tytuł</span>
        <input name="title" required value="{{ old('title', $item?->title) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block" x-show="type === 'article'">
        <span class="text-sm font-medium text-gray-700">Treść</span>
        <textarea name="content" rows="8" :required="type === 'article'" class="mt-1 w-full rounded border-gray-300">{{ old('content', $item?->content) }}</textarea>
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Krótki opis</span>
        <textarea name="excerpt" rows="3" :required="type !== 'article'" class="mt-1 w-full rounded border-gray-300">{{ old('excerpt', $item?->excerpt) }}</textarea>
    </label>

    <div class="grid gap-4 md:grid-cols-2">
        <label class="block" x-show="type === 'article'">
            <span class="text-sm font-medium text-gray-700">Autor artykułu</span>
            <input name="article_author" value="{{ old('article_author', $item?->article_author) }}" class="mt-1 w-full rounded border-gray-300">
        </label>

        <label class="block" x-show="type === 'article' || type === 'gallery'">
            <span class="text-sm font-medium text-gray-700">Autor zdjęć</span>
            <input name="photo_author" value="{{ old('photo_author', $item?->photo_author) }}" class="mt-1 w-full rounded border-gray-300">
        </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <label class="block">
            <span class="text-sm font-medium text-gray-700">Data publikacji</span>
            <input name="publish_at" type="datetime-local" value="{{ old('publish_at', $item?->publish_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded border-gray-300">
        </label>

        <label class="flex items-center gap-2 rounded border border-gray-200 p-3">
            <input name="is_visible" type="checkbox" value="1" class="rounded border-gray-300 text-yellow-500" @checked(old('is_visible', $item?->is_visible ?? true))>
            <span class="text-sm font-medium text-gray-700">Widoczne publicznie</span>
        </label>

        <label class="block" x-show="type === 'article'">
            <span class="text-sm font-medium text-gray-700">Zdjęcie główne</span>
            <input name="main_image" type="file" accept="image/*" class="mt-1 w-full rounded border border-gray-300 bg-white text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
        </label>
    </div>

    <label class="block" x-show="type === 'video'">
        <span class="text-sm font-medium text-gray-700">Link do filmu YouTube</span>
        <input name="video_url" type="url" :required="type === 'video'" value="{{ old('video_url', $item?->video_url) }}" placeholder="https://www.youtube.com/watch?v=..." class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block" x-show="type === 'article' || type === 'gallery'">
        <span class="text-sm font-medium text-gray-700" x-text="type === 'gallery' ? 'Galeria zdjęć, maksymalnie 100 plików' : 'Dodatkowa galeria zdjęć, maksymalnie 100 plików'"></span>
        <input name="gallery[]" type="file" accept="image/*" multiple class="mt-1 w-full rounded border border-gray-300 bg-white text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
    </label>
</div>
