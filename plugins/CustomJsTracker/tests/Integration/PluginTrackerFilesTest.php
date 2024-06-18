<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker\tests\Integration;

use PHPUnit\Framework\MockObject\MockObject;
use Piwik\Piwik;
use Piwik\Plugins\CustomJsTracker\TrackingCode\PluginTrackerFiles;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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
        $trackerFiles = $this->getMockedTrackerFiles();
        $foundFiles = $trackerFiles->find();

        $this->assertCount(1, $foundFiles);
        $this->assertTrue(isset($foundFiles['CustomJsTracker']));
        $this->assertEquals('tracker.min.js', $foundFiles['CustomJsTracker']->getName());
    }

    public function testFindShouldIgnoreMinifiedVersionIfRequested()
    {
        $trackerFiles = $this->getMockedTrackerFiles();
        $trackerFiles->ignoreMinified();
        $foundFiles = $trackerFiles->find();

        $this->assertCount(1, $foundFiles);
        $this->assertTrue(isset($foundFiles['CustomJsTracker']));
        $this->assertEquals('tracker.js', $foundFiles['CustomJsTracker']->getName());
    }

    public function testFindEventsCanIgnoreFiles()
    {
        $trackerFiles = $this->getMockedTrackerFiles();
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

    /**
     * @return PluginTrackerFiles|MockObject
     */
    private function getMockedTrackerFiles(): MockObject
    {
        $mock = self::getMockBuilder(PluginTrackerFiles::class)->onlyMethods(['getDirectoriesToLook']);
        $trackerFiles = $mock->getMock();
        $trackerFiles->method('getDirectoriesToLook')->willReturn([
            'CustomJsTracker' => PIWIK_DOCUMENT_ROOT . '/plugins/CustomJsTracker/tests/resources/'
        ]);

        return $trackerFiles;
    }
}
