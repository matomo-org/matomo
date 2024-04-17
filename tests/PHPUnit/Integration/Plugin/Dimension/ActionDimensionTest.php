<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// there is a test that requires the class to be defined in a plugin
namespace Piwik\Plugins\Test;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Segment;
use Piwik\Plugin\Manager;
use Piwik\Segment\SegmentsList;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class FakeActionDimension extends ActionDimension
{
    protected $columnName  = 'fake_action_dimension_column';
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
class ActionDimensionTest extends IntegrationTestCase
{
    /**
     * @var FakeActionDimension
     */
    private $dimension;

    public function setUp(): void
    {
        parent::setUp();

        Manager::getInstance()->unloadPlugins();
        Manager::getInstance()->doNotLoadAlwaysActivatedPlugins();

        $this->dimension = new FakeActionDimension();
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

    public function test_install_shouldAlwaysInstallLogAction_IfColumnNameAndTypeGiven()
    {
        $expected = array(
            'log_link_visit_action' => array(
                "ADD COLUMN `fake_action_dimension_column` VARCHAR (255) DEFAULT 0"
            )
        );

        $this->assertEquals($expected, $this->dimension->install());
    }

    public function test_update_shouldAlwaysUpdateLogVisit_IfColumnNameAndTypeGiven()
    {
        $expected = array(
            'log_link_visit_action' => array(
                "MODIFY COLUMN `fake_action_dimension_column` VARCHAR (255) DEFAULT 0"
            )
        );

        $this->assertEquals($expected, $this->dimension->update(array()));
    }

    public function test_getVersion_shouldUseColumnTypeAsVersion()
    {
        $this->assertEquals('VARCHAR (255) DEFAULT 0', $this->dimension->getVersion());
    }

    public function test_getSegment_ShouldReturnConfiguredSegments()
    {
        $list = new SegmentsList();
        $this->dimension->configureSegments($list, new DimensionSegmentFactory($this->dimension));

        $segments = $list->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf('\Piwik\Plugin\Segment', $segments[0]);
        $this->assertInstanceOf('\Piwik\Plugin\Segment', $segments[1]);
    }

    public function test_addSegment_ShouldPrefilSomeSegmentValuesIfNotDefinedYet()
    {
        $list = new SegmentsList();
        $this->dimension->configureSegments($list, new DimensionSegmentFactory($this->dimension));

        $segments = $list->getSegments();

        $this->assertEquals('log_link_visit_action.fake_action_dimension_column', $segments[0]->getSqlSegment());
        $this->assertEquals(Segment::TYPE_DIMENSION, $segments[0]->getType());
    }

    public function test_addSegment_ShouldNotOverwritePreAssignedValues()
    {
        $list = new SegmentsList();
        $this->dimension->configureSegments($list, new DimensionSegmentFactory($this->dimension));

        $segments = $list->getSegments();

        $this->assertEquals('customValue', $segments[1]->getSqlSegment());
        $this->assertEquals(Segment::TYPE_METRIC, $segments[1]->getType());
    }

    public function test_getDimensions_shouldOnlyLoadAllActionDimensionsFromACertainPlugin()
    {
        Manager::getInstance()->loadPlugins(array('Actions'));
        $plugin = Manager::getInstance()->loadPlugin('Actions');

        $dimensions = ActionDimension::getDimensions($plugin);

        $this->assertGreaterThan(5, count($dimensions));

        foreach ($dimensions as $dimension) {
            $this->assertInstanceOf('\Piwik\Plugin\Dimension\ActionDimension', $dimension);
            $this->assertStringStartsWith('Piwik\Plugins\Actions\Columns', get_class($dimension));
        }
    }

    public function test_getAllDimensions_shouldLoadAllDimensionsButOnlyIfLoadedPlugins()
    {
        Manager::getInstance()->loadPlugins(array('Actions', 'Events'));

        $dimensions = ActionDimension::getAllDimensions();

        $this->assertGreaterThan(8, count($dimensions));

        foreach ($dimensions as $dimension) {
            $this->assertInstanceOf('\Piwik\Plugin\Dimension\ActionDimension', $dimension);
            $this->assertRegExp('/Piwik.Plugins.(Actions|Events).Columns/', get_class($dimension));
        }
    }
}
