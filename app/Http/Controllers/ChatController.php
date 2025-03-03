<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function prompt(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        // Process the message - in a real app, you might call an API, run some logic, etc.
        $response = "You said: " . $validated['message'];

        // Return to the previous page with flash data
        return redirect()->back()->with([
            'response' => $response,
            'success' => 'Message received successfully!'
        ]);
    }
}
