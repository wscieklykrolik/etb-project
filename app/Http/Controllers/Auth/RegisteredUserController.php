<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ActivationCodeMail;
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
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'accepted_terms' => ['accepted'],
            'accepted_privacy' => ['accepted'],
            'marketing_email_consent' => ['nullable', 'boolean'],
        ]);

        $code = (string) random_int(100000, 999999);
        $ttlMinutes = max(1, (int) config('security.registration_code.ttl_minutes', 10));

        PendingRegistration::where('code_expires_at', '<', now())->delete();
        PendingRegistration::where('email', $validated['email'])->delete();

        PendingRegistration::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'accepted_terms' => (bool) $request->boolean('accepted_terms'),
            'accepted_privacy' => (bool) $request->boolean('accepted_privacy'),
            'marketing_email_consent' => (bool) $request->boolean('marketing_email_consent'),
            'verification_code' => Hash::make($code),
            'code_expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        Mail::to($validated['email'])->send(new ActivationCodeMail($code));

        return redirect()->route('register.verify.notice', [
            'email' => $validated['email'],
        ])->with('status', 'Wysłaliśmy kod aktywacyjny na podany adres e-mail.');
    }

    public function showVerificationForm(Request $request): View
    {
        return view('auth.verify-registration-code', [
            'email' => (string) $request->query('email'),
        ]);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);
        $maxAttempts = max(1, (int) config('security.registration_code.max_attempts', 5));

        $pending = PendingRegistration::where('email', Str::lower($validated['email']))
            ->latest('id')
            ->first();

        if (! $pending) {
            $this->throwInvalidActivationCode();
        }

        if (now()->greaterThan($pending->code_expires_at)) {
            $pending->delete();
            $this->throwInvalidActivationCode();
        }

        if ($pending->verification_attempts >= $maxAttempts) {
            $pending->delete();
            $this->throwInvalidActivationCode();
        }

        if (! Hash::check($validated['code'], $pending->verification_code)) {
            $pending->increment('verification_attempts');
            $pending->refresh();

            if ($pending->verification_attempts >= $maxAttempts) {
                $pending->delete();
            }

            $this->throwInvalidActivationCode();
        }

        $user = User::create([
            'name' => $pending->name,
            'email' => $pending->email,
            'password' => $pending->password,
            'role' => User::ROLE_FAN,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $user->fanProfile()->create([
            'can_buy_tickets' => true,
            'can_buy_merch' => true,
            'marketing_email_consent' => $pending->marketing_email_consent,
        ]);

        $pending->delete();

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(route('home', absolute: false));
    }

    private function throwInvalidActivationCode(): never
    {
        throw ValidationException::withMessages([
            'code' => 'Nieprawidłowy lub wygasły kod aktywacyjny.',
        ]);
    }
}
