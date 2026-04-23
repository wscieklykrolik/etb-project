<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'accepted_terms' => ['accepted'],
            'accepted_privacy' => ['accepted'],
        ]);

        $code = (string) random_int(100000, 999999);

        PendingRegistration::where('email', $validated['email'])->delete();

        PendingRegistration::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'accepted_terms' => (bool) $request->boolean('accepted_terms'),
            'accepted_privacy' => (bool) $request->boolean('accepted_privacy'),
            'verification_code' => Hash::make($code),
            'code_expires_at' => now()->addMinutes(15),
        ]);

        Mail::raw(
            "Twój kod aktywacyjny ETB: {$code}. Kod jest ważny 15 minut.",
            static fn ($message) => $message->to($validated['email'])->subject('Kod aktywacyjny ETB')
        );

        return redirect()->route('register.verify.notice', ['email' => $validated['email']])
            ->with('status', 'Wysłaliśmy kod aktywacyjny na podany adres e-mail.');
    }

    public function showVerificationForm(Request $request): View
    {
        return view('auth.verify-registration-code', [
            'email' => (string) $request->query('email'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $pending = PendingRegistration::where('email', Str::lower($validated['email']))->latest('id')->first();

        if (! $pending || now()->greaterThan($pending->code_expires_at) || ! Hash::check($validated['code'], $pending->verification_code)) {
            throw ValidationException::withMessages([
                'code' => 'Nieprawidłowy lub wygasły kod aktywacyjny.',
            ]);
        }

        $user = User::create([
            'name' => $pending->name,
            'email' => $pending->email,
            'password' => $pending->password,
            'role' => User::ROLE_FAN,
        ]);

        $user->fanProfile()->create([
            'can_buy_tickets' => true,
            'can_buy_merch' => true,
        ]);

        $pending->delete();

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
