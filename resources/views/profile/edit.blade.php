@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 min-h-screen text-gray-900" x-data="{ openModal: null }" @keydown.escape.window="openModal = null">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if ($isAdmin)
            <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                <h3 class="text-lg font-semibold mb-4">Zarządzanie użytkownikami</h3>
                <table class="min-w-full text-sm border"><tbody>
                @foreach ($users as $managedUser)
                    <tr class="border-t"><td class="p-2">{{ $managedUser->name }}</td><td class="p-2">{{ $managedUser->email }}</td><td class="p-2">
                        <form method="POST" action="{{ route('admin.users.role.update', $managedUser) }}">@csrf @method('PATCH')
                            <select name="role" class="border rounded px-2 py-1">@foreach ($availableRoles as $role)<option value="{{ $role }}" @selected($managedUser->role === $role)>{{ strtoupper($role) }}</option>@endforeach</select>
                            <button type="submit" class="ml-2 px-3 py-1 bg-yellow-500 text-black rounded">Zapisz</button>
                        </form></td></tr>
                @endforeach</tbody></table>
            </div>
        @endif

        @foreach ([['Matches','matches','opponent'],['News','news','title'],['Players','players','name']] as [$label,$key,$field])
            <div class="p-6 bg-white border border-gray-200 shadow rounded-lg">
                <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold">Latest {{ $label }}</h3><button class="px-3 py-2 bg-indigo-600 text-white rounded" @click="openModal='{{ $key }}'">Add</button></div>
                @forelse ($$key as $item)<div class="text-sm mb-1">{{ $key === 'players' ? $item->first_name.' '.$item->last_name : $item->$field }}</div>@empty<div>Brak {{ strtolower($label) }}.</div>@endforelse
            </div>
        @endforeach

        <x-modal name="matches-modal" ::show="openModal==='matches'" maxWidth="lg"><div class="p-6"><h4 class="font-semibold mb-4">Add match</h4><form method="POST" action="{{ route('matches.store') }}" class="space-y-3">@csrf
            <input name="opponent" required placeholder="Opponent" class="w-full border rounded" />
            <input type="datetime-local" name="match_date" required class="w-full border rounded" />
            <input name="location" required placeholder="Location" class="w-full border rounded" />
            <input name="result" placeholder="Result" class="w-full border rounded" />
            <input type="datetime-local" name="publish_at" class="w-full border rounded" />
            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button></form></div></x-modal>

        <x-modal name="news-modal" ::show="openModal==='news'" maxWidth="lg"><div class="p-6"><h4 class="font-semibold mb-4">Add news</h4><form method="POST" action="{{ route('news.store') }}" class="space-y-3">@csrf
            <input name="title" required class="w-full border rounded" />
            <textarea name="content" required class="w-full border rounded"></textarea>
            <input type="datetime-local" name="publish_at" class="w-full border rounded" />
            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button></form></div></x-modal>

        <x-modal name="players-modal" ::show="openModal==='players'" maxWidth="lg"><div class="p-6"><h4 class="font-semibold mb-4">Add player</h4><form method="POST" action="{{ route('players.store') }}" class="space-y-3">@csrf
            <input name="name" required placeholder="Name" class="w-full border rounded" />
            <input name="position" required class="w-full border rounded" />
            <input type="number" name="number" class="w-full border rounded" />
            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button></form></div></x-modal>
    </div>
</div>
@endsection
