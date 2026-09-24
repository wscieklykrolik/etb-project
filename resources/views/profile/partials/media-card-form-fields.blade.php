@php
    $item = $item ?? null;
    $withCoach = $withCoach ?? false;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="block">
        <span class="text-sm font-medium text-gray-700">Imię i nazwisko</span>
        <input name="name" required value="{{ old('name', $item?->name) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Rola</span>
        <input name="role" required value="{{ old('role', $item?->role) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Kolejność</span>
        <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $item?->sort_order ?? 0) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Zdjęcie</span>
        <input name="photo" type="file" accept="image/*" class="mt-1 w-full rounded border border-gray-300 bg-white text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Rok urodzenia (opcjonalnie)</span>
        <input name="birth_year" type="number" min="1900" max="{{ now()->year }}" step="1" placeholder="np. 2003" value="{{ old('birth_year', $item?->birth_year) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    @if ($withCoach)
        <label class="flex items-center gap-2 rounded border border-gray-200 p-3 md:col-span-2">
            <input name="is_coach" type="checkbox" value="1" class="rounded border-gray-300 text-yellow-500" @checked(old('is_coach', $item?->is_coach))>
            <span class="text-sm font-medium text-gray-700">Trener drużyny 3x3</span>
        </label>
    @endif

    <label class="block md:col-span-2">
        <span class="text-sm font-medium text-gray-700">Opis</span>
        <textarea name="description" rows="4" class="mt-1 w-full rounded border-gray-300">{{ old('description', $item?->description) }}</textarea>
    </label>
</div>
