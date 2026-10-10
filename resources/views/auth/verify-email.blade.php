<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Potwierdź aktualny adres e-mail swojego konta, klikając link wysłany w wiadomości. Jeśli wiadomość nie dotarła, możemy wysłać ją ponownie.
    </div>

    <p class="mb-4 break-words text-sm font-semibold text-gray-900">Adres do potwierdzenia: {{ auth()->user()->email }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Link potwierdzający został wysłany na aktualny adres e-mail Twojego konta.
        </div>
    @endif

    <details class="mb-6 rounded-lg border border-gray-200 p-4 text-gray-900">
        <summary class="cursor-pointer text-sm font-semibold">Popraw adres e-mail</summary>
        <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-3">
            @csrf
            @method('PATCH')
            <input type="hidden" name="name" value="{{ auth()->user()->name }}">
            <x-input-label for="verification-email" value="Adres e-mail" />
            <x-text-input id="verification-email" name="email" type="email" class="block w-full" :value="old('email', auth()->user()->email)" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" />
            <p class="text-sm text-gray-600">Po zmianie adresu wyślemy na niego nowy link potwierdzający.</p>
            <x-primary-button>Zapisz poprawiony adres</x-primary-button>
        </form>
    </details>

    @if (session('mail_error'))
        <div class="mb-4 text-sm font-medium text-red-700" role="alert">{{ session('mail_error') }}</div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Wyślij ponownie e-mail weryfikacyjny
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Wyloguj się
            </button>
        </form>
    </div>
</x-guest-layout>
