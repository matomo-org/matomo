<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeatureFlags\tests\System\Commands;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DI;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

class EnableFeatureFlagCommandTest extends ConsoleCommandTestCase
{
    public function testEnableFeatureFlagAddsToConfig()
    {
        $container = StaticContainer::getContainer();
        $container->set('featureflag.dir_of_feature_flags', DI::string('tests/System/Commands/FeatureFlags'));
        $container->get(Config::class)->FeatureFlags = [];

        $this->applicationTester->run([
            'command' => 'featureflags:enable',
            'featureFlagName' => 'SystemTest',
        ]);

        $flags = $container->get(Config::class)->FeatureFlags;

        $this->assertEquals(['SystemTest_feature' => 'enabled'], $flags);
    }

    public function testEnableFeatureFlagUpdatesConfig()
    {
        $container = StaticContainer::getContainer();
        $container->set('featureflag.dir_of_feature_flags', DI::string('tests/System/Commands/FeatureFlags'));
        $container->get(Config::class)->FeatureFlags = ['SystemTest_feature' => 'disabled'];

        $this->applicationTester->run([
            'command' => 'featureflags:enable',
            'featureFlagName' => 'SystemTest',
        ]);

        $flags = $container->get(Config::class)->FeatureFlags;

        $this->assertEquals(['SystemTest_feature' => 'enabled'], $flags);
    }
}