<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Config;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\TrackerConfig;

/**
 * @group Core
 * @group TrackerConfigTest
 */
class TrackerConfigTest extends IntegrationTestCase
{
    public function testGetConfigValueWithUseThirdPartyIdCookieReturnsResult(): void
    {
        Config::getInstance()->FeatureFlags['PrivacyCompliance_feature'] = 'enabled';

        $this->assertEquals(false, TrackerConfig::getConfigValue('use_third_party_id_cookie'));
    }
    public function testGetConfigValueWithUseThirdPartyIdCookieReturnsResultWithFeatureFlagDisabled(): void
    {
        Config::getInstance()->FeatureFlags['PrivacyCompliance_feature'] = 'disabled';

        $this->assertEquals(false, TrackerConfig::getConfigValue('use_third_party_id_cookie'));
    }
}
