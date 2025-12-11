<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;


class PasswordChangeController extends Controller
{
    /**
     * Show the form to force a password change.
     */
    public function showChangeForm()
    {
        return view('auth.force-password-change');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        $user->update([
            'password' => bcrypt($request->password),
            'force_password_change' => false,
        ]);

        return redirect('/')->with('success', 'Your password has been changed successfully.');
    }
}
