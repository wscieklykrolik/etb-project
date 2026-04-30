<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-black min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-12">
                <aside class="lg:col-span-4">
                    <section class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg h-full">
                        <h3 class="text-xl font-semibold mb-1">Common Profile</h3>
                        <p class="text-sm text-zinc-400">Manage your account details and security settings.</p>
                    </section>
                </aside>

                <main class="lg:col-span-8 space-y-6">
                    <section class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                        <h3 class="text-xl font-semibold mb-1">Profile Information</h3>
                        <p class="text-sm text-zinc-400 mb-6">Update your name and email address.</p>

                        <div class="max-w-2xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </section>

                    <section class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                        <h3 class="text-xl font-semibold mb-1">Password</h3>
                        <p class="text-sm text-zinc-400 mb-6">Update your account password.</p>

                        <div class="max-w-2xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </section>

                    @if ($isAdmin)
                        <section class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                            <h3 class="text-xl font-semibold mb-4">User Management</h3>

                            @if (session('status') === 'role-updated')
                                <p class="text-sm text-green-400 mb-4">User role updated successfully.</p>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-700 text-sm">
                                    <thead>
                                        <tr>
                                            <th class="py-2 text-left">Name</th>
                                            <th class="py-2 text-left">Email</th>
                                            <th class="py-2 text-left">Role</th>
                                            <th class="py-2 text-left">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-800">
                                        @foreach ($users as $managedUser)
                                            <tr>
                                                <td class="py-3 pr-4">{{ $managedUser->name }}</td>
                                                <td class="py-3 pr-4">{{ $managedUser->email }}</td>
                                                <td class="py-3 pr-4 uppercase">{{ $managedUser->role }}</td>
                                                <td class="py-3 pr-4">
                                                    <form method="POST" action="{{ route('admin.users.role.update', $managedUser) }}" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="role" class="rounded-md border-zinc-700 bg-zinc-800 text-zinc-100">
                                                            @foreach ($availableRoles as $role)
                                                                <option value="{{ $role }}" @selected($managedUser->role === $role)>
                                                                    {{ strtoupper($role) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="px-3 py-1 rounded bg-yellow-500 text-black font-semibold">
                                                            Save
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif

                    @if ($isEmployee)
                        <section class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                            <h3 class="text-xl font-semibold mb-4">Quick Access Panel</h3>
                            <div class="grid md:grid-cols-3 gap-4 text-sm">
                                <a href="{{ route('players.create') }}" class="rounded-lg border border-zinc-700 p-4 hover:bg-zinc-800">Create player</a>
                                <a href="{{ route('news.create') }}" class="rounded-lg border border-zinc-700 p-4 hover:bg-zinc-800">Create news</a>
                                <a href="{{ route('matches.create') }}" class="rounded-lg border border-zinc-700 p-4 hover:bg-zinc-800">Create match</a>
                            </div>
                        </section>
                    @endif

                    @if ($isAthlete)
                        <section class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                            <h3 class="text-xl font-semibold mb-4">Athlete Panel</h3>

                            <div class="mb-6">
                                <p class="text-sm text-zinc-400">Player data</p>
                                @if ($athleteProfile)
                                    <pre class="mt-2 text-xs bg-zinc-950 border border-zinc-800 rounded p-3 overflow-x-auto">{{ json_encode($athleteProfile->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    <p class="text-sm text-zinc-300 mt-1">No athlete profile data found yet.</p>
                                @endif
                            </div>

                            <div class="grid md:grid-cols-3 gap-4 text-sm">
                                <div class="rounded-lg border border-zinc-700 p-4">Training schedule (placeholder)</div>
                                <div class="rounded-lg border border-zinc-700 p-4">Availability (placeholder)</div>
                                <div class="rounded-lg border border-zinc-700 p-4">Stats (future)</div>
                            </div>
                        </section>
                    @endif

                    <section class="p-4 sm:p-8 bg-zinc-900 border border-zinc-800 shadow sm:rounded-lg">
                        <h3 class="text-xl font-semibold mb-4">Delete account</h3>
                        <div class="max-w-2xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
