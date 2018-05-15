<?php

// used in integration tests to see if POST method works.
// for security reasons we allow max 3 post vars, each key and value is only allowed to have max 6 hex characters

function accept($key)
{
    if (ctype_xdigit($key) && strlen($key) <= 6) {
        return $key;
    }
}

if (count($_POST) > 4) {
    exit;
}

$values = array();
foreach ($_POST as $key => $value) {
    if (accept($key) && accept($value)) {
        $values[$key] = $value;
    }
}

if (!empty($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
    $values['method'] = 'post';
}

echo json_encode($values);
exit;
