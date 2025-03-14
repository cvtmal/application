<?php

use App\Services\SecurityService;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    $this->securityService = new SecurityService;
});

test('basic security checks reject long messages', function (): void {
    Config::set('security.max_input_length', 10);

    $result = $this->securityService->performBasicSecurityChecks('Short msg');
    expect($result['passed'])->toBeTrue();

    $result = $this->securityService->performBasicSecurityChecks('This message is way too long and should be rejected');
    expect($result['passed'])->toBeFalse()
        ->and($result['reason'])->toContain('exceeds maximum allowed length');
});

test('legitimate messages pass basic security checks', function (): void {
    $legitimateMessages = [
        'Hello, how are you?',
        'Can you tell me about your experience with Laravel?',
        "What's your background in web development?",
        'Do you have any experience with React.js?',
    ];

    foreach ($legitimateMessages as $message) {
        $result = $this->securityService->performBasicSecurityChecks($message);
        expect($result['passed'])->toBeTrue();
    }
});
