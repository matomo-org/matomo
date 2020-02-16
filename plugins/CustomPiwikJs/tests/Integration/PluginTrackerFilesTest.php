<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs\tests\Integration;

use Piwik\Piwik;
use Piwik\Plugins\CustomPiwikJs\TrackingCode\PluginTrackerFiles;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CustomPluginTrackerFiles extends PluginTrackerFiles {


    protected function getDirectoriesToLook() {
        return array(
            'CustomPiwikJs' => PIWIK_DOCUMENT_ROOT . '/plugins/CustomPiwikJs/tests/resources/'
        );
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

    public function test_find_EventsCanIgnoreFiles()
    {
        $trackerFiles = new CustomPluginTrackerFiles();
        $foundFiles = $trackerFiles->find();
        $this->assertCount(1, $foundFiles);

        Piwik::addAction('CustomPiwikJs.shouldAddTrackerFile', function (&$shouldAdd, $pluginName) {
            if ($pluginName === 'CustomPiwikJs') {
                $shouldAdd = false;
            }
        });

        $foundFiles = $trackerFiles->find();
        $this->assertCount(0, $foundFiles);
    }

}
