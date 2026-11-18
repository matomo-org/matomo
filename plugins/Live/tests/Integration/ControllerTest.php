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
use Piwik\Exception\ThingNotFoundException;
use Piwik\FrontController;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Live
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private array $get = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->get = $_GET;

        Fixture::createSuperUser();
        Fixture::createWebsite('2010-01-01');
    }

    public function tearDown(): void
    {
        $_GET = $this->get;

        parent::tearDown();
    }

    public function testGetVisitorProfilePopupAnswersNotFoundForAnUnknownVisitor()
    {
        $_GET = [
            'module' => 'Live',
            'action' => 'getVisitorProfilePopup',
            'idSite' => '1',
            'period' => 'day',
            'date' => 'today',
            'visitorId' => 'a9f860aa3565d59b',
        ];

        $this->expectException(ThingNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Visitor could not be found');

        $outputBufferLevel = ob_get_level();
        try {
            Access::doAsSuperUser(function () {
                FrontController::getInstance()->fetchDispatch('Live', 'getVisitorProfilePopup');
            });
        } finally {
            while (ob_get_level() > $outputBufferLevel) {
                ob_end_clean();
            }
        }
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
