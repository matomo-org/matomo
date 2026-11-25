<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\Integration;

use Piwik\Config;
use Piwik\Exception\NoPrivilegesException;
use Piwik\Plugins\UserCountry\Controller;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UserCountry
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    public function testAdminIndexIsForbiddenWhenGeolocationAdminIsDisabled()
    {
        Config::getInstance()->General['enable_geolocation_admin'] = 0;

        $controller = $this->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(NoPrivilegesException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Geo location setting page has been disabled.');

        $controller->adminIndex();
    }
}
