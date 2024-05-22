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

class DeleteFeatureFlagCommandTest extends ConsoleCommandTestCase
{
    public function testDeleteFeatureFlagRemovesFromConfig()
    {
        $container = StaticContainer::getContainer();
        $container->set('featureflag.dir_of_feature_flags', DI::string('tests/System/Commands/FeatureFlags'));
        $container->get(Config::class)->FeatureFlags =  ['SystemTest_feature' => 'enabled'];

        $this->applicationTester->run([
            'command' => 'featureflags:delete',
            'featureFlagName' => 'SystemTest'
        ]);

        $flags = $container->get(Config::class)->FeatureFlags;
        $this->assertArrayNotHasKey('SystemTest_feature', $flags);
    }
}