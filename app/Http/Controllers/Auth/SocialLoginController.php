<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirect(string $provider)
    {
        if (! in_array($provider, ['google', 'facebook'])) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        if (! in_array($provider, ['google', 'facebook'])) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect()
                ->route('login')
                ->with('error', 'Unable to login using '.ucfirst($provider).'. Please try again.');
        }

        // 🔒 1. Enforce email presence
        $email = $socialUser->getEmail();

        if (! $email) {
            return redirect()
                ->route('register')
                ->with('error', 'We could not retrieve your email from '.ucfirst($provider).'. Please register using the email form and include your WhatsApp number.');
        }

        // ✅ Normal social login flow
        $user = User::where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (! $user) {
            // Try existing user by email
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->getName(),
                'first_name' => $this->extractFirstName($socialUser->getName()),
                'last_name' => $this->extractLastName($socialUser->getName()),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(32)),
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                // 'whatsapp_number' => null, // will be collected in onboarding
            ]);
        } else {
            $user->update([
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
            ]);
        }

        Auth::login($user, true);

        // 🔒 2. Enforce WhatsApp number presence
        if (empty($user->whatsapp_number)) {
            return redirect()->route('onboarding.contact');
        }

        return redirect()->intended(route('dashboard'));
    }

    protected function extractFirstName(?string $fullName): string
    {
        if (! $fullName) {
            return 'Guest';
        }

        $parts = preg_split('/\s+/', trim($fullName));

        return $parts[0] ?? $fullName;
    }

    protected function extractLastName(?string $fullName): string
    {
        if (! $fullName) {
            return '';
        }

        $parts = preg_split('/\s+/', trim($fullName));
        array_shift($parts);

        return implode(' ', $parts);
    }
}
