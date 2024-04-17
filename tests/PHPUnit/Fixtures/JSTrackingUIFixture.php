<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2\Php;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tests\Framework\Fixture;

class JSTrackingUIFixture extends Fixture
{
    public function setUp(): void
    {
        parent::setUp();

        self::resetPluginsInstalledConfig();
        self::updateDatabase();
        self::installAndActivatePlugins($this->getTestEnvironment());
        self::updateDatabase();

        $trackerUpdater = StaticContainer::get('Piwik\Plugins\CustomJsTracker\TrackerUpdater');
        $trackerUpdater->update();

        // for proper geolocation
        LocationProvider::setCurrentProvider(Php::ID);
        IPAnonymizer::deactivate();

        Fixture::createWebsite('2012-02-02 00:00:00');
    }

    public function performSetUp($setupEnvironmentOnly = false)
    {
        $this->extraTestEnvVars = array(
            'loadRealTranslations' => 1,
        );
        $this->extraPluginsToLoad = array(
            'CustomJsTracker',
            'ExampleTracker',
        );

        parent::performSetUp($setupEnvironmentOnly);

        $this->testEnvironment->overlayUrl = UITestFixture::getLocalTestSiteUrl();
        UITestFixture::createOverlayTestSite($idSite = 1);

        $this->testEnvironment->tokenAuth = self::getTokenAuth();
        $this->testEnvironment->pluginsToLoad = $this->extraPluginsToLoad;
        $this->testEnvironment->save();
    }
}
