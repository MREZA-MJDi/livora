<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VPS-level mail provider
    |--------------------------------------------------------------------------
    |
    | Each Laravel application receives a domain-scoped API token. The provider
    | must enforce the token's allowed domain; never rely on this client-side
    | domain value as the only authorization boundary.
    |
    */
    'api_url' => env('MAIL_PROVIDER_API_URL'),
    'api_token' => env('MAIL_PROVIDER_API_TOKEN'),
    'domain' => env('MAIL_PROVIDER_DOMAIN'),
    'mailbox_password' => env('MAIL_PROVIDER_MAILBOX_PASSWORD'),

    'smtp_host' => env('MAIL_PROVIDER_SMTP_HOST'),
    'smtp_port' => (int) env('MAIL_PROVIDER_SMTP_PORT', 587),
    'smtp_encryption' => env('MAIL_PROVIDER_SMTP_ENCRYPTION', 'tls'),

    'imap_host' => env('MAIL_PROVIDER_IMAP_HOST'),
    'imap_port' => (int) env('MAIL_PROVIDER_IMAP_PORT', 993),
    'imap_encryption' => env('MAIL_PROVIDER_IMAP_ENCRYPTION', 'ssl'),

    'quota_mb' => (int) env('MAIL_PROVIDER_DEFAULT_QUOTA_MB', 512),
];
