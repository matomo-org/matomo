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
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Plugin\Manager;
use Piwik\Segment\SegmentsList;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class FakeVisitDimension extends VisitDimension
{
    protected $columnName  = 'fake_visit_dimension_column';
    protected $columnType  = 'VARCHAR (255) DEFAULT 0';
    public $requiredFields = array();

    public function set($param, $value)
    {
        $this->$param = $value;
    }

    public function getRequiredVisitFields()
    {
        return $this->requiredFields;
    }
}

class FakeConversionVisitDimension extends FakeVisitDimension
{
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return false;
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
class VisitDimensionTest extends IntegrationTestCase
{
    /**
     * @var FakeVisitDimension
     */
    private $dimension;

    /**
     * @var FakeConversionVisitDimension
     */
    private $conversionDimension;

    public function setUp(): void
    {
        parent::setUp();

        Manager::getInstance()->unloadPlugins();
        Manager::getInstance()->doNotLoadAlwaysActivatedPlugins();

        $this->dimension = new FakeVisitDimension();
        $this->conversionDimension = new FakeConversionVisitDimension();
    }

    public function test_install_shouldNotReturnAnything_IfColumnTypeNotSpecified()
    {
        $this->dimension->set('columnType', '');
        $this->assertEquals(array(), $this->dimension->install());
    }

    public function test_install_shouldNotReturnAnything_IfColumnNameNotSpecified()
    {
        $this->dimension->set('columnName', '');
        $this->assertEquals(array(), $this->dimension->install());
    }

    public function test_install_shouldAlwaysInstallLogVisit_IfColumnNameAndTypeGiven()
    {
        $expected = array(
            'log_visit' => array(
                "ADD COLUMN `fake_visit_dimension_column` VARCHAR (255) DEFAULT 0"
            )
        );

        $this->assertEquals($expected, $this->dimension->install());
    }

    public function test_install_shouldInstallLogVisitAndConversion_IfConversionMethodIsImplemented()
    {
        $expected = array(
            'log_visit' => array(
                "ADD COLUMN `fake_visit_dimension_column` VARCHAR (255) DEFAULT 0"
            ),
            'log_conversion' => array(
                "ADD COLUMN `fake_visit_dimension_column` VARCHAR (255) DEFAULT 0"
            )
        );

        $this->assertEquals($expected, $this->conversionDimension->install());
    }

    public function test_update_shouldAlwaysUpdateLogVisit_IfColumnNameAndTypeGiven()
    {
        $expected = array(
            'log_visit' => array(
                "MODIFY COLUMN `fake_visit_dimension_column` VARCHAR (255) DEFAULT 0"
            )
        );

        $this->assertEquals($expected, $this->dimension->update());
    }

    public function test_update_shouldUpdateLogVisitAndAddConversion_IfConversionMethodIsImplementedButNotInstalledYet()
    {
        $expected = array(
            'log_visit' => array(
                "MODIFY COLUMN `fake_visit_dimension_column` VARCHAR (255) DEFAULT 0"
            ),
            'log_conversion' => array(
                "ADD COLUMN `fake_visit_dimension_column` VARCHAR (255) DEFAULT 0"
            )
        );

        $this->assertEquals($expected, $this->conversionDimension->update());
    }

    public function test_getVersion_shouldUseColumnTypeAsVersion()
    {
        $this->assertEquals('VARCHAR (255) DEFAULT 0', $this->dimension->getVersion());
    }

    public function test_getVersion_shouldIncludeConversionMethodIntoVersionNumber_ToMakeSureUpdateMethodWillBeTriggeredWhenPluginAddedConversionMethodInNewVersion()
    {
        $this->assertEquals('VARCHAR (255) DEFAULT 01', $this->conversionDimension->getVersion());
    }

    public function test_getSegment_ShouldReturnNoSegments_IfNoneConfigured()
    {
        $this->assertEquals(array(), $this->dimension->getSegments());
    }

