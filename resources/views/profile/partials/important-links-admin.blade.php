<section id="important-links" class="{{ $activeSection === 'important-links' ? '' : 'hidden' }} min-w-0 bg-white p-5" x-data="{ selectedPage: @js(array_key_exists(old('page_slug', ''), \App\Models\ImportantPage::PAGES) ? old('page_slug') : 'polityka-prywatnosci') }">
    <h2 class="text-xl font-black">Ważne linki</h2>
    <div role="tablist" aria-label="Strony informacyjne" class="mt-5 flex flex-wrap gap-2 border-b border-slate-200 pb-4">
        @foreach (\App\Models\ImportantPage::PAGES as $pageSlug => $pageTitle)
            <button type="button" role="tab" id="tab-{{ $pageSlug }}" aria-controls="editor-{{ $pageSlug }}" :aria-selected="selectedPage === '{{ $pageSlug }}'" @click="selectedPage = '{{ $pageSlug }}'" @keydown.arrow-right.prevent="$el.nextElementSibling ? $el.nextElementSibling.click() : $el.parentElement.firstElementChild.click(); ( $el.nextElementSibling || $el.parentElement.firstElementChild ).focus()" @keydown.arrow-left.prevent="$el.previousElementSibling ? $el.previousElementSibling.click() : $el.parentElement.lastElementChild.click(); ( $el.previousElementSibling || $el.parentElement.lastElementChild ).focus()" :class="selectedPage === '{{ $pageSlug }}' ? 'bg-yellow-400 text-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="rounded-lg px-4 py-2 text-sm font-bold">{{ $pageTitle }}</button>
        @endforeach
    </div>
    @foreach (\App\Models\ImportantPage::PAGES as $slug => $title)
        @php
            $page = $importantPages->get($slug);
            $pageErrors = $errors->getBag($slug);
            $isSubmitted = old('page_slug') === $slug;
        @endphp
        <form id="editor-{{ $slug }}" role="tabpanel" aria-labelledby="tab-{{ $slug }}" x-show="selectedPage === '{{ $slug }}'" x-cloak x-data="{ preview: null }" method="POST" action="{{ route('admin.important-pages.update', $slug) }}" enctype="multipart/form-data" class="space-y-4 py-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="page_slug" value="{{ $slug }}">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-bold">{{ $title }}</h3>
                <a href="{{ route('important-pages.show', $slug) }}" class="inline-flex items-center gap-2 text-sm font-semibold"><i data-lucide="external-link" class="h-4 w-4"></i>Zobacz stronę</a>
            </div>
            @foreach ($pageErrors->all() as $error)
                <p class="text-sm text-red-700" role="alert">{{ $error }}</p>
            @endforeach
            <div>
                <label for="body-{{ $slug }}" class="mb-2 block text-sm font-bold">Treść</label>
                <textarea id="body-{{ $slug }}" name="body" rows="10" maxlength="100000" class="w-full rounded-lg border-slate-300">{{ $isSubmitted ? old('body') : $page?->body }}</textarea>
            </div>
            @if ($page?->image_path)
                <img src="{{ \App\Support\MediaStorage::url($page->image_path) }}" alt="{{ $title }}" class="max-h-72 max-w-full object-contain">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_image" value="1" @checked($isSubmitted && old('remove_image'))>Usuń zdjęcie</label>
            @endif
            <div>
                <label for="image-{{ $slug }}" class="mb-2 block text-sm font-bold">Zdjęcie (JPG, PNG, WebP lub GIF, maks. 5 MB)</label>
                <input id="image-{{ $slug }}" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" @change="if (preview) URL.revokeObjectURL(preview); preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null" class="block w-full min-w-0 rounded-lg border border-slate-300 p-3 text-sm">
                <template x-if="preview"><img :src="preview" alt="Podgląd wybranego zdjęcia" class="mt-4 max-h-72 max-w-full object-contain"></template>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-yellow-400 px-4 py-2 font-bold text-black"><i data-lucide="save" class="h-4 w-4"></i>Zapisz</button>
        </form>
    @endforeach
</section>
