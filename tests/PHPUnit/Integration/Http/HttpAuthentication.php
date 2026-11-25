<?php

$validCredentials = array('test:test', '0:test', 'test:0', '0:0');
$providedCredentials = ($_SERVER['PHP_AUTH_USER'] ?? '') . ':' . ($_SERVER['PHP_AUTH_PW'] ?? '');

if (in_array($providedCredentials, $validCredentials, true)) {
    echo 'Authentication successful';
    exit;
} else {
    header('WWW-Authenticate: Basic realm="TestAuth"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required';
    exit;
}
