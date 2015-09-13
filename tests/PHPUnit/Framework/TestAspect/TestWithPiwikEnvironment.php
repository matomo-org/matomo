<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestAspect;

use Piwik\Application\Environment;
use Piwik\Tests\Framework\TestAspect;
use Piwik\Tests\Framework\TestCase\PiwikTestCase;

/**
 * TODO
 */
class TestWithPiwikEnvironment extends TestAspect
{
    /**
     * @var Environment
     */
    private $environment;

    public static function isMethodAspect()
    {
        return false;
    }

    public function setUp(PiwikTestCase $testCase)
    {
        // TODO
    }

    public function tearDown(PiwikTestCase $testCase)
    {
        // TODO
    }
}