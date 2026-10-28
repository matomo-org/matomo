<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\EventDispatcher;
use Piwik\FrontController;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class FrontControllerNestedDispatchTest extends IntegrationTestCase
{
    /** @var array */
    private $backupGet;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }
        FakeAccess::clearAccess($superUser = true);

        $_GET['idSite'] = 1;
        $_GET['period'] = 'day';
        $_GET['date'] = 'today';
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;

        parent::tearDown();
    }

    public function testDispatchingAnActionThatIsStillRunningIsRejected(): void
    {
        $nestedDispatches = 0;
        EventDispatcher::getInstance()->addObserver(
            'Controller.SitesManager.getSiteEmptyState',
            function () use (&$nestedDispatches) {
                if ($nestedDispatches++ === 0) {
                    FrontController::getInstance()->dispatch('SitesManager', 'getSiteEmptyState');
                }
            }
        );

        try {
            FrontController::getInstance()->dispatch('SitesManager', 'getSiteEmptyState');
            $this->fail('Expected the nested dispatch to be rejected');
        } catch (\Exception $e) {
            $this->assertStringContainsString('SitesManager.getSiteEmptyState is already being dispatched', $e->getMessage());
        }

        // the rejected dispatch must not stay on the stack: running the action again works
        $this->assertContains(FrontController::getInstance()->dispatch('SitesManager', 'getSiteEmptyState'), ['true', 'false']);
    }

    public function testDispatchingADifferentActionFromARunningOneIsAllowed(): void
    {
        $nestedResult = null;
        EventDispatcher::getInstance()->addObserver(
            'Controller.CoreHome.index',
            function () use (&$nestedResult) {
                $nestedResult = FrontController::getInstance()->dispatch('SitesManager', 'getSiteEmptyState');
                throw new \RuntimeException('stop before rendering the page');
            }
        );

        try {
            FrontController::getInstance()->dispatch('CoreHome', 'index');
        } catch (\RuntimeException $e) {
            $this->assertSame('stop before rendering the page', $e->getMessage());
        }

        $this->assertContains($nestedResult, ['true', 'false']);
    }
}
