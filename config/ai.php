<?php

return [
    'enabled' => (bool) env('AI_ENABLED', false),
    'data_processing_approved' => (bool) env('AI_DATA_PROCESSING_APPROVED', false),
    'student_text_allowed' => (bool) env('AI_ALLOW_STUDENT_TEXT', false),
    'provider' => env('AI_PROVIDER', 'deepseek'),
    'api_key' => env('AI_API_KEY'),
    'model' => env('AI_MODEL'),
    'base_url' => env('AI_BASE_URL'),
    'timeout_seconds' => (int) env('AI_TIMEOUT_SECONDS', 30),
    'prompt_version' => env('AI_PROMPT_VERSION', 'writewise-feedback-v1'),

    // Default Provider & Parameter untuk Perancang Tugas (Assignment AI Generator)
    'assignment_provider' => env('AI_ASSIGNMENT_PROVIDER', 'deepseek'),
    'assignment_temperature' => (float) env('AI_ASSIGNMENT_TEMPERATURE', 0.2),

    // Konfigurasi Multi-Provider AI (DeepSeek, Google Gemini, OpenAI / ChatGPT)
    'providers' => [
        'deepseek' => [
            'name' => 'DeepSeek AI',
            'api_key' => env('DEEPSEEK_API_KEY', env('AI_API_KEY')),
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'timeout' => (int) env('DEEPSEEK_TIMEOUT_SECONDS', 35),
        ],
        'gemini' => [
            'name' => 'Google Gemini',
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'timeout' => (int) env('GEMINI_TIMEOUT_SECONDS', 30),
        ],
        'openai' => [
            'name' => 'ChatGPT / OpenAI',
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'timeout' => (int) env('OPENAI_TIMEOUT_SECONDS', 30),
        ],
    ],
];
