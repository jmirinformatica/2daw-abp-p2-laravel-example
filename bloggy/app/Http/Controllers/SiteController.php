<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Barryvdh\Debugbar\Facades\Debugbar;

class SiteController extends Controller
{
    public function home(): View {
        Log::info('Loading welcome page');
        Debugbar::info('Loading welcome page!!!');
        return view('welcome');
    }
}
