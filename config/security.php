<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the security service that protects against
    | prompt injection and other malicious inputs.
    |
    */

    // Maximum length of user input allowed
    'max_input_length' => env('SECURITY_MAX_INPUT_LENGTH', 1000),

    'banned_patterns' => [
        // Prompt injection attempts
        '/ignore (all|previous|your) (instructions|prompts)/i',
        '/disregard (all|previous|your) (instructions|prompts)/i',
        '/forget (all|previous|your) (instructions|prompts)/i',
        '/override (all|previous|your) (instructions|prompts)/i',
        '/you are not (damian|an assistant)/i',

        // German prompt injection attempts
        '/ignoriere (alle|vorherige|deine) (anweisungen|instruktionen|befehle)/i',
        '/missachte (alle|vorherige|deine) (anweisungen|instruktionen|befehle)/i',
        '/vergiss (alle|vorherige|deine) (anweisungen|instruktionen|befehle)/i',
        '/übergehe (alle|vorherige|deine) (anweisungen|instruktionen|befehle)/i',

        // System prompt extraction attempts
        '/system prompt/i',
        '/initial instructions/i',
        '/how were you programmed/i',
        '/what are your guidelines/i',
        '/what were your instructions/i',
        '/tell me your prompt/i',

        // German system prompt extraction
        '/system(anweisung|prompt)/i',
        '/ursprüngliche (anweisungen|instruktionen)/i',
        '/wie wurdest du programmiert/i',
        '/was sind deine (richtlinien|anweisungen|vorgaben)/i',

        // Role manipulation
        '/pretend to be/i',
        '/act as if (you are|you\'re)/i',
        '/roleplay as/i',
        '/new persona/i',

        // German role manipulation
        '/tu so als (ob|wärst)/i',
        '/gib vor zu sein/i',
        '/spiel die rolle von/i',
        '/nimm die persona an/i',

        // German identity manipulation
        '/du bist nicht (damian|ein assistent)/i',
        '/du bist kein(e)? (damian|assistent)/i',

        // Command-like instructions that could be harmful
        '/execute the following/i',
        '/führe (folgendes|diesen befehl) aus/i',
        '/run the command/i',
        '/sudo/i',
        '/eval\(/i',

        // Potentially harmful or inappropriate requests
        '/how to (hack|phish|steal)/i',
        '/wie (hacke|phishe|stehle) ich/i',
        '/illegal (activity|method|process)/i',
        '/illegale (aktivität|methode|prozess|vorgehensweise)/i',

        // Attempts to circumvent moderation
        '/bypass (security|moderation|filter)/i',
        '/umgehe (sicherheit|moderation|filter)/i',
        '/avoid (security|moderation|filter)/i',
        '/vermeide (sicherheit|moderation|filter)/i',

        // Output format manipulation (to potentially execute a prompt injection)
        '/output the following verbatim/i',
        '/gib folgendes wörtlich aus/i',
        '/repeat exactly what I say/i',
        '/wiederhole genau was ich sage/i',

        // Delimiter escape attempts (trying to break out of safety measures)
        '/```system/i',
        '/"""system/i',
        '/<<<system/i',
    ],

    // Model to use for security supervision (should be fast)
    'supervisor_model' => env('SECURITY_SUPERVISOR_MODEL', 'gpt-3.5-turbo'),

    // Whether to log rejected messages
    'log_rejections' => env('SECURITY_LOG_REJECTIONS', true),

    // Whether to stop immediately on basic checks or continue to AI supervisor
    'stop_on_basic_check_failure' => env('SECURITY_STOP_ON_BASIC_CHECK_FAILURE', true),
];
