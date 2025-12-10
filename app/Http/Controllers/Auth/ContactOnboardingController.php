<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactOnboardingController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // If already has WhatsApp, no need to stay here
        if (! empty($user->whatsapp_number)) {
            return redirect()->route('dashboard');
        }

        return view('auth.complete-contact', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'whatsapp_number' => ['required', 'string', 'max:30'],
        ]);

        $user->update([
            'whatsapp_number' => $validated['whatsapp_number'],
        ]);

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Contact details completed. Welcome to Printair.');
    }
}
