@extends('layouts.app')
@section('content')
<div class="py-6"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><article class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4"><h1 class="text-2xl font-semibold">{{ $news->title }}</h1><p class="text-sm text-gray-600">By {{ $news->author->name ?? 'Unknown author' }} · {{ $news->created_at?->format('Y-m-d H:i') }}</p><div class="whitespace-pre-line">{{ $news->content }}</div>@can('update', $news)<a href="{{ route('news.edit', $news) }}" class="underline">Edit</a>@endcan @can('delete', $news)<form action="{{ route('news.destroy', $news) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="underline">Delete</button></form>@endcan</article></div></div>
@endsection
