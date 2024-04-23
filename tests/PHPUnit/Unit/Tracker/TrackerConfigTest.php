<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Tracker;

use Piwik\Config;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Tracker\TrackerConfig;

class TrackerConfigTest extends UnitTestCase
{
    public function testGetConfigValueReturnsTrackerConfigValueIfNoSiteSpecificValue()
    {
        Config::getInstance()->Tracker['setting'] = 1;
        Config::getInstance()->Tracker_10['setting'] = 0;

        $this->assertEquals(1, TrackerConfig::getConfigValue('setting', 5));
    }

    public function testGetConfigValueReturnsSiteSpecificConfigValueIfOneIsSpecified()
    {
        Config::getInstance()->Tracker['setting'] = 1;
        Config::getInstance()->Tracker_10['setting'] = 0;

        $this->assertEquals(0, TrackerConfig::getConfigValue('setting', 10));
    }
}
