<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/BenchmarkTestCase.php';

/**
 * Tracks 12,500 pageviews on one site. Uses bulk tracking (no
 * point in measuring curl/HTTP).
 */
class TrackerBenchmark extends BenchmarkTestCase
{
    private $urls = array();
    private $pageTitles = array();
    private $visitDates = array();
    private $visitTimes = array();
    private $t = null;

    public function setUp()
    {
        // set up action URLs
        for ($i = 0; $i != 100; ++$i) {
            $this->urls[] = "http://whatever.com/$i/" . ($i + 1);
            $this->pageTitles[] = "Page Title $i / " . ($i + 1);
        }

        // set dates & times
        $date = Date::factory(self::$fixture->date);
        for ($i = 0; $i != 25; ++$i) {
            $this->visitDates[] = $date->addDay($i)->toString('Y-m-d');
        }
        for ($i = 0; $i != 5; ++$i) {
            $this->visitTimes[] = $date->addHour($i)->toString('H:i:s');
        }

        // create tracker before tracking test
        $this->t = $this->getTracker(self::$fixture->idSite, self::$fixture->date);
        $this->t->setTokenAuth(self::getTokenAuth());
        $this->t->enableBulkTracking();

        // track 12,500 actions: 50 visitors w/ 5 visits each per day for 25 days w/ 2 actions per visit
        $urlIdx = 0;
        foreach ($this->visitDates as $date) {
            foreach ($this->visitTimes as $time) {
                for ($visitor = 0; $visitor != 50; ++$visitor) {
                    $this->t->setIp('157.5.6.' . ($visitor + 1));
                    $this->t->setForceVisitDateTime($date . ' ' . $time);
                    for ($action = 0; $action != 2; ++$action) {
                        $realIdx = $urlIdx % count($this->urls);

                        $this->t->setUrl($this->urls[$realIdx]);
                        $this->t->doTrackPageView($this->pageTitles[$realIdx]);

                        ++$urlIdx;
                    }
                }
            }
        }
    }

    /**
     * @group        Benchmarks
     */
    public function testTracker()
    {
        self::checkResponse($this->t->doBulkTrack());
    }
}
