<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;
use Piwik\Tests\Framework\TestCase\PiwikTestCase;

/**
 * TODO
 */
abstract class TestAspect
{
    public function setUpBeforeClass($testCaseClass)
    {
        // empty
    }

    public function tearDownAfterClass($testCaseClass)
    {
        // empty
    }

    public function setUp(PiwikTestCase $testCase)
    {
        // empty
    }

    public function tearDown(PiwikTestCase $testCase)
    {
        // empty
    }

    public static function isClassAspect()
    {
        return true;
    }

    public static function isMethodAspect()
    {
        return true;
    }
}