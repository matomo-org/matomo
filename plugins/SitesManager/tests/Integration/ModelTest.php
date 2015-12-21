<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Plugins\SitesManager\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugins
 * @group ModelTest
 * @group SitesManager
 */
class ModelTest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    private $model;

    public function setUp()
    {
        parent::setUp();

        $this->model = new Model();
    }

    public function test_getUsedTypeIds_shouldReturnNoType_IfNoSitesExist()
    {
        $this->assertSame(array(), $this->model->getUsedTypeIds());
    }

    public function test_getUsedTypeIds_shouldReturnOnlyOneType_IfAllSitesUseSameType()
    {
        for ($i = 0; $i < 9; $i++) {
            $this->createMeasurable('website');
        }

        $this->assertSame(array('website'), $this->model->getUsedTypeIds());
    }

    public function test_getUsedTypeIds_shouldReturnAnotherType_IfDifferentOnesAreUsed()
    {
        for ($i = 0; $i < 9; $i++) {
            $this->createMeasurable('website');
            $this->createMeasurable('universal');
            $this->createMeasurable('mobileapp');
        }

        $this->assertSame(array('website', 'universal', 'mobileapp'), $this->model->getUsedTypeIds());
    }

    public function test_getAllKnownUrlsForAllSites_shouldReturnAllUrls()
    {
        $idSite = $this->createMeasurable('website', 'http://apache.piwik');
        $this->model->insertSiteUrl($idSite, 'http://example.apache.piwik');
        $this->model->insertSiteUrl($idSite, 'http://example.org');

        $idSite2 = $this->createMeasurable('website');
        $this->model->insertSiteUrl($idSite2, 'http://example.org');
        $this->model->insertSiteUrl($idSite2, 'http://example.com');

        $idSite3 = $this->createMeasurable('website', 'http://example.pro');

        $expected = array(
            array(
                'idsite' => $idSite,
                'url' => 'http://apache.piwik'
            ),
            array(
                'idsite' => $idSite2,
                'url' => 'http://piwik.net'
            ),
            array(
                'idsite' => $idSite3,
                'url' => 'http://example.pro'
            ),
            array(
                'idsite' => $idSite,
                'url' => 'http://example.apache.piwik'
            ),
            array(
                'idsite' => $idSite,
                'url' => 'http://example.org'
            ),
            array(
                'idsite' => $idSite2,
                'url' => 'http://example.com'
            ),
            array(
                'idsite' => $idSite2,
                'url' => 'http://example.org'
            )

        );
        $this->assertEquals($expected, $this->model->getAllKnownUrlsForAllSites());
    }

    private function createMeasurable($type, $siteUrl = false)
    {
        return Fixture::createWebsite('2015-01-01 00:00:00',
            $ecommerce = 0, $siteName = false, $siteUrl,
            $siteSearch = 1, $searchKeywordParameters = null,
            $searchCategoryParameters = null, $timezone = null, $type);
    }
}
