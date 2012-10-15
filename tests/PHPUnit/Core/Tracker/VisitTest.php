<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
class Tracker_VisitTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Zend_Registry::set('access', $pseudoMockAccess);
        
        Piwik_PluginsManager::getInstance()->loadPlugins(array('SitesManager'));
    }

    /**
     * Dataprovider
     */
    public function getExcludedIpTestData()
    {
        return array(
            array('12.12.12.12', array(
                '12.12.12.12' => true,
                '12.12.12.11' => false,
                '12.12.12.13' => false,
                '0.0.0.0' => false,
                '255.255.255.255' => false
            )),
            array('12.12.12.12/32', array(
                '12.12.12.12' => true,
                '12.12.12.11' => false,
                '12.12.12.13' => false,
                '0.0.0.0' => false,
                '255.255.255.255' => false
            )),
            array('12.12.12.*', array(
                '12.12.12.0' => true,
                '12.12.12.255' => true,
                '12.12.12.12' => true,
                '12.12.11.255' => false,
                '12.12.13.0' => false,
                '0.0.0.0' => false,
                '255.255.255.255' => false,
            )),
            array('12.12.12.0/24', array(
                '12.12.12.0' => true,
                '12.12.12.255' => true,
                '12.12.12.12' => true,
                '12.12.11.255' => false,
                '12.12.13.0' => false,
                '0.0.0.0' => false,
                '255.255.255.255' => false,
            )),
            // add some ipv6 addresses!
        );
    }
    
    /**
     * @group Core
     * @group Tracker
     * @group Tracker_Visit
     * @dataProvider getExcludedIpTestData
     */
    public function testIsVisitorIpExcluded($excludedIp, $tests)
    {
        $visit = new Test_Piwik_TrackerVisit_public();
        $idsite = Piwik_SitesManager_API::getInstance()->addSite("name","http://piwik.net/",$ecommerce=0,
	        $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null, $excludedIp);
        $visit->setRequest(array('idsite' => $idsite));

        // test that IPs within the range, or the given IP, are excluded
        foreach($tests as $ip => $expected)
        {
            $testIpIsExcluded = Piwik_IP::P2N($ip);
            $this->assertSame($expected, $visit->public_isVisitorIpExcluded($testIpIsExcluded));
        }
    }
}

class Test_Piwik_TrackerVisit_public extends Piwik_Tracker_Visit {
    public function public_isVisitorIpExcluded( $ip )
    {
        return $this->isVisitorIpExcluded($ip);
    }
}
