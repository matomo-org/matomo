<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs\tests\Integration;

use Piwik\Plugins\CustomPiwikJs\TrackingCode\PluginTrackerFiles;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CustomPluginTrackerFiles extends PluginTrackerFiles {

    private $pluginNamesForFile = array();

    public function __construct($pluginNameForRegularTrackerFile = 'CustomPiwikJs', $pluginNameForMinifiedTracker = 'CustomPiwikJs')
    {
        parent::__construct();

        $this->dir = PIWIK_DOCUMENT_ROOT . '/plugins/CustomPiwikJs/tests/';

        $this->pluginNamesForFile = array(
            'tracker.js' => $pluginNameForRegularTrackerFile,
            'tracker.min.js' => $pluginNameForMinifiedTracker
        );
    }

    protected function getPluginNameFromFile($file)
    {
        $fileName = basename($file);
        return $this->pluginNamesForFile[$fileName];
    }
}

class CustomPluginTrackerFiles2 extends PluginTrackerFiles {

    public function getPluginNameFromFile($file)
    {
        return parent::getPluginNameFromFile($file);
    }
}

/**
 * @group CustomPiwikJs
 * @group PluginTrackerFilesTest
 * @group PluginTrackerFiles
 * @group Plugins
 */
class PluginTrackerFilesTest extends IntegrationTestCase
{
    public function test_find_ifAPluginDefinesAMinifiedAndARegularTrackerItShouldPreferTheMinifiedVersion()
    {
        $trackerFiles = new CustomPluginTrackerFiles();
        $foundFiles = $trackerFiles->find();

        $this->assertCount(1, $foundFiles);
        $this->assertTrue(isset($foundFiles['CustomPiwikJs']));
        $this->assertEquals('tracker.min.js', $foundFiles['CustomPiwikJs']->getName());
    }

    public function test_find_shouldIgnoreMinifiedVersion_IfRequested()
    {
        $trackerFiles = new CustomPluginTrackerFiles();
        $trackerFiles->ignoreMinified();
        $foundFiles = $trackerFiles->find();

        $this->assertCount(1, $foundFiles);
        $this->assertTrue(isset($foundFiles['CustomPiwikJs']));
        $this->assertEquals('tracker.js', $foundFiles['CustomPiwikJs']->getName());
    }

    public function test_find_ifMultiplePluginsImplementATracker_ShouldReturnEachOfThem()
    {
        $trackerFiles = new CustomPluginTrackerFiles('CustomPiwikJs', 'Goals');
        $foundFiles = $trackerFiles->find();

        $this->assertCount(2, $foundFiles);
        $this->assertTrue(isset($foundFiles['CustomPiwikJs']));
        $this->assertTrue(isset($foundFiles['Goals']));
        $this->assertEquals('tracker.js', $foundFiles['CustomPiwikJs']->getName());
        $this->assertEquals('tracker.min.js', $foundFiles['Goals']->getName());
    }

    public function test_find_shouldNotReturnATrackerFile_IfPluginIsNotActivatedOrLoaded()
    {
        $trackerFiles = new CustomPluginTrackerFiles('MyNotExistingPlugin', 'Goals');
        $foundFiles = $trackerFiles->find();

        $this->assertCount(1, $foundFiles);
        $this->assertTrue(isset($foundFiles['Goals']));
        $this->assertEquals('tracker.min.js', $foundFiles['Goals']->getName());

        $trackerFiles = new CustomPluginTrackerFiles('Goals', 'MyNotExistingPlugin');
        $foundFiles = $trackerFiles->find();

        $this->assertCount(1, $foundFiles);
        $this->assertTrue(isset($foundFiles['Goals']));
        $this->assertEquals('tracker.js', $foundFiles['Goals']->getName());
    }

    public function test_find_shouldNotReturnFileIfNoPluginActivated()
    {
        $trackerFiles = new CustomPluginTrackerFiles('MyNotExistingPlugin', 'MyNotExistingPlugin2');
        $foundFiles = $trackerFiles->find();

        $this->assertSame(array(), $foundFiles);
    }

    public function test_getPluginNameFromFile_shouldDetectPluginName()
    {
        $trackerFiles = new CustomPluginTrackerFiles2();
        $pluginName = $trackerFiles->getPluginNameFromFile(PIWIK_DOCUMENT_ROOT . '/plugins/MyFooBarPlugin/tracker.js');
        $this->assertSame('MyFooBarPlugin', $pluginName);

        $pluginName = $trackerFiles->getPluginNameFromFile(PIWIK_DOCUMENT_ROOT . '/plugins//MyFooBarPlugin//tracker.js');
        $this->assertSame('MyFooBarPlugin', $pluginName);

        $pluginName = $trackerFiles->getPluginNameFromFile(PIWIK_DOCUMENT_ROOT . '/plugins//MyFooBarPlugin//tracker.min.js');
        $this->assertSame('MyFooBarPlugin', $pluginName);
    }

}
