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
use Piwik\Plugins\FeatureFlags\tests\System\Commands\FeatureFlags\FakeFeatureFlag;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

class ManageFeatureFlagsCommandTest extends ConsoleCommandTestCase
{
    public function testEnableFeatureFlagSetsInConfig()
    {
        $container = StaticContainer::getContainer();
        $container->set('featureflag.feature_flags', DI::add([FakeFeatureFlag::class]));
        StaticContainer::push($container);

        $this->applicationTester->run([
            'command' => 'featureflags:manage',
            '--name' => 'SystemTest',
            '--action' => 'enable'
        ]);

        $this->assertEquals(['SystemTest_feature' => 'enabled'], $container->get(Config::class)->FeatureFlags);
    }

    public function testDisableFeatureFlagSetsInConfig()
    {
        $container = StaticContainer::getContainer();
        $container->set('featureflag.feature_flags', DI::add([FakeFeatureFlag::class]));
        StaticContainer::push($container);

        $this->applicationTester->run([
            'command' => 'featureflags:manage',
            '--name' => 'SystemTest',
            '--action' => 'enable'
        ]);
        $this->applicationTester->run([
            'command' => 'featureflags:manage',
            '--name' => 'SystemTest',
            '--action' => 'disable'
        ]);

        $this->assertEquals(['SystemTest_feature' => 'disabled'], $container->get(Config::class)->FeatureFlags);
    }

    public function testDeletesFeatureFlagRemovesFromConfig()
    {
        $container = StaticContainer::getContainer();
        $container->set('featureflag.feature_flags', DI::add([FakeFeatureFlag::class]));
        StaticContainer::push($container);

        $this->applicationTester->run([
            'command' => 'featureflags:manage',
            '--name' => 'SystemTest',
            '--action' => 'enable'
        ]);
        $this->applicationTester->run([
            'command' => 'featureflags:manage',
            '--name' => 'SystemTest',
            '--action' => 'delete'
        ]);

        $this->assertEquals([], $container->get(Config::class)->FeatureFlags);
    }
}
