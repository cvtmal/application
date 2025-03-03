<?php

namespace App\Services;

use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\ValueObjects\Messages\AssistantMessage;
use EchoLabs\Prism\ValueObjects\Messages\SystemMessage;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Support\Collection;

class ChatService
{
   protected Collection $messages;

    public function __construct()
    {
        $this->messages = collect();
    }

    public function initializeWithHistory(array $chatHistory): self
    {
        $this->messages = collect();

        // Add system message with Damian's information
        $this->messages->push(new SystemMessage($this->getSystemPrompt()));

        // Add conversation history
        foreach ($chatHistory as $message) {
            if ($message['type'] === 'user') {
                $this->messages->push(new UserMessage($message['content']));
            } elseif ($message['type'] === 'assistant') {
                $this->messages->push(new AssistantMessage($message['content']));
            }
        }

        return $this;
    }

    public function getResponseForMessage(string $userMessage): string
    {
        // Add the new user message
        $this->messages->push(new UserMessage($userMessage));

        // Create Prism instance and generate response
        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withMessages($this->messages->toArray())
            ->generate();

        // Save the assistant's response to the messages collection
        $this->messages->push(new AssistantMessage($response->text));

        return $response->text;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    protected function getSystemPrompt(): string
    {
        $systemPrompt = config('systemprompts.system_prompt');
        $damianInfo = config('systemprompts.damian');

        return $systemPrompt . ' ' . $damianInfo;
    }
}
