<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function contact()
    {
        $seo = [
            'title' => 'Contact Us | Printair Advertising',
            'description' => 'Contact Printair Advertising for quotations, corporate partnerships, and printing services. Email, WhatsApp, or send a message through our contact form.',
            'keywords' => 'Printair contact, printair.lk contact, printing sri lanka contact, whatsapp printair',
            'canonical' => url('/contact'),
            'image' => asset('assets/printair/printairlogo.png'),
        ];

        return view('pages.contact', compact('seo'));
    }

    public function sendContact(ContactRequest $request)
    {
        if ($request->filled('website')) {
            return back()->with('success', 'Thanks! Your message has been received.');
        }

        $to = 'contact@printair.lk';

        Mail::to($to)->send(new ContactMessageMail([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
        ]));

        return back()->with('success', 'Thanks! We received your message and will respond soon.');
    }
}

