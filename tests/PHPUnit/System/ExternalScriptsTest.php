<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\DbHelper;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

class ExternalScriptsTest extends SystemTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        DbHelper::createAnonymousUser();

        // the api_internal_call.php uses idSite=7, so we create 7 sites
        for ($i = 0; $i != 7; ++$i) {
            Fixture::createWebsite("2011-01-01 00:00:00", $ecommerce = 1, $siteName = "Site #$i");
        }

        // the script uses anonymous token auth, so give the anonymous user access
        \Piwik\Plugins\UsersManager\API::getInstance()->setUserAccess('anonymous', 'view', array(7));
    }

    public function test_ApiInternalCallScript_ExecutesCorrectly()
    {
        $output = $this->executeApiInternalCall();
        $expectedFileOutput = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<result />";
        $this->assertEquals($expectedFileOutput, $output);
    }

    private function executeApiInternalCall()
    {
        $proxyIncludeScript = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/include_single_file.php';
        $apiInternalCallScript = PIWIK_INCLUDE_PATH . '/misc/others/api_internal_call.php';

        $command = "php '$proxyIncludeScript' '$apiInternalCallScript' 2>&1";
        return shell_exec($command);
    }
}
