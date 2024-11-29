<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Barryvdh\Debugbar\Facades\Debugbar;

class SiteController extends Controller
{
    public function home(): View 
    {
        Log::info('Loading welcome page');
        Debugbar::info('Loading welcome page!!!');
        return view('welcome');
    }

    public function contact(): View 
    {
        return view('contact');
    }

    public function sendMail(): RedirectResponse 
    {
        // Validació de les dades del formulari
        $validatedData = $request->validate([
            'name'    => 'nullable|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:15',
            'message' => 'required|string|min:5',
        ]);

        // Enviament del correu
        Mail::send('emails.contact', $validatedData, function ($message) use ($validatedData) {
            $message->to(config('mail.from.address'))
                    ->subject('Nou missatge de contacte')
                    ->replyTo($validatedData['email']);
        });

        return Redirect::to('contact');
    }
}
