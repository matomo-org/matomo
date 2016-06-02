<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoVisitsWithCustomVariables;

/**
 * Tests use of custom variable segments.
 *
 * @group Plugins
 * @group TwoVisitsWithCustomVariablesSegmentContainsTest
 */
class TwoVisitsWithCustomVariablesSegmentContainsTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public static function getOutputPrefix()
    {
        return 'twoVisitsWithCustomVariables';
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $return = array();

        $api = array('Actions.getPageUrls', 'Actions.getPageTitles', 'VisitsSummary.get');
        $segmentsToTest = array(
            // array( SegmentString , TestSuffix , Array of API to test)
            array("pageTitle=@*_)%", '_SegmentPageTitleContainsStrangeCharacters', array('Actions.getPageTitles', 'VisitsSummary.get')),
            array("pageUrl=@user/profile", '_SegmentPageUrlContains', $api),
            array("pageTitle=@Profile pa", '_SegmentPageTitleContains', $api),
            array("pageUrl!@user/profile", '_SegmentPageUrlExcludes', $api),
            array("pageTitle!@Profile pa", '_SegmentPageTitleExcludes', $api),
            // starts with
            array('pageUrl=^example.org/home', '_SegmentPageUrlStartsWith', array('Actions.getPageUrls')),
            array('pageTitle=^Profile pa', '_SegmentPageTitleStartsWith', array('Actions.getPageTitles')),

            // ends with
            array('pageUrl=$er/profile', '_SegmentPageUrlEndsWith', array('Actions.getPageUrls')),
            array('pageTitle=$page', '_SegmentPageTitleEndsWith', array('Actions.getPageTitles')),
        );

        foreach ($segmentsToTest as $segment) {
            // Also test "Page URL / Page title CONTAINS string" feature
            $return[] = array($segment[2],
                              array('idSite'       => $idSite, 'date' => $dateTime, 'periods' => array('day'),
                                    'setDateLastN' => false,
                                    'segment'      => $segment[0],
                                    'testSuffix'   => $segment[1])
            );
        }
        return $return;
    }
}

TwoVisitsWithCustomVariablesSegmentContainsTest::$fixture = new TwoVisitsWithCustomVariables();
TwoVisitsWithCustomVariablesSegmentContainsTest::$fixture->doExtraQuoteTests = false;