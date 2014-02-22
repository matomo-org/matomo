<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\UI;

abstract class UIUnitTest extends UITest
{
    public static function generateScreenshots()
    {
        $url = static::getUrlToTest();
        $screens = static::getScreensToCapture();

        $screensData = array();
        foreach ($screens as $info) {
            list($name, $js) = $info;

            list($processedScreenshotPath, $expectedScreenshotPath) = self::getProcessedAndExpectedScreenshotPaths($name);
            $screensData[] = array($processedScreenshotPath, $js);
        }

        $captureProgramData = array(
            'url' => self::getProxyUrl() . $url,
            'screens' => $screensData
        );

        echo "Generating screenshots...\n";
        self::runCaptureProgram($captureProgramData);
    }

    protected function compareScreenshot($name, $ignore = false)
    {
        list($processedScreenshotPath, $expectedScreenshotPath) = self::getProcessedAndExpectedScreenshotPaths($name);

        $this->compareScreenshotAgainstExpected($name, false, $processedScreenshotPath, $expectedScreenshotPath);
    }
}