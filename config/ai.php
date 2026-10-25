<?php

return [
    'enabled' => (bool) env('AI_ENABLED', false),
    'data_processing_approved' => (bool) env('AI_DATA_PROCESSING_APPROVED', false),
    'student_text_allowed' => (bool) env('AI_ALLOW_STUDENT_TEXT', false),
    'provider' => env('AI_PROVIDER'),
    'api_key' => env('AI_API_KEY'),
    'model' => env('AI_MODEL'),
    'base_url' => env('AI_BASE_URL'),
    'timeout_seconds' => (int) env('AI_TIMEOUT_SECONDS', 20),
    'prompt_version' => env('AI_PROMPT_VERSION', 'writewise-feedback-v1'),
];
