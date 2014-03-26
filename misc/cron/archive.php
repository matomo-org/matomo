<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

$callee = array_shift($_SERVER['argv']);
$args   = array($callee);
$args[] = 'core:archive';
foreach ($_SERVER['argv'] as $arg) {
    if (0 === strpos($arg, '--')) {
        $args[] = $arg;
    } elseif (0 === strpos($arg, '-')) {
        $args[] = '-' . $arg;
    } else {
        $args[] = '--' . $arg;
    }
}

$_SERVER['argv'] = $args;

$piwikHome = realpath(dirname(__FILE__) . "/../..");

if (false !== strpos($callee, 'archive.php')) {
echo "
-------------------------------------------------------
Using this 'archive.php' script is no longer recommended.
Please use '/path/to/php $piwikHome/console core:archive " . implode(' ', array_slice($args, 2)) . "' instead.
-------------------------------------------------------
\n\n";
}

include $piwikHome . '/console';