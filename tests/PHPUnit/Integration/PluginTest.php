<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Integration;

use Piwik\CronArchive;
use Piwik\CronArchive\ReArchiveList;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugin;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class PluginTest extends IntegrationTestCase
{
    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createWebsite('2020-03-04 00:00:00');
    }

    public function testSchedulePluginReArchivingShouldReArchiveFromLastDeactivationTime()
    {
        $time = Date::today()->subDay(3);
        Option::set(Plugin\Manager::LAST_PLUGIN_DEACTIVATION_TIME_OPTION_PREFIX . 'ExamplePlugin', $time->getTimestamp());

        $plugin = new Plugin('ExamplePlugin');
        $plugin->schedulePluginReArchiving();

        $date = $this->getDateFromReArchiveList();
        $this->assertEquals($time->getDatetime(), $date);
    }

    public function testSchedulePluginReArchivingShouldReArchiveFromLastCoreArchiveTimeIfEarlier()
    {
        $time = Date::today()->subDay(3);
        Option::set(Plugin\Manager::LAST_PLUGIN_DEACTIVATION_TIME_OPTION_PREFIX . 'ExamplePlugin', $time->getTimestamp());

        $cronTime = Date::today()->subDay(5);
        Option::set(CronArchive::OPTION_ARCHIVING_FINISHED_TS, $cronTime->getTimestamp());

        $plugin = new Plugin('ExamplePlugin');
        $plugin->schedulePluginReArchiving();

        $date = $this->getDateFromReArchiveList();
        $this->assertEquals($cronTime->getDatetime(), $date);
    }

    public function testSchedulePluginReArchivingShouldReArchiveFromLastCoreArchiveTimeIfNoDeactivation()
    {
        $cronTime = Date::today()->subDay(5);
        Option::set(CronArchive::OPTION_ARCHIVING_FINISHED_TS, $cronTime->getTimestamp());

        $plugin = new Plugin('ExamplePlugin');
        $plugin->schedulePluginReArchiving();

        $date = $this->getDateFromReArchiveList();
        $this->assertNull($date);
    }

    public function testSchedulePluginReArchivingShouldReArchiveFromNMonthsAgoIfNoDecativationTimeOrCronTimeExists()
    {
        $plugin = new Plugin('ExamplePlugin');
        $plugin->schedulePluginReArchiving();

        $date = $this->getDateFromReArchiveList();
        $this->assertNull($date);
    }

    private function getDateFromReArchiveList()
    {
        $list = new ReArchiveList();
        $items = $list->getAll();

        $item = reset($items);
        $item = json_decode($item, $assocc = true);

        if (empty($item['startDate'])) {
            return null;
        }

        $date = $item['startDate'];
        return Date::factory($date)->getDatetime();
    }
}
