<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\Access;
use Piwik\Date;
use Piwik\FrontController;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Live
 * @group LiveController
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::createWebsite('2010-01-01');
    }

    public function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    public function testSegmentedVisitorLogOfRangeRowListsItsVisitsAndKeepsTheRowFilterForPaging()
    {
        $this->trackVisitWithActions('2012-01-01 10:00:00', ['https://example.org/a', 'https://example.org/b']);
        $this->trackVisitWithActions('2012-01-01 11:00:00', ['https://example.org/a']);

        $_GET = [
            'module' => 'Live',
            'action' => 'indexVisitorLog',
            'idSite' => '1',
            'period' => 'day',
            'date' => '2012-01-01',
            'segment' => 'pageUrl=@example.org',
            'intersectSegment' => 'actions>=2;actions<=3',
            'small' => '1',
        ];

        $html = Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('Live', 'indexVisitorLog');
        });

        $this->assertStringNotContainsString('is not valid', $html);
        $this->assertSame(1, substr_count($html, 'class="visitorDetails"'));

        $this->assertSame(1, preg_match('/data-params="([^"]*)"/', $html, $matches));
        $params = json_decode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5), true);
        $this->assertSame('actions>=2;actions<=3', $params['intersectSegment']);
    }

    private function trackVisitWithActions(string $dateTime, array $urls): void
    {
        $tracker = Fixture::getTracker(1, $dateTime, $defaultInit = true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setNewVisitorId();

        foreach ($urls as $index => $url) {
            $tracker->setForceVisitDateTime(Date::factory($dateTime)->addPeriod($index, 'second')->getDatetime());
            $tracker->setUrl($url);
            Fixture::checkResponse($tracker->doTrackPageView('Visit action ' . $index));
        }
    }
}
