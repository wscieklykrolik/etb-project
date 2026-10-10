@props([
    'name',
    'value' => '',
    'id' => null,
    'required' => false,
    'minHeight' => '12rem',
])

@php
    $editorId = $id ?: 'rich-text-'.str_replace(['[', ']', '.'], '-', $name);
    $editorValue = \App\Support\RichText::forEditor($value);
@endphp

<div class="etb-rich-text-editor" x-data="richTextEditor()" x-init="init()">
    <div class="etb-rich-text-toolbar" role="toolbar" aria-label="Formatowanie tekstu">
        <button type="button" @mousedown.prevent @click="format('bold')" title="Pogrubienie" aria-label="Pogrubienie"><strong>B</strong></button>
        <button type="button" @mousedown.prevent @click="format('italic')" title="Kursywa" aria-label="Kursywa"><em>I</em></button>
        <button type="button" @mousedown.prevent @click="format('underline')" title="Podkreślenie" aria-label="Podkreślenie"><u>U</u></button>
        <button type="button" @mousedown.prevent @click="addLink()" title="Wstaw link" aria-label="Wstaw link"><i data-lucide="link" class="h-4 w-4"></i><span>Link</span></button>
        <button type="button" @mousedown.prevent @click="format('insertUnorderedList')" title="Lista punktowana" aria-label="Lista punktowana"><i data-lucide="list" class="h-4 w-4"></i></button>
        <button type="button" @mousedown.prevent @click="format('insertOrderedList')" title="Lista numerowana" aria-label="Lista numerowana"><i data-lucide="list-ordered" class="h-4 w-4"></i></button>
        <button type="button" @mousedown.prevent @click="format('removeFormat')" title="Usuń formatowanie" aria-label="Usuń formatowanie"><i data-lucide="eraser" class="h-4 w-4"></i></button>
    </div>

    <textarea x-ref="input" id="{{ $editorId }}" name="{{ $name }}" class="sr-only" @if($required) required @endif {{ $attributes->only(['maxlength']) }}>{{ $editorValue }}</textarea>
    <div
        x-ref="editor"
        contenteditable="true"
        role="textbox"
        aria-multiline="true"
        aria-labelledby="{{ $editorId }}-label"
        class="etb-rich-text-editor__surface"
        style="min-height: {{ $minHeight }}"
        @input="sync()"
        @blur="sync()"
    ></div>
    <p class="mt-2 px-3 pb-3 text-xs text-slate-500">Zaznacz tekst, a następnie wybierz formatowanie. Linki otwierają się w nowej karcie.</p>
</div>
