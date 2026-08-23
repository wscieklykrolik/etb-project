@extends('layouts.app')

@php
    use Carbon\CarbonPeriod;

    $calendarStart = $month->copy()->startOfMonth()->startOfWeek();
    $calendarEnd = $month->copy()->endOfMonth()->endOfWeek();
    $days = collect(CarbonPeriod::create($calendarStart, $calendarEnd));
    $trainingsByDay = $trainings->groupBy(fn ($training) => $training->starts_at->format('Y-m-d'));
    $calendarNotes = $calendarNotes ?? collect();
    $publicHolidaysByDay = ($publicHolidays ?? collect())->keyBy('date');
    $holidaySourceUrl = $holidaySourceUrl ?? 'https://date.nager.at';
    $polishMonths = [
        1 => 'styczeń',
        2 => 'luty',
        3 => 'marzec',
        4 => 'kwiecień',
        5 => 'maj',
        6 => 'czerwiec',
        7 => 'lipiec',
        8 => 'sierpień',
        9 => 'wrzesień',
        10 => 'październik',
        11 => 'listopad',
        12 => 'grudzień',
    ];
    $monthLabel = $polishMonths[(int) $month->format('n')].' '.$month->format('Y');
@endphp

@section('content')
<section class="bg-yellow-400 text-black">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 rounded-lg bg-black p-6 text-yellow-400">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-lg bg-yellow-400 p-3">
                    <x-site-logo :url="$academyLogoUrl" alt="Logo akademii" image-class="max-h-full max-w-full object-contain" fallback="Akademia" fallback-class="text-center text-sm font-black uppercase tracking-wide text-black" />
                </div>
                <h1 class="text-3xl font-black sm:text-4xl">Akademia</h1>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[20rem_1fr]">
            <aside class="space-y-4">
                <h2 class="text-lg font-black">Prowadzone grupy</h2>
                <div class="space-y-3">
                    @forelse ($groups as $group)
                        <a href="{{ route('academy.groups.show', $group) }}" class="block rounded-lg border-2 border-black bg-black p-4 text-yellow-400 transition hover:-translate-y-0.5 hover:bg-zinc-900" style="--group-color: {{ $group->color }}">
                            <div class="flex items-center justify-between gap-3">
                                <span class="rounded-full px-3 py-1 text-xs font-black text-slate-950" style="background-color: var(--group-color)">{{ $group->code }}</span>
                                <span class="text-xs font-bold uppercase text-yellow-200">Szczegóły</span>
                            </div>
                            <h3 class="mt-3 font-black">{{ $group->name }}</h3>
                            @if ($group->trainers->isNotEmpty())
                                <p class="mt-1 text-sm text-yellow-100">Trenerzy: {{ $group->trainers->pluck('name')->join(', ') }}</p>
                            @endif
                        </a>
                    @empty
                        <p class="rounded-lg border-2 border-dashed border-black p-4 text-sm font-semibold text-black">Brak aktywnych grup akademii.</p>
                    @endforelse
                </div>
            </aside>

            <div class="space-y-6">
                <section class="rounded-lg border-2 border-black bg-white p-4 text-slate-950 shadow-xl sm:p-5" x-data="{ training: null, day: null }">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-black">Kalendarz treningów</h2>
                            <p class="text-sm font-bold text-slate-700">{{ $monthLabel }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('academy', ['month' => $month->copy()->subMonth()->format('Y-m-d')]) }}" class="rounded-lg border border-black bg-yellow-400 px-3 py-2 text-sm font-bold text-black hover:bg-yellow-300">Poprzedni</a>
                            <a href="{{ route('academy', ['month' => $month->copy()->addMonth()->format('Y-m-d')]) }}" class="rounded-lg border border-black bg-yellow-400 px-3 py-2 text-sm font-bold text-black hover:bg-yellow-300">Następny</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 border border-black bg-black text-center text-xs font-black uppercase text-yellow-400">
                        @foreach (['Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob', 'Nd'] as $dayName)
                            <div class="border-r border-yellow-400/30 px-2 py-2 last:border-r-0">{{ $dayName }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 border-x border-black sm:grid-cols-7">
                        @foreach ($days as $day)
                            @php($dayTrainings = $trainingsByDay->get($day->format('Y-m-d'), collect()))
                            @php($dayNotes = $calendarNotes->filter(fn ($note) => $note->starts_on->lte($day) && $note->ends_on->gte($day))->values())
                            @php($dayHoliday = $publicHolidaysByDay->get($day->format('Y-m-d')))
                            @php($isRedCalendarDay = $day->isSunday() || $dayHoliday)
                            @php($dayBaseClasses = $isRedCalendarDay ? 'bg-red-50 text-red-500' : ($day->month === $month->month ? 'bg-white' : 'bg-yellow-50 text-slate-500'))
                            @php($dayItemCount = $dayTrainings->count() + $dayNotes->count() + ($dayHoliday ? 1 : 0))
                            @php($dayPayload = [
                                'date' => $day->format('d.m.Y'),
                                'trainings' => $dayTrainings->map(fn ($training) => [
                                    'group' => $training->group?->name,
                                    'code' => $training->group?->code,
                                    'title' => $training->title ?: 'Trening',
                                    'time' => $training->timeRange(),
                                    'location' => $training->location ?: 'Miejsce do ustalenia',
                                    'trainer' => $training->trainer_name ?: 'Trener do ustalenia',
                                    'status' => $training->statusLabel(),
                                    'cancelled' => $training->isCancelled(),
                                    'reason' => $training->cancelled_reason,
                                    'description' => $training->description,
                                    'color' => $training->group?->color ?? '#facc15',
                                ])->values(),
                                'notes' => $dayNotes->map(fn ($note) => [
                                    'title' => $note->title,
                                    'body' => $note->body,
                                    'range' => $note->starts_on->format('d.m.Y').' - '.$note->ends_on->format('d.m.Y'),
                                ])->values(),
                                'holidays' => $dayHoliday ? [[
                                    'title' => $dayHoliday['name'],
                                    'source' => 'Nager.Date',
                                ]] : [],
                            ])
                            <div data-academy-calendar-day="{{ $day->format('Y-m-d') }}" data-academy-holiday="{{ $dayHoliday ? '1' : '0' }}" class="min-h-32 border-b border-r border-black p-2 last:border-r-0 {{ $dayBaseClasses }}">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-sm font-black">{{ $day->format('j') }}</span>
                                    @if ($dayItemCount > 0)
                                        <span class="text-[11px] font-bold {{ $isRedCalendarDay ? 'text-red-400' : 'text-slate-500' }}">{{ $dayItemCount }}</span>
                                    @endif
                                </div>

                                <div class="space-y-1.5">
                                    @if ($dayHoliday)
                                        <button type="button" title="Święto publiczne" class="block w-full rounded bg-red-100 px-2 py-1.5 text-left text-xs font-black text-red-700 shadow-sm transition hover:bg-red-200" @click="day = {{ Js::from($dayPayload) }}">
                                            {{ $dayHoliday['name'] }}
                                        </button>
                                    @endif
                                    @foreach ($dayTrainings->take(3) as $training)
                                        @php($payload = [
                                            'group' => $training->group?->name,
                                            'code' => $training->group?->code,
                                            'title' => $training->title ?: 'Trening',
                                            'date' => $training->starts_at->format('d.m.Y'),
                                            'time' => $training->timeRange(),
                                            'location' => $training->location ?: 'Miejsce do ustalenia',
                                            'trainer' => $training->trainer_name ?: 'Trener do ustalenia',
                                            'status' => $training->statusLabel(),
                                            'cancelled' => $training->isCancelled(),
                                            'reason' => $training->cancelled_reason,
                                            'description' => $training->description,
                                        ])
                                        <button type="button" class="relative block w-full rounded px-2 py-1.5 text-left text-xs font-black text-slate-950 shadow-sm transition hover:scale-[1.01] {{ $training->isCancelled() ? 'opacity-55' : '' }}" style="background-color: {{ $training->group?->color ?? '#facc15' }}" @click="training = {{ Js::from($payload) }}">
                                            <span class="{{ $training->isCancelled() ? 'line-through decoration-2' : '' }}">{{ $training->group?->code }} {{ $training->starts_at->format('H:i') }}</span>
                                            @if ($training->isCancelled())
                                                <span class="block text-[10px] uppercase">Odwołany</span>
                                            @endif
                                        </button>
                                    @endforeach
                                    @foreach ($dayNotes->take(1) as $note)
                                        <button type="button" title="{{ $note->body }}" class="block w-full rounded bg-slate-200 px-2 py-1.5 text-left text-xs font-black text-slate-800 shadow-sm transition hover:bg-slate-300" @click="day = {{ Js::from($dayPayload) }}">
                                            {{ $note->title }}
                                        </button>
                                    @endforeach
                                    @if ($dayTrainings->count() > 3)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($dayTrainings->skip(3) as $training)
                                                <span class="h-3 w-3 rounded-sm border border-black/20" style="background-color: {{ $training->group?->color ?? '#facc15' }}" title="{{ $training->group?->code }} {{ $training->starts_at->format('H:i') }}"></span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($dayTrainings->isNotEmpty() || $dayNotes->isNotEmpty() || $dayHoliday)
                                        <button type="button" data-academy-day-preview class="mt-1 w-full rounded border border-black px-2 py-1 text-[11px] font-black uppercase text-black hover:bg-yellow-200" @click="day = {{ Js::from($dayPayload) }}">Podgląd dnia</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div x-show="training" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                        <div class="w-full max-w-xl rounded-lg border-2 border-yellow-400 bg-black p-6 text-yellow-400 shadow-2xl" @click.outside="training = null">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black uppercase text-yellow-200" x-text="training?.code"></p>
                                    <h3 class="text-2xl font-black" x-text="training?.title"></h3>
                                </div>
                                <button type="button" class="rounded p-1 hover:bg-yellow-400 hover:text-black" @click="training = null"><i data-lucide="x" class="h-5 w-5"></i></button>
                            </div>
                            <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                                <div class="rounded-lg bg-yellow-400 p-3 text-black"><dt class="font-bold text-zinc-700">Grupa</dt><dd class="font-black" x-text="training?.group"></dd></div>
                                <div class="rounded-lg bg-yellow-400 p-3 text-black"><dt class="font-bold text-zinc-700">Termin</dt><dd class="font-black"><span x-text="training?.date"></span>, <span x-text="training?.time"></span></dd></div>
                                <div class="rounded-lg bg-yellow-400 p-3 text-black"><dt class="font-bold text-zinc-700">Miejsce</dt><dd class="font-black" x-text="training?.location"></dd></div>
                                <div class="rounded-lg bg-yellow-400 p-3 text-black"><dt class="font-bold text-zinc-700">Prowadzi</dt><dd class="font-black" x-text="training?.trainer"></dd></div>
                            </dl>
                            <p x-show="training?.description" class="mt-4 rounded-lg bg-yellow-50 p-3 text-sm text-black" x-text="training?.description"></p>
                            <div x-show="training?.cancelled" class="mt-4 rounded-lg border border-yellow-400 bg-zinc-950 p-4 text-sm text-yellow-200">
                                <p class="font-black">Trening odwołany</p>
                                <p class="mt-1" x-text="training?.reason"></p>
                            </div>
                        </div>
                    </div>

                    <div x-show="day" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg border-2 border-yellow-400 bg-black p-6 text-yellow-400 shadow-2xl" @click.outside="day = null">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black uppercase text-yellow-200">Podgląd dnia</p>
                                    <h3 class="text-2xl font-black" x-text="day?.date"></h3>
                                </div>
                                <button type="button" class="rounded p-1 hover:bg-yellow-400 hover:text-black" @click="day = null"><i data-lucide="x" class="h-5 w-5"></i></button>
                            </div>

                            <div class="mt-5 space-y-4">
                                <template x-if="day?.holidays?.length">
                                    <div class="space-y-2">
                                        <h4 class="font-black text-yellow-200">Święta publiczne</h4>
                                        <template x-for="holiday in day.holidays" :key="holiday.title">
                                            <article class="rounded-lg bg-red-100 p-4 text-red-800">
                                                <h5 class="font-black" x-text="holiday.title"></h5>
                                                <p class="mt-1 text-xs font-bold" x-text="`Źródło: ${holiday.source}`"></p>
                                            </article>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="day?.notes?.length">
                                    <div class="space-y-2">
                                        <h4 class="font-black text-yellow-200">Wpisy w kalendarzu</h4>
                                        <template x-for="note in day.notes" :key="`${note.title}-${note.range}`">
                                            <article class="rounded-lg bg-slate-200 p-4 text-slate-950">
                                                <p class="text-xs font-black uppercase text-slate-600" x-text="note.range"></p>
                                                <h5 class="font-black" x-text="note.title"></h5>
                                                <p x-show="note.body" class="mt-1 text-sm" x-text="note.body"></p>
                                            </article>
                                        </template>
                                    </div>
                                </template>

                                <div class="space-y-2">
                                    <h4 class="font-black text-yellow-200">Treningi</h4>
                                    <template x-if="!day?.trainings?.length">
                                        <p class="rounded-lg border border-yellow-400/40 p-4 text-sm text-yellow-100">Brak treningów w tym dniu.</p>
                                    </template>
                                    <template x-for="training in day.trainings" :key="`${training.code}-${training.time}-${training.title}`">
                                        <article class="rounded-lg bg-yellow-400 p-4 text-black" :style="`border-left: 0.5rem solid ${training.color}`">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-black uppercase text-zinc-700" x-text="training.code"></p>
                                                    <h5 class="font-black" :class="training.cancelled ? 'line-through' : ''" x-text="training.title"></h5>
                                                </div>
                                                <span class="rounded-full bg-black px-2 py-1 text-xs font-black text-yellow-400" x-text="training.status"></span>
                                            </div>
                                            <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                                                <div><dt class="font-bold text-zinc-700">Godzina</dt><dd class="font-black" x-text="training.time"></dd></div>
                                                <div><dt class="font-bold text-zinc-700">Miejsce</dt><dd class="font-black" x-text="training.location"></dd></div>
                                                <div><dt class="font-bold text-zinc-700">Prowadzi</dt><dd class="font-black" x-text="training.trainer"></dd></div>
                                            </dl>
                                            <p x-show="training.description" class="mt-2 text-sm" x-text="training.description"></p>
                                            <p x-show="training.cancelled" class="mt-2 rounded bg-black px-3 py-2 text-sm font-bold text-yellow-400">Odwołany: <span x-text="training.reason"></span></p>
                                        </article>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-3 text-xs font-semibold text-slate-600">
                        Święta publiczne są synchronizowane z Nager.Date Holiday API:
                        <a href="{{ $holidaySourceUrl }}" target="_blank" rel="noopener noreferrer" class="font-black text-slate-900 underline hover:text-red-600">{{ $holidaySourceUrl }}</a>.
                    </p>
                </section>

                <section>
                    <h2 class="rounded-lg bg-black p-4 text-xl font-black text-yellow-400">Najbliższe treningi</h2>
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @forelse ($upcomingTrainings as $training)
                            <article class="rounded-lg border-2 border-black bg-black p-4 text-yellow-400">
                                <p class="text-sm font-bold text-yellow-200">{{ $training->starts_at->format('d.m.Y') }} · {{ $training->timeRange() }}</p>
                                <h3 class="mt-1 font-black {{ $training->isCancelled() ? 'line-through' : '' }}">{{ $training->group?->code }} {{ $training->title ?: 'Trening' }}</h3>
                                <p class="mt-1 text-sm text-yellow-100">{{ $training->location ?: 'Miejsce do ustalenia' }}</p>
                                @if ($training->isCancelled())
                                    <p class="mt-2 text-sm font-bold text-yellow-200">Odwołany: {{ $training->cancelled_reason }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-lg border-2 border-dashed border-black p-4 text-sm font-semibold text-black">Brak nadchodzących treningów.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
