<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Config;
use Piwik\Date;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site and tracks some visits across multiple pages with a couple conversions and a single country segment
 */
class SomePageGoalVisitsWithConversions extends Fixture
{
    public $dateTime = '2009-01-05 00:00:00';
    public $idSite = 1;
    public $segmentCountryCode = 'jp';
    private $ticks = 0;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        $this->setUpSegment();
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;

        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        // Newsletter signup goal
        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            APIGoals::getInstance()->addGoal($this->idSite, 'Goal 1', 'event_action', 'click',
                'contains', false, 10);
        }

        // Contact me signup goal
        if (!self::goalExists($idSite = 1, $idGoal = 2)) {
            APIGoals::getInstance()->addGoal($this->idSite, 'Goal "<2~$%+"', 'event_action', 'press',
                'contains', false, 10);
        }
    }

    private function setUpSegment()
    {
        APISegmentEditor::getInstance()->add('goalsByCountry', 'countryCode==' . $this->segmentCountryCode,
                                             $this->idSite, true, true);
    }

    private function doPageVisit($t, string $pageLetter, ?string $subPage = null)
    {
        $pageUrl = 'http://example.org/page_' . $pageLetter . ($subPage ? '/' . $subPage : '');
        $t->setUrl($pageUrl);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour((($this->ticks += 0.1)))->getDatetime());
        self::checkResponse($t->doTrackPageView('Page ' . $pageLetter . ($subPage ? ' - ' . $subPage : '')));
    }

    private function doConversion($t, int $idGoal)
    {
        if ($idGoal == 1) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour((($this->ticks += 0.1)))->getDatetime());
            self::checkResponse($t->doTrackEvent('category', 'click_action', 'name'));
        }
        if ($idGoal == 2) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour((($this->ticks += 0.1)))->getDatetime());
            self::checkResponse($t->doTrackEvent('category', 'press_action', 'name'));
        }
    }

    private function doNewVisitor($t, $id, $countryCode = 'us')
    {
        $t->setVisitorId($id);
        $t->setCountry($countryCode);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour((($this->ticks++) * 2))->getDatetime());
        $t->setTokenAuth($this->getTokenAuth());
        $t->setForceNewVisit();
    }

    private function trackVisits()
    {

        $t = self::getTracker(1, $this->dateTime, $defaultInit = true);

        // Day 1 - 2009-01-05

        // Visit 1: A > B > A/X > C > Conversion 1
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'A', 'X');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);

        // Visit 2: A > A/Z > C > Conversion 1
        $this->doNewVisitor($t, 'f66bc315f2a01a79', 'fr');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'A', 'Z');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);

        // Visit 3: A > D > No conversion
        $this->doNewVisitor($t, 'a13b7c5a62f72dea', 'fr');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'D');

        // Visit 4: A > C > Conversion 1
        //          A > B > C > Conversion 2
        $this->doNewVisitor($t, '39f72e3961e18b4e', 'fr');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 2);

        // Day 2 - 2009-01-06

        $this->dateTime = Date::factory($this->dateTime)->addDay(1)->getDatetime();

        // Visit 5: A > A/Z > A/Y > C > Conversion 1
        //          A > B > C > Conversion 2
        $this->doNewVisitor($t, '5f3756ae8b4cceba', 'fr');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'A', 'Z');
        $this->doPageVisit($t, 'A', 'Y');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 2);

        // Visit 6: A > Conversion 1
        // Only allocating one visit to the segment to make it easier to check manually
        $this->doNewVisitor($t, '132886427a57e7ba', $this->segmentCountryCode);
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doConversion($t, 1);

        // Day 3 - 2009-01-07

        $this->dateTime = Date::factory($this->dateTime)->addDay(1)->getDatetime();

        // Visit 7: A > B > A/Z > Conversion 2
        $this->doNewVisitor($t, '0335a0c08ac15bb8');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'A', 'Z');
        $this->doConversion($t, 2);
    }
}
