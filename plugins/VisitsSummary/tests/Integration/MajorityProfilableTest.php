<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitsSummary\tests\Integration;

use Piwik\Date;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Plugins\VisitsSummary\MajorityProfilable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class MajorityProfilableTest extends IntegrationTestCase
{
    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();
        Fixture::createWebsite('2020-02-02 03:04:05');
    }

    public function test_isPeriodMajorityProfilable_whenMultiplePeriodsRequested()
    {
        $majorityProfilable = new MajorityProfilable();
        $actual = $majorityProfilable->isPeriodMajorityProfilable(1, 'month', '2020-03-01,2020-06-01');
        $this->assertTrue($actual);
    }

    /**
     * @dataProvider getTestDataForIsPeriodMajorityProfilableWithBadInput
     */
    public function test_isPeriodMajorityProfilable_whenBadInputsSupplied($idSite, $period, $date, $segment)
    {
        $majorityProfilable = new MajorityProfilable();
        $actual = $majorityProfilable->isPeriodMajorityProfilable($idSite, $period, $date, $segment);
        $this->assertTrue($actual);
    }

    public function getTestDataForIsPeriodMajorityProfilableWithBadInput()
    {
        return [
            // no parameters + no $_GET/$_POST values
            [null, null, null, null],

            ['all', 'day', '2020-03-04', null],
        ];
    }

    public function test_isPeriodMajorityProfilable_whenThereAreProfilableVisits()
    {
        $this->trackVisits('2020-03-04', true);

        $majorityProfilable = new MajorityProfilable();
        $actual = $majorityProfilable->isPeriodMajorityProfilable(1, 'week', '2020-03-04');
        $this->assertTrue($actual);
    }

    public function test_isPeriodMajorityProfilable_whenThereAreNoProfilableVisits()
    {
        $this->trackVisits('2020-03-04', false);

        $majorityProfilable = new MajorityProfilable();
        $actual = $majorityProfilable->isPeriodMajorityProfilable(1, 'week', '2020-03-04');
        $this->assertFalse($actual);
    }

    public function test_isPeriodMajorityProfilable_usesWeekPeriodWhenDaySelected()
    {
        $this->trackVisitsForWeek('2020-03-04');

        $majorityProfilable = new MajorityProfilable();
        $actual = $majorityProfilable->isPeriodMajorityProfilable(1, 'day', '2020-03-04');
        $this->assertTrue($actual);
    }

    public function test_isPeriodMajorityProfilable_usesTransientCacheCorrectly()
    {
        $this->trackVisits('2020-03-04', false);

        $callCount = 0;
        Piwik::addAction('API.VisitsSummary.get', function () use (&$callCount) {
            ++$callCount;
        });

        $majorityProfilable = new MajorityProfilable();
        $actual = $majorityProfilable->isPeriodMajorityProfilable(1, 'week', '2020-03-04');
        $this->assertFalse($actual);

        $actual = $majorityProfilable->isPeriodMajorityProfilable(1, 'week', '2020-03-04');
        $this->assertFalse($actual);

        $this->assertEquals(1, $callCount);
    }

    private function trackVisits($date, $isProfilable)
    {
        /** @var \MatomoTracker $t */
        $t = Fixture::getTracker(1, $date, true, true);

        if (!$isProfilable) {
            $this->unsetVisitorId($t);
        }

        $dateTime = $date . ' 05:03:04';
        $t->setForceVisitDateTime(Date::factory($dateTime)->getDatetime());
        $t->setUrl('http://site.com/some/page');
        Fixture::checkResponse($t->doTrackPageView('page view'));

        $t->setUrl('http://site.com/another/page');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.2)->getDatetime());
        Fixture::checkResponse($t->doTrackPageView('another page view'));

        $t->setUrl('http://site.com/second/visit');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(2.2)->getDatetime());
        Fixture::checkResponse($t->doTrackPageView('second visit'));

        $t->setUrl('http://site.com/second/visit/page');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(2.3)->getDatetime());
        Fixture::checkResponse($t->doTrackPageView('another page view in second visit'));
    }

    public function trackVisitsForWeek($date)
    {
        /** @var \MatomoTracker $t */
        $t = Fixture::getTracker(1, $date, true, true);

        $period = Factory::build('week', $date);

        $dateEnd = $period->getDateEnd()->addDay(1);
        for ($dateIter = $period->getDateStart(); $dateIter->isEarlier($dateEnd); $dateIter = $dateIter->addDay(1)) {
            // profilable visits on every other date
            $dateTime = $dateIter->toString() . ' 05:03:04';
            $t->setForceVisitDateTime($dateTime);
            $t->setUrl('http://site.com/some/page');
            if ($dateIter->toString() === $date) {
                // no profilable visits on the date we're testing on
                $this->unsetVisitorId($t);
            }
            Fixture::checkResponse($t->doTrackPageView('page view'));
        }
    }

    private function unsetVisitorId(\MatomoTracker $t)
    {
        $t->randomVisitorId = false;
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
