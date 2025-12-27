<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class CheckoutPageController extends Controller
{
    public function index()
    {
        $seo = [
            'title' => 'Checkout | Printair',
            'description' => 'Secure checkout for Printair orders with email verification.',
            'keywords' => 'printair checkout, printair order',
            'canonical' => url('/checkout'),
            'image' => asset('assets/printair/printairlogo.png'),
        ];

        return view('public.checkout.index', compact('seo'));
    }
}

