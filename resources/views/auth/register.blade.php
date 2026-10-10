<x-guest-layout>
    <h1 class="text-2xl font-bold text-black">Rejestracja konta</h1>
    <p class="mt-1 text-sm text-black/80">Masz już konto? <a href="{{ route('login') }}" class="font-semibold underline">Zaloguj się</a>.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="name" class="text-black font-semibold" value="Imię i nazwisko" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-700" />
        </div>

        <div>
            <x-input-label for="email" class="text-black font-semibold" value="Adres e-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-700" />
        </div>

        <div>
            <x-input-label for="password" class="text-black font-semibold" value="Hasło" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-700" />
        </div>

        <div>
            <x-input-label for="password_confirmation" class="text-black font-semibold" value="Potwierdź hasło" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-700" />
        </div>

        <div class="rounded-md border-2 border-black bg-white p-4 text-black space-y-3">
            <label class="flex items-start gap-2">
                <input type="checkbox" name="accepted_terms" value="1" class="mt-1 rounded border-black text-black focus:ring-black" {{ old('accepted_terms') ? 'checked' : '' }} required>
                <span class="text-sm">Akceptuję <a href="{{ route('important-pages.show', 'regulamin-sklepu') }}" target="_blank" rel="noopener noreferrer" class="font-semibold underline">regulamin</a> oraz warunki korzystania z platformy ETB.</span>
            </label>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="accepted_privacy" value="1" class="mt-1 rounded border-black text-black focus:ring-black" {{ old('accepted_privacy') ? 'checked' : '' }} required>
                <span class="text-sm">Akceptuję <a href="{{ route('important-pages.show', 'polityka-prywatnosci') }}" target="_blank" rel="noopener noreferrer" class="font-semibold underline">politykę prywatności</a> i przetwarzanie danych zgodnie z wymaganiami ETB.</span>
            </label>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="marketing_email_consent" value="1" class="mt-1 rounded border-black text-black focus:ring-black" {{ old('marketing_email_consent') ? 'checked' : '' }}>
                <span class="text-sm">Chcę otrzymywać informacje promocyjne, newsletter i wiadomości marketingowe ETB na podany adres e-mail.</span>
            </label>
            <x-input-error :messages="$errors->get('accepted_terms')" class="text-red-700" />
            <x-input-error :messages="$errors->get('accepted_privacy')" class="text-red-700" />
            <x-input-error :messages="$errors->get('marketing_email_consent')" class="text-red-700" />
        </div>

        <div class="flex items-center justify-end pt-2">
            <x-primary-button>
                Zarejestruj się
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
