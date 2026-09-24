@php
    use App\Enums\BasketballPosition;

    $player = $player ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="block">
        <span class="text-sm font-medium text-gray-700">Imię</span>
        <input name="first_name" required value="{{ old('first_name', $player?->first_name) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Nazwisko</span>
        <input name="last_name" required value="{{ old('last_name', $player?->last_name) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Numer</span>
        <input name="number" type="number" required min="0" max="99" value="{{ old('number', $player?->number) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Pozycja</span>
        <select name="position" required class="mt-1 w-full rounded border-gray-300">
            @foreach (BasketballPosition::options() as $value => $label)
                <option value="{{ $value }}" @selected(old('position', $player?->position) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Rok urodzenia (opcjonalnie)</span>
        <input name="birth_year" type="number" min="1900" max="{{ now()->year }}" step="1" placeholder="np. 2003" value="{{ old('birth_year', $player?->birth_year ?? $player?->date_of_birth?->year) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Zdjęcie zawodnika</span>
        <input name="photo" type="file" accept="image/*" class="mt-1 w-full rounded border border-gray-300 bg-white text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Wzrost w cm</span>
        <input name="height" type="number" min="100" max="250" value="{{ old('height', $player?->height) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium text-gray-700">Waga w kg</span>
        <input name="weight" type="number" min="40" max="200" value="{{ old('weight', $player?->weight) }}" class="mt-1 w-full rounded border-gray-300">
    </label>

    <label class="flex items-center gap-2 rounded border border-gray-200 p-3 md:col-span-2">
        <input name="publish_description" type="checkbox" value="1" class="rounded border-gray-300 text-yellow-500" @checked(old('publish_description', $player?->publish_description))>
        <span class="text-sm font-medium text-gray-700">Publikuj opis zawodnika i włącz publiczny widok szczegółów</span>
    </label>

    <label class="flex items-center gap-2 rounded border border-gray-200 p-3 md:col-span-2">
        <input name="is_starting_five" type="checkbox" value="1" class="rounded border-gray-300 text-yellow-500" @checked(old('is_starting_five', $player?->is_starting_five))>
        <span class="text-sm font-medium text-gray-700">Pokaż zawodnika w pierwszej piątce na stronie głównej</span>
    </label>

    <label class="block md:col-span-2">
        <span class="text-sm font-medium text-gray-700">Opis zawodnika</span>
        <textarea name="description" rows="5" class="mt-1 w-full rounded border-gray-300">{{ old('description', $player?->description) }}</textarea>
    </label>
</div>
