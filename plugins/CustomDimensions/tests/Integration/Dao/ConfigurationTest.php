<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Integration\Dao;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Zend_Db_Statement_Exception;

/**
 * @group CustomDimensions
 * @group ConfigurationTest
 * @group Dao
 * @group Plugins
 */
class ConfigurationTest extends IntegrationTestCase
{
    /**
     * @var Configuration
     */
    private $config;

    private $tableName;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = new Configuration();
        $this->tableName = Common::prefixTable('custom_dimensions');
    }

    public function testShouldInstallConfigTable()
    {
        $columns = DbHelper::getTableColumns($this->tableName);
        $columns = array_keys($columns);

        $expected = array(
            'idcustomdimension', 'idsite', 'name', 'index', 'scope', 'active', 'extractions', 'case_sensitive'
        );
        $this->assertSame($expected, $columns);
    }

    public function testShouldBeAbleToUninstallConfigTable()
    {
        $this->expectException(\Zend_Db_Statement_Exception::class);
        $this->expectExceptionMessage('custom_dimensions');

        $this->config->uninstall();

        try {
            DbHelper::getTableColumns($this->tableName); // doesn't work anymore as table was removed
        } catch (Zend_Db_Statement_Exception $e) {
            $this->config->install();
            throw $e;
        }

        $this->config->install();
    }

    public function testConfigureNewDimensionShouldGenerateDimensionIdCorrectlyAndShouldNextFreeId()
    {
        $cases = $this->createManyCustomDimensionCases();

        // verify they were created correctly
        foreach ($cases as $index => $case) {
            $dimension = $this->config->getCustomDimension($case['expectedId'], $case['idSite']);
            $this->assertSame('Test' . $index, $dimension['name']);
            $this->assertSame($case['scope'], $dimension['scope']);
            $this->assertSame($case['active'], $dimension['active']);
            $this->assertSame($case['index'] . '', $dimension['index']);
            $this->assertSame($case['expectedId'] . '', $dimension['idcustomdimension']);
            $this->assertSame($case['case_sensitive'], $dimension['case_sensitive']);
        }
    }

    public function testConfigureNewDimensionShouldNotBePossibleToAssignSameIndexForSameScopeAndIdSiteTwice()
    {
        $this->expectException(\Zend_Db_Statement_Exception::class);
        $this->expectExceptionMessage('Duplicate');

        $this->configureNewDimension();
        $this->configureNewDimension();
    }

    /**
     * @dataProvider getExtractionsProvider
     */
    public function testShouldSaveExtractionsSerializedAndUnserializeWhenGettingThem($expectedExtractions, $extractions)
    {
        $idSite = 1;
        $name = 'Test';
        $scope = 'action';
        $index = 5;
        $active = false;

        $idDimension = $this->config->configureNewDimension($idSite, $name, $scope, $index, $active, $extractions, $caseSensitive = true);

        $dimension = $this->config->getCustomDimension($idDimension, $idSite);
        $this->assertSame($expectedExtractions, $dimension['extractions']);

        $dimensions = $this->config->getCustomDimensionsForSite($idSite);
        $dimension = array_shift($dimensions);
        $this->assertSame($expectedExtractions, $dimension['extractions']);

        $dimensions = $this->config->getCustomDimensionsHavingIndex($scope, $index);
        $dimension = array_shift($dimensions);
        $this->assertSame($expectedExtractions, $dimension['extractions']);
    }

    /**
     * @dataProvider getExtractionsProvider
     */
    public function testConfigureExistingDimensionShouldSerializeExtractionsAndGetThemCorrectly($expectedExtractions, $extractions)
    {
        $idSite = 1;
        $name = 'Test';
        $scope = 'action';
        $index = 5;
        $active = false;

        $idDimension = $this->config->configureNewDimension($idSite, $name, $scope, $index, $active, array(), $caseSensitive = true);
        $this->config->configureExistingDimension($idDimension, $idSite, $name, $active, $extractions, $caseSensitive = true);

        $dimension = $this->config->getCustomDimension($idDimension, $idSite);
        $this->assertSame($expectedExtractions, $dimension['extractions']);
    }

    public function testConfigureExistingDimensionShouldUpdateDimensionWhenIdSiteMatches()
    {
        $idSite = 1;
        $idDimension = $this->configureNewDimension();

        $extraction1 = array('dimension' => 'url');

        $this->config->configureExistingDimension($idDimension, $idSite, $name = 'new name', $active = false, $extractions = array($extraction1), $caseSensitive = true);

        $dimension = $this->config->getCustomDimension($idDimension, $idSite);
        $this->assertSame('new name', $dimension['name']);
        $this->assertSame(false, $dimension['active']);
        $this->assertSame(true, $dimension['case_sensitive']);

        $this->config->configureExistingDimension($idDimension, $idSite, $name = 'new nam2', $active = true, $extractions = array($extraction1), $caseSensitive = false);

        $dimension = $this->config->getCustomDimension($idDimension, $idSite);
        $this->assertSame('new nam2', $dimension['name']);
        $this->assertSame(true, $dimension['active']);
        $this->assertSame(false, $dimension['case_sensitive']);
    }

    public function testConfigureExistingDimensionShouldNotUpdateDimensionIfIdSiteDoesNotMatch()
    {
        $idDimension = $this->configureNewDimension();

        $extraction1 = array('dimension' => 'url');

        $this->config->configureExistingDimension($idDimension, $idSite = 2, $name = 'new name', $active = false, $extractions = array($extraction1), $caseSensitive = false);

        // verify it stays unchanged
        $dimension = $this->config->getCustomDimension($idDimension, $idSite = 1);
        $this->assertSame('Test', $dimension['name']);
        $this->assertSame(true, $dimension['active']);
        $this->assertSame(true, $dimension['case_sensitive']);

        // verify no dimension for other site was created
        $dimension = $this->config->getCustomDimension($idDimension, $idSite = 2);
        $this->assertFalse($dimension);
    }

    public function getExtractionsProvider()
    {
        $tests = array();

        // should convert any non array value to an array
        $tests[] = array(array(), null);
        $tests[] = array(array(), 'invalid');
        $tests[] = array(array(), 5);

        $tests[] = array(array(), array());

        // this would be in theory possibly but shouldn't be done. Model should be stupid and not check it
        $tests[] = array(array(5), array(5));

        $extraction1 = array('dimension' => 'url', 'pattern' => 'index_(.*).html');
        $tests[] = array(array($extraction1), array($extraction1));

        $extraction2 = array('dimension' => 'action_name', 'pattern' => 'index(.*)');
        $tests[] = array(array($extraction1, $extraction2), array($extraction1, $extraction2));

        return $tests;
    }

    public function testGetCustomDimensionsForSiteShouldBeEmptyIfThereAreNoCustomDimensions()
    {
        $this->assertSame(array(), $this->config->getCustomDimensionsForSite($idSite = 1));
    }

    private function configureNewDimension($idSite = 1, $name = 'Test', $scope = 'action', $index = 5, $active = true, $extractions = array())
    {
        return $this->config->configureNewDimension($idSite, $name, $scope, $index, $active, $extractions, $caseSensitive = true);
    }

    public function testGetCustomDimensionShouldOnlyFindDimensionMatchingIdDimensionAndIdSite()
    {
        $this->createManyCustomDimensionCases();

        $this->assertEmpty($this->config->getCustomDimension($idDimension = 999, $idSite = 1));
        $this->assertEmpty($this->config->getCustomDimension($idDimension = 1, $idSite = 999));
        $this->assertEmpty($this->config->getCustomDimension($idDimension = 999, $idSite = 999));

        $this->assertNotEmpty($this->config->getCustomDimension($idDimension = 1, $idSite = 1));
        $this->assertNotEmpty($this->config->getCustomDimension($idDimension = 2, $idSite = 1));
        $this->assertNotEmpty($this->config->getCustomDimension($idDimension = 3, $idSite = 1));
        $this->assertNotEmpty($this->config->getCustomDimension($idDimension = 1, $idSite = 2));
        $this->assertNotEmpty($this->config->getCustomDimension($idDimension = 2, $idSite = 2));
    }

    public function testGetCustomDimensionShouldReturnDimension()
    {
        $this->createManyCustomDimensionCases();

        $dimension = $this->config->getCustomDimension($idDimension = 1, $idSite = 1);
        $expected  = array(
            'idcustomdimension' => '1',
            'idsite' => '1',
            'name' => 'Test0',
            'index' => '1',
            'scope' => 'action',
            'active' => true,
            'extractions' => array(),
            'case_sensitive' => true,
        );

        $this->assertSame($expected, $dimension);
    }

    public function testGetCustomDimensionsForSiteShouldFindEntriesHavingThisSite()
    {
        $this->createManyCustomDimensionCases();

        $dimensions = $this->config->getCustomDimensionsForSite($idSite = 1);

        $this->assertCount(5, $dimensions);

        foreach ($dimensions as $dimension) {
            $this->assertSame('1', $dimension['idsite']);
            $this->assertTrue(is_bool($dimension['active']));
        }
        $dimensions = $this->config->getCustomDimensionsForSite($idSite = 2);

        $this->assertCount(3, $dimensions);

        foreach ($dimensions as $dimension) {
            $this->assertSame('2', $dimension['idsite']);
        }

        // nothing matches
        $dimensions = $this->config->getCustomDimensionsForSite($idSite = 99);
        $this->assertSame(array(), $dimensions);
    }

    public function testGetCustomDimensionsHavingIndexShouldFindEntriesHavingIndexAndSite()
    {
        $this->createManyCustomDimensionCases();

        $dimensions = $this->config->getCustomDimensionsHavingIndex($scope = 'visit', $index = 1);

        $this->assertCount(2, $dimensions);

        foreach ($dimensions as $dimension) {
            $this->assertSame('visit', $dimension['scope']);
            $this->assertSame('1', $dimension['index']);
            $this->assertTrue(is_bool($dimension['active']));
        }

        $dimensions = $this->config->getCustomDimensionsHavingIndex($scope = 'action', $index = 2);

        $this->assertCount(1, $dimensions);

        foreach ($dimensions as $dimension) {
            $this->assertSame('action', $dimension['scope']);
            $this->assertSame('2', $dimension['index']);
            $this->assertTrue(is_bool($dimension['active']));
        }

        // nothing matches
        $dimensions = $this->config->getCustomDimensionsHavingIndex($scope = 'visit', $index = 99);
        $this->assertSame(array(), $dimensions);
    }

    public function testGdeleteConfigurationsForSiteShouldOnlyDeleteConfigsForThisSite()
    {
        $this->createManyCustomDimensionCases();
        $idSite = 1;

        $dimensions = $this->config->getCustomDimensionsForSite($idSite);
        $this->assertCount(5, $dimensions);

        $this->config->deleteConfigurationsForSite($idSite);

        // verify deleted
        $dimensions = $this->config->getCustomDimensionsForSite($idSite);
        $this->assertSame(array(), $dimensions);

        // verify entries for other site still exist
        $dimensions = $this->config->getCustomDimensionsForSite($idSite = 2);
        $this->assertCount(3, $dimensions);
    }

    public function testGetCustomDimensionsHavingIndexShouldOnlyDeleteConfigsForThisSite()
    {
        $this->createManyCustomDimensionCases();
        $index = 1;

        $dimensions = $this->config->getCustomDimensionsHavingIndex('visit', $index);
        $this->assertCount(2, $dimensions);
        $dimensions = $this->config->getCustomDimensionsHavingIndex('action', $index);
        $this->assertCount(2, $dimensions);

        $this->config->deleteConfigurationsForIndex($index, 'visit');

        // verify deleted
        $dimensions = $this->config->getCustomDimensionsHavingIndex('visit', $index);
        $this->assertSame(array(), $dimensions);

        $dimensions = $this->config->getCustomDimensionsHavingIndex('action', $index);
        $this->assertCount(2, $dimensions);

        $this->config->deleteConfigurationsForIndex($index, 'action');

        // verify now also deleted
        $dimensions = $this->config->getCustomDimensionsHavingIndex('action', $index);
        $this->assertSame(array(), $dimensions);

        // verify entries for other site still exist
        $dimensions = $this->config->getCustomDimensionsHavingIndex('visit', $index = 2);
        $this->assertCount(2, $dimensions);
    }

    private function createManyCustomDimensionCases()
    {
        return self::createManyCustomDimensionCasesFor($this->config);
    }

    public static function createManyCustomDimensionCasesFor(Configuration $config)
    {
        $cases = array(
            array('idSite' => 1, 'scope' => 'action', 'index' => 1, 'expectedId' => 1, 'case_sensitive' => true, 'active' => true),
            array('idSite' => 1, 'scope' => 'visit',  'index' => 1, 'expectedId' => 2, 'case_sensitive' => false, 'active' => false),
            array('idSite' => 1, 'scope' => 'visit',  'index' => 2, 'expectedId' => 3, 'case_sensitive' => true, 'active' => false),
            array('idSite' => 2, 'scope' => 'action', 'index' => 1, 'expectedId' => 1, 'case_sensitive' => false, 'active' => false),
            array('idSite' => 1, 'scope' => 'action', 'index' => 2, 'expectedId' => 4, 'case_sensitive' => false, 'active' => true),
            array('idSite' => 1, 'scope' => 'visit',  'index' => 3, 'expectedId' => 5, 'case_sensitive' => false, 'active' => false),
            array('idSite' => 2, 'scope' => 'visit',  'index' => 1, 'expectedId' => 2, 'case_sensitive' => true, 'active' => true),
            array('idSite' => 2, 'scope' => 'visit',  'index' => 2, 'expectedId' => 3, 'case_sensitive' => true, 'active' => false),
        );

        foreach ($cases as $index => $case) {
            $idDimension = $config->configureNewDimension($case['idSite'], $name = 'Test' . $index, $case['scope'], $case['index'], $case['active'], $extractions = array(), $case['case_sensitive']);
            self::assertSame($case['expectedId'], $idDimension);
        }

        return $cases;
    }
}
