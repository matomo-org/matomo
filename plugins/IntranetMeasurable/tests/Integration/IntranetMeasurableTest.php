<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable\tests\Integration;

use Piwik\API\Request;
use Piwik\Plugins\IntranetMeasurable\Type;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;

/**
 * @group IntranetMeasurable
 * @group IntranetMeasurableTest
 * @group Plugins
 */
class IntranetMeasurableTest extends IntegrationTestCase
{
    private $idSiteEnabled;
    private $idSiteDisabled;
    private $idSiteNotIntranet;

    public function setUp()
    {
        parent::setUp();
        $this->idSiteEnabled = Request::processRequest('SitesManager.addSite', array(
            'siteName' => 'My Site',
            'type' => Type::ID,
            'settingValues' => array('IntranetMeasurable' => array(array('name' => 'trust_visitors_cookies', 'value' => '1')))
        ));
        $this->idSiteDisabled = Request::processRequest('SitesManager.addSite', array(
            'siteName' => 'My Site',
            'type' => Type::ID,
            'settingValues' => array('IntranetMeasurable' => array(array('name' => 'trust_visitors_cookies', 'value' => '0')))
        ));
        $this->idSiteNotIntranet = Fixture::createWebsite('2014-01-02 03:04:05');
    }

    public function test_recordWebsiteDataInCache_enabled()
    {
        $cache = Cache::getCacheWebsiteAttributes($this->idSiteEnabled);
        $this->assertEquals('1', $cache['enable_trust_visitors_cookies']);
    }

    public function test_recordWebsiteDataInCache_disabled()
    {
        $cache = Cache::getCacheWebsiteAttributes($this->idSiteDisabled);
        $this->assertEquals('0', $cache['enable_trust_visitors_cookies']);
    }

    public function test_recordWebsiteDataInCache_notIntranet()
    {
        $cache = Cache::getCacheWebsiteAttributes($this->idSiteNotIntranet);
        $this->assertArrayNotHasKey('enable_trust_visitors_cookies', $cache);
    }

}
