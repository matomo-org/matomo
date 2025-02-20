<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Plugin\Manager;
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

    public function setUp(): void
    {
        parent::setUp();

        Manager::getInstance()->activatePlugin('MobileAppMeasurable');

        $this->model = new Model();
    }

    public function testGetUsedTypeIdsShouldReturnNoTypeIfNoSitesExist()
    {
        $this->assertSame([], $this->model->getUsedTypeIds());
    }

    public function testGetUsedTypeIdsShouldReturnOnlyOneTypeIfAllSitesUseSameType()
    {
        for ($i = 0; $i < 9; $i++) {
            $this->createMeasurable('website');
        }

        $this->assertSame(['website'], $this->model->getUsedTypeIds());
    }

    public function testGetUsedTypeIdsShouldReturnAnotherTypeIfDifferentOnesAreUsed()
    {
        for ($i = 0; $i < 9; $i++) {
            $this->createMeasurable('website');
            $this->createMeasurable('intranet');
            $this->createMeasurable('mobileapp');
        }

        $this->assertEqualsCanonicalizing(['website', 'intranet', 'mobileapp'], $this->model->getUsedTypeIds());
    }

    public function testGetAllKnownUrlsForAllSitesShouldReturnAllUrls()
    {
        $idSite = $this->createMeasurable('website', 'http://apache.piwik');
        $this->model->insertSiteUrl($idSite, 'http://example.apache.piwik');
        $this->model->insertSiteUrl($idSite, 'http://example.org');

        $idSite2 = $this->createMeasurable('website');
        $this->model->insertSiteUrl($idSite2, 'http://example.org');
        $this->model->insertSiteUrl($idSite2, 'http://example.com');

        $idSite3 = $this->createMeasurable('website', 'http://example.pro');

        $expected = [
            [
                'idsite' => $idSite,
                'url' => 'http://apache.piwik'
            ],
            [
                'idsite' => $idSite2,
                'url' => 'http://piwik.net'
            ],
            [
                'idsite' => $idSite3,
                'url' => 'http://example.pro'
            ],
            [
                'idsite' => $idSite,
                'url' => 'http://example.apache.piwik'
            ],
            [
                'idsite' => $idSite,
                'url' => 'http://example.org'
            ],
            [
                'idsite' => $idSite2,
                'url' => 'http://example.com'
            ],
            [
                'idsite' => $idSite2,
                'url' => 'http://example.org'
            ]

        ];
        $this->assertEquals($expected, $this->model->getAllKnownUrlsForAllSites());
    }

    private function createMeasurable($type, $siteUrl = false)
    {
        return Fixture::createWebsite(
            '2015-01-01 00:00:00',
            $ecommerce = 0,
            $siteName = false,
            $siteUrl,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $timezone = null,
            $type
        );
    }
}
