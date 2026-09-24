@extends('layouts.app')

@section('content')
    <div class="bg-gray-100 py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div x-data="newsEditor({ saveOnClose: @js($news->is_draft || ! $news->is_visible), existing: true, returnUrl: @js(route('profile.edit', ['section' => 'news'])) })" @keydown.escape.window="closeEditor()" class="rounded border border-gray-200 bg-white p-6 shadow-sm">
                <h1 class="mb-6 text-2xl font-semibold">Edytuj aktualność</h1>
                <div x-show="errors.length" x-cloak role="alert" class="mb-4 text-sm text-red-700">
                    <template x-for="(error, index) in errors" :key="index"><p x-text="error"></p></template>
                </div>
                <form x-ref="editorForm" @submit.prevent="submitEditor()" action="{{ route('news.update', $news) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')
                    @include('profile.partials.news-form-fields', ['item' => $news])
                    <button type="button" @click="closeEditor()" :disabled="submitting" class="mr-3 rounded border px-4 py-2 text-sm font-semibold">Zamknij</button>
                    <button :disabled="submitting" class="rounded bg-yellow-500 px-4 py-2 text-sm font-semibold text-black hover:bg-yellow-400">Zapisz zmiany</button>
                </form>
            </div>
        </div>
    </div>
@endsection
