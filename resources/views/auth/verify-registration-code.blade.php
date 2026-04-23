<x-guest-layout>
    <x-auth-session-status class="mb-4 text-sm text-black" :status="session('status')" />

    <h1 class="text-2xl font-bold text-black">Weryfikacja maila</h1>
    <p class="mt-1 text-sm text-black/80">Wpisz 6-cyfrowy kod wysłany na adres e-mail, aby dokończyć tworzenie konta.</p>

    <form method="POST" action="{{ route('register.verify') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="email" class="text-black font-semibold" value="Email" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email', $email)" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-700" />
        </div>

        <div>
            <x-input-label for="code" class="text-black font-semibold" value="Kod aktywacyjny" />
            <x-text-input id="code" class="mt-1 block w-full tracking-[0.4em] text-center" type="text" name="code" maxlength="6" inputmode="numeric" :value="old('code')" required />
            <x-input-error :messages="$errors->get('code')" class="mt-2 text-red-700" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('register') }}" class="text-sm text-black underline">Wróć do rejestracji</a>
            <x-primary-button>
                Potwierdź kod
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
