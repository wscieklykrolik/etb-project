@php
    $sectionId = $sectionId ?? null;
    $eyebrow = $eyebrow ?? null;
    $title = $title ?? 'Sekcja';
    $description = $description ?? 'Sekcja gotowa do dodawania treści, tekstu, zdjęć i materiałów wideo.';
    $panelTitle = $panelTitle ?? 'Panel treści';
    $panelText = $panelText ?? 'Tutaj można osadzać artykuły, galerie, listy zawodników i inne moduły.';
    $actionUrl = $actionUrl ?? null;
    $actionLabel = $actionLabel ?? null;
    $externalAction = $externalAction ?? false;
@endphp

<section @if($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-28">
    <div class="mb-6">
        @if ($eyebrow)
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">{{ $eyebrow }}</p>
        @endif
        <h2 class="mt-2 text-3xl font-black text-white">{{ $title }}</h2>
        <p class="mt-3 max-w-3xl text-zinc-300">{{ $description }}</p>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-zinc-700 bg-gradient-to-br from-zinc-900 via-zinc-900 to-zinc-950 p-6 shadow-xl shadow-black/20 sm:p-8">
        @if ($externalAction)
            <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-yellow-400/10 blur-3xl" aria-hidden="true"></div>
        @endif

        <div class="relative @if($externalAction) grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center @endif">
            <div>
                @if ($externalAction)
                    <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-400 text-black shadow-lg shadow-yellow-400/10" aria-hidden="true">
                        <i data-lucide="trophy" class="h-5 w-5"></i>
                    </div>
                @endif

                <h3 class="text-lg font-bold text-white">{{ $panelTitle }}</h3>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-300">{{ $panelText }}</p>

                @if ($externalAction)
                    <div class="mt-5 flex max-w-2xl items-start gap-3 rounded-xl border border-yellow-400/25 bg-yellow-400/10 px-4 py-3 text-sm leading-5 text-yellow-50">
                        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-yellow-400" aria-hidden="true"></i>
                        <p>
                            Oficjalna strona ŁZKosz otworzy się w nowej karcie.
                            <strong class="font-bold text-white">Strona ETB pozostanie otwarta tutaj.</strong>
                        </p>
                    </div>
                @endif
            </div>

            @if ($actionUrl && $actionLabel)
                <a
                    href="{{ $actionUrl }}"
                    @if($externalAction) target="_blank" rel="noopener noreferrer" @endif
                    class="mt-5 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-yellow-400 px-5 py-3 text-sm font-black text-black shadow-lg shadow-yellow-400/10 transition hover:-translate-y-0.5 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 focus:ring-offset-zinc-900 lg:mt-0"
                    @if($externalAction) aria-describedby="third-league-new-tab-note" @endif
                >
                    {{ $actionLabel }}
                    @if ($externalAction)
                        <i data-lucide="external-link" class="h-4 w-4" aria-hidden="true"></i>
                        <span class="sr-only">(otwiera się w nowej karcie)</span>
                    @endif
                </a>
            @endif
        </div>

        @if ($externalAction)
            <span id="third-league-new-tab-note" class="sr-only">Oficjalna strona ŁZKosz otworzy się w nowej karcie, a strona ETB pozostanie otwarta.</span>
        @endif
    </div>
</section>
