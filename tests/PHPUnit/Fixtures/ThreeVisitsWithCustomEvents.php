<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Tests\Framework\Fixture;
use MatomoTracker;

/**
 * Tracks custom events
 */
class ThreeVisitsWithCustomEvents extends Fixture
{
    public $dateTime = '2010-01-03 11:22:33';
    public $idSite = 1;
    public static $idGoalTriggeredOnEventCategory = 3;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    private function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            // These two goals are to check events don't trigger for URL or Title matching
            APIGoals::getInstance()->addGoal($this->idSite, 'triggered js', 'url', 'webradio', 'contains');
            APIGoals::getInstance()->addGoal($this->idSite, 'triggered js', 'title', 'Music', 'contains');
            $idGoalTriggeredOnEventCategory = APIGoals::getInstance()->addGoal(
                $this->idSite,
                'event matching',
                'event_category',
                'CategoryTriggersGoal',
                'contains',
                false,
                8,
                true,
                '',
                true
            );

            $this->assertEquals($idGoalTriggeredOnEventCategory, self::$idGoalTriggeredOnEventCategory);
        }
    }

    public function trackVisits()
    {
        $uselocal = false;
        $vis = self::getTracker($this->idSite, $this->dateTime, $useDefault = true, $uselocal);

        // $vis will start with a pageview, while $vis2 will directly start with the event
        $vis->setUrl('http://example.org/webradio');
        $vis->setPerformanceTimings(5, 111, 245, 145, 66, 0);
        self::checkResponse($vis->doTrackPageView('Welcome!'));

        $this->trackMusicPlaying($vis);
        $this->trackMusicRatings($vis);
        $this->trackEventWithoutUrl($vis);
        $this->trackMovieWatchingIncludingInterval($vis);

        $this->dateTime = Date::factory($this->dateTime)->addHour(0.5)->getDatetime();
        $vis2 = self::getTracker($this->idSite, $this->dateTime, $useDefault = true, $uselocal);
        $vis2->setUrl('http://example.com/piwik/');
        $vis2->setIp('111.1.1.1');
        $vis2->setPlugins($flash = false, $java = false);

        $this->trackMusicPlaying($vis2);
        $this->trackMusicRatings($vis2);
        $this->trackMovieWatchingIncludingInterval($vis2);

        $this->dateTime = Date::factory($this->dateTime)->addDay(1)->getDatetime();
        $vis3 = self::getTracker($this->idSite, $this->dateTime, $useDefault = true, $uselocal);
        $vis3->setUrl('http://example.com/piwik/');
        $vis3->setIp('111.1.1.2');
        $vis3->setPlugins($flash = false, $java = false);

        $this->trackMusicPlaying($vis3);
        $this->trackMusicRatings($vis3);
        $this->trackMovieWatchingIncludingInterval($vis3);
    }

    private function moveTimeForward(MatomoTracker $vis, $minutes)
    {
        $hour = $minutes / 60;
        return $vis->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($hour)->getDatetime());
    }

    protected function trackEventWithoutUrl(MatomoTracker $vis)
    {
        $url = $vis->pageUrl;
        $vis->setUrl('');

        // no value, use default revenue
        $this->moveTimeForward($vis, 1);
        self::checkResponse($vis->doTrackEvent('CategoryTriggersGoal here', 'This is an event without a URL', $name = false));

        // event value
        $this->moveTimeForward($vis, 2);
        self::checkResponse($vis->doTrackEvent('CategoryTriggersGoal here', 'This is an event without a URL', $name = false, $value = 23));

        // event value is 0, use 0
        $this->moveTimeForward($vis, 3);
        self::checkResponse($vis->doTrackEvent('CategoryTriggersGoal here', 'This is an event without a URL', $name = false, $value = 0));

        $vis->setUrl($url);
    }

    protected function trackMusicPlaying(MatomoTracker $vis)
    {
        $this->moveTimeForward($vis, 1);
        $this->setMusicEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Music', 'play', 'La fiancée de l\'eau'));

        $this->moveTimeForward($vis, 2);
        $this->setMusicEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Music', 'play25%', 'La fiancée de l\'eau'));
        $this->moveTimeForward($vis, 3);
        $this->setMusicEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Music', 'play50%', 'La fiancée de l\'eau'));
        $this->moveTimeForward($vis, 4);
        $this->setMusicEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Music', 'play75%', 'La fiancée de l\'eau'));

        $this->moveTimeForward($vis, 4.5);
        $this->setMusicEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Music', 'playEnd', 'La fiancée de l\'eau'));
    }

    protected function trackMusicRatings(MatomoTracker $vis)
    {
        $this->moveTimeForward($vis, 5);
        $this->setMusicEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Music', 'rating', 'La fiancée de l\'eau', 9));

        $this->moveTimeForward($vis, 5.02);
        $this->setMusicEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Music', 'rating', 'La fiancée de l\'eau', 10));
    }

    protected function trackMovieWatchingIncludingInterval(MatomoTracker $vis)
    {
        // First a pageview so the time on page is tracked properly
        $this->moveTimeForward($vis, 30);
        $vis->setUrl('http://example.org/movies');
        $vis->setPerformanceTimings(0, 455, 169, 20, 99, 160);
        self::checkResponse($vis->doTrackPageView('Movie Theater'));

        $this->moveTimeForward($vis, 31);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'playTrailer', 'Princess Mononoke (もののけ姫)'));
        $this->moveTimeForward($vis, 33);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'playTrailer', 'Ponyo (崖の上のポニョ)'));
        $this->moveTimeForward($vis, 35);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'playTrailer', 'Spirited Away (千と千尋の神隠し)'));
        $this->moveTimeForward($vis, 36);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'clickBuyNow', 'Spirited Away (千と千尋の神隠し)'));
        $this->moveTimeForward($vis, 38);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'playStart', 'Spirited Away (千と千尋の神隠し)'));
        $this->moveTimeForward($vis, 60);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'play25%', 'Spirited Away (千と千尋の神隠し)'));

        // trackEvent without a name
        $this->moveTimeForward($vis, 150);
        self::checkResponse($vis->doTrackEvent('Movie', 'Search'));

        // taking 2+ hours break & resuming this epic moment of cinema
        $this->moveTimeForward($vis, 200);

        $this->moveTimeForward($vis, 222);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'play50%', 'Spirited Away (千と千尋の神隠し)'));
        $this->moveTimeForward($vis, 244);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'play75%', 'Spirited Away (千と千尋の神隠し)'));

        $this->moveTimeForward($vis, 251);
        self::checkResponse($vis->doTrackEvent('Movie', 'Search', 'Search query here'));

        $this->moveTimeForward($vis, 266);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'playEnd', 'Spirited Away (千と千尋の神隠し)'));

        // Test Events without a URL
        $vis->setUrl('');
        $this->moveTimeForward($vis, 268);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('Movie', 'rating', 'Spirited Away (千と千尋の神隠し)', 9.66));

        // Test event with long names should be truncated
        $vis->setUrl('http://example.org/finishedMovie');
        $append = "Extremely long Extremely long Extremely long Extremely long Extremely long Extremely long Extremely long Extremely long Extremely long Extremely long";
        $append .= " ---> SHOULD APPEAR IN TEST OUTPUT NOT TRUNCATED <---         ";
        $this->moveTimeForward($vis, 280);
        $this->setMovieEventCustomVar($vis);
        self::checkResponse($vis->doTrackEvent('event category ' . $append, 'event action ' . $append, 'event name ' . $append, 9.66));

        $this->moveTimeForward($vis, 352);
        self::checkResponse($vis->doTrackEvent('Movie', 'Search'));
        $this->moveTimeForward($vis, 453);
        self::checkResponse($vis->doTrackEvent('Movie', 'Purchase'));
    }

    private function setMusicEventCustomVar(MatomoTracker $vis)
    {
        $vis->setCustomVariable($id = 1, $name = 'Page Scope Custom var', $value = 'should not appear in events report', $scope = 'page');
        $vis->setCustomVariable($id = 1, $name = 'album', $value = 'En attendant les caravanes...', $scope = 'event');
        $vis->setCustomVariable($id = 1, $name = 'genre', $value = 'World music', $scope = 'event');
    }

    private function setMovieEventCustomVar(MatomoTracker $vis)
    {
        $vis->setCustomVariable($id = 1, $name = 'country', $value = '日本', $scope = 'event');
        $vis->setCustomVariable($id = 2, $name = 'genre', $value = 'Greatest animated films', $scope = 'event');
        $vis->setCustomVariable($id = 4, $name = 'genre', $value = 'Adventure', $scope = 'event');
        $vis->setCustomVariable($id = 5, $name = 'genre', $value = 'Family', $scope = 'event');
        $vis->setCustomVariable($id = 5, $name = 'movieid', $value = 15763, $scope = 'event');

        $vis->setCustomVariable($id = 1, $name = 'Visit Scope Custom var', $value = 'should not appear in events report Bis', $scope = 'visit');
    }

    public function tearDown(): void
    {
    }
}
