<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin\tests\System;

use Piwik\API\Request;
use Piwik\Plugins\CustomDirPlugin\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group CustomDirPlugin
 * @group APITest
 * @group Plugins
 */
class APITest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    public function test_api()
    {
        $this->assertEquals(42, Request::processRequest('CustomDirPlugin.getCustomAnswerToLive'));
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

APITest::$fixture = new SimpleFixtureTrackFewVisits();
