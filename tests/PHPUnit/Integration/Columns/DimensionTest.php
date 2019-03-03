<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Columns;

    // there is a test that requires the class to be defined in a plugin

use Piwik\Columns\Dimension;
use Piwik\Plugin\Segment;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translate;

class CustomDimensionTest extends Dimension
{
    protected $columnName  = 'test_dimension';
    protected $columnType  = 'INTEGER (10) DEFAULT 0';
    protected $dbTableName  = 'log_visit';

    public function getId()
    {
        return $this->generateIdFromClass('Piwik\Plugins\Test\Columns\DimensionTest');
    }

    public function hasImplementedEvent($method)
    {
        $method = new \ReflectionMethod($this, $method);
        $declaringClass = $method->getDeclaringClass();

        return 0 === strpos($declaringClass->name, 'Piwik\Tests');
    }

    public function set($param, $value)
    {
        $this->$param = $value;
    }

    public function setColumnType($columnType)
    {
        $this->columnType = $columnType;
    }

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('exitPageUrl');
        $segment->setName('Actions_ColumnExitPageURL');
        $segment->setCategory('General_Visit');
        $this->addSegment($segment);

        // custom type and sqlSegment
        $segment = new Segment();
        $segment->setSegment('exitPageUrl');
        $segment->setSqlSegment('customValue');
        $segment->setType(Segment::TYPE_METRIC);
        $segment->setName('Actions_ColumnExitPageURL');
        $segment->setCategory('General_Visit');
        $this->addSegment($segment);
    }
}


/**
 * @group Core
 */
class ColumnDimensionTest extends IntegrationTestCase
{
    /**
     * @var CustomDimensionTest
     */
    private $dimension;

    public function setUp()
    {
        parent::setUp();

        Translate::loadEnglishTranslation();

        Fixture::createWebsite('2014-04-05 01:02:03');

        Manager::getInstance()->unloadPlugins();
        Manager::getInstance()->doNotLoadAlwaysActivatedPlugins();

        $this->dimension = new CustomDimensionTest();
    }

    public function tearDown()
    {
        Translate::unloadEnglishTranslation();
        parent::tearDown();
    }

    public function test_hasImplementedEvent_shouldDetectWhetherAMethodWasOverwrittenInTheActualPluginClass()
    {
        $this->assertTrue($this->dimension->hasImplementedEvent('set'));
        $this->assertTrue($this->dimension->hasImplementedEvent('configureSegments'));

        $this->assertFalse($this->dimension->hasImplementedEvent('getSegments'));
    }

    public function test_getColumnName_shouldReturnTheNameOfTheColumn()
    {
        $this->assertSame('test_dimension', $this->dimension->getColumnName());
    }

    public function test_hasColumnType_shouldDetectWhetherAColumnTypeIsSet()
    {
        $this->assertTrue($this->dimension->hasColumnType());

        $this->dimension->set('columnType', '');
        $this->assertFalse($this->dimension->hasColumnType());
    }

    public function test_getName_ShouldNotReturnANameByDefault()
    {
        $this->assertSame('', $this->dimension->getName());
    }

    public function test_getAllDimensions_shouldReturnAllKindOfDimensions()
    {
        Manager::getInstance()->loadPlugins(array('Actions', 'Events', 'DevicesDetector', 'Goals', 'CustomVariables'));

        $dimensions = Dimension::getAllDimensions();

        $this->assertGreaterThan(20, count($dimensions));

        $foundConversion = false;
        $foundVisit      = false;
        $foundAction     = false;
        $foundNormal     = false;

        foreach ($dimensions as $dimension) {
            if ($dimension instanceof ConversionDimension) {
                $foundConversion = true;
            } else if ($dimension instanceof ActionDimension) {
                $foundAction = true;
            } else if ($dimension instanceof VisitDimension) {
                $foundVisit = true;
            } else if ($dimension instanceof Dimension) {
                $foundNormal = true;
            } else {
                $this->fail('Unexpected dimension class found');
            }

            if (get_class($dimension) === 'Piwik\Plugins\CustomVariables\CustomDimension') {
                continue;
            }

            $this->assertRegExp('/Piwik.Plugins.(Actions|Events|DevicesDetector|Goals|CustomVariables).Columns/', get_class($dimension));
        }

        $this->assertTrue($foundConversion);
        $this->assertTrue($foundAction);
        $this->assertTrue($foundVisit);
        $this->assertTrue($foundNormal);
    }

    public function test_getDimensions_shouldReturnAllKindOfDimensionsThatBelongToASpecificPlugin()
    {
        Manager::getInstance()->loadPlugins(array('Actions', 'Events', 'DevicesDetector', 'Goals'));

        $dimensions = Dimension::getDimensions(Manager::getInstance()->loadPlugin('Actions'));

        $this->assertGreaterThan(10, count($dimensions));

        $foundVisit      = false;
        $foundAction     = false;

        foreach ($dimensions as $dimension) {
            if ($dimension instanceof ActionDimension) {
                $foundAction = true;
            } else if ($dimension instanceof VisitDimension) {
                $foundVisit = true;
            }

            $this->assertRegExp('/Piwik.Plugins.Actions.Columns/', get_class($dimension));
        }

        $this->assertTrue($foundAction);
        $this->assertTrue($foundVisit);
    }

