<?php

use App\Http\Controllers\Api\Public\NavbarController;
use Illuminate\Support\Facades\Route;

Route::get('/public/navbar', NavbarController::class)->name('api.public.navbar');

