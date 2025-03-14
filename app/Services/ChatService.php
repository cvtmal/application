<?php

namespace App\Services;

use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\ValueObjects\Messages\AssistantMessage;
use EchoLabs\Prism\ValueObjects\Messages\SystemMessage;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChatService
{
    protected Collection $messages;
    protected SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->messages = collect();
        $this->securityService = $securityService;
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

    public function getResponseForMessage(string $userMessage): array
    {
        // STEP 1: Perform basic security checks first (non-AI based)
        $initialChecks = $this->securityService->performBasicSecurityChecks($userMessage);
        if (!$initialChecks['passed']) {
            return [
                'success' => false,
                'message' => "Sorry, I can't process that message.",
                'reason' => $initialChecks['reason']
            ];
        }

        // STEP 2: Use the supervisor model
        $supervisorCheck = $this->securityService->supervisorCheck($userMessage, $this->getHistoryAsArray());

        // STEP 3: If the supervisor model rejects the input, return an error
        if (!$supervisorCheck['isAllowed']) {
            // Log the rejection
            $this->securityService->logRejection($userMessage, $supervisorCheck['reason']);

            return [
                'success' => false,
                'message' => "I can't respond to that request.",
                'reason' => $supervisorCheck['safeReason'] ?? 'This request is not allowed by our policies.'
            ];
        }

        // STEP 4: Use the sanitized input from the supervisor
        $sanitizedInput = $supervisorCheck['sanitizedInput'];
        
        // Add the sanitized user message to the messages collection
        $this->messages->push(new UserMessage($sanitizedInput));

        try {
            // STEP 5: Generate a response using the main model
            $response = Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o')
                ->withMessages($this->messages->toArray())
                ->generate();

            // Save the assistant's response to the messages collection
            $this->messages->push(new AssistantMessage($response->text));

            return [
                'success' => true,
                'message' => $response->text,
                'metrics' => [
                    'riskScore' => $supervisorCheck['riskScore'] ?? 0
                ]
            ];
        } catch (\Exception $e) {
        Log::error('Error generating response', [
            'message' => $e->getMessage(),
            'userInput' => $userMessage
        ]);

        return [
            'success' => false,
            'message' => "I'm sorry, I encountered an error while processing your request.",
            'reason' => 'AI service error'
        ];
    }
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

    /**
     * Convert the messages collection to an array format for the security service
     */
    protected function getHistoryAsArray(): array
    {
        $history = [];

        foreach ($this->messages as $message) {
            if ($message instanceof UserMessage) {
                $history[] = [
                    'type' => 'user',
                    'content' => $message->content
                ];
            } else if ($message instanceof AssistantMessage) {
                $history[] = [
                    'type' => 'assistant',
                    'content' => $message->content
                ];
            }
            // We skip SystemMessage as it contains sensitive information
        }

        return $history;
    }
}