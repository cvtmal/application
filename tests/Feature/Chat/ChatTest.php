<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('chat system maintains conversation history across requests', function (): void {
    $this->post('/submit-message', [
        'message' => 'First question',
    ]);

    // Second msg
    $response = $this->post('/submit-message', [
        'message' => 'Second question',
    ]);

    $response->assertRedirect('/');

    $chatHistory = session('chat_history');
    expect($chatHistory)->toHaveCount(4); // 2 from user + 2 from ai response

    $response = $this->get('/');
    $response->assertInertia(fn (Assert $page) => $page->component('welcome')
        ->has('chatHistory', 4)
    );
});
