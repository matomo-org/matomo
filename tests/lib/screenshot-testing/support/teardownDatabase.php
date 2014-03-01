<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

foreach ($argv as $arg) {
    if (strpos($arg, "--server=") === 0) {
        $serverStr = substr($arg, strlen("--server="));
        
        $_SERVER = json_decode($serverStr, true);
    }
}

require_once "PHPUnit/Autoload.php";
require_once dirname(__FILE__) . "/../../../PHPUnit/bootstrap.php";

$fixture = new \Piwik\Tests\Fixtures\UITestFixture();
$fixture->dropDatabaseInSetUp = false;
$fixture->printToScreen = true;
$fixture->performSetUp("", $environmentOnly = true);
$fixture->performTearDown("");