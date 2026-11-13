@props(['match', 'showResult' => false])

@php
    $ourLogoUrl = \App\Support\MediaStorage::url($match->home_logo)
        ?: ($clubLogoUrl ?? null)
        ?: \App\Support\MediaStorage::url(\App\Models\AppSetting::getValue('default_home_logo'));
    $opponentLogo = $match->opponent_logo ?: $match->opponent?->logo_path;
@endphp

<div {{ $attributes->class(['grid w-full grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-start gap-3']) }}>
    <div class="flex min-w-0 flex-col items-center gap-2 text-center" data-team="etb">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded bg-white p-2">
            <x-site-logo :url="$ourLogoUrl" :alt="'Logo '.$publicTeamName" image-class="max-h-full max-w-full object-contain" fallback-class="text-lg font-black text-zinc-900" />
        </div>
        <span class="break-words text-base font-black">{{ $publicTeamName }}</span>
    </div>
    @if ($showResult && $match->hasResult())
        <div class="flex h-16 flex-col items-center justify-center {{ $match->isWin() ? 'text-emerald-400' : 'text-red-400' }}">
            <span class="whitespace-nowrap text-xl font-black leading-none tabular-nums sm:text-2xl">{{ $match->our_score }}:{{ $match->opponent_score }}</span>
            <span class="mt-1 text-[0.625rem] font-bold uppercase tracking-wide">{{ $match->isWin() ? 'Zwycięstwo' : 'Porażka' }}</span>
        </div>
    @else
        <span class="pt-5 text-lg font-black" aria-label="kontra">–</span>
    @endif
    <div class="flex min-w-0 flex-col items-center gap-2 text-center" data-team="opponent">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded bg-white p-2">
            @if ($opponentLogo)
                <img src="{{ \App\Support\MediaStorage::url($opponentLogo) }}" alt="Logo {{ $match->opponent_name }}" class="max-h-full max-w-full object-contain">
            @else
                <span class="text-xs font-black text-zinc-500">Brak logo</span>
            @endif
        </div>
        <span class="break-words text-base font-black">{{ $match->opponent_name }}</span>
    </div>
</div>
