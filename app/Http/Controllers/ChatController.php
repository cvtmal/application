<?php

namespace App\Http\Controllers;

use App\Jobs\LogChatRequestJob;
use App\Services\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(Request $request): Response
    {
        $chatHistory = session('chat_history', []);

        return Inertia::render('welcome', [
            'chatHistory' => $chatHistory,
        ]);
    }

    public function submitMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chatHistory = session('chat_history', []);

        // Add user message to chat history
        $chatHistory[] = [
            'type' => 'user',
            'content' => $validated['message'],
        ];

        // Initialize chat service with history
        $this->chatService->initializeWithHistory($chatHistory);

        // Get response from chat service, which now includes security checks
        $result = $this->chatService->getResponseForMessage($validated['message']);

        if (!$result['success']) {
            // If security check failed, add a system message explaining why
            $chatHistory[] = [
                'type' => 'assistant',
                'content' => $result['message'],
            ];

            // Store the updated chat history
            session(['chat_history' => $chatHistory]);

            // Log the rejection if configured
            if (config('security.log_rejections', true)) {
                Log::warning('Message rejected', [
                    'message' => $validated['message'],
                    'reason' => $result['reason'] ?? 'Unknown'
                ]);
            }

            return redirect()->back()->with('error', $result['reason'] ?? 'Message could not be processed');
        }

        // If successful, add the assistant's response to chat history
        $chatHistory[] = [
            'type' => 'assistant',
            'content' => $result['message'],
        ];

        // Store the updated chat history
        session(['chat_history' => $chatHistory]);

        // Dispatch log job
        LogChatRequestJob::dispatch();

        return redirect()->back();
    }

    public function clearChat(Request $request): RedirectResponse
    {
        session()->forget('chat_history');

        return redirect()->route('chat.index');
    }
}
