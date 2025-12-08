<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HomeController extends Controller
{
    protected function usertype()
    {
        if (!Auth::check()) {
            return 'login';
        } else {
            $role = Auth::user()->role->name;
            $dashboard = match ($role) {
                'Super Admin' => 'superadmin.dashboard',
                'Admin' => 'admin.dashboard',
                'Staff' => 'staff.dashboard',
                'User' => 'user.dashboard',
                default => 'user.dashboard',
            };
            return $dashboard;
        }
    }

    public function index()
    {
        $seo = [
            'title' => 'Home',
            'description' => 'Printair Advertising is a leading printing company in Sri Lanka, offering premium digital, offset, and large-format printing solutions. From roll-up banners and X-banners to stickers, labels, invitations, business cards, and custom branding materials, we deliver fast, high-quality, professional printing for businesses and events.',
            'keywords' => 'printing sri lanka, printair, print shop sri lanka, printing services, roll up banner sri lanka, x banner printing, sticker printing sri lanka, digital printing, offset printing, large format printing, business cards sri lanka, invitation printing, label printing, custom banners sri lanka, signage printing, outdoor printing',
        ];

        $dashboard = $this->usertype();
        return view('home', compact('seo', 'dashboard'));
    }
}
