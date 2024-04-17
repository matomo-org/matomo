<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Fixtures;

use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that makes the update over HTTPS fail to be able to test that users can still update over HTTP.
 */
class FailUpdateHttpsFixture extends Fixture
{
    public function provideContainerConfig()
    {
        return array(
            'Piwik\Plugins\CoreUpdater\Updater' => \Piwik\DI::autowire('Piwik\Plugins\CoreUpdater\tests\Mock\UpdaterMock'),
        );
    }
}