    public function test_getDimensions_shouldReturnConversionDimensionsThatBelongToASpecificPlugin()
    {
        Manager::getInstance()->loadPlugins(array('Actions', 'Events', 'DevicesDetector', 'Goals'));

        $dimensions = Dimension::getDimensions(Manager::getInstance()->loadPlugin('Goals'));

        $this->assertGreaterThan(2, count($dimensions));

        $foundConversion = false;

        foreach ($dimensions as $dimension) {
            if ($dimension instanceof ConversionDimension) {
                $foundConversion = true;
            }

            $this->assertRegExp('/Piwik.Plugins.Goals.Columns/', get_class($dimension));
        }

        $this->assertTrue($foundConversion);
    }

    public function test_getSegment_ShouldReturnConfiguredSegments()
    {
        $segments = $this->dimension->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf('\Piwik\Plugin\Segment', $segments[0]);
        $this->assertInstanceOf('\Piwik\Plugin\Segment', $segments[1]);
    }

    /**
     * @param $expectedType
     * @param $columnType
     * @dataProvider getTypeProvider
     */
    public function test_getType_shouldGuessTypeBasedOnColumnType($expectedType, $columnType)
    {
        $this->dimension->setColumnType($columnType);
        $this->assertSame($expectedType, $this->dimension->getType());
    }

    public function getTypeProvider()
    {
        return array(
            array($expected = Dimension::TYPE_NUMBER, $columnType = 'INTEGER (10) DEFAULT 0'),
            array($expected = Dimension::TYPE_NUMBER, $columnType = 'INTEGER(10) DEFAULT 0'),
            array($expected = Dimension::TYPE_NUMBER, $columnType = 'INT(10) DEFAULT 0'),
            array($expected = Dimension::TYPE_NUMBER, $columnType = 'int(10) DEFAULT 0'),
            array($expected = Dimension::TYPE_NUMBER, $columnType = 'SMALLINT(10) DEFAULT 0'),
            array($expected = Dimension::TYPE_FLOAT, $columnType = 'FLOAT (10) DEFAULT 0'),
            array($expected = Dimension::TYPE_FLOAT, $columnType = 'DECIMAL(10) DEFAULT 0'),
            array($expected = Dimension::TYPE_BINARY, $columnType = 'BINARY(8)'),
            array($expected = Dimension::TYPE_TIMESTAMP, $columnType = 'timestamp null'),
            array($expected = Dimension::TYPE_TIMESTAMP, $columnType = 'timeStAmp null'),
            array($expected = Dimension::TYPE_DATETIME, $columnType = 'DATETIME NOT NULL'),
            array($expected = Dimension::TYPE_DATE, $columnType = 'DATE NOT NULL'),
            array($expected = Dimension::TYPE_TEXT, $columnType = ''),
        );
    }

    public function test_addSegment_ShouldPrefilSomeSegmentValuesIfNotDefinedYetAndGuessTypeMetric()
    {
        $segments = $this->dimension->getSegments();

        $this->assertEquals(Segment::TYPE_METRIC, $segments[0]->getType());
    }

    public function test_addSegment_ShouldPrefilSomeSegmentValuesIfNotDefinedYetAndGuessTypeDimension()
    {
        $this->dimension->setColumnType('TEXT NOT NULL');
        $segments = $this->dimension->getSegments();

        $this->assertEquals(Segment::TYPE_DIMENSION, $segments[0]->getType());
    }

    public function test_addSegment_ShouldNotOverwritePreAssignedValues()
    {
        $segments = $this->dimension->getSegments();

        $this->assertEquals(Segment::TYPE_METRIC, $segments[1]->getType());
    }

    public function test_getId_ShouldCorrectlyGenerateIdFromDimensionsQualifiedClassName()
    {
        $this->assertEquals("Test.DimensionTest", $this->dimension->getId());
    }

    public function test_factory_ShouldCreateDimensionFromDimensionId()
    {
        Manager::getInstance()->loadPlugins(array('ExampleTracker'));

        $dimension = Dimension::factory("ExampleTracker.ExampleDimension");
        $this->assertInstanceOf('Piwik\Plugins\ExampleTracker\Columns\ExampleDimension', $dimension);
    }


    /**
     * @dataProvider getFormatValueProvider
     */
    public function test_formatValue($type, $value, $expected)
    {
        $formatter = new Formatter();
        $this->dimension->setType($type);
        $formatted = $this->dimension->formatValue($value, $idSite = 1, $formatter);

        $this->assertEquals($expected, $formatted);
    }

    public function getFormatValueProvider()
    {
        return array(
            array($type = Dimension::TYPE_NUMBER, $value = 5.354, $expected = 5),
            array($type = Dimension::TYPE_FLOAT, $value = 5.354, $expected = 5.35),
            array($type = Dimension::TYPE_MONEY, $value = 5.392, $expected = '$ 5.39'),
            array($type = Dimension::TYPE_PERCENT, $value = 0.343, $expected = '34.3%'),
            array($type = Dimension::TYPE_DURATION_S, $value = 121, $expected = '00:02:01'),
            array($type = Dimension::TYPE_DURATION_MS, $value = 0.392, $expected = '0.39s'),
            array($type = Dimension::TYPE_BYTE, $value = 3912, $expected = '3.8 K'),
            array($type = Dimension::TYPE_BOOL, $value = 0, $expected = 'No'),
            array($type = Dimension::TYPE_BOOL, $value = 1, $expected = 'Yes'),
        );
    }
}
