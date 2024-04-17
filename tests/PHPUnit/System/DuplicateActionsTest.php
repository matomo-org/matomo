<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * The tracker inserts actions in separate SQL queries which can cause
 * duplicate actions to be in the DB (actions w/ the same name, hash + type, but
 * different idaction). The tracker will delete duplicate actions, but
 * if for some reason the tracker fails before the DELETE occurs, there can be
 * stray duplicate actions. This test is there to ensure reports are not affected
 * by duplicate action entries.
 *
 * @group Core
 * @group DuplicateActionsTest
 */
class DuplicateActionsTest extends SystemTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture = null; // initialized below class

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // add duplicates for every action
        $table = Common::prefixTable('log_action');
        foreach (Db::fetchAll("SELECT * FROM $table") as $row) {
            $insertSql = "INSERT INTO $table (name, type, hash, url_prefix)
                               VALUES (?, ?, CRC32(?), ?)";

            Db::query($insertSql, array($row['name'], $row['type'], $row['name'], $row['url_prefix']));
        }
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function test_PiwikApiWorks_WhenDuplicateActionsExistInDb($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $api = array('VisitsSummary', 'Actions', 'Contents', 'Events');
        return array(
            array($api, array('idSite' => $idSite,
                              'periods' => 'day',
                              'date' => $dateTime,
                              'compareAgainst' => 'OneVisitorTwoVisits',
                              'otherRequestParameters' => array(
                                   'hideColumns' => OneVisitorTwoVisits::getValueForHideColumns(),
                              )
            ))
        );
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Config' => \Piwik\DI::decorate(function ($previous) {
                $general = $previous->General;
                $general['action_title_category_delimiter'] = "/";
                $previous->General = $general;
                return $previous;
            }),
        );
    }
}

DuplicateActionsTest::$fixture = new OneVisitorTwoVisits();
