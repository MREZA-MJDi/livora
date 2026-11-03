<?php

return [
    /* Keep the administration URL out of source control and change it per deploy. */
    'path' => trim((string) env('ADMIN_PATH', 'admin'), '/'),

    // Enforced on every admin request; separate from customer session lifetime.
    'session_timeout_minutes' => min(240, max(5, (int) env('ADMIN_SESSION_TIMEOUT_MINUTES', 30))),
];
