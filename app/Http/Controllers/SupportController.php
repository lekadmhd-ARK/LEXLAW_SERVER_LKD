<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function showForm()
    {
        return view('support.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'min:10'],
        ]);

        $user = $request->user();

        Mail::raw(
            "Dari: {$user->name} <{$user->email}>\nPerusahaan: {$user->company?->name}\n\n" . $validated['message'],
            function ($message) use ($validated) {
                $message->to('support@arktech.id')
                    ->replyTo(auth()->user()->email, auth()->user()->name)
                    ->subject('[Support LEXLAW] ' . $validated['subject']);
            }
        );

        return back()->with('success', 'Pesan Anda terkirim. Tim kami akan membalas secepatnya.');
    }
}