@extends('layouts.admin')
@section('title', 'Panel administracyjny')

@section('admin-content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-950">Panel administracyjny</h1>
    <p class="mt-1 text-sm text-slate-500">Przegląd statystyk i aktywności klubu.</p>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 mb-8">
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Użytkownicy</p>
        <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stats['users']) }}</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Zamówienia</p>
        <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stats['orders']) }}</p>
        @if($stats['ordersPending'] > 0)
            <p class="mt-1 text-xs text-yellow-800">{{ $stats['ordersPending'] }} oczekuje na płatność</p>
        @endif
    </div>
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Przychód</p>
        <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stats['revenue'] / 100, 2, ',', '') }} zł</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Produkty</p>
        <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stats['products']) }}</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Mecze</p>
        <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stats['matches']) }}</p>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-3">
            <h2 class="text-sm font-semibold text-slate-900">Ostatnie zamówienia</h2>
        </div>
        <div class="p-5">
            @if($recentOrders->isNotEmpty())
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-500">
                            <th class="pb-2 pr-2 font-medium">ID</th>
                            <th class="pb-2 pr-2 font-medium">Klient</th>
                            <th class="pb-2 pr-2 font-medium">Status</th>
                            <th class="pb-2 text-right font-medium">Kwota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($recentOrders as $order)
                            <tr>
                                <td class="py-2 pr-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-yellow-800 hover:text-yellow-900">#{{ $order->id }}</a>
                                </td>
                                <td class="py-2 pr-2 text-slate-600">{{ $order->user->name }}</td>
                                <td class="py-2 pr-2">
                                    @php $colors = [
                                        'pending_payment' => 'bg-yellow-500/10 text-yellow-800',
                                        'paid' => 'bg-green-500/10 text-emerald-700',
                                        'shipped' => 'bg-blue-500/10 text-blue-700',
                                        'delivered' => 'bg-zinc-500/10 text-slate-600',
                                        'cancelled' => 'bg-red-500/10 text-red-700',
                                        'failed' => 'bg-red-500/10 text-red-700',
                                    ]; @endphp
                                    <span class="inline-block rounded px-2 py-0.5 text-xs font-medium {{ $colors[$order->status] ?? 'bg-zinc-500/10 text-slate-600' }}">
                                        {{ $order->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-2 text-right text-slate-900">{{ $order->displayTotal() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-slate-500">Brak zamówień.</p>
            @endif
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-3">
            <h2 class="text-sm font-semibold text-slate-900">Ostatnie mecze</h2>
        </div>
        <div class="p-5">
            @if($recentMatches->isNotEmpty())
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-500">
                            <th class="pb-2 pr-2 font-medium">Przeciwnik</th>
                            <th class="pb-2 pr-2 font-medium">Data</th>
                            <th class="pb-2 pr-2 font-medium">Miejsce</th>
                            <th class="pb-2 text-right font-medium">Wynik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($recentMatches as $match)
                            <tr>
                                <td class="py-2 pr-2 text-slate-900">{{ $match->opponent }}</td>
                                <td class="py-2 pr-2 text-slate-600">{{ $match->match_date->format('d.m.Y') }}</td>
                                <td class="py-2 pr-2 text-slate-600">{{ $match->location }}</td>
                                <td class="py-2 text-right">
                                    @if($match->isFinished())
                                        <span class="font-medium text-slate-900">{{ $match->home_score }}:{{ $match->away_score }}</span>
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-slate-500">Brak meczów.</p>
            @endif
        </div>
    </div>

    @if($ordersByStatus->isNotEmpty())
        <div class="rounded-lg border border-slate-200 bg-white lg:col-span-2">
            <div class="border-b border-slate-200 px-5 py-3">
                <h2 class="text-sm font-semibold text-slate-900">Zamówienia według statusu</h2>
            </div>
            <div class="flex flex-wrap gap-3 p-5">
                @foreach($ordersByStatus as $status => $count)
                    @php $map = [
                        'pending_payment' => ['label' => 'Oczekujące', 'color' => 'bg-yellow-500/10 text-yellow-800 border-yellow-500/20'],
                        'paid' => ['label' => 'Opłacone', 'color' => 'bg-green-500/10 text-emerald-700 border-green-500/20'],
                        'shipped' => ['label' => 'Wysłane', 'color' => 'bg-blue-500/10 text-blue-700 border-blue-500/20'],
                        'delivered' => ['label' => 'Dostarczone', 'color' => 'bg-zinc-500/10 text-slate-600 border-zinc-500/20'],
                        'cancelled' => ['label' => 'Anulowane', 'color' => 'bg-red-500/10 text-red-700 border-red-500/20'],
                        'failed' => ['label' => 'Błąd', 'color' => 'bg-red-500/10 text-red-700 border-red-500/20'],
                    ]; @endphp
                    <div class="rounded-lg border px-4 py-3 {{ $map[$status]['color'] ?? 'bg-zinc-500/10 text-slate-600 border-zinc-500/20' }}">
                        <p class="text-xs font-medium uppercase tracking-wider">{{ $map[$status]['label'] ?? $status }}</p>
                        <p class="mt-1 text-2xl font-bold">{{ $count }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
