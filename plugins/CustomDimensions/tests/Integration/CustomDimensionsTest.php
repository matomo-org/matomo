<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Integration;

use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Plugins\CustomDimensions\VisitorDetails;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;

/**
 * @group CustomDimensions
 * @group CustomDimensionsTest
 * @group Plugins
 */
class CustomDimensionsTest extends IntegrationTestCase
{
    /**
     * @var CustomDimensions
     */
    private $plugin;

    public function setUp(): void
    {
        parent::setUp();

        foreach (array(1, 2) as $idSite) {
            if (!Fixture::siteCreated($idSite)) {
                Fixture::createWebsite('2012-01-01 00:00:00');
            }
        }

        $this->plugin = new CustomDimensions();
    }

    public function testInstallShouldCreate5IndexesPerScopeAndCreateConfigurationTable()
    {
        foreach (CustomDimensions::getScopes() as $scope) {
            $logTable = new LogTable($scope);
            $this->assertSame(range(1, 5), $logTable->getInstalledIndexes());
        }

        // should succeed as table configured
        $this->configureSomeDimensions();
    }

    public function testInstallMultipleTimesShouldNotChangeAnythingAndNotFail()
    {
        $this->plugin->install();
        $this->plugin->install();
        $this->plugin->install();
        $this->plugin->install();

        foreach (CustomDimensions::getScopes() as $scope) {
            $logTable = new LogTable($scope);
            $this->assertSame(range(1, 5), $logTable->getInstalledIndexes());
        }

        // should succeed as table configured
        $this->configureSomeDimensions();
    }

    public function testUninstallShouldRemoveAllColumnsFromLogTablesAndUninstallConfigTable()
    {
        $this->plugin->uninstall();
        foreach (CustomDimensions::getScopes() as $scope) {
            $logTable = new LogTable($scope);
            $this->assertSame(array(), $logTable->getInstalledIndexes());
        }

        try {
            $this->configureSomeDimensions();
        } catch (\Exception $e) {
            // should fail as table was removed
        }

        $this->plugin->install();
    }

    public function testAddVisitFieldsToPersist()
    {
        $fields = array('existingField');

        $this->plugin->addVisitFieldsToPersist($fields);

        $expected = array(
            'existingField',
            'last_idlink_va',
            'custom_dimension_1',
            'custom_dimension_2',
            'custom_dimension_3',
            'custom_dimension_4',
            'custom_dimension_5'
        );
        $this->assertSame($expected, $fields);
    }

    public function testAddConversionInformation()
    {
        $this->configureSomeDimensions();

        $conversion = array();

        $request = new Request(array('idsite' => 1));
        $visit = array(
            'existingField' => 'any existing',
            'custom_dimension_' => 'value',
            'custom_dimension_1' => 'value 1',
            'custom_dimension_2' => 'value 2', // should be ignored as inactive
            'custom_dimension_3' => 'value 3', // should be ignored as does not exist
            'custom_dimension_4' => 'value 4',
        );
        $this->plugin->addConversionInformation($conversion, $visit, $request);

        $this->assertSame(array(
            'custom_dimension_1' => 'value 1',
            'custom_dimension_4' => 'value 4',
        ), $conversion);
    }

    public function testAddConversionInformationShouldIgnoreAnIndexIfTheIndexIsMissingInConversionTable()
    {
        $this->configureSomeDimensions();

        $logTable = new LogTable(CustomDimensions::SCOPE_CONVERSION);
        $logTable->removeCustomDimension(1);
        Cache::deleteTrackerCache();

        $conversion = array();

        $request = new Request(array('idsite' => 1));
        $visit = array(
            'custom_dimension_1' => 'value 1',
            'custom_dimension_4' => 'value 4',
        );
        $this->plugin->addConversionInformation($conversion, $visit, $request);

        $this->assertSame(array(
            'custom_dimension_4' => 'value 4',
        ), $conversion);
    }

    public function testGetCachedInstalledIndexesForScopeShouldIgnoreAnIndexIfTheIndexIsMissingInConversionTable()
    {
        $indexes = $this->plugin->getCachedInstalledIndexesForScope(CustomDimensions::SCOPE_CONVERSION);
        $this->assertSame(range(2, 5), $indexes);

        $indexes = $this->plugin->getCachedInstalledIndexesForScope(CustomDimensions::SCOPE_VISIT);
        $this->assertSame(range(1, 5), $indexes);

        $this->plugin->uninstall();
        $this->plugin->install();

        $indexes = $this->plugin->getCachedInstalledIndexesForScope(CustomDimensions::SCOPE_CONVERSION);
        $this->assertSame(range(1, 5), $indexes);
    }

