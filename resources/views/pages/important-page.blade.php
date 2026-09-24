@extends('layouts.app')

@section('content')
<article class="mx-auto max-w-4xl px-4 py-12 text-white sm:px-6">
    <h1 class="mb-8 text-3xl font-black">{{ $title }}</h1>
    @if (filled($page->body))
        <div class="whitespace-pre-wrap break-words text-base leading-8">{{ $page->body }}</div>
    @endif
    @if ($page->image_path)
        <img src="{{ \App\Support\MediaStorage::url($page->image_path) }}" alt="{{ $title }}" class="mt-8 h-auto max-w-full">
    @endif
    @if (blank($page->body) && !$page->image_path)
        <p class="text-zinc-300">Treść zostanie wkrótce uzupełniona.</p>
    @endif
</article>
@endsection
