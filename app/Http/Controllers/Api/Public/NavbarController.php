<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\NavbarDataService;

class NavbarController extends Controller
{
    public function __invoke(NavbarDataService $service)
    {
        return response()->json($service->get());
    }
}

