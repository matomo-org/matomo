<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Fixtures;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Tests\Framework\Fixture;

class TrackingFixture extends Fixture
{
    public function setUp(): void
    {
        self::createWebsite('2020-01-01 01:00:00');

        // enable cloud blocking so the conditional "Organisation block list" setting is visible
        // in the settings screenshot
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->block_clouds->setValue(true);
        $settings->save();
    }
}
