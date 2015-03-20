<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Test\Fixtures;

use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that makes the update over HTTPS fail to be able to test that users can still update over HTTP.
 */
class FailUpdateHttpsFixture extends Fixture
{
    public function provideContainerConfig()
    {
        return array(
            'Piwik\Plugins\CoreUpdater\Updater' => \DI\object('Piwik\Plugins\CoreUpdater\Test\Mock\UpdaterMock'),
        );
    }
}
