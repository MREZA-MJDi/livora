<?php

return [
    /* Keep the administration URL out of source control and change it per deploy. */
    'path' => trim((string) env('ADMIN_PATH', 'admin'), '/'),

    // Maximum admin inactivity window, enforced server-side on every admin request.
    'session_timeout_minutes' => max(5, (int) env('ADMIN_SESSION_TIMEOUT_MINUTES', 30)),
];
