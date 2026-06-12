<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration;

use Piwik\Plugins\BotTracking\API;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Guards that "Unique Human Pageviews" in the archived Favoured Pages reports counts the same thing
 * the Actions Pages report does: real pageviews only. A page URL that appears solely as the context
 * of an event must not be counted (it is not a pageview), otherwise the metric would diverge from
 * the Pages report.
 *
 * @group BotTracking
 * @group AIChatbotFavouredPages
 * @group Plugins
 */
class AIChatbotFavouredPagesPageviewParityTest extends IntegrationTestCase
{
    private const DATE = '2024-05-01';

    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite(self::DATE . ' 00:00:00', 0, false, 'https://example.com');

        // Two distinct visits that view /human-page (a real pageview).
        $this->trackPageView('visitor-human-1', 'https://example.com/human-page');
        $this->trackPageView('visitor-human-2', 'https://example.com/human-page');

        // One visit that only fires an event on /event-only-page (no pageview of it). The event row
        // carries idaction_url = /event-only-page, so a naive pageview query would wrongly count it.
        $this->trackEventOnPage('visitor-event', 'https://example.com/event-only-page');
    }

    public function testEventOnlyPageIsNotCountedAsHumanPageview(): void
    {
        $table = API::getInstance()->getAIChatbotHumanFavouredPages($this->idSite, 'day', self::DATE);

        $humanPage = $table->getRowFromLabel('example.com/human-page');
        self::assertNotFalse($humanPage, 'the real pageview URL should be present');
        self::assertSame(2, (int) $humanPage->getColumn(Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS));

        // The page that only hosted an event is not a pageview and must be absent.
        self::assertFalse(
            $table->getRowFromLabel('example.com/event-only-page'),
            'a page URL seen only as an event context must not appear as a human pageview'
        );
    }

    private function trackPageView(string $visitorId, string $url): void
    {
        $tracker = Fixture::getTracker($this->idSite, self::DATE . ' 03:00:00', true);
        $tracker->setVisitorId(substr(md5($visitorId), 0, 16));
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackPageView('Page'));
    }

    private function trackEventOnPage(string $visitorId, string $url): void
    {
        $tracker = Fixture::getTracker($this->idSite, self::DATE . ' 04:00:00', true);
        $tracker->setVisitorId(substr(md5($visitorId), 0, 16));
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackEvent('Media', 'Play'));
    }
}
