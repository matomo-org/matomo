<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoVisitsWithCustomVariables;

/**
 * Test CSV export with Expanded rows, Translated labels, Different languages
 *
 * @group CsvExportTest
 * @group Core
 */
class CsvExportTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

        $enExtraParam = array('expanded' => 0, 'flat' => 1, 'include_aggregate_rows' => 0, 'translateColumnNames' => 1);

        $deExtraParam = array('expanded' => 0, 'flat' => 1, 'include_aggregate_rows' => 1, 'translateColumnNames' => 1);

        return array(
            array($apiToCall, array('idSite'                 => $idSite,
                                    'date'                   => $dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => array('expanded' => 0, 'flat' => 0),
                                    'testSuffix'             => '_xp0')),

            array($apiToCall, array('idSite'                 => $idSite,
                                    'date'                   => $dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => $enExtraParam,
                                    'language'               => 'en',
                                    'testSuffix'             => '_xp1_inner0_trans-en')),

            array($apiToCall, array('idSite'                 => $idSite,
                                    'date'                   => $dateTime,
                                    'format'                 => 'csv',
                                    'otherRequestParameters' => $deExtraParam,
                                    'language'               => 'de',
                                    'testSuffix'             => '_xp1_inner1_trans-de')),

            array('Live.getLastVisitsDetails', array('idSite'                 => $idSite,
                                                     'date'                   => $dateTime,
                                                     'format'                 => 'csv',
                                                     'otherRequestParameters' => array(
                                                         'hideColumns' => 'serverDate,actionDetails,serverTimestamp,serverTimePretty,'
                                                                        . 'serverDatePretty,serverDatePrettyFirstAction,serverTimePrettyFirstAction,'
                                                                        . 'goalTimePretty,serverTimePretty,visitorId,visitServerHour,date,'
                                                                        . 'prettyDate,serverDateTimePrettyFirstAction'
                                                     )))
        );
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public static function getOutputPrefix()
    {
        return 'csvExport';
    }
}

CsvExportTest::$fixture = new TwoVisitsWithCustomVariables();
CsvExportTest::$fixture->visitorId = null;
CsvExportTest::$fixture->useEscapedQuotes = false;
CsvExportTest::$fixture->doExtraQuoteTests = false;