    public function test_getSegment_ShouldReturnConfiguredSegments()
    {
        $segments = $this->conversionDimension->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf('\Piwik\Plugin\Segment', $segments[0]);
        $this->assertInstanceOf('\Piwik\Plugin\Segment', $segments[1]);
    }

    public function test_addSegment_ShouldPrefilSomeSegmentValuesIfNotDefinedYet()
    {
        $segments = $this->conversionDimension->getSegments();

        $this->assertEquals('log_visit.fake_visit_dimension_column', $segments[0]->getSqlSegment());
        $this->assertEquals(Segment::TYPE_DIMENSION, $segments[0]->getType());
    }

    public function test_addSegment_ShouldNotOverwritePreAssignedValues()
    {
        $segments = $this->conversionDimension->getSegments();

        $this->assertEquals('customValue', $segments[1]->getSqlSegment());
        $this->assertEquals(Segment::TYPE_METRIC, $segments[1]->getType());
    }

    public function test_sortDimensions_ShouldResolveDependencies()
    {
        $dimension1 = new FakeVisitDimension();
        $dimension1->set('columnName', 'column1');
        $dimension1->requiredFields = array('column2');

        $dimension2 = new FakeVisitDimension();
        $dimension2->set('columnName', 'column2');
        $dimension2->requiredFields = array('column3', 'column4');

        $dimension3 = new FakeVisitDimension();
        $dimension3->set('columnName', 'column3');
        $dimension3->requiredFields = array();

        $dimension4 = new FakeVisitDimension();
        $dimension4->set('columnName', 'column4');
        $dimension4->requiredFields = array('column3');

        $instances = array($dimension1, $dimension2, $dimension3, $dimension4);

        $instances = VisitDimension::sortDimensions($instances);

        $this->assertSame(array($dimension3, $dimension4, $dimension2, $dimension1), $instances);
    }

    public function test_sortDimensions_ShouldThrowAnException_IfCircularReferenceDetected()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Circular reference detected for required field column4 in dimension column2');

        $dimension1 = new FakeVisitDimension();
        $dimension1->set('columnName', 'column1');
        $dimension1->requiredFields = array('column3');

        $dimension2 = new FakeVisitDimension();
        $dimension2->set('columnName', 'column2');
        $dimension2->requiredFields = array('column3', 'column4');

        $dimension3 = new FakeVisitDimension();
        $dimension3->set('columnName', 'column3');
        $dimension3->requiredFields = array();

        $dimension4 = new FakeVisitDimension();
        $dimension4->set('columnName', 'column4');
        $dimension4->requiredFields = array('column2');

        $instances = array($dimension1, $dimension2, $dimension3, $dimension4);

        $instances = VisitDimension::sortDimensions($instances);

        $this->assertSame(array($dimension3, $dimension4, $dimension2, $dimension1), $instances);
    }

    public function test_getDimensions_shouldOnlyLoadAllVisitDimensionsFromACertainPlugin()
    {
        Manager::getInstance()->loadPlugins(array('Actions'));
        $plugin = Manager::getInstance()->loadPlugin('Actions');

        $dimensions = VisitDimension::getDimensions($plugin);

        $this->assertGreaterThan(5, count($dimensions));

        foreach ($dimensions as $dimension) {
            $this->assertInstanceOf('\Piwik\Plugin\Dimension\VisitDimension', $dimension);
            $this->assertStringStartsWith('Piwik\Plugins\Actions\Columns', get_class($dimension));
        }
    }


    public function test_getAllDimensions_shouldLoadAllDimensionsButOnlyIfLoadedPlugins()
    {
        Manager::getInstance()->loadPlugins(array('Actions', 'DevicesDetection'));

        $dimensions = VisitDimension::getAllDimensions();

        $this->assertGreaterThan(10, count($dimensions));

        foreach ($dimensions as $dimension) {
            $this->assertInstanceOf('\Piwik\Plugin\Dimension\VisitDimension', $dimension);
            $this->assertRegExp('/Piwik.Plugins.(DevicesDetection|Actions).Columns/', get_class($dimension));
        }
    }
}
