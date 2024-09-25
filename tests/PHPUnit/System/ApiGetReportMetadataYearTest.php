<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\InvalidVisits;

/**
 * test the Yearly metadata API response,
 * with no visits, with custom response language
 *
 * @group Plugins
 * @group ApiGetReportMetadataYearTest
 */
class ApiGetReportMetadataYearTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $params = array('idSite'   => self::$fixture->idSite,
                        'date'     => self::$fixture->dateTime,
                        'periods'  => 'year',
                        'language' => 'fr');
        return [array('API.getProcessedReport', $params)];
        return array(
            array('API.getProcessedReport', $params),
            array('LanguagesManager.getAvailableLanguageNames', $params),
            array('SitesManager.getJavascriptTag', $params)
        );
    }

    public static function getOutputPrefix()
    {
        return 'apiGetReportMetadata_year';
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}

ApiGetReportMetadataYearTest::$fixture = new InvalidVisits();
ApiGetReportMetadataYearTest::$fixture->trackInvalidRequests = false;
