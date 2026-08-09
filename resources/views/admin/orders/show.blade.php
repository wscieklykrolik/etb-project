<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Zamówienie #{{ $order->id }}
            </h2>
            <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:underline text-sm">&larr; Powrót do listy</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Klient</p>
                            <p class="font-medium mt-1">{{ $order->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $order->user->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Status</p>
                            <p class="mt-1">
                                @php $colors = [
                                    'pending_payment' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'shipped' => 'bg-blue-100 text-blue-800',
                                    'delivered' => 'bg-gray-100 text-gray-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                ]; @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $colors[$order->status] ?? 'bg-gray-100' }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Data zamówienia</p>
                            <p class="font-medium mt-1">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        @if($order->paid_at)
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Data płatności</p>
                                <p class="font-medium mt-1">{{ $order->paid_at->format('d.m.Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $transitions = [
                    'paid' => ['label' => 'Oznacz jako opłacone', 'target' => 'paid', 'color' => 'bg-green-600 hover:bg-green-700'],
                    'shipped' => ['label' => 'Oznacz jako wysłane', 'target' => 'shipped', 'color' => 'bg-blue-600 hover:bg-blue-700'],
                    'delivered' => ['label' => 'Oznacz jako dostarczone', 'target' => 'delivered', 'color' => 'bg-gray-600 hover:bg-gray-700'],
                    'cancelled' => ['label' => 'Anuluj zamówienie', 'target' => 'cancelled', 'color' => 'bg-red-600 hover:bg-red-700'],
                ];
                $available = $order->availableTransitions();
            @endphp

            @if(!empty($available))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Zmiana statusu</h3>

                        @if($order->status === 'cancelled')
                            <form method="POST" action="{{ route('admin.orders.transition', $order) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="pending_payment">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium">Ponowne otwarcie zamówienia</p>
                                        <p class="text-sm text-gray-500">Anuluje poprzedni status i przywróci do oczekiwania na płatność.</p>
                                    </div>
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-4 py-2 rounded text-sm">
                                        Ponownie otwórz
                                    </button>
                                </div>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.orders.transition', $order) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')
                                <div class="flex flex-wrap gap-2">
                                    @foreach($available as $transition)
                                        <button type="submit" name="status" value="{{ $transition }}" class="{{ $transitions[$transition]['color'] ?? 'bg-gray-600 hover:bg-gray-700' }} text-white font-semibold px-4 py-2 rounded text-sm">
                                            {{ $transitions[$transition]['label'] ?? ucfirst($transition) }}
                                        </button>
                                    @endforeach
                                </div>
                                @if(in_array('cancelled', $available))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Notatka do anulowania (opcjonalna)</label>
                                        <input type="text" name="note" placeholder="Np. brak płatności, rezygnacja klienta..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    </div>
                                @endif
                                @if(in_array('shipped', $available))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Numer przesyłki</label>
                                        <input type="text" name="tracking_number" placeholder="Wpisz numer przesyłki..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    </div>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Pozycje zamówienia</h3>
                    <table class="w-full text-sm">
                        <thead class="text-gray-500 uppercase text-xs border-b">
                            <tr>
                                <th class="pb-2 text-left">Produkt</th>
                                <th class="pb-2 text-left">Rozmiar</th>
                                <th class="pb-2 text-right">Cena jedn.</th>
                                <th class="pb-2 text-right">Ilość</th>
                                <th class="pb-2 text-right">Suma</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-3">{{ $item->product->name ?? '[usunięty]' }}</td>
                                    <td class="py-3 text-gray-600">{{ $item->variantSize->size_label ?? '—' }}</td>
                                    <td class="py-3 text-right">{{ number_format($item->unit_price_grosze / 100, 2, ',', '') }} zł</td>
                                    <td class="py-3 text-right">{{ $item->qty }}</td>
                                    <td class="py-3 text-right font-medium">{{ number_format($item->subtotal() / 100, 2, ',', '') }} zł</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="text-sm font-medium border-t">
                            <tr>
                                <td colspan="3" class="pt-3 text-right">Produkty:</td>
                                <td class="pt-3 text-right">{{ number_format($order->total_grosze / 100, 2, ',', '') }} zł</td>
                            </tr>
                            @if($order->shipping_grosze > 0)
                                <tr>
                                    <td colspan="3" class="pt-1 text-right">Dostawa ({{ $order->shipping_method ?? '—' }}):</td>
                                    <td class="pt-1 text-right">{{ number_format($order->shipping_grosze / 100, 2, ',', '') }} zł</td>
                                </tr>
                            @endif
                            <tr class="text-base">
                                <td colspan="3" class="pt-2 text-right">Razem:</td>
                                <td class="pt-2 text-right">{{ $order->displayTotal() }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($order->shipping_address)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-3">Dane do nadania</h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Odbiorca</p>
                                <p class="mt-1 font-semibold">{{ $order->shipping_address['recipient_name'] ?? $order->user->name }}</p>
                                <p class="text-sm text-gray-600">{{ $order->user->email }}</p>
                            </div>
                            @if(! empty($order->shipping_address['locker_name']))
                                <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-yellow-800">Paczkomat do nadania</p>
                                    <p class="mt-1 text-lg font-black text-gray-950">{{ $order->shipping_address['locker_name'] }}</p>
                                    <p class="text-sm text-gray-700">{{ $order->shipping_address['locker_address'] ?? '' }}, {{ $order->shipping_address['locker_city'] ?? '' }}</p>
                                </div>
                            @else
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Adres</p>
                                    <p class="mt-1">{{ $order->shipping_address['street'] ?? '' }}</p>
                                    <p>{{ $order->shipping_address['postal_code'] ?? '' }} {{ $order->shipping_address['city'] ?? '' }}</p>
                                    <p>{{ $order->shipping_address['country'] ?? '' }}</p>
                                </div>
                            @endif
                        </div>
                        @if($order->shipping_method)
                            @php $shippingLabel = config('shipping.methods.' . $order->shipping_method . '.label', $order->shipping_method); @endphp
                            <p class="mt-3 inline-flex rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-900">Metoda: {{ $shippingLabel }}</p>
                        @endif
                        @if($order->tracking_number)
                            <p class="mt-1 text-sm text-gray-600">Numer przesyłki: {{ $order->tracking_number }}</p>
                        @endif
                        @if(in_array($order->status, ['paid', 'shipped']))
                            <form method="POST" action="{{ route('admin.orders.label', $order) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-3 py-1.5 rounded">
                                    Generuj etykietę
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            @if($order->invoice)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-3">Faktura</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Numer: <span class="font-medium text-gray-800">{{ $order->invoice->number }}</span></p>
                                <p class="text-gray-500">Data wystawienia: <span class="font-medium text-gray-800">{{ $order->invoice->issued_at->format('d.m.Y') }}</span></p>
                            </div>
                            <div class="text-right">
                                <p>Netto: <span class="font-medium">{{ $order->invoice->displayTotalNet() }}</span></p>
                                <p>VAT: <span class="font-medium">{{ $order->invoice->displayTotalVat() }}</span></p>
                                <p>Brutto: <span class="font-medium">{{ $order->invoice->displayTotalGross() }}</span></p>
                            </div>
                        </div>
                        <a href="{{ route('admin.orders.invoice', $order) }}" class="inline-block mt-3 text-sm bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1.5 rounded">
                            Pobierz PDF
                        </a>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-3">Informacje techniczne</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div class="flex gap-2">
                            <dt class="text-gray-500">ID:</dt>
                            <dd class="font-mono">{{ $order->id }}</dd>
                        </div>
                        @if($order->payment_session_id)
                            <div class="flex gap-2">
                                <dt class="text-gray-500">Session ID:</dt>
                                <dd class="font-mono text-xs break-all">{{ $order->payment_session_id }}</dd>
                            </div>
                        @endif
                        @if($order->idempotency_key)
                            <div class="flex gap-2">
                                <dt class="text-gray-500">Idempotency Key:</dt>
                                <dd class="font-mono text-xs break-all">{{ $order->idempotency_key }}</dd>
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <dt class="text-gray-500">Utworzono:</dt>
                            <dd>{{ $order->created_at->format('d.m.Y H:i:s') }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="text-gray-500">Zaktualizowano:</dt>
                            <dd>{{ $order->updated_at->format('d.m.Y H:i:s') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($order->statusLogs->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Historia zmian statusu</h3>
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($order->statusLogs as $log)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-5 -ml-px h-full w-0.5 bg-gray-200"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                        <span class="text-xs font-bold text-gray-600">#{{ $log->id }}</span>
                                                    </span>
                                                </div>
                                                <div class="flex-1 pt-1 flex justify-between items-start">
                                                    <div>
                                                        <p class="text-sm text-gray-800">
                                                            @if($log->from_status)
                                                                <span class="font-medium">{{ $order->statusLabel() }}</span>
                                                                {{ ' → ' }}
                                                                <span class="font-medium">{{ $log->to_status }}</span>
                                                            @else
                                                                Utworzono jako <span class="font-medium">{{ $log->to_status }}</span>
                                                            @endif
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            przez {{ $log->user->name }}
                                                            · {{ $log->created_at->format('d.m.Y H:i') }}
                                                        </p>
                                                        @if($log->note)
                                                            <p class="text-xs text-gray-600 mt-1 italic">"{{ $log->note }}"</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
