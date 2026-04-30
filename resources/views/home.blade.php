@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-semibold">Welcome to ETB</h1>
            <p class="mt-2 text-gray-700">Stay up to date with the latest club announcements and updates.</p>
        </section>

        <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-xl font-medium mb-3">Latest news</h2>
            <div class="space-y-3">
                @forelse($latestNews as $item)
                    <article>
                        <h3 class="font-medium"><a class="underline" href="{{ route('news.show', $item) }}">{{ $item->title }}</a></h3>
                        <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($item->content, 120) }}</p>
                    </article>
                @empty
                    <p>No news yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
