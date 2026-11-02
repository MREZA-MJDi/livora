<?php

return [
    /* Keep the administration URL out of source control and change it per deploy. */
    'path' => trim((string) env('ADMIN_PATH', 'admin'), '/'),
];
