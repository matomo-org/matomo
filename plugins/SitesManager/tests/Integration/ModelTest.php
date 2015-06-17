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

    private function createMeasurable($type)
    {
        Fixture::createWebsite('2015-01-01 00:00:00',
            $ecommerce = 0, $siteName = false, $siteUrl = false,
            $siteSearch = 1, $searchKeywordParameters = null,
            $searchCategoryParameters = null, $timezone = null, $type);
    }
}
