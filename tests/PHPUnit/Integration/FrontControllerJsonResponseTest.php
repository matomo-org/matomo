<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\EventDispatcher;
use Piwik\FrontController;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class FrontControllerJsonResponseTest extends IntegrationTestCase
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

        Common::$headersSentInTests = [];
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;
        Common::$headersSentInTests = [];

        parent::tearDown();
    }

    /**
     * Dispatches a real controller action carrying #[JsonResponse] and, via a listener that runs on
     * Request.dispatch.end (i.e. after the action), overwrites the Content-Type with text/html. The
     * JSON header must still win, proving FrontController re-applies it after the dispatch events.
     */
    public function testDispatchReAppliesJsonHeaderAfterLaterOutputOverwritesIt(): void
    {
        EventDispatcher::getInstance()->addObserver('Request.dispatch.end', function () {
            Common::sendHeader('Content-Type: text/html; charset=utf-8');
        });

        $_GET['idSite'] = 1;
        $_GET['period'] = 'day';
        $_GET['date'] = 'today';

        $response = FrontController::getInstance()->dispatch('SitesManager', 'getSiteEmptyState');

        // the action returns a JSON-encoded boolean ...
        $this->assertContains($response, ['true', 'false']);
        // ... and the JSON Content-Type wins over the later text/html header
        $this->assertSame(
            'application/json; charset=utf-8',
            trim(Common::$headersSentInTests['Content-Type'] ?? '')
        );
    }
}
