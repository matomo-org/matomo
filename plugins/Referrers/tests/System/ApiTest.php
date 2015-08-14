<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\System;

use Piwik\Tests\Fixtures\TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Referrers
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $api = array(
            'API.getProcessedReport'
        );

        $apiToTest   = array();

        // we make sure it returns a subtableIds even if a DataTable\Map is requested
        $apiToTest[] = array($api,
            array(
                'idSite'     => 1,
                'apiModule'  => 'Referrers',
                'apiAction'  => 'getReferrerType',
                'date'       => '2010-01-01,2010-03-10',
                'periods'    => array('day'),
                'testSuffix' => 'Referrers_getReferrerType',
                'otherRequestParameters' => array('expanded' => 0)
            )
        );

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

ApiTest::$fixture = new TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers();