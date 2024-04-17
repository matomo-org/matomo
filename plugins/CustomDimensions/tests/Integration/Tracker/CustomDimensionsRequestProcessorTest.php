<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Integration\Dao;

use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Plugins\CustomDimensions\Tracker\CustomDimensionsRequestProcessor as Processor;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\ActionPageview;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * @group CustomDimensions
 * @group CustomDimensionsRequestProcessorTest
 * @group CustomDimensionsRequestProcessor
 * @group Dao
 * @group Plugins
 */
class CustomDimensionsRequestProcessorTest extends IntegrationTestCase
{
    /**
     * @var Processor
     */
    private $processor;

    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }
        if (!Fixture::siteCreated(2)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        Cache::clearCacheGeneral();
        Cache::deleteCacheWebsiteAttributes($idSite = 1);
        Cache::deleteCacheWebsiteAttributes($idSite = 2);

        $this->processor = new Processor();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_buildCustomDimensionTrackingApiName()
    {
        $this->assertNull(Processor::buildCustomDimensionTrackingApiName(''));
        $this->assertNull(Processor::buildCustomDimensionTrackingApiName('0'));
        $this->assertNull(Processor::buildCustomDimensionTrackingApiName(null));
        $this->assertNull(Processor::buildCustomDimensionTrackingApiName(array()));
        $this->assertNull(Processor::buildCustomDimensionTrackingApiName(array('idcustomdimension' => '')));

        $this->assertSame('dimension1', Processor::buildCustomDimensionTrackingApiName(1));
        $this->assertSame('dimension3', Processor::buildCustomDimensionTrackingApiName('3'));
        $this->assertSame('dimension4', Processor::buildCustomDimensionTrackingApiName(array('idcustomdimension' => '4')));
    }

    public function test_getCachedCustomDimensionIndexes()
    {
        $logTable = new LogTable(CustomDimensions::SCOPE_ACTION);
        $logTable->removeCustomDimension(1);

        $indexes = Processor::getCachedCustomDimensionIndexes(CustomDimensions::SCOPE_VISIT);
        $this->assertSame(range(1, 5), $indexes);

        $indexes = Processor::getCachedCustomDimensionIndexes(CustomDimensions::SCOPE_ACTION);
        $this->assertSame(range(2, 5), $indexes);
    }

    public function test_getCachedCustomDimensions_shouldReturnDimensionsForSiteButOnlyActiveOnes()
    {
        $this->configureSomeDimensions();

        $request = new Request(array('idsite' => 1));
        $dimensions = Processor::getCachedCustomDimensions($request);

        $this->assertCount(5, $dimensions);

        foreach ($dimensions as $dimension) {
            $this->assertSame('1', $dimension['idsite']);
            $this->assertTrue($dimension['active']);
            $this->assertStringStartsWith('MyName', $dimension['name']);
        }

        $request = new Request(array('idsite' => 2));
        $dimensions = Processor::getCachedCustomDimensions($request);
        $this->assertCount(1, $dimensions);
    }

    public function test_hasActionCustomDimensionConfiguredInSite_whenHasActionDimensionConfigured()
    {
        $this->configureSomeDimensions();

        $request = new Request(array('idsite' => 1));
        $this->assertTrue(Processor::hasActionCustomDimensionConfiguredInSite($request));
    }

    public function test_hasActionCustomDimensionConfiguredInSite_whenHasOnlyVisitDimensions()
    {
        $request = new Request(array('idsite' => 2));
        $this->assertFalse(Processor::hasActionCustomDimensionConfiguredInSite($request));
    }

    public function test_hasActionCustomDimensionConfiguredInSite_WhenNoDimensionsAreConfgigured()
    {
        $request = new Request(array('idsite' => 1));
        $this->assertFalse(Processor::hasActionCustomDimensionConfiguredInSite($request));
    }

    public function test_onExistingVisit_ShouldOnlyAddColumnsOfCustomDimensionsInScopeVisit()
    {
        $this->configureSomeDimensions();

        $valuesToUpdate = array();
        $visitProperties = new VisitProperties();

        $request = new Request(array(
            'idsite' => 1,
            'something' => 5,
            Processor::buildCustomDimensionTrackingApiName(2) => '2 value',
            Processor::buildCustomDimensionTrackingApiName(6) => '6 value',
            'dimension_' => 'not an actual dimension',
            'dimension99' => 'not an actual dimension2',
            Processor::buildCustomDimensionTrackingApiName(3) => '3 value', // should be ignored as it is an action dimension
            Processor::buildCustomDimensionTrackingApiName(9) => '9 value', // should be ignored as 9 dimensions are not installed
        ));

        $this->processor->onExistingVisit($valuesToUpdate, $visitProperties, $request);

        $expected = array(
            'custom_dimension_2' => '2 value',
            'custom_dimension_4' => '6 value',
        );
        $this->assertSame($expected, $valuesToUpdate);
    }

    public function test_onNewVisit_afterRequestProcessed_ShouldSaveManuallySetDimensionValues_ForActivatedDimensions()
    {
        $this->configureSomeDimensions();

        $visitProperties = new VisitProperties();
        $request = new Request(array(
            'idsite' => 1,
            'dimension1' => 'value1',
            'dimension3' => 'value3',
            'dimension4' => 'value4',
            'dimension5' => 'value5',
            'dimension6' => 'value6',
            'dimension7' => 'value7', // this one should be ignored as it is not configured to be used
        ));

        $action = new ActionPageview($request);
        $request->setMetadata('Actions', 'action', $action);

        // should only add visit scope dimensions
        $this->processor->onNewVisit($visitProperties, $request);

        $expected1 = array(
            'custom_dimension_1' => 'value1',
            // there should be no value for dimension2 as nothing was set
            // there should be no value for dimension 3, 4 and 5 as they are in scope action
            'custom_dimension_4' => 'value6'
        );
        $this->assertSame($expected1, $visitProperties->getProperties());
        $this->assertSame(array(), $action->getCustomFields());

        $this->processor->afterRequestProcessed($visitProperties, $request);

        $expected2 = array(
            // the value for dimension4 should be ignored as it is not active
            'custom_dimension_1' => 'value3', // dimension 3 maps to index 1
            'custom_dimension_3' => 'value5', // dimension 4 maps to index 3 in db
        );
        $this->assertSame($expected2, $action->getCustomFields());

        // should not have changed visit properties
        $this->assertSame($expected1, $visitProperties->getProperties());
    }

    public function test_afterRequestProcessed_ShouldSaveManuallySetDimensionValues_ForActivatedDimensions()
    {
        $this->configureSomeDimensions();

        $visitProperties = new VisitProperties();
        $request = new Request(array(
            'idsite' => 1,
            'dimension1' => 'value1',
            'dimension3' => 'value3',
            'dimension4' => 'value4',
            'dimension5' => 'value5',
            'dimension6' => 'value6',
            'dimension7' => 'value7', // this one should be ignored as it is not configured to be used
        ));

        $action = new ActionPageview($request);
        $request->setMetadata('Actions', 'action', $action);

        $this->processor->onNewVisit($visitProperties, $request);

        $expected1 = array(
            'custom_dimension_1' => 'value1',
            // there should be no value for dimension2 as nothing was set
            // there should be no value for dimension 3, 4 and 5 as they are in scope action
            'custom_dimension_4' => 'value6'
        );
        $this->assertSame($expected1, $visitProperties->getProperties());
        $this->assertSame(array(), $action->getCustomFields()); // should not set any action dimensions

        $this->processor->afterRequestProcessed($visitProperties, $request);

        $expected2 = array(
            // the value for dimension4 should be ignored as it is not active
            'custom_dimension_1' => 'value3', // dimension 3 maps to index 1
            'custom_dimension_3' => 'value5', // dimension 4 maps to index 3 in db
        );
        $this->assertSame($expected2, $action->getCustomFields());
        $this->assertSame($expected1, $visitProperties->getProperties()); // should not have added visit dimensions
    }

    public function test_onNewVisit_afterRequestProcessed_NoDimensionsConfigured_ShouldSaveNothing()
    {
        $visitProperties = new VisitProperties();
        $request = new Request(array(
            'idsite' => 1,
            'dimension1' => 'value1',
            'dimension3' => 'value3',
            'dimension4' => 'value4',
            'dimension5' => 'value5',
            'dimension6' => 'value6',
        ));

        $this->processor->onNewVisit($visitProperties, $request);
        $this->processor->afterRequestProcessed($visitProperties, $request);

        $this->assertSame(array(), $visitProperties->getProperties());
    }

    public function test_onNewVisit_afterRequestProcessed_NoActionSet_ShouldNotFailIfThereIsNoActionSet()
    {
        $this->configureSomeDimensions();

        $visitProperties = new VisitProperties();
        $request = new Request(array(
            'idsite' => 1,
            'dimension4' => 'value4',
            'dimension5' => 'value5',
            'dimension6' => 'value6',
        ));

        $this->processor->onNewVisit($visitProperties, $request);

        $expected = array(
            'custom_dimension_4' => 'value6'
        );
        $this->assertSame($expected, $visitProperties->getProperties());

        $this->processor->afterRequestProcessed($visitProperties, $request);

        $this->assertSame($expected, $visitProperties->getProperties());
    }

    public function test_afterRequestProcessed_NoActionSet_ShouldNotSaveAnEmptyValue()
    {
        $this->configureSomeDimensions();

        $visitProperties = new VisitProperties();
        $request = new Request(array(
            'idsite' => 1,
            'dimension4' => '',
            'dimension5' => '',
            'dimension6' => '',
        ));

        $this->processor->onNewVisit($visitProperties, $request);
        $this->assertSame(array('custom_dimension_4' => ''), $visitProperties->getProperties());

        $this->processor->afterRequestProcessed($visitProperties, $request);

        $this->assertSame(array('custom_dimension_4' => ''), $visitProperties->getProperties());
    }

    public function test_afterRequestProcessed_NoActionSet_ShouldBeAbleToExtractAValue()
    {
        $configuration = new Configuration();
        $extractions = array(
            array('dimension' => 'url', 'pattern' => 'www(.+).com')
        );
        $configuration->configureNewDimension($idSite = 1, 'MyName1', CustomDimensions::SCOPE_VISIT, 1, true, $extractions, $caseSensitive = true);

        $extractions = array(
            array('dimension' => 'url', 'pattern' => 'www.piwik(.+).com'), // first one doesn't match
            array('dimension' => 'url', 'pattern' => 'www.ex(.+).com'), // but second matches
            array('dimension' => 'url', 'pattern' => 'www(.+).com'), // third one matches too but should use the one that matches first
        );
        $configuration->configureNewDimension($idSite = 1, 'MyName2', CustomDimensions::SCOPE_VISIT, 2, true, $extractions, $caseSensitive = true);

        $extractions = array(
            array('dimension' => 'urlparam', 'pattern' => 'id'), // first one doesn't match
        );
        $configuration->configureNewDimension($idSite = 1, 'MyName3', CustomDimensions::SCOPE_VISIT, 3, true, $extractions, $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName4', CustomDimensions::SCOPE_ACTION, 1, true, $extractions, $caseSensitive = true);

        $request = new Request(array('idsite' => 1, 'url' => 'http://www.example.com/test?id=11&module=test'));
        $action = new ActionPageview($request);
        $request->setMetadata('Actions', 'action', $action);

        $visitProperties = new VisitProperties();

        $this->processor->onNewVisit($visitProperties, $request);

        $this->assertSame(array(
            'custom_dimension_1' => '.example',
            'custom_dimension_2' => 'ample',
            'custom_dimension_3' => '11',
        ), $visitProperties->getProperties());

        $this->processor->afterRequestProcessed($visitProperties, $request);

        $this->assertSame(array('custom_dimension_1' => '11'), $action->getCustomFields());
    }

    public function test_afterRequestProcessed_NoActionSet_ShouldBeAbleToHandleCaseSensitive()
    {
        $configuration = new Configuration();
        $extractions = array(
            array('dimension' => 'url', 'pattern' => 'wwW(.+).com')
        );
        $configuration->configureNewDimension($idSite = 1, 'MyName1', CustomDimensions::SCOPE_ACTION, 1, true, $extractions, $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName2', CustomDimensions::SCOPE_ACTION, 2, true, $extractions, $caseSensitive = false);

        $request = new Request(array('idsite' => 1, 'url' => 'http://www.exAmple.com/test?id=11&module=test'));
        $action = new ActionPageview($request);
        $request->setMetadata('Actions', 'action', $action);

        $this->processor->afterRequestProcessed(new VisitProperties(), $request);

        $this->assertSame(array(
            'custom_dimension_2' => '.exAmple',
        ), $action->getCustomFields());
    }

    public function test_valueWithWhitespace_isTrimmed()
    {
        $this->configureSomeDimensions();

        $visitProperties = new VisitProperties();
        $request = new Request(array(
            'idsite' => 1,
            'dimension1' => 'value1 ',
        ));

        $action = new ActionPageview($request);
        $request->setMetadata('Actions', 'action', $action);

        // should only add visit scope dimensions
        $this->processor->onNewVisit($visitProperties, $request);

        $expected1 = array(
            'custom_dimension_1' => 'value1',
        );
        $this->assertSame($expected1, $visitProperties->getProperties());
        $this->assertSame(array(), $action->getCustomFields());
    }

    public function test_veryLongValue_isTruncated()
    {
        $this->configureSomeDimensions();

        $veryLongStr =  '12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
        $trimmedStr =   '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';

        $visitProperties = new VisitProperties();
        $request = new Request(array(
            'idsite' => 1,
            'dimension1' => $veryLongStr,
        ));

        $action = new ActionPageview($request);
        $request->setMetadata('Actions', 'action', $action);

        // should only add visit scope dimensions
        $this->processor->onNewVisit($visitProperties, $request);

        $expected1 = array(
            'custom_dimension_1' => $trimmedStr,
        );
        $this->assertSame($expected1, $visitProperties->getProperties());
        $this->assertSame(array(), $action->getCustomFields());
    }

    public function test_valueWithWhitespace_isTrimmed_extractedFromUrl()
    {
        $configuration = new Configuration();
        $extractions = array(
            array('dimension' => 'urlparam', 'pattern' => 'id'),
        );
        $configuration->configureNewDimension($idSite = 1, 'Dimension1', CustomDimensions::SCOPE_VISIT, 1, true, $extractions, $caseSensitive = true);

        $extractions = array(
            array('dimension' => 'urlparam', 'pattern' => 'module'),
        );
        $configuration->configureNewDimension($idSite = 1, 'Dimension2', CustomDimensions::SCOPE_VISIT, 2, true, $extractions, $caseSensitive = true);

        $request = new Request(array('idsite' => 1, 'url' => 'http://www.example.com/test?id=11 &module=test%20'));
        $action = new ActionPageview($request);
        $request->setMetadata('Actions', 'action', $action);

        $visitProperties = new VisitProperties();

        $this->processor->onNewVisit($visitProperties, $request);

        $this->assertSame(array(
            'custom_dimension_1' => '11',
            'custom_dimension_2' => 'test'
        ), $visitProperties->getProperties());
    }

    public function test_veryLongValue_isTrimmed_extractedFromUrl()
    {
        $veryLongStr =  '12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
        $trimmedStr =   '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';

        $configuration = new Configuration();
        $extractions = array(
            array('dimension' => 'urlparam', 'pattern' => 'module'),
        );
        $configuration->configureNewDimension($idSite = 1, 'Dimension1', CustomDimensions::SCOPE_VISIT, 1, true, $extractions, $caseSensitive = true);

        $request = new Request(array('idsite' => 1, 'url' => 'http://www.example.com/test?id=11&module=' . $veryLongStr));
        $action = new ActionPageview($request);
        $request->setMetadata('Actions', 'action', $action);

        $visitProperties = new VisitProperties();

        $this->processor->onNewVisit($visitProperties, $request);

        $this->assertSame(array(
            'custom_dimension_1' => $trimmedStr,
        ), $visitProperties->getProperties());
    }

    private function configureSomeDimensions()
    {
        $configuration = new Configuration();
        $configuration->configureNewDimension($idSite = 1, 'MyName1', CustomDimensions::SCOPE_VISIT, 1, true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName2', CustomDimensions::SCOPE_VISIT, 2, true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 2, 'MyName1', CustomDimensions::SCOPE_VISIT, 1, true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName3', CustomDimensions::SCOPE_ACTION, 1, true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName4', CustomDimensions::SCOPE_ACTION, 2, $active = false, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName5', CustomDimensions::SCOPE_ACTION, 3, $active = true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($idSite = 1, 'MyName6', CustomDimensions::SCOPE_VISIT, 4, $active = true, $extractions = array(), $caseSensitive = true);
    }
}
