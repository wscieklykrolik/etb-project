<span class="inline-flex items-center gap-1.5" role="list" aria-label="Ostatnie mecze od najstarszego do najnowszego">
    @foreach($results as $result)
        @php($won = $result['result'] === 'W')
        <span role="listitem" title="{{ $won ? 'Zwycięstwo' : 'Porażka' }}{{ !empty($result['opponent']) ? ' — '.$result['opponent'] : '' }}" aria-label="{{ $won ? 'Zwycięstwo' : 'Porażka' }}{{ !empty($result['opponent']) ? ' — '.$result['opponent'] : '' }}" @class(['inline-flex h-7 w-7 items-center justify-center rounded-md text-xs font-black ring-1 ring-inset', 'bg-emerald-400/15 text-emerald-300 ring-emerald-400/30' => $won, 'bg-red-400/15 text-red-300 ring-red-400/30' => ! $won])>{{ $won ? 'Z' : 'P' }}</span>
    @endforeach
</span>
