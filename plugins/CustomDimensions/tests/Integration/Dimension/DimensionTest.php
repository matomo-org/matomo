<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Integration\Dimension;

use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Dimension\Dimension;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomDimensions
 * @group DimensionTest
 * @group Dimension
 * @group Dao
 * @group Plugins
 */
class DimensionTest extends IntegrationTestCase
{
    private $id1;
    private $id2;
    private $id3;

    public function setUp(): void
    {
        parent::setUp();

        $this->id1 = $this->createIndex(CustomDimensions::SCOPE_VISIT, $index = 1, $active = true);
        $this->id2 = $this->createIndex(CustomDimensions::SCOPE_VISIT, $index = 2, $active = true);
        $this->id3 = $this->createIndex(CustomDimensions::SCOPE_ACTION, $index = 1, $active = false);
    }

    public function test_checkExists_shouldNotFailIfDimensionExists()
    {
        $this->expectNotToPerformAssertions();
        $this->getDimension($this->id1, 1)->checkExists();
        $this->getDimension($this->id2, 1)->checkExists();
        $this->getDimension($this->id3, 1)->checkExists();
    }

    public function test_checkExists_shouldFailIfDimensionDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CustomDimensions_ExceptionDimensionDoesNotExist');

        $this->getDimension($this->id1, 2)->checkExists();
    }

    public function test_checkActive_shouldNotFailIfDimensionExistsAndIsActive()
    {
        $this->expectNotToPerformAssertions();
        $this->getDimension($this->id1, 1)->checkActive();
        $this->getDimension($this->id2, 1)->checkActive();
    }

    public function test_checkActive_shouldFailIfDimensionExistsButIsNotActive()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CustomDimensions_ExceptionDimensionIsNotActive');

        $this->getDimension($this->id3, 1)->checkActive();
    }

    public function test_checkActive_shouldFailIfDimensionDoesNotExistAndThereforeIsNotActive()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CustomDimensions_ExceptionDimensionDoesNotExist');

        $this->getDimension($this->id3, 2)->checkActive();
    }

    private function createIndex($scope, $index, $active)
    {
        $configuration = new Configuration();
        return $configuration->configureNewDimension($idSite = 1, 'MyName', $scope, $index, $active, array(), $caseSensitive = true);
    }

    private function getDimension($idDimension, $idSite)
    {
        return new Dimension($idDimension, $idSite);
    }
}
