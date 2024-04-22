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
        $isStable = $this->version->isVersionNumber($versionNumber);
        $this->assertTrue($isStable);
    }

    private function assertNotVersionNumber($versionNumber)
    {
        $isStable = $this->version->isVersionNumber($versionNumber);
        $this->assertFalse($isStable);
    }
}
