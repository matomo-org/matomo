<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Menu;

use Piwik\Access;
use Piwik\Menu\MenuAdmin;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Menu
 */
class MenuAdminTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2020-01-01 00:00:00');
        MenuAdmin::unsetInstance();
    }

    public function tearDown(): void
    {
        MenuAdmin::unsetInstance();
        parent::tearDown();
    }

    public function testGetMenuIsBuiltAgainForTheUserAfterDoAsSuperUser()
    {
        FakeAccess::clearAccess(false, [], [1], 'viewUser');
        $menuForUser = MenuAdmin::getInstance()->getMenu();
        MenuAdmin::unsetInstance();

        $menuInside = Access::doAsSuperUser(function () {
            return MenuAdmin::getInstance()->getMenu();
        });

        $this->assertNotEquals($menuForUser, $menuInside);
        $this->assertEquals($menuForUser, MenuAdmin::getInstance()->getMenu());
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
