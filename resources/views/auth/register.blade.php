<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" value="Imię" />
                <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="last_name" value="Nazwisko" />
                <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Hasło" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <p class="mt-1 text-xs text-gray-500">Minimum 8 znaków, 1 duża litera, 1 cyfra i 1 znak specjalny.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Powtórz hasło" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="bg-gray-50 rounded-lg p-3 border space-y-2 text-sm">
            <label class="flex items-start gap-2">
                <input type="checkbox" name="accepted_store_terms" class="mt-1" value="1" {{ old('accepted_store_terms') ? 'checked' : '' }} required>
                <span>Akceptuję regulamin sklepu.</span>
            </label>
            <x-input-error :messages="$errors->get('accepted_store_terms')" class="mt-1" />

            <label class="flex items-start gap-2">
                <input type="checkbox" name="accepted_ticket_terms" class="mt-1" value="1" {{ old('accepted_ticket_terms') ? 'checked' : '' }} required>
                <span>Akceptuję regulamin zakupu biletów.</span>
            </label>
            <x-input-error :messages="$errors->get('accepted_ticket_terms')" class="mt-1" />

            <label class="flex items-start gap-2">
                <input type="checkbox" name="accepted_privacy_policy" class="mt-1" value="1" {{ old('accepted_privacy_policy') ? 'checked' : '' }} required>
                <span>Akceptuję politykę prywatności i przetwarzanie danych (RODO).</span>
            </label>
            <x-input-error :messages="$errors->get('accepted_privacy_policy')" class="mt-1" />

            <label class="flex items-start gap-2">
                <input type="checkbox" name="accepted_marketing_consent" class="mt-1" value="1" {{ old('accepted_marketing_consent') ? 'checked' : '' }} required>
                <span>Wyrażam zgodę marketingową.</span>
            </label>
            <x-input-error :messages="$errors->get('accepted_marketing_consent')" class="mt-1" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                Mam już konto
            </a>

            <x-primary-button class="ms-4">
                Zarejestruj
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
