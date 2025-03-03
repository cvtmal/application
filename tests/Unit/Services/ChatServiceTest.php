<?php

use App\Services\ChatService;

test('chat service initializes with history', function () {
    $chatService = new ChatService();

    $history = [
        ['type' => 'user', 'content' => 'Hello'],
        ['type' => 'assistant', 'content' => 'Hi there!'],
    ];

    $chatService->initializeWithHistory($history);

    $messages = $chatService->getMessages();
    expect($messages->count())->toBe(3);
});
