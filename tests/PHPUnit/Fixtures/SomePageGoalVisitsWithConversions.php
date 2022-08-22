<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site and tracks some visits across mulitple pages with couple conversions.
 */
class SomePageGoalVisitsWithConversions extends Fixture
{
    public $dateTime = '2009-01-04 00:11:42';
    public $idSite = 1;
    private $ticks = 0;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
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
            API::getInstance()->addGoal($this->idSite, 'Goal 1', 'event_action', 'click',
                'contains', false, 10);
        }

        // Contact me signup goal
        if (!self::goalExists($idSite = 1, $idGoal = 2)) {
            API::getInstance()->addGoal($this->idSite, 'Goal "<2~$%+"', 'event_action', 'press',
                'contains', false, 10);
        }

    }

    private function doPageVisit($t, string $pageLetter, ?string $subPage = null)
    {
        $pageUrl = 'http://example.org/page_'.$pageLetter.($subPage ? '/'.$subPage : '');
        $t->setUrl($pageUrl);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour((($this->ticks += 0.1)))->getDatetime());
        self::checkResponse($t->doTrackPageView('Page '.$pageLetter.($subPage ? ' - '.$subPage : '')));
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

    private function doNewVisitor($t, $id)
    {

        $t->setVisitorId($id);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour((($this->ticks++)*2))->getDatetime());
        $t->setTokenAuth($this->getTokenAuth());
        $t->setForceNewVisit();

    }

    private function trackVisits()
    {

        $t = self::getTracker(1, $this->dateTime, $defaultInit = true);

        // Day 1 - 2009-01-04

        // Visit 1: A > B > A/X > C > Conversion 1
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'A', 'X');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);

        // Visit 2: A > A/Z > C > Conversion 1
        $this->doNewVisitor($t, 'f66bc315f2a01a79');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'A','Z');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);

        // Visit 3: A > D > No conversion
        $this->doNewVisitor($t,  'a13b7c5a62f72dea');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'D');

        // Visit 4: A > C > Conversion 1
        //          A > B > C > Conversion 2
        $this->doNewVisitor($t,  '39f72e3961e18b4e');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 2);

        // Day 2 - 2009-01-05

        $this->dateTime = Date::factory($this->dateTime)->addDay(1)->getDatetime();

        // Visit 5: A > A/Z > A/Y > C > Conversion 1
        //          A > B > C > Conversion 2
        $this->doNewVisitor($t, '5f3756ae8b4cceba');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'A','Z');
        $this->doPageVisit($t, 'A','Y');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 2);

        // Visit 6: A > Conversion 1
        $this->doNewVisitor($t, '132886427a57e7ba');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doConversion($t, 1);

        // Day 3 - 2009-01-06

        $this->dateTime = Date::factory($this->dateTime)->addDay(1)->getDatetime();

        // Visit 7: A > B > A/Z > Conversion 2
        $this->doNewVisitor($t, '0335a0c08ac15bb8');
        $this->doPageVisit($t, 'A', 'index.html');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'A', 'Z');
        $this->doConversion($t, 2);


    }

}