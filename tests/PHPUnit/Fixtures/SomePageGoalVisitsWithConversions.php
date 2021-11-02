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
            API::getInstance()->addGoal($this->idSite, 'Newsletter signup', 'event_action', 'click',
                'contains', false, 10);
        }

    }

    private function doPageVisit($t, string $pageLetter)
    {
        $pageUrl = 'http://example.org/page_'.$pageLetter;
        $t->setUrl($pageUrl);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour((($this->ticks += 0.1)))->getDatetime());
        self::checkResponse($t->doTrackPageView('Page '.$pageLetter));
    }

    private function doConversion($t, int $idGoal)
    {
        if ($idGoal == 1) {
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour((($this->ticks += 0.1)))->getDatetime());
            self::checkResponse($t->doTrackEvent('category', 'click_action', 'name'));
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

        // Visit 1: A > B > A > C > Conversion
        $this->doPageVisit($t, 'A');
        $this->doPageVisit($t, 'B');
        $this->doPageVisit($t, 'A');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);

        // Visit 2: A > C > Conversion
        $this->doNewVisitor($t, 'f66bc315f2a01a79');
        $this->doPageVisit($t, 'A');
        $this->doPageVisit($t, 'C');
        $this->doConversion($t, 1);

        // Visit 3: A > D > No conversion
        $this->doNewVisitor($t,  'a13b7c5a62f72dea');
        $this->doPageVisit($t, 'A');
        $this->doPageVisit($t, 'D');

    }

}