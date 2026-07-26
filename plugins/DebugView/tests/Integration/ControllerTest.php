<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Plugins\DebugView\Controller;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DebugView
 * @group DebugViewControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (class_exists('\Piwik\Plugins\TagManager\TagManager')) {
            \Piwik\Plugins\TagManager\TagManager::$enableAutoContainerCreation = false;
        }

        Fixture::loadAllTranslations();
        Fixture::createSuperUser();
        FakeAccess::$superUser = true;

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2020-01-01 00:00:00');
        }
        // a second site, so the header site selector is rendered
        // (it hides itself when only a single site exists)
        if (!Fixture::siteCreated(2)) {
            Fixture::createWebsite('2020-01-01 00:00:00');
        }

        $_GET['idSite'] = '1';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testIndexFailsForUserWithoutAnyViewAccess()
    {
        $this->expectException(\Piwik\NoAccessException::class);

        FakeAccess::clearAccess(false);
        FakeAccess::$identity = 'aUser';
        FakeAccess::$idSitesView = [];
        FakeAccess::$idSitesAdmin = [];

        (new Controller())->index();
    }

    public function testIndexFailsForUserWithoutAccessToTheRequestedSite()
    {
        $this->expectException(\Piwik\NoAccessException::class);

        FakeAccess::clearAccess(false);
        FakeAccess::$identity = 'aUser';
        FakeAccess::$idSitesView = [999];
        FakeAccess::$idSitesAdmin = [];

        (new Controller())->index();
    }

    public function testIndexFailsWhenNoSiteIsGivenInTheRequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('idSite is not found in the request');

        unset($_GET['idSite']);

        (new Controller())->index();
    }

    public function testIndexRendersTheStreamContainerWhenEnabled()
    {
        $html = (new Controller())->index();

        $this->assertStringContainsString('vue-entry="DebugView.DebugViewPage"', $html);
        $this->assertStringContainsString('top_bar_sites_selector', $html);
        $this->assertStringNotContainsString('Top events', $html);
    }

    public function testIndexRendersTheFriendlyNoticeWhenTheVisitsLogIsDisabled()
    {
        $settings = new \Piwik\Plugins\Live\SystemSettings();
        $settings->disableVisitorLog->setValue(true);
        $settings->save();
        \Piwik\Cache::getTransientCache()->flushAll();

        $html = (new Controller())->index();

        $this->assertStringNotContainsString('vue-entry="DebugView.DebugViewPage"', $html);
        $this->assertStringContainsString('Visits Log', $html);
        // the site selector is shown on the disabled page too
        $this->assertStringContainsString('top_bar_sites_selector', $html);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
