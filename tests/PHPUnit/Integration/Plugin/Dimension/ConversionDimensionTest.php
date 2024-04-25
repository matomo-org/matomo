<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// there is a test that requires the class to be defined in a plugin
namespace Piwik\Plugins\Test;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Segment;
use Piwik\Plugin\Manager;
use Piwik\Segment\SegmentsList;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class FakeConversionDimension extends ConversionDimension
{
    protected $columnName  = 'fake_conversion_dimension_column';
    protected $columnType  = 'VARCHAR (255) DEFAULT 0';

    public function set($param, $value)
    {
        $this->$param = $value;
    }

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $segment = new Segment();
        $segment->setSegment('exitPageUrl');
        $segment->setName('Actions_ColumnExitPageURL');
        $segment->setCategory('General_Visit');
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));

        // custom type and sqlSegment
        $segment = new Segment();
        $segment->setSegment('exitPageUrl');
        $segment->setSqlSegment('customValue');
        $segment->setType(Segment::TYPE_METRIC);
        $segment->setName('Actions_ColumnExitPageURL');
        $segment->setCategory('General_Visit');
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }
}

/**
 * @group Core
 */
class ConversionDimensionTest extends IntegrationTestCase
{
    /**
     * @var FakeConversionDimension
     */
    private $dimension;

    public function setUp(): void
    {
        parent::setUp();

        Manager::getInstance()->unloadPlugins();
        Manager::getInstance()->doNotLoadAlwaysActivatedPlugins();

        $this->dimension = new FakeConversionDimension();
    }

    public function testInstallShouldNotReturnAnythingIfColumnTypeNotSpecified()
    {
        $this->dimension->set('columnType', '');
        $this->assertEquals(array(), $this->dimension->install());
    }

    public function testInstallShouldNotReturnAnythingIfColumnNameNotSpecified()
    {
        $this->dimension->set('columnName', '');
        $this->assertEquals(array(), $this->dimension->install());
    }

    public function testInstallShouldAlwaysInstallLogActionIfColumnNameAndTypeGiven()
    {
        $expected = array(
            'log_conversion' => array(
                "ADD COLUMN `fake_conversion_dimension_column` VARCHAR (255) DEFAULT 0"
            )
        );

        $this->assertEquals($expected, $this->dimension->install());
    }

    public function testUpdateShouldAlwaysUpdateLogVisitIfColumnNameAndTypeGiven()
    {
        $expected = array(
            'log_conversion' => array(
                "MODIFY COLUMN `fake_conversion_dimension_column` VARCHAR (255) DEFAULT 0"
            )
        );

        $this->assertEquals($expected, $this->dimension->update(array()));
    }

    public function testGetVersionShouldUseColumnTypeAsVersion()
    {
        $this->assertEquals('VARCHAR (255) DEFAULT 0', $this->dimension->getVersion());
    }

    public function testGetSegmentShouldReturnConfiguredSegments()
    {
        $list = new SegmentsList();
        $this->dimension->configureSegments($list, new DimensionSegmentFactory($this->dimension));

        $segments = $list->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf('\Piwik\Plugin\Segment', $segments[0]);
        $this->assertInstanceOf('\Piwik\Plugin\Segment', $segments[1]);
    }

    public function testAddSegmentShouldPrefilSomeSegmentValuesIfNotDefinedYet()
    {
        $list = new SegmentsList();
        $this->dimension->configureSegments($list, new DimensionSegmentFactory($this->dimension));

        $segments = $list->getSegments();

        $this->assertEquals('log_conversion.fake_conversion_dimension_column', $segments[0]->getSqlSegment());
        $this->assertEquals(Segment::TYPE_DIMENSION, $segments[0]->getType());
    }

    public function testAddSegmentShouldNotOverwritePreAssignedValues()
    {
        $list = new SegmentsList();
        $this->dimension->configureSegments($list, new DimensionSegmentFactory($this->dimension));

        $segments = $list->getSegments();

        $this->assertEquals('customValue', $segments[1]->getSqlSegment());
        $this->assertEquals(Segment::TYPE_METRIC, $segments[1]->getType());
    }

    public function testGetDimensionsShouldOnlyLoadAllConversionDimensionsFromACertainPlugin()
    {
        Manager::getInstance()->loadPlugins(array('ExampleTracker'));
        $plugin = Manager::getInstance()->loadPlugin('ExampleTracker');

        $dimensions = ConversionDimension::getDimensions($plugin);

        $this->assertGreaterThanOrEqual(1, count($dimensions));

        foreach ($dimensions as $dimension) {
            $this->assertInstanceOf('\Piwik\Plugin\Dimension\ConversionDimension', $dimension);
            $this->assertStringStartsWith('Piwik\Plugins\ExampleTracker\Columns', get_class($dimension));
        }
    }

    public function testGetAllDimensionsShouldLoadAllDimensionsButOnlyIfLoadedPlugins()
    {
        Manager::getInstance()->loadPlugins(array('Goals', 'Ecommerce', 'ExampleTracker'));

        $dimensions = ConversionDimension::getAllDimensions();

        $this->assertGreaterThan(5, count($dimensions));

        foreach ($dimensions as $dimension) {
            $this->assertInstanceOf('\Piwik\Plugin\Dimension\ConversionDimension', $dimension);
            $this->assertRegExp('/Piwik.Plugins.(ExampleTracker|Ecommerce|Goals).Columns/', get_class($dimension));
        }
    }
}
