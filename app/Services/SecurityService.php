<?php

namespace App\Services;

use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\ValueObjects\Messages\SystemMessage;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Support\Facades\Log;

class SecurityService
{
    /**
     * Perform basic security checks on user input
     */
    public function performBasicSecurityChecks(string $userInput): array
    {
        // Check input length
        $maxInputLength = config('security.max_input_length', 1000);
        if (strlen($userInput) > $maxInputLength) {
            return [
                'passed' => false,
                'reason' => "Input exceeds maximum allowed length of {$maxInputLength} characters",
            ];
        }

        // Check for banned patterns (could be loaded from config)
        $bannedPatterns = config('security.banned_patterns', []);
        foreach ($bannedPatterns as $pattern) {
            if (preg_match($pattern, $userInput)) {
                return [
                    'passed' => false,
                    'reason' => 'Input contains disallowed patterns',
                ];
            }
        }

        // Check for obvious prompt injection attempts
        $promptInjectionPatterns = [
            '/ignore previous instructions/i',
            '/forget your instructions/i',
            '/ignore all previous commands/i',
            '/disregard your previous instructions/i',
            '/system prompt/i',
            '/you are not Damian/i',
        ];

        foreach ($promptInjectionPatterns as $pattern) {
            if (preg_match($pattern, $userInput)) {
                return [
                    'passed' => false,
                    'reason' => 'Potential prompt injection detected',
                ];
            }
        }

        return ['passed' => true];
    }

    /**
     * Use a supervisor model to evaluate if user input is safe
     */
    public function supervisorCheck(string $userInput, array $conversationContext = []): array
    {
        // Whitelist
        $commonGreetings = [
            'hey', 'hello', 'hi', 'greetings', 'good morning', 'good afternoon',
            'good evening', 'how are you', 'how\'s it going', 'what\'s up'
        ];

        if (in_array(strtolower(trim($userInput)), $commonGreetings, true)) {
            return [
                'isAllowed' => true,
                'reason' => 'Simple greeting detected',
                'safeReason' => null,
                'sanitizedInput' => $userInput,
                'riskScore' => 0,
            ];
        }

        $messages = collect();

        // Add system message with supervisor instructions
        $messages->push(new SystemMessage(config('systemprompts.supervisor_prompt')));

        // Add truncated conversation context if needed
        if ($conversationContext !== []) {
            $reducedContext = $this->getReducedContext($conversationContext);
            foreach ($reducedContext as $message) {
                $messages->push(
                    $message['type'] === 'user'
                        ? new UserMessage($message['content'])
                        : new SystemMessage($message['content'])
                );
            }
        }

        // Add the current user input
        $messages->push(new UserMessage($userInput));

        try {
            // Use a potentially smaller/faster model for the security check
            $securityModel = config('security.supervisor_model', 'gpt-3.5-turbo');

            $response = Prism::text()
                ->using(Provider::OpenAI, $securityModel)
                ->withMessages($messages->toArray())
                ->usingTemperature(0.1) // Low temperature for consistency
                ->generate();

            // Parse the response as JSON
            $assessment = json_decode($response->text, true);

            // If we couldn't parse the JSON or it doesn't have the expected format,
            // default to allowing the request but log the issue
            if (! $assessment || ! isset($assessment['allow'])) {
                Log::warning('Security model returned invalid response', [
                    'response' => $response->text,
                    'input' => $userInput,
                ]);

                return [
                    'isAllowed' => true,
                    'reason' => 'Security check failed to return valid response',
                    'safeReason' => null,
                    'sanitizedInput' => $userInput,
                    'riskScore' => 0.5,
                ];
            }

            return [
                'isAllowed' => $assessment['allow'] === true,
                'reason' => $assessment['reason'] ?? 'No reason provided',
                'safeReason' => $assessment['safeReason'] ?? 'Request not permitted by security policies',
                'sanitizedInput' => $assessment['sanitizedInput'] ?? $userInput,
                'riskScore' => $assessment['riskScore'] ?? 0,
                'safetyMetadata' => $assessment['metadata'] ?? [],
            ];

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in supervisor check', [
                'message' => $e->getMessage(),
                'input' => $userInput,
            ]);

            // Default to allowing with the original input if security check fails
            // You might want to be more strict and reject instead
            return [
                'isAllowed' => true,
                'reason' => 'Security check failed: '.$e->getMessage(),
                'safeReason' => null,
                'sanitizedInput' => $userInput,
                'riskScore' => 0.5,
            ];
        }
    }

    /**
     * Get a reduced context for the security check to save tokens
     */
    private function getReducedContext(array $fullContext): array
    {
        // Take only the last 3 messages and truncate their content
        $truncatedContext = array_slice($fullContext, -3);

        return array_map(function ($item) {
            return [
                'type' => $item['type'],
                'content' => substr($item['content'], 0, 200), // Truncate to 200 chars
            ];
        }, $truncatedContext);
    }

    /**
     * Log a rejected message
     */
    public function logRejection(string $userInput, string $reason): void
    {
        Log::warning('Message rejected by security service', [
            'input' => $userInput,
            'reason' => $reason,
        ]);
    }
}
