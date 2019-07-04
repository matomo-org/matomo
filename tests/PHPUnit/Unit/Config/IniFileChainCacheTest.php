<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Unit\Config;

use PHPUnit_Framework_TestCase;
use Piwik\Config\IniFileChain;

/**
 * @group Core
 */
class IniFileChainCacheTest extends IniFileChainTest
{
    public function setUp()
    {
        $GLOBALS['ENABLE_CONFIG_PHP_CACHE'] = true;
        $_SERVER['HTTP_HOST'] = 'mytest.matomo.org';
        parent::setUp();
    }

    public function tearDown()
    {
        unset($GLOBALS['ENABLE_CONFIG_PHP_CACHE']);
        unset($_SERVER['HTTP_HOST']);
        parent::tearDown();
    }
}