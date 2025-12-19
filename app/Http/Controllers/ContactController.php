<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactSubmitRequest;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(ContactSubmitRequest $request)
    {
        $to = "printair2@gmail.com";

        if (! $to) {
            Log::warning('Contact form submitted but CONTACT_EMAIL is not configured.');

            return back()
                ->withInput()
                ->with('error', 'Contact email is not configured. Please try again later.');
        }

        $payload = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
            'ip' => $request->ip(),
            'ua' => (string) $request->userAgent(),
        ];

        try {
            Mail::to($to)->send(new ContactMessageMail($payload));

            return back()->with('success', 'Thanks! We received your message. We’ll reply ASAP.');
        } catch (\Throwable $e) {
            Log::error('Contact form email send failed', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Sorry — something went wrong while sending your message.');
        }
    }
}