    public function testShouldCacheInstalledIndexes()
    {
        Cache::clearCacheGeneral();
        $cache = Cache::getCacheGeneral();

        $test = array(
            CustomDimensions::SCOPE_VISIT => range(1, 5),
            CustomDimensions::SCOPE_ACTION => range(1, 5),
            CustomDimensions::SCOPE_CONVERSION => range(2, 5),
        );

        foreach (CustomDimensions::getScopes() as $scope) {
            $key = 'custom_dimension_indexes_installed_' . $scope;
            $this->assertArrayHasKey($key, $cache);
            $this->assertSame(range(1, 5), $cache[$key]);
        }
    }

    public function testShouldCacheDimensinsViaWebsiteAttributesButOnlyActiveOnes()
    {
        $this->configureSomeDimensions();
        $cache = Cache::getCacheWebsiteAttributes($idSite = 1);
        $this->assertCount(4, $cache['custom_dimensions']);

        foreach ($cache['custom_dimensions'] as $dimension) {
            $this->assertTrue($dimension['active']);
        }

        $cache = Cache::getCacheWebsiteAttributes($idSite = 2);
        $this->assertCount(1, $cache['custom_dimensions']);

        foreach ($cache['custom_dimensions'] as $dimension) {
            $this->assertTrue($dimension['active']);
        }
    }

    public function testExtendVisitorDetails()
    {
        $this->configureSomeDimensions();

        $visitor = array('idSite' => 1);
        $details = array(
            'custom_dimension_1' => 'my value 1',
            'custom_dimension_2' => 'my value 2', // should be ignored as not active
            'custom_dimension_3' => 'my value 3', // should be ignored as does not exist in visit scope
            'custom_dimension_4' => 'my value 4',
            'custom_dimension_5' => 'my value 5', // index is not used
            'custom_dimension_6' => 'my value 6', // index does not exist
        );

        $visitorDetails = new VisitorDetails();
        $visitorDetails->setDetails($details);
        $visitorDetails->extendVisitorDetails($visitor);

        $expected = array(
            'idSite' => 1,
            'dimension1' => 'my value 1',
            'dimension6' => 'my value 4'
        );
        $this->assertSame($expected, $visitor);
    }

    public function testDeleteCustomDimensionDefinitionsForSiteShouldRemoveConfigurationsForOneSiteWhenSiteIsDeleted()
    {
        $this->configureSomeDimensions();
        $config = new Configuration();
        $this->assertNotEmpty($config->getCustomDimensionsForSite($idSite = 1));

        \Piwik\API\Request::processRequest('SitesManager.deleteSite', array('idSite' => $idSite));

        // verify removed
        $this->assertSame(array(), $config->getCustomDimensionsForSite($idSite));

        // verify entries for other site still exists
        $this->assertNotEmpty($config->getCustomDimensionsForSite($idSite = 2));
    }

    /**
     * @dataProvider getScopesSupportExtractions
     */
    public function testDoesScopeSupportExtractions($expectedSupportsExtractions, $scope)
    {
        $this->assertSame($expectedSupportsExtractions, CustomDimensions::doesScopeSupportExtractions($scope));
    }

    public function getScopesSupportExtractions()
    {
        return array(
            array($support = true, CustomDimensions::SCOPE_ACTION),
            array($support = false, CustomDimensions::SCOPE_VISIT),
            array($support = false, CustomDimensions::SCOPE_CONVERSION),
            array($support = false, 'anyRanDOm'),
        );
    }

    private function configureSomeDimensions()
    {
        $configuration = new Configuration();
        $configuration->configureNewDimension($idSite = 1, 'MyName1', CustomDimensions::SCOPE_VISIT, 1, true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName2', CustomDimensions::SCOPE_VISIT, 2, false, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 2, 'MyName1', CustomDimensions::SCOPE_VISIT, 1, true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName3', CustomDimensions::SCOPE_ACTION, 1, true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName4', CustomDimensions::SCOPE_ACTION, 2, $active = false, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName5', CustomDimensions::SCOPE_ACTION, 3, $active = true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName6', CustomDimensions::SCOPE_VISIT, 4, $active = true, $extractions = array(), $caseSensitive = true);

        Cache::deleteCacheWebsiteAttributes(1);
        Cache::deleteCacheWebsiteAttributes(2);
    }
}
