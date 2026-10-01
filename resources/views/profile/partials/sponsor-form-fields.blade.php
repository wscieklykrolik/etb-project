@php
    $sponsor = $sponsor ?? null;
    $useSameLogo = (bool) old('use_same_logo', ! $sponsor?->homepage_logo_path);
@endphp

<div>
    <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-name-{{ $sponsor?->id ?? 'new' }}">Nazwa partnera</label>
    <input id="sponsor-name-{{ $sponsor?->id ?? 'new' }}" name="name" value="{{ old('name', $sponsor?->name) }}" required class="w-full rounded-lg border-slate-300 text-sm">
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-category-{{ $sponsor?->id ?? 'new' }}">Kategoria partnera</label>
        <select id="sponsor-category-{{ $sponsor?->id ?? 'new' }}" name="sponsor_category_id" required class="w-full rounded-lg border-slate-300 text-sm">
            @foreach ($sponsorCategories as $category)
                <option value="{{ $category->id }}" @selected((int) old('sponsor_category_id', $sponsor?->sponsor_category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-sort-{{ $sponsor?->id ?? 'new' }}">Kolejność</label>
        <input id="sponsor-sort-{{ $sponsor?->id ?? 'new' }}" type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', $sponsor?->sort_order ?? 0) }}" class="w-full rounded-lg border-slate-300 text-sm">
    </div>
</div>

<div>
    <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-url-{{ $sponsor?->id ?? 'new' }}">Link po kliknięciu w logo</label>
    <input id="sponsor-url-{{ $sponsor?->id ?? 'new' }}" type="url" name="url" value="{{ old('url', $sponsor?->url) }}" required placeholder="https://example.com" class="w-full rounded-lg border-slate-300 text-sm">
</div>

<div x-data="{ useSameLogo: {{ $useSameLogo ? 'true' : 'false' }} }" class="space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
    <div>
        <h5 class="font-black text-slate-900">Zdjęcia sponsora</h5>
        <p class="mt-1 text-xs leading-5 text-slate-600">Zdecyduj, czy oba miejsca mają korzystać z tego samego pliku. Link po kliknięciu pozostaje wspólny.</p>
    </div>

    <input type="hidden" name="use_same_logo" value="0">
    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-white p-3">
        <input type="checkbox" name="use_same_logo" value="1" x-model="useSameLogo" class="mt-0.5 rounded border-slate-300 text-yellow-500 focus:ring-yellow-400">
        <span>
            <span class="block text-sm font-bold text-slate-800">Używaj tego samego zdjęcia w obu miejscach</span>
            <span class="mt-0.5 block text-xs text-slate-500">Odznacz, aby wgrać osobne zdjęcie do jasnych i ciemnych kafelków.</span>
        </span>
    </label>

    <div class="grid gap-4" :class="useSameLogo ? 'grid-cols-1' : 'md:grid-cols-2'">
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-logo-{{ $sponsor?->id ?? 'new' }}">
                <span x-text="useSameLogo ? 'Zdjęcie wspólne dla obu miejsc' : 'Zdjęcie w stopce — jasne kafelki'"></span>
            </label>
            <p class="mb-3 text-xs text-slate-500" x-show="!useSameLogo">To zdjęcie pojawi się także w zakładce „Sponsorzy”.</p>
            @if ($sponsor?->logo_path)
                <div class="mb-3 flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <img src="{{ \App\Support\MediaStorage::url($sponsor->logo_path) }}" alt="{{ $sponsor->name }}" class="h-12 w-28 object-contain">
                    <span class="text-xs text-slate-500">Aktualny plik</span>
                </div>
            @endif
            <input id="sponsor-logo-{{ $sponsor?->id ?? 'new' }}" type="file" name="logo" accept="image/*" @required(! $sponsor) class="w-full rounded-lg border border-slate-300 p-2 text-sm">
            @if ($sponsor)
                <p class="mt-2 text-xs text-slate-500">Pozostaw puste, aby zachować obecne zdjęcie.</p>
            @endif
        </div>

        <div x-show="!useSameLogo" x-cloak class="rounded-lg border border-yellow-300 bg-yellow-50 p-3">
            <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-homepage-logo-{{ $sponsor?->id ?? 'new' }}">Zdjęcie na stronie głównej — ciemne kafelki</label>
            <p class="mb-3 text-xs text-slate-500">Wgraj wariant przygotowany do ciemnego tła.</p>
            @if ($sponsor?->homepage_logo_path)
                <div class="mb-3 flex items-center gap-3 rounded-lg border border-yellow-200 bg-white p-3">
                    <img src="{{ \App\Support\MediaStorage::url($sponsor->homepage_logo_path) }}" alt="{{ $sponsor->name }}" class="h-12 w-28 object-contain">
                    <span class="text-xs text-slate-500">Aktualny plik</span>
                </div>
            @endif
            <input id="sponsor-homepage-logo-{{ $sponsor?->id ?? 'new' }}" type="file" name="homepage_logo" accept="image/*" :required="!useSameLogo && {{ $sponsor?->homepage_logo_path ? 'false' : 'true' }}" class="w-full rounded-lg border border-yellow-300 bg-white p-2 text-sm">
            @if ($sponsor?->homepage_logo_path)
                <p class="mt-2 text-xs text-slate-500">Pozostaw puste, aby zachować obecne zdjęcie.</p>
            @endif
        </div>
    </div>
</div>

<label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $sponsor?->is_active ?? true)) class="rounded border-slate-300 text-yellow-500 focus:ring-yellow-400">
    Widoczny na stronie
</label>

