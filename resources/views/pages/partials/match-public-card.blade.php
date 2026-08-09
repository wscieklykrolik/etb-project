@php
    use App\Models\TeamMatch;

    $logo = $match?->opponent_logo ?: $match?->opponent?->logo_path;
    $isFeatured = $featured ?? false;

    $cardClasses = $isFeatured
        ? 'scale-[1.03] border-yellow-400 bg-yellow-400 p-6 text-black shadow-2xl shadow-yellow-400/20 hover:-translate-y-2 hover:border-white hover:bg-yellow-300 lg:scale-105 lg:p-7'
        : 'border-zinc-800 bg-zinc-950 p-5 text-white shadow-xl hover:-translate-y-1 hover:border-yellow-400/70';

    $labelClasses = $isFeatured ? 'text-black' : 'text-yellow-400';
    $titleClasses = $isFeatured ? 'text-black' : 'text-white';
    $metaClasses = $isFeatured ? 'text-black/70' : 'text-zinc-400';
    $dateClasses = $isFeatured ? 'text-black/60' : 'text-zinc-500';
    $timeClasses = $isFeatured ? 'text-black' : 'text-white';
    $badgeClasses = $isFeatured ? 'bg-black text-yellow-400' : 'bg-zinc-900 text-yellow-400';
    $ticketClasses = $isFeatured ? 'bg-black px-4 py-3 text-yellow-400 hover:bg-white hover:text-black' : 'bg-yellow-400 px-3 py-2 text-black';
@endphp

@if($match)
<a href="{{ route('schedule.matches.show', $match) }}" class="group rounded-lg border transition duration-300 {{ $cardClasses }}">
    @isset($label)
        <p class="mb-4 text-xs font-black uppercase tracking-[0.2em] {{ $labelClasses }}">{{ $label }}</p>
    @endisset

    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded bg-white p-2">
                @if ($logo)
                    <img src="{{ asset('storage/'.$logo) }}" alt="{{ $match->opponent_name }}" class="max-h-full max-w-full object-contain">
                @else
                    <span class="text-xs font-black text-zinc-500">LOGO</span>
                @endif
            </div>
            <div>
                <h3 class="{{ $isFeatured ? 'text-2xl' : 'text-xl' }} font-black {{ $titleClasses }}">{{ $match->opponent_name }}</h3>
                <p class="text-sm font-semibold {{ $metaClasses }}">{{ $match->is_home ? 'Domowy' : 'Wyjazdowy' }} · {{ $match->location }}</p>
            </div>
        </div>

        <span class="rounded px-2 py-1 text-xs font-bold uppercase {{ $badgeClasses }}">
            {{ $match->status === TeamMatch::STATUS_FINISHED ? 'Zakończony' : 'Nadchodzący' }}
        </span>
    </div>

    <div class="mt-6 flex items-end justify-between gap-4">
        <div>
            <p class="text-sm uppercase tracking-widest {{ $dateClasses }}">{{ $match->match_date?->format('d.m.Y') }}</p>
            <p class="mt-1 {{ $isFeatured ? 'text-3xl' : 'text-2xl' }} font-black {{ $timeClasses }}">{{ $match->match_date?->format('H:i') }}</p>
        </div>

        @if ($match->status === TeamMatch::STATUS_FINISHED)
            <div class="text-right">
                <p class="text-3xl font-black {{ $match->isWin() ? 'text-emerald-400' : 'text-red-400' }}">{{ $match->our_score }}:{{ $match->opponent_score }}</p>
                <p class="text-xs font-bold uppercase tracking-widest {{ $match->isWin() ? 'text-emerald-400' : 'text-red-400' }}">{{ $match->isWin() ? 'Zwycięstwo' : 'Porażka' }}</p>
            </div>
        @elseif ($match->ticketSalesActive())
            <span class="rounded text-sm font-black transition {{ $ticketClasses }}">Kup bilety</span>
        @endif
    </div>
</a>
@endif
