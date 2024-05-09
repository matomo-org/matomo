<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Version;

class VersionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Version
     */
    private $version;

    public function setUp(): void
    {
        $this->version = new Version();
    }

    public function testIsStableVersion()
    {
        $this->assertIsStableVersion('3.3.3');
        $this->assertIsStableVersion('3.0.0');
        $this->assertIsStableVersion('100.999.9191');

        $this->assertNotStableVersion('3.3');
        $this->assertNotStableVersion('3.3.');
        $this->assertNotStableVersion('3-3-3');
        $this->assertNotStableVersion('a3.3.3');
        $this->assertNotStableVersion('3.0.0b');
        $this->assertNotStableVersion('3.3.3-b1');
        $this->assertNotStableVersion('3.3.3-rc1');
    }

    public function testIsVersionNumber()
    {
        $this->assertIsVersionNumber('3.3.3');
        $this->assertIsVersionNumber('3.3.3-b1');
        $this->assertIsVersionNumber('100.999.9991-rc90');
        $this->assertIsVersionNumber('100.999.9991-b90');
        $this->assertIsVersionNumber('100.999.9991-beta90');

        $this->assertNotVersionNumber('3.3');
        $this->assertNotVersionNumber('3.3.');
        $this->assertNotVersionNumber('3-3-3');
        $this->assertNotVersionNumber('a3.3.3');
        $this->assertNotVersionNumber('3.0.0b');
        $this->assertNotVersionNumber('3.0.0beta1'); // missing dash
        $this->assertNotVersionNumber('3.3.3-bbeta1'); // max 4 allowed but bbeta is 5
    }

    public function testIsPreviewVersion()
    {
        $this->assertIsPreviewVersion('3.3.3-dev-p20240509114000');
        $this->assertIsPreviewVersion('3.3.3-dev-p33331224183000');
        $this->assertIsPreviewVersion('3.3.3-b1-p20240509114000');
        $this->assertIsPreviewVersion('100.999.9191-rc4-p20240509114000');

        $this->assertNotPreviewVersion('3.3');
        $this->assertNotPreviewVersion('3.3.');
        $this->assertNotPreviewVersion('3-3-3');
        $this->assertNotPreviewVersion('a3.3.3');
        $this->assertNotPreviewVersion('3.0.0b');
        $this->assertNotPreviewVersion('3.3.3-b1');
        $this->assertNotPreviewVersion('3.3.3-b1-pp20240509114000');
        $this->assertNotPreviewVersion('3.3.3-b1-p20240509114000a');
        $this->assertNotPreviewVersion('3.3.3-rc1');
        $this->assertNotPreviewVersion('3.3.3-p20240509114000');
        $this->assertNotPreviewVersion('p20240509114000');
        $this->assertNotPreviewVersion('3.3.3-b1-p202405091140');
        $this->assertNotPreviewVersion('3.3.3-b1-p20243309114000');
        $this->assertNotPreviewVersion('3.3.3-b1-p20240544114000');
        $this->assertNotPreviewVersion('3.3.3-b1-p20240509554000');
        $this->assertNotPreviewVersion('3.3.3-b1-p20240509117700');
        $this->assertNotPreviewVersion('3.3.3-b1-p20240509114088');
    }

    private function assertIsStableVersion($versionNumber)
    {
        $isStable = $this->version->isStableVersion($versionNumber);
        $this->assertTrue($isStable);
    }

    private function assertNotStableVersion($versionNumber)
    {
        $isStable = $this->version->isStableVersion($versionNumber);
        $this->assertFalse($isStable);
    }

    private function assertIsVersionNumber($versionNumber)
    {
        $isVersionNumber = $this->version->isVersionNumber($versionNumber);
        $this->assertTrue($isVersionNumber);
    }

    private function assertNotVersionNumber($versionNumber)
    {
        $isVersionNumber = $this->version->isVersionNumber($versionNumber);
        $this->assertFalse($isVersionNumber);
    }

    private function assertIsPreviewVersion($versionNumber)
    {
        $isPreviewVersion = $this->version->isPreviewVersion($versionNumber);
        $this->assertTrue($isPreviewVersion);
    }

    private function assertNotPreviewVersion($versionNumber)
    {
        $isPreviewVersion = $this->version->isPreviewVersion($versionNumber);
        $this->assertFalse($isPreviewVersion);
    }
}
