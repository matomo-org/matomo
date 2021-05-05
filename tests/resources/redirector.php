<?php

/**
 * Script used to test redirects. If no redirect is left, the script will simply output the current url
 */

$redirect = $_GET['redirects'] ?? 0;
$target = $_GET['target'] ?? '';

$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
        "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
    $_SERVER['REQUEST_URI'];

if ($target) {
    header('HTTP/1.1 302 Found');
    header('Location: ' . $target);
    exit;
}

if ($redirect > 0) {
    header('HTTP/1.1 302 Found');
    header('Location: ' . preg_replace('/(redirects=[0-9]+)/', 'redirects=' . ($redirect-1), $url));
    exit;
}

echo $url;