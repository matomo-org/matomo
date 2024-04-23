<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker\tests\Integration;

use Piwik\Piwik;
use Piwik\Plugins\CustomJsTracker\TrackingCode\PluginTrackerFiles;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CustomPluginTrackerFiles extends PluginTrackerFiles
{
    protected function getDirectoriesToLook()
    {
        return array(
            'CustomJsTracker' => PIWIK_DOCUMENT_ROOT . '/plugins/CustomJsTracker/tests/resources/'
        );
    }
}

/**
 * @group CustomJsTracker
 * @group PluginTrackerFilesTest
 * @group PluginTrackerFiles
 * @group Plugins
 */
class PluginTrackerFilesTest extends IntegrationTestCase
{
    public function testFindIfAPluginDefinesAMinifiedAndARegularTrackerItShouldPreferTheMinifiedVersion()
    {
        $trackerFiles = new CustomPluginTrackerFiles();
        $foundFiles = $trackerFiles->find();

        $this->assertCount(1, $foundFiles);
        $this->assertTrue(isset($foundFiles['CustomJsTracker']));
        $this->assertEquals('tracker.min.js', $foundFiles['CustomJsTracker']->getName());
    }

    public function testFindShouldIgnoreMinifiedVersionIfRequested()
    {
        $trackerFiles = new CustomPluginTrackerFiles();
        $trackerFiles->ignoreMinified();
        $foundFiles = $trackerFiles->find();

        $this->assertCount(1, $foundFiles);
        $this->assertTrue(isset($foundFiles['CustomJsTracker']));
        $this->assertEquals('tracker.js', $foundFiles['CustomJsTracker']->getName());
    }

    public function testFindEventsCanIgnoreFiles()
    {
        $trackerFiles = new CustomPluginTrackerFiles();
        $foundFiles = $trackerFiles->find();
        $this->assertCount(1, $foundFiles);

        Piwik::addAction('CustomJsTracker.shouldAddTrackerFile', function (&$shouldAdd, $pluginName) {
            if ($pluginName === 'CustomJsTracker') {
                $shouldAdd = false;
            }
        });

        $foundFiles = $trackerFiles->find();
        $this->assertCount(0, $foundFiles);
    }
}
