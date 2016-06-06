<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Fixtures\ThreeSitesWithSharedVisitors;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 * @group MultipleSitesArchivingTest
 */
class MultipleSitesArchivingTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $extraSite = Fixture::createWebsite(self::$fixture->dateTime, $ecommerce = 1, "the site");

        Piwik::addAction("ArchiveProcessor.Parameters.getIdSites", function (&$sites, $period) use ($extraSite) {
            if (reset($sites) == $extraSite) {
                $sites = array(1, 2, 3);
            }
        });

        Config::getInstance()->General['enable_processing_unique_visitors_multiple_sites'] = 1;
        Config::getInstance()->Tracker['enable_fingerprinting_across_websites'] = 1;
    }

    public function getApiForTesting()
    {
        $dateTime = self::$fixture->dateTime;

        return array(
            array('VisitsSummary.get', array('idSite' => 4,
                                             'date' => $dateTime,
                                             'periods' => array('day', 'month'),
                                             'testSuffix' => '_sitesGroup')),
        );
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}

MultipleSitesArchivingTest::$fixture = new ThreeSitesWithSharedVisitors();