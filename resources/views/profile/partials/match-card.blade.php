<article data-admin-search x-show="!sectionQuery || $el.textContent.toLowerCase().includes(sectionQuery.toLowerCase())" class="etb-admin-card rounded border border-gray-200 bg-white p-4 shadow-sm">
    <div class="flex gap-4">
        <div class="flex items-center gap-2">
            @if ($match->home_logo)
                <img src="{{ \App\Support\MediaStorage::url($match->home_logo) }}"
                     alt="Logo ETB"
                     class="h-12 w-12 rounded bg-white object-contain p-1 ring-1 ring-gray-200">
            @endif

            @if ($match->opponent_logo)
                <img src="{{ \App\Support\MediaStorage::url($match->opponent_logo) }}"
                     alt="Logo przeciwnika {{ $match->opponent_name }}"
                     class="h-12 w-12 rounded bg-white object-contain p-1 ring-1 ring-gray-200">
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded bg-gray-100 text-xs font-semibold text-gray-500 ring-1 ring-gray-200">
                    Logo
                </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h5 class="font-semibold text-gray-950">ETB kontra {{ $match->opponent_name }}</h5>
                    <p class="text-sm text-gray-600">{{ $match->match_date->format('d.m.Y H:i') }}</p>
                </div>

                <span class="inline-flex w-fit rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-semibold text-yellow-900">
                    {{ $match->is_home ? 'U siebie' : 'Wyjazd' }}
                </span>
            </div>

            <dl class="mt-3 grid gap-2 text-sm text-gray-700">
                <div>
                    <dt class="font-medium text-gray-900">Lokalizacja</dt>
                    <dd>{{ $match->location }}</dd>
                </div>

                @if ($match->hasResult())
                    <div>
                        <dt class="font-medium text-gray-900">Wynik</dt>
                        <dd class="text-lg font-bold">{{ $match->resultLabel() }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('matches.show', $match) }}"
                   class="rounded border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-gray-100">
                    Szczegóły
                </a>

                @can('update', $match)
                    <button type="button"
                            class="rounded border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold hover:bg-gray-100"
                            @click="openModal = 'match-edit-{{ $match->id }}'">
                        Edytuj
                    </button>
                @endcan

                @can('delete', $match)
                    <form method="POST"
                          action="{{ route('matches.destroy', $match) }}"
                          onsubmit="return confirm('Czy na pewno usunąć ten mecz?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">
                            Usuń
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</article>

