<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Date;
use Piwik\Plugin\Menu;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Cache
 */
class MenuTest extends IntegrationTestCase
{
    private $idSiteOld;
    private $idSiteToday;

    /**
     * @var Menu
     */
    private $menu;

    private $dateSiteCreatedToday = '2020-08-08';

    public function setUp(): void
    {
        parent::setUp();

        $this->idSiteToday = Fixture::createWebsite($this->dateSiteCreatedToday . ' 08:08:08');

        $siteModel = new Model();
        $siteModel->updateSite(array('ts_created' => $this->dateSiteCreatedToday . ' 08:08:08'), $this->idSiteToday);
        // have to update it manually before Fixture::createWebsite always removes one day...

        $this->idSiteOld = Fixture::createWebsite('2020-07-07 08:08:08');

        $this->menu = new Menu();
    }

    public function test_urlForDefaultUserParams_default()
    {
        $default = $this->menu->urlForDefaultUserParams();
        $this->assertEquals([
            'idSite' => $this->idSiteToday,
            'period' => 'day',
            'date' => 'yesterday',
        ], $default);
    }

    /**
     * @dataProvider getPeriodsProvider
     */
    public function test_urlForDefaultUserParams_siteWasCreatedFewDaysAgo($period)
    {
        $default = $this->menu->urlForDefaultUserParams($this->idSiteOld, $period);
        $this->assertEquals([
            'idSite' => $this->idSiteOld,
            'period' => $period,
            'date' => 'yesterday',
        ], $default);
    }

    /**
     * @dataProvider getPeriodsProvider
     */
    public function test_urlForDefaultUserParams_siteWasCreatedToday_shouldChangeDateToSiteCreationDate($period)
    {
        $yesterday = Date::factory($this->dateSiteCreatedToday)->subDay(1)->toString();
        $default = $this->menu->urlForDefaultUserParams($this->idSiteToday, $period, $yesterday);
        $this->assertEquals([
            'idSite' => $this->idSiteToday,
            'period' => $period,
            'date' => $period === 'week' ? $yesterday : $this->dateSiteCreatedToday,
        ], $default);
    }

    /**
     * @dataProvider getPeriodsProvider
     */
    public function test_urlForDefaultUserParams_dateInPastShouldChangeToSiteCreationDay($period)
    {
        $default = $this->menu->urlForDefaultUserParams($this->idSiteToday, $period, '2017-05-05');
        $this->assertEquals([
            'idSite' => $this->idSiteToday,
            'period' => $period,
            'date' => $this->dateSiteCreatedToday,
        ], $default);
    }

    /**
     * @dataProvider getPeriodsProvider
     */
    public function test_urlForDefaultUserParams_dateInFutureShouldNotChangeDate($period)
    {
        $tomorrow = Date::now()->addDay(1)->toString();
        $default = $this->menu->urlForDefaultUserParams($this->idSiteToday, $period, $tomorrow);
        $this->assertEquals([
            'idSite' => $this->idSiteToday,
            'period' => $period,
            'date' => $tomorrow,
        ], $default);
    }

    public function test_urlForDefaultUserParams_recognisesStartOfTheWeek()
    {
        // date is before site creation date but it is the current week so no need to change it
        $default = $this->menu->urlForDefaultUserParams($this->idSiteToday, 'week', '2020-08-06');
        $this->assertEquals([
            'idSite' => $this->idSiteToday,
            'period' => 'week',
            'date' => '2020-08-06',
        ], $default);

        // the week before
        $default = $this->menu->urlForDefaultUserParams($this->idSiteToday, 'week', '2020-08-01');
        $this->assertEquals([
            'idSite' => $this->idSiteToday,
            'period' => 'week',
            'date' => $this->dateSiteCreatedToday,
        ], $default);

        // the next week
        $default = $this->menu->urlForDefaultUserParams($this->idSiteToday, 'week', '2020-08-12');
        $this->assertEquals([
            'idSite' => $this->idSiteToday,
            'period' => 'week',
            'date' => '2020-08-12',
        ], $default);
    }

    public function getPeriodsProvider()
    {
        return [
            ['day'],
            ['week'],
        ];
    }
}
