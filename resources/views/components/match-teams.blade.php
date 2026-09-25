@props(['match'])

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
    <span class="pt-5 text-lg font-black" aria-label="kontra">–</span>
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
