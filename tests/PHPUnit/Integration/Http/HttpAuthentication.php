<?php

if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == 'test' && $_SERVER['PHP_AUTH_PW'] == 'test') {
    echo 'Authentication successful';
    exit;
} else {
    header('WWW-Authenticate: Basic realm="TestAuth"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required';
    exit;
}