<?php

ini_set('display_errors', 0);
define('PIWIK_PRINT_ERROR_BACKTRACE', true);
define('PIWIK_ENABLE_DISPATCH', false);

require_once __DIR__ . '/../../tests/PHPUnit/proxy/index.php';

$environment = new \Piwik\Application\Environment(null);
$environment->init();

\Piwik\Access::getInstance()->setSuperUserAccess(true);

class MyClass
{
    public function triggerError($arg1, $arg2)
    {
        try {
            \Piwik\ErrorHandler::pushFatalErrorBreadcrumb(static::class, ['arg1' => $arg1, 'arg2' => $arg2]);

            $val = "";
            while (true) {
                $val .= str_repeat("*", 1024 * 1024 * 1024);
            }
        } finally {
            \Piwik\ErrorHandler::popFatalErrorBreadcrumb();
        }
    }

    public static function staticMethod()
    {
        try {
            \Piwik\ErrorHandler::pushFatalErrorBreadcrumb(static::class);

            $instance = new MyClass();
            $instance->triggerError('argval', 'another');
        } finally {
            \Piwik\ErrorHandler::popFatalErrorBreadcrumb();
        }
    }
}

class MyDerivedClass extends MyClass
{
}

function myFunction()
{
    try {
        \Piwik\ErrorHandler::pushFatalErrorBreadcrumb();

        MyDerivedClass::staticMethod();
    } finally {
        \Piwik\ErrorHandler::popFatalErrorBreadcrumb();
    }
}

myFunction();
