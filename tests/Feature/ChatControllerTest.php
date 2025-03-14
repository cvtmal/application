<?php

use App\Services\ChatService;

beforeEach(function (): void {
    $this->mock(ChatService::class, function ($mock): void {
        $mock->shouldReceive('initializeWithHistory')->andReturnSelf();
        $mock->shouldReceive('getResponseForMessage')
            ->andReturn('This is a mocked response from the AI assistant.');
    });
});

test('chat index page can be rendered', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('user can clear chat history', function (): void {
    session(['chat_history' => [
        ['type' => 'user', 'content' => 'Question 1'],
        ['type' => 'assistant', 'content' => 'Answer 1'],
    ]]);

    $response = $this->get('/clear-chat');

    $response->assertRedirect('/');
    expect(session('chat_history'))->toBeNull();
});

test('message validation fails with empty message', function (): void {
    $response = $this->post('/submit-message', [
        'message' => '',
    ]);

    $response->assertSessionHasErrors(['message']);
});

test('message validation fails with too long message', function (): void {
    $longMessage = str_repeat('a', 1500);

    $response = $this->post('/submit-message', [
        'message' => $longMessage,
    ]);

    $response->assertSessionHasErrors(['message']);
});
