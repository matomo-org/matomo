<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs\tests\Integration;

use Piwik\Plugins\CustomPiwikJs\tests\Framework\Mock\PluginTrackerFilesMock;
use Piwik\Plugins\CustomPiwikJs\TrackingCode\PiwikJsManipulator;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomPiwikJs
 * @group PiwikJsManipulatorTest
 * @group PiwikJsManipulator
 * @group Plugins
 */
class PiwikJsManipulatorTest extends IntegrationTestCase
{
    private $content = 'var Piwik.js = "mytest";
/*!!! pluginTrackerHook */

var myArray = [];
';

    public function test_manipulateContent_shouldAddCodeOfTrackerPlugins()
    {
        $manipulator = $this->makeManipulator(array(
            '/plugins/CustomPiwikJs/tests/resources/tracker.js',
            '/plugins/CustomPiwikJs/tests/resources/tracker.min.js',
        ));

        $updatedContent = $manipulator->manipulateContent();

        $this->assertSame('var Piwik.js = "mytest";
/*!!! pluginTrackerHook */

/* GENERATED: tracker.min.js */
/* my license header */
var mySecondCustomTracker = \'test\';
/* END GENERATED: tracker.min.js */


/* GENERATED: tracker.js */
/** my license header*/
var myCustomTracker = \'test\';

var fooBar = \'baz\';
/* END GENERATED: tracker.js */


var myArray = [];
', $updatedContent);
    }

    public function test_manipulateContent_shouldNotAddCodeOfTrackerPlugins_IfThereAreNoTrackerFiles()
    {
        $manipulator = $this->makeManipulator(array());

        $updatedContent = $manipulator->manipulateContent();

        $this->assertSame($this->content, $updatedContent);
    }

    private function makeManipulator($files)
    {
        return new PiwikJsManipulator($this->content, new PluginTrackerFilesMock($files));
    }

}
