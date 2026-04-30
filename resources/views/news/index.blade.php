@extends('layouts.app')
@section('content')
<div class="py-6"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"><h1 class="text-2xl font-semibold mb-4">News</h1>@can('create', \App\Models\News::class)<a href="{{ route('news.create') }}" class="underline">Create news</a>@endcan</div>
@forelse($newsItems as $item)<article class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"><h2 class="text-xl font-medium"><a class="underline" href="{{ route('news.show', $item) }}">{{ $item->title }}</a></h2><p class="mt-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($item->content, 140) }}</p></article>@empty<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">No news available.</div>@endforelse
</div></div>
@endsection
