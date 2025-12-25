<?php

return [
    'provider' => env('VIRUS_SCAN_PROVIDER', 'virustotal'),

    'virustotal' => [
        'api_key' => env('VIRUSTOTAL_API_KEY'),
        'base_url' => 'https://www.virustotal.com/api/v3',
        'poll_seconds' => (int) env('VIRUSTOTAL_POLL_SECONDS', 8),
        'max_polls' => (int) env('VIRUSTOTAL_MAX_POLLS', 10),
    ],
];
