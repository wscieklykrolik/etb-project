@php
    use App\Models\MatchGame;

    $match = $match ?? null;
    $currentStatus = old('status', $match?->status ?? MatchGame::STATUS_UPCOMING);
    $isHome = filter_var(old('is_home', $match?->is_home ?? true), FILTER_VALIDATE_BOOLEAN);
    $includeInLzkosz = filter_var(old('include_in_lzkosz', $match?->include_in_lzkosz ?? false), FILTER_VALIDATE_BOOLEAN);
    $isTicketed = filter_var(old('is_ticketed', $match?->is_ticketed ?? false), FILTER_VALIDATE_BOOLEAN);
@endphp

<div x-data="matchForm({
        status: @js($currentStatus),
        includeInLzkosz: @js($includeInLzkosz),
        isTicketed: @js($isTicketed),
        opponentLogo: @js($match?->opponent_logo ? \App\Support\MediaStorage::url($match->opponent_logo) : null),
        locationsUrl: @js(route('admin.match-suggestions.locations')),
        opponentsUrl: @js(route('admin.match-suggestions.opponents'))
    })"
     class="space-y-5">
    <div class="grid gap-3 sm:grid-cols-2">
        <label class="flex cursor-pointer items-center gap-3 rounded border border-gray-200 p-3">
            <input type="radio" name="status" value="{{ MatchGame::STATUS_UPCOMING }}" x-model="status" class="border-gray-300 text-yellow-500">
            <span>
                <span class="block text-sm font-semibold text-gray-900">Mecz nadchodzący</span>
                <span class="block text-xs text-gray-500">Wynik pozostaje ukryty.</span>
            </span>
        </label>

        <label class="flex cursor-pointer items-center gap-3 rounded border border-gray-200 p-3">
            <input type="radio" name="status" value="{{ MatchGame::STATUS_FINISHED }}" x-model="status" class="border-gray-300 text-yellow-500">
            <span>
                <span class="block text-sm font-semibold text-gray-900">Mecz zakończony</span>
                <span class="block text-xs text-gray-500">Uzupełnij końcowy wynik.</span>
            </span>
        </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <label class="relative block">
            <span class="text-sm font-medium text-gray-700">Przeciwnik</span>
            <input name="opponent_name" type="text" required autocomplete="off" value="{{ old('opponent_name', $match?->opponent_name) }}" class="mt-1 w-full rounded border-gray-300" @input.debounce.300ms="loadOpponents($event.target.value)" @focus="loadOpponents($event.target.value)">
            <div x-show="opponents.length" x-cloak class="absolute z-30 mt-1 w-full overflow-hidden rounded border border-gray-200 bg-white shadow-lg">
                <template x-for="opponent in opponents" :key="opponent.id">
                    <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-yellow-50" @click="selectOpponent(opponent)">
                        <span class="font-medium" x-text="opponent.name"></span>
                    </button>
                </template>
            </div>
        </label>

        <label class="block">
            <span class="text-sm font-medium text-gray-700">Data meczu</span>
            <input name="match_date" type="datetime-local" required :min="status === '{{ MatchGame::STATUS_UPCOMING }}' ? '{{ now()->format('Y-m-d\T00:00') }}' : null" value="{{ old('match_date', $match?->match_date?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded border-gray-300">
        </label>

        <label class="relative block">
            <span class="text-sm font-medium text-gray-700">Lokalizacja</span>
            <input name="location" type="text" required autocomplete="off" value="{{ old('location', $match?->location) }}" class="mt-1 w-full rounded border-gray-300" @input.debounce.300ms="loadLocations($event.target.value)" @focus="loadLocations($event.target.value)">
            <div x-show="locations.length" x-cloak class="absolute z-30 mt-1 w-full overflow-hidden rounded border border-gray-200 bg-white shadow-lg">
                <template x-for="location in locations" :key="location.id">
                    <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-yellow-50" @click="selectLocation(location)">
                        <span x-text="location.name"></span>
                    </button>
                </template>
            </div>
        </label>

        <label class="block">
            <span class="text-sm font-medium text-gray-700">Godzina</span>
            <input type="time" class="mt-1 w-full rounded border-gray-300" @change="syncTime($event.target.value)">
        </label>

        <label class="block">
            <span class="text-sm font-medium text-gray-700">Sezon</span>
            <input name="season" type="text" value="{{ old('season', $match?->season) }}" placeholder="2025/2026" class="mt-1 w-full rounded border-gray-300">
        </label>

        <label class="flex items-center gap-2 rounded border border-gray-200 p-3">
            <input name="is_home" type="checkbox" value="1" class="rounded border-gray-300 text-yellow-500" @checked($isHome)>
            <span class="text-sm font-medium text-gray-700">Mecz u siebie</span>
        </label>

        <label class="flex items-center gap-2 rounded border border-gray-200 p-3">
            <input name="include_in_lzkosz" type="checkbox" value="1" x-model="includeInLzkosz" class="rounded border-gray-300 text-yellow-500">
            <span class="text-sm font-medium text-gray-700">Dodaj do terminarza ŁZKosz</span>
        </label>

        <label class="block" x-show="includeInLzkosz" x-cloak>
            <span class="text-sm font-medium text-gray-700">Runda ŁZKosz</span>
            <select name="lzkosz_round" :disabled="!includeInLzkosz" class="mt-1 w-full rounded border-gray-300 disabled:bg-gray-100">
                <option value="{{ MatchGame::LZKOSZ_ROUND_ONE }}" @selected(old('lzkosz_round', $match?->lzkosz_round ?? MatchGame::LZKOSZ_ROUND_ONE) === MatchGame::LZKOSZ_ROUND_ONE)>Runda 1</option>
                <option value="{{ MatchGame::LZKOSZ_ROUND_TWO }}" @selected(old('lzkosz_round', $match?->lzkosz_round) === MatchGame::LZKOSZ_ROUND_TWO)>Runda 2</option>
            </select>
        </label>

        <label class="flex items-center gap-2 rounded border border-gray-200 p-3">
            <input name="is_ticketed" type="checkbox" value="1" x-model="isTicketed" class="rounded border-gray-300 text-yellow-500">
            <span class="text-sm font-medium text-gray-700">Mecz biletowany</span>
        </label>

        <label class="block" x-show="isTicketed" x-cloak>
            <span class="text-sm font-medium text-gray-700">Link sprzedaży biletów</span>
            <input name="ticket_url" type="url" :disabled="!isTicketed" value="{{ old('ticket_url', $match?->ticket_url) }}" placeholder="https://..." class="mt-1 w-full rounded border-gray-300 disabled:bg-gray-100">
        </label>

        <div class="grid gap-4 md:col-span-2 md:grid-cols-2" x-show="status === '{{ MatchGame::STATUS_FINISHED }}'">
            <label class="block">
                <span class="text-sm font-medium text-gray-700">Punkty ETB</span>
                <input name="our_score" type="number" min="0" max="999" :disabled="status !== '{{ MatchGame::STATUS_FINISHED }}'" value="{{ old('our_score', $match?->our_score) }}" class="mt-1 w-full rounded border-gray-300 disabled:bg-gray-100">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-gray-700">Punkty przeciwnika</span>
                <input name="opponent_score" type="number" min="0" max="999" :disabled="status !== '{{ MatchGame::STATUS_FINISHED }}'" value="{{ old('opponent_score', $match?->opponent_score) }}" class="mt-1 w-full rounded border-gray-300 disabled:bg-gray-100">
            </label>
        </div>

        <label class="block">
            <span class="text-sm font-medium text-gray-700">Logo przeciwnika</span>
            <input name="opponent_logo" type="file" accept="image/*" class="mt-1 w-full rounded border border-gray-300 bg-white text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
            <template x-if="opponentLogo">
                <img :src="opponentLogo" alt="Logo przeciwnika" class="mt-3 h-14 w-14 rounded object-contain ring-1 ring-gray-200">
            </template>
        </label>

        <label class="block">
            <span class="text-sm font-medium text-gray-700">Logo drużyny ETB</span>
            <input name="home_logo" type="file" accept="image/*" class="mt-1 w-full rounded border border-gray-300 bg-white text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
            @if ($match?->home_logo)
                <img src="{{ \App\Support\MediaStorage::url($match->home_logo) }}" alt="Logo ETB" class="mt-3 h-14 w-14 rounded object-contain ring-1 ring-gray-200">
            @endif
        </label>

        <label class="block md:col-span-2">
            <span class="text-sm font-medium text-gray-700">Dodatkowe informacje</span>
            <textarea name="notes" rows="4" class="mt-1 w-full rounded border-gray-300">{{ old('notes', $match?->notes) }}</textarea>
        </label>
    </div>
</div>

