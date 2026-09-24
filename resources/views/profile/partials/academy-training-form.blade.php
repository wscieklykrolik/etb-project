@php
    $training = $training ?? null;
    $selectedGroupId = (string) old('academy_group_id', $training?->academy_group_id ?? '');
    $trainerOptions = $academyGroups
        ->mapWithKeys(fn ($group) => [
            (string) $group->id => $group->trainers
                ->map(fn ($trainer) => [
                    'name' => $trainer->name,
                    'role' => $trainer->role,
                    'phone' => $trainer->phone,
                ])
                ->values(),
        ])
        ->all();
@endphp

<div
    x-data="{
        selectedGroupId: @js($selectedGroupId),
        trainerName: @js(old('trainer_name', $training?->trainer_name)),
        trainersByGroup: @js($trainerOptions),
    }"
    class="space-y-4"
>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="text-sm font-bold text-slate-700">Sekcja</label>
            <select name="academy_group_id" required x-model="selectedGroupId" class="mt-1 w-full rounded-lg border-slate-300">
                <option value="">Wybierz sekcję</option>
                @foreach ($academyGroups as $group)
                    <option value="{{ $group->id }}" @selected((int) old('academy_group_id', $training?->academy_group_id) === $group->id)>{{ $group->code }} · {{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">Nazwa treningu</label>
            <input name="title" value="{{ old('title', $training?->title) }}" placeholder="Trening techniczny" class="mt-1 w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">Data treningu</label>
            <input name="training_date" type="date" required value="{{ old('training_date', $training?->starts_at?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">Godzina od</label>
            <input name="start_time" type="time" required value="{{ old('start_time', $training?->starts_at?->format('H:i')) }}" class="mt-1 w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">Godzina do</label>
            <input name="end_time" type="time" value="{{ old('end_time', $training?->ends_at?->format('H:i')) }}" class="mt-1 w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">Miejsce</label>
            <input name="location" value="{{ old('location', $training?->location) }}" placeholder="Hala ETB" class="mt-1 w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">Prowadzi</label>
            <input name="trainer_name" x-model="trainerName" placeholder="Imię i nazwisko trenera" class="mt-1 w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                <option value="scheduled" @selected(old('status', $training?->status ?? 'scheduled') === 'scheduled')>Planowany</option>
                <option value="cancelled" @selected(old('status', $training?->status) === 'cancelled')>Odwołany</option>
            </select>
        </div>
    </div>

    <div x-show="trainersByGroup[selectedGroupId]?.length" x-cloak data-academy-training-trainers class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs font-black uppercase text-slate-500">Trenerzy przypisani do sekcji</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <template x-for="trainer in trainersByGroup[selectedGroupId]" :key="`${selectedGroupId}-${trainer.name}`">
                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-bold hover:bg-yellow-50" @click="trainerName = trainer.name">
                    <span x-text="trainer.name"></span>
                    <span x-show="trainer.role" class="font-medium text-slate-500"> · <span x-text="trainer.role"></span></span>
                </button>
            </template>
        </div>
        <p class="mt-2 text-xs text-slate-500">Trener nie jest wymagany do zapisania treningu, ale jeśli sekcja ma trenerów, możesz wybrać jednego z listy.</p>
    </div>

    @if ($allowRecurrence ?? false)
        <div x-data="{ repeatWeekly: {{ old('repeat_weekly') ? 'true' : 'false' }} }" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <label class="flex items-start gap-3 text-sm font-bold text-slate-800">
                <input type="checkbox" name="repeat_weekly" value="1" x-model="repeatWeekly" class="mt-1 rounded border-slate-300 text-yellow-500 focus:ring-yellow-400">
                <span>
                    Powtarzaj trening co tydzień
                    <span class="block text-xs font-medium text-slate-500">System utworzy treningi w ten sam dzień tygodnia i o tej samej godzinie do wskazanej daty włącznie.</span>
                </span>
            </label>
            <div x-show="repeatWeekly" x-cloak class="mt-4">
                <label class="text-sm font-bold text-slate-700">Powtarzaj do dnia</label>
                <input name="repeat_until" type="date" value="{{ old('repeat_until') }}" class="mt-1 w-full rounded-lg border-slate-300">
            </div>
        </div>
    @endif

    @if ($training?->recurrence_series_id)
        <fieldset class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <legend class="px-1 text-sm font-bold text-slate-700">Zakres zmiany</legend>
            <label class="mt-2 flex items-start gap-3 text-sm">
                <input type="radio" name="edit_scope" value="single" x-model="academyTrainingScope" class="mt-1 rounded border-slate-300 text-yellow-500">
                <span><strong>Tylko ten trening</strong><span class="block text-slate-500">Edytujesz dokładnie termin wskazany w kalendarzu.</span></span>
            </label>
            <label class="mt-3 flex items-start gap-3 text-sm">
                <input type="radio" name="edit_scope" value="series" x-model="academyTrainingScope" class="mt-1 rounded border-slate-300 text-yellow-500">
                <span><strong>Ten i wszystkie kolejne treningi z serii</strong><span class="block text-slate-500">Nowa data określa dzień tygodnia oraz pierwszy zmieniony termin.</span></span>
            </label>
        </fieldset>
    @endif

    <div>
        <label class="text-sm font-bold text-slate-700">Opis treningu</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $training?->description) }}</textarea>
    </div>

    <div>
        <label class="text-sm font-bold text-slate-700">Powód odwołania</label>
        <textarea name="cancelled_reason" rows="3" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Wypełnij, jeżeli trening jest odwołany.">{{ old('cancelled_reason', $training?->cancelled_reason) }}</textarea>
    </div>
</div>
