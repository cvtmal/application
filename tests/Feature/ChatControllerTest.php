<?php

use App\Services\ChatService;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->mock(ChatService::class, function ($mock) {
        $mock->shouldReceive('initializeWithHistory')->andReturnSelf();
        $mock->shouldReceive('getResponseForMessage')
            ->andReturn('This is a mocked response from the AI assistant.');
    });
});

test('chat index page can be rendered', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) =>
    $page->component('welcome')
        ->has('chatHistory')
    );
});

test('user can submit a message', function () {
    $response = $this->post('/submit-message', [
        'message' => 'Hello, who are you?'
    ]);

    $response->assertRedirect('/');

    $chatHistory = session('chat_history');
    expect($chatHistory)->toBeArray();
    expect($chatHistory)->toHaveCount(2); // User msg and assistant response
    expect($chatHistory[0]['type'])->toBe('user');
    expect($chatHistory[0]['content'])->toBe('Hello, who are you?');
    expect($chatHistory[1]['type'])->toBe('assistant');
});

test('user can clear chat history', function () {
    // First add some chat history
    session(['chat_history' => [
        ['type' => 'user', 'content' => 'Question 1'],
        ['type' => 'assistant', 'content' => 'Answer 1'],
    ]]);

    $response = $this->get('/clear-chat');

    $response->assertRedirect('/');
    expect(session('chat_history'))->toBeNull();
});

test('message validation fails with empty message', function () {
    $response = $this->post('/submit-message', [
        'message' => ''
    ]);

    $response->assertSessionHasErrors(['message']);
});

test('message validation fails with too long message', function () {
    $longMessage = str_repeat('a', 1500);

    $response = $this->post('/submit-message', [
        'message' => $longMessage
    ]);

    $response->assertSessionHasErrors(['message']);
});
