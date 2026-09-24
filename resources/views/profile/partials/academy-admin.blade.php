<section id="academy" class="{{ $activeSection === 'academy' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-white p-5 shadow-sm" x-data="{ sectionQuery: '' }">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-black">Akademia</h2>
            <p class="text-sm text-slate-600">Grupy, trenerzy, komunikaty i kalendarz treningów akademii.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <input type="search" x-model="sectionQuery" placeholder="Szukaj w akademii" class="rounded-lg border-slate-300 text-sm">
            <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold hover:bg-yellow-50" @click="openModal = 'academy-training-create'">Dodaj trening</button>
            <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold hover:bg-yellow-50" @click="openModal = 'academy-calendar-note-create'">Dodaj wpis</button>
            <button type="button" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300" @click="openModal = 'academy-group-create'">Dodaj sekcję</button>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.2fr_1fr]">
        <div class="space-y-4">
            <h3 class="font-black">Sekcje akademii</h3>
            @forelse ($academyGroups as $group)
                <article data-admin-search x-show="!sectionQuery || $el.textContent.toLowerCase().includes(sectionQuery.toLowerCase())" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-black text-slate-950" style="background-color: {{ $group->color }}">{{ $group->code }}</span>
                                <span class="text-xs font-bold uppercase {{ $group->is_active ? 'text-emerald-700' : 'text-slate-500' }}">{{ $group->is_active ? 'Widoczna' : 'Ukryta' }}</span>
                            </div>
                            <h4 class="mt-2 text-lg font-black">{{ $group->name }}</h4>
                            @if ($group->description)
                                <p class="mt-1 text-sm text-slate-600">{{ $group->description }}</p>
                            @endif
                            <p class="mt-2 text-sm text-slate-500">Trenerzy: {{ $group->trainers->count() }} · Komunikaty: {{ $group->messages->count() }} · Treningi: {{ $group->trainings->count() }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('academy.groups.show', $group) }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50">Podglad</a>
                            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = 'academy-group-edit-{{ $group->id }}'">Edytuj</button>
                            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = 'academy-trainer-create-{{ $group->id }}'">Trener</button>
                            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="openModal = 'academy-message-create-{{ $group->id }}'">Komunikat</button>
                            <form method="POST" action="{{ route('admin.academy.groups.destroy', $group) }}" onsubmit="return confirm('Usunąć sekcję akademii wraz z treningami?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 lg:grid-cols-2">
                        <div>
                            <p class="text-xs font-black uppercase text-slate-500">Trenerzy</p>
                            <div class="mt-2 space-y-2">
                                @forelse ($group->trainers as $trainer)
                                    <div class="rounded border border-slate-200 bg-white p-3 text-sm">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="font-black">{{ $trainer->name }}</p>
                                                <p class="text-slate-600">{{ $trainer->role }} {{ $trainer->phone ? '· '.$trainer->phone : '' }}</p>
                                            </div>
                                            <div class="flex gap-1">
                                                <button type="button" class="rounded border border-slate-200 px-2 py-1 text-xs font-bold" @click="openModal = 'academy-trainer-edit-{{ $trainer->id }}'">Edytuj</button>
                                                <form method="POST" action="{{ route('admin.academy.trainers.destroy', $trainer) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="rounded border border-red-200 px-2 py-1 text-xs font-bold text-red-700">Usuń</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Brak trenerów.</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase text-slate-500">Komunikaty</p>
                            <div class="mt-2 space-y-2">
                                @forelse ($group->messages->take(3) as $message)
                                    <div class="rounded border border-slate-200 bg-white p-3 text-sm">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="font-black">{{ $message->title }}</p>
                                                <p class="line-clamp-2 text-slate-600">{{ $message->body }}</p>
                                            </div>
                                            <div class="flex gap-1">
                                                <button type="button" class="rounded border border-slate-200 px-2 py-1 text-xs font-bold" @click="openModal = 'academy-message-edit-{{ $message->id }}'">Edytuj</button>
                                                <form method="POST" action="{{ route('admin.academy.messages.destroy', $message) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="rounded border border-red-200 px-2 py-1 text-xs font-bold text-red-700">Usuń</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Brak komunikatów.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Brak sekcji akademii. Dodaj pierwszą grupę, np. U15M albo U17K.</p>
            @endforelse
        </div>

        <div class="space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div>
                <h3 class="font-black">Stały plan treningów</h3>
                <p class="mt-1 text-sm text-slate-600">Jedna pozycja oznacza całą serię. Edycja zmienia wskazany termin i wszystkie kolejne treningi z tej serii.</p>
            </div>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($academyTrainingSeries as $training)
                    <article data-admin-search x-show="!sectionQuery || $el.textContent.toLowerCase().includes(sectionQuery.toLowerCase())" class="rounded-lg border border-slate-200 bg-white p-4">
                        <p class="text-xs font-black uppercase text-slate-500">{{ $training->group?->code }} · {{ $training->starts_at?->locale('pl')->translatedFormat('l') }}</p>
                        <h4 class="mt-1 text-lg font-black">{{ $training->timeRange() }}</h4>
                        <p class="text-sm font-semibold text-slate-700">{{ $training->title ?: 'Trening' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $training->location ?: 'Brak miejsca' }} · {{ $training->trainer_name ?: 'Brak trenera' }}</p>
                        @if ($training->description)
                            <p class="mt-2 text-sm text-slate-600">{{ $training->description }}</p>
                        @endif
                        <p class="mt-3 text-xs text-slate-500">Pierwszy przyszły termin: {{ $training->starts_at?->format('d.m.Y') }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="academyTrainingScope = 'series'; openModal = 'academy-training-edit-{{ $training->id }}'">Edytuj serię</button>
                            <form method="POST" action="{{ route('admin.academy.training-series.destroy', $training) }}" onsubmit="return confirm('Usunąć wszystkie przyszłe treningi z tej serii?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń serię</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="rounded-lg border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-600 md:col-span-2 xl:col-span-3">Brak serii treningów. Zaznacz „Powtarzaj trening co tydzień” podczas dodawania treningu.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="font-black">Ostatnie i nadchodzące treningi</h3>
            <form method="GET" action="{{ route('profile.edit') }}" class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                <input type="hidden" name="section" value="academy">
                <div class="grid gap-3 sm:grid-cols-[1fr_auto_auto] sm:items-end">
                    <div>
                        <label class="text-xs font-black uppercase text-slate-500">Filtruj po dacie</label>
                        <input type="date" name="academy_training_date" value="{{ $academyTrainingDate }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <button class="rounded-lg bg-slate-950 px-4 py-2 text-sm font-black text-yellow-400 hover:bg-slate-800">Filtruj</button>
                    @if ($academyTrainingDate)
                        <a href="{{ route('profile.edit', ['section' => 'academy']) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-bold hover:bg-yellow-50">Wyczyść</a>
                    @endif
                </div>
            </form>
            <div class="admin-scroll-list space-y-3">
                @forelse ($academyTrainings as $training)
                    <article data-admin-search x-show="!sectionQuery || $el.textContent.toLowerCase().includes(sectionQuery.toLowerCase())" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase text-slate-500">{{ $training->starts_at?->format('d.m.Y H:i') }}</p>
                                <h4 class="font-black {{ $training->isCancelled() ? 'line-through' : '' }}">{{ $training->group?->code }} · {{ $training->title ?: 'Trening' }}</h4>
                                <p class="text-sm text-slate-600">{{ $training->location ?: 'Brak miejsca' }} · {{ $training->trainer_name ?: 'Brak trenera' }}</p>
                                @if ($training->isCancelled())
                                    <p class="mt-2 rounded bg-red-50 px-2 py-1 text-sm font-semibold text-red-800">Odwołany: {{ $training->cancelled_reason }}</p>
                                @endif
                            </div>
                            <span class="rounded-full px-2 py-1 text-xs font-black {{ $training->isCancelled() ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $training->statusLabel() }}</span>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-yellow-50" @click="academyTrainingScope = 'single'; openModal = 'academy-training-edit-{{ $training->id }}'">Edytuj</button>
                            @if ($training->isCancelled())
                                <form method="POST" action="{{ route('admin.academy.trainings.restore', $training) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Przywróć</button>
                                </form>
                            @else
                                <button type="button" class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50" @click="openModal = 'academy-training-cancel-{{ $training->id }}'">Odwołaj</button>
                            @endif
                            <form method="POST" action="{{ route('admin.academy.trainings.destroy', $training) }}" onsubmit="return confirm('Usunąć trening?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Brak treningów w kalendarzu akademii.</p>
                @endforelse
            </div>

            <div class="mt-6 space-y-3">
                <h3 class="font-black">Globalne wpisy w kalendarzu</h3>
                @forelse ($academyCalendarNotes as $note)
                    <article data-admin-search x-show="!sectionQuery || $el.textContent.toLowerCase().includes(sectionQuery.toLowerCase())" class="rounded-lg border border-slate-200 bg-slate-100 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase text-slate-500">{{ $note->starts_on?->format('d.m.Y') }} - {{ $note->ends_on?->format('d.m.Y') }}</p>
                                <h4 class="font-black">{{ $note->title }}</h4>
                                @if ($note->body)
                                    <p class="mt-1 text-sm text-slate-600">{{ $note->body }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.academy.calendar-notes.destroy', $note) }}" onsubmit="return confirm('Usunąć wpis z kalendarza?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Usuń</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600">Brak globalnych wpisów w kalendarzu.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

<div x-show="openModal === 'academy-calendar-note-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
        <h4 class="mb-4 text-lg font-black">Dodaj globalny wpis w kalendarzu</h4>
        <form method="POST" action="{{ route('admin.academy.calendar-notes.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-bold text-slate-700">Tytuł</label>
                <input name="title" required value="{{ old('title') }}" placeholder="Zwolnienie z zajęć" class="mt-1 w-full rounded-lg border-slate-300">
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-bold text-slate-700">Od dnia</label>
                    <input name="starts_on" type="date" required value="{{ old('starts_on') }}" class="mt-1 w-full rounded-lg border-slate-300">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Do dnia</label>
                    <input name="ends_on" type="date" required value="{{ old('ends_on') }}" class="mt-1 w-full rounded-lg border-slate-300">
                </div>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Opis</label>
                <textarea name="body" rows="4" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Informacja widoczna w podglądzie dnia.">{{ old('body') }}</textarea>
            </div>
            <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz wpis</button></div>
        </form>
    </div>
</div>

<div x-show="openModal === 'academy-group-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
        <h4 class="mb-4 text-lg font-black">Dodaj sekcję akademii</h4>
        <form method="POST" action="{{ route('admin.academy.groups.store') }}" class="space-y-4">
            @csrf
            @include('profile.partials.academy-group-form')
            <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
        </form>
    </div>
</div>

<div x-show="openModal === 'academy-training-create'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
        <h4 class="mb-4 text-lg font-black">Dodaj trening</h4>
        <form method="POST" action="{{ route('admin.academy.trainings.store') }}" class="space-y-4">
            @csrf
            @include('profile.partials.academy-training-form', ['academyGroups' => $academyGroups, 'allowRecurrence' => true])
            <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
        </form>
    </div>
</div>

@foreach ($academyGroups as $group)
    <div x-show="openModal === 'academy-group-edit-{{ $group->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
            <h4 class="mb-4 text-lg font-black">Edytuj sekcję akademii</h4>
            <form method="POST" action="{{ route('admin.academy.groups.update', $group) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('profile.partials.academy-group-form', ['academyGroup' => $group])
                <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
            </form>
        </div>
    </div>

    <div x-show="openModal === 'academy-trainer-create-{{ $group->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
            <h4 class="mb-4 text-lg font-black">Dodaj trenera: {{ $group->code }}</h4>
            <form method="POST" action="{{ route('admin.academy.groups.trainers.store', $group) }}" class="space-y-4">
                @csrf
                @include('profile.partials.academy-trainer-form')
                <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
            </form>
        </div>
    </div>

    <div x-show="openModal === 'academy-message-create-{{ $group->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
            <h4 class="mb-4 text-lg font-black">Dodaj komunikat: {{ $group->code }}</h4>
            <form method="POST" action="{{ route('admin.academy.groups.messages.store', $group) }}" class="space-y-4">
                @csrf
                @include('profile.partials.academy-message-form')
                <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz</button></div>
            </form>
        </div>
    </div>
@endforeach

@foreach ($academyGroups->flatMap->trainers as $trainer)
    <div x-show="openModal === 'academy-trainer-edit-{{ $trainer->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
            <h4 class="mb-4 text-lg font-black">Edytuj trenera</h4>
            <form method="POST" action="{{ route('admin.academy.trainers.update', $trainer) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('profile.partials.academy-trainer-form', ['trainer' => $trainer])
                <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
            </form>
        </div>
    </div>
@endforeach

@foreach ($academyGroups->flatMap->messages as $message)
    <div x-show="openModal === 'academy-message-edit-{{ $message->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
            <h4 class="mb-4 text-lg font-black">Edytuj komunikat</h4>
            <form method="POST" action="{{ route('admin.academy.messages.update', $message) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('profile.partials.academy-message-form', ['message' => $message])
                <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
            </form>
        </div>
    </div>
@endforeach

@foreach ($academyTrainings->concat($academyTrainingSeries)->unique('id') as $training)
    <div x-show="openModal === 'academy-training-edit-{{ $training->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
            <h4 class="mb-4 text-lg font-black">Edytuj trening</h4>
            <form method="POST" action="{{ route('admin.academy.trainings.update', $training) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('profile.partials.academy-training-form', ['training' => $training, 'academyGroups' => $academyGroups])
                <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-black text-black hover:bg-yellow-300">Zapisz zmiany</button></div>
            </form>
        </div>
    </div>

    <div x-show="openModal === 'academy-training-cancel-{{ $training->id }}'" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 text-slate-950 shadow-xl" @click.outside="openModal = null">
            <h4 class="text-lg font-black">Odwołaj trening</h4>
            <p class="mt-2 text-sm text-slate-600">{{ $training->group?->code }} · {{ $training->starts_at?->format('d.m.Y H:i') }}</p>
            <form method="POST" action="{{ route('admin.academy.trainings.cancel', $training) }}" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="text-sm font-bold text-slate-700">Powód odwołania</label>
                    <textarea name="cancelled_reason" required rows="4" class="mt-1 w-full rounded-lg border-slate-300">{{ old('cancelled_reason', $training->cancelled_reason) }}</textarea>
                </div>
                <div class="flex justify-between"><button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold" @click="openModal = null">Anuluj</button><button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-black text-white hover:bg-red-500">Odwołaj trening</button></div>
            </form>
        </div>
    </div>
@endforeach
