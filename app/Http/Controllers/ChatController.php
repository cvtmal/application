<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(Request $request)
    {
        $chatHistory = session('chat_history', []);

        return Inertia::render('welcome', [
            'chatHistory' => $chatHistory,
        ]);
    }

    public function submitMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chatHistory = session('chat_history', []);

        $chatHistory[] = [
            'type' => 'user',
            'content' => $validated['message'],
        ];

        $this->chatService->initializeWithHistory($chatHistory);

        $aiResponse = $this->chatService->getResponseForMessage($validated['message']);

        $chatHistory[] = [
            'type' => 'assistant',
            'content' => $aiResponse,
        ];

        session(['chat_history' => $chatHistory]);

        return redirect()->back();
    }

    public function clearChat(Request $request)
    {
        // Clear chat history in session
        session()->forget('chat_history');

        return redirect()->route('chat.index');
    }
}
