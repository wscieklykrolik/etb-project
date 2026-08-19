@php
    $sponsor = $sponsor ?? null;
@endphp

<div>
    <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-name-{{ $sponsor?->id ?? 'new' }}">Nazwa partnera</label>
    <input id="sponsor-name-{{ $sponsor?->id ?? 'new' }}" name="name" value="{{ old('name', $sponsor?->name) }}" required class="w-full rounded-lg border-slate-300 text-sm">
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-type-{{ $sponsor?->id ?? 'new' }}">Typ partnera</label>
        <select id="sponsor-type-{{ $sponsor?->id ?? 'new' }}" name="type" required class="w-full rounded-lg border-slate-300 text-sm">
            @foreach ($sponsorTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $sponsor?->type) === $value)>{{ $label }}</option>
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

<div>
    <label class="mb-1 block text-sm font-semibold text-slate-700" for="sponsor-logo-{{ $sponsor?->id ?? 'new' }}">Logo</label>
    @if ($sponsor?->logo_path)
        <div class="mb-3 flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
            <img src="{{ \App\Support\MediaStorage::url($sponsor->logo_path) }}" alt="{{ $sponsor->name }}" class="h-12 w-28 object-contain">
            <span class="text-xs text-slate-500">Wgraj nowe logo tylko wtedy, gdy chcesz je podmienić.</span>
        </div>
    @endif
    <input id="sponsor-logo-{{ $sponsor?->id ?? 'new' }}" type="file" name="logo" accept="image/*" @required(! $sponsor) class="w-full rounded-lg border border-slate-300 p-2 text-sm">
</div>

<label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $sponsor?->is_active ?? true)) class="rounded border-slate-300 text-yellow-500 focus:ring-yellow-400">
    Widoczny na stronie
</label>

