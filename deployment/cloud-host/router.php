<?php

declare(strict_types=1);

/**
 * PHP Built-in Server: Request-Body einmal aus php://input lesen und an provisioner.php durchreichen.
 * Sonst kann php://input leer sein bzw. HMAC passt nicht → 401 invalid signature.
 */
$GLOBALS['CLH_RAW_HTTP_BODY'] = file_get_contents('php://input');

require __DIR__ . '/provisioner.php';
