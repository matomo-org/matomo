<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Composer\Semver\VersionParser;
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
        $this->assertNotStableVersion('3.3.3-alpha');
        $this->assertNotStableVersion('3.3.3-b1');
        $this->assertNotStableVersion('3.3.3-rc1');
        $this->assertNotStableVersion('3.3.3-rc1.20240509114000');
    }

    public function testIsVersionNumber()
    {
        $this->assertIsVersionNumber('3.3.3');
        $this->assertIsVersionNumber('3.3.3-alpha');
        $this->assertIsVersionNumber('3.3.3-b1');
        $this->assertIsVersionNumber('3.3.3-rc1.20240509114000');
        $this->assertIsVersionNumber('100.999.9991-rc90');
        $this->assertIsVersionNumber('100.999.9991-b90');
        $this->assertIsVersionNumber('100.999.9991-beta90');

        $this->assertNotVersionNumber('3.3');
        $this->assertNotVersionNumber('3.3.');
        $this->assertNotVersionNumber('3-3-3');
        $this->assertNotVersionNumber('a3.3.3');
        $this->assertNotVersionNumber('3.0.0b');
        $this->assertNotVersionNumber('3.0.0beta1'); // missing dash
        $this->assertNotVersionNumber('3.3.3-bbeta1'); // unknown stability
        $this->assertNotVersionNumber('3.3.3-rc1.2024'); // short preview
    }

    public function testIsPreviewVersion()
    {
        $this->assertIsPreviewVersion('3.3.3-alpha.20240509114000');
        $this->assertIsPreviewVersion('3.3.3-alpha.33331224183000');
        $this->assertIsPreviewVersion('3.3.3-b1.20240509114000');
        $this->assertIsPreviewVersion('100.999.9191-rc4.20240509114000');

        $this->assertNotPreviewVersion('3.3');
        $this->assertNotPreviewVersion('3.3.');
        $this->assertNotPreviewVersion('3-3-3');
        $this->assertNotPreviewVersion('a3.3.3');
        $this->assertNotPreviewVersion('3.0.0b');
        $this->assertNotPreviewVersion('3.3.3-alpha');
        $this->assertNotPreviewVersion('3.3.3-b1');
        $this->assertNotPreviewVersion('3.3.3-b1.p20240509114000');
        $this->assertNotPreviewVersion('3.3.3-b1.20240509114000a');
        $this->assertNotPreviewVersion('3.3.3-rc1');
        $this->assertNotPreviewVersion('3.3.3-dev.20240509114000');
        $this->assertNotPreviewVersion('3.3.3.20240509114000');
        $this->assertNotPreviewVersion('p20240509114000');
        $this->assertNotPreviewVersion('3.3.3-b1.202405091140');
        $this->assertNotPreviewVersion('3.3.3-b1.20243309114000');
        $this->assertNotPreviewVersion('3.3.3-b1.20240544114000');
        $this->assertNotPreviewVersion('3.3.3-b1.20240509554000');
        $this->assertNotPreviewVersion('3.3.3-b1.20240509117700');
        $this->assertNotPreviewVersion('3.3.3-b1.20240509114088');
    }

    public function testNextPreviewVersion()
    {
        $this->assertNextVersionIsEmpty('3.3.3-alpha.29990101000000'); // preview is newer
        $this->assertNextVersionIsEmpty('3.3.3-dev'); // unsupported stability
        $this->assertNextVersionIsEmpty('p20240509114000');

        $this->assertNextVersionExists('3.3.3');
        $this->assertNextVersionExists('3.3.3-alpha');
        $this->assertNextVersionExists('3.3.3-b1');
        $this->assertNextVersionExists('3.3.3-rc1');
        $this->assertNextVersionExists('3.3.3-alpha.20201224180000');
        $this->assertNextVersionExists('3.3.3-b1.20201224180000');
        $this->assertNextVersionExists('3.3.3-rc1.20201224180000');
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

    private function assertNextVersionIsEmpty($versionNumber)
    {
        $nextVersionNumber = $this->version->nextPreviewVersion($versionNumber);
        $this->assertStringEqualsStringIgnoringLineEndings('', $nextVersionNumber);
    }

    private function assertNextVersionExists($versionNumber)
    {
        $nextVersionNumber = $this->version->nextPreviewVersion($versionNumber);
        $this->assertTrue($this->version->isPreviewVersion($nextVersionNumber));
    }

    /**
     * @dataProvider getLowerVersionCompares
     */
    public function testVersionContraints($v1, $v2)
    {
        $v = new VersionParser();
        $v1p = $v->parseConstraints($v1);
        $v2p = $v->parseConstraints('<' . $v2);

        self::assertTrue($v2p->matches($v1p));
    }

    /**
     * @dataProvider getLowerVersionCompares
     */
    public function testVersionCompares($v1, $v2)
    {
        self::assertTrue(version_compare($v1, $v2, '<'));
    }

    public function getLowerVersionCompares()
    {
        return [
            [ '5.1.0', '6.0.0-b1' ],
            [ '5.1.0-alpha.20240517231100', '5.1.0-b1' ],
            [ '5.1.0-alpha.20240517231100', '5.1.0-rc1' ],
            [ '5.1.0-alpha.20240517231100', '5.1.0-alpha.20240617231100' ],
            [ '5.1.0-b1.20240517231100', '5.1.0-b2' ],
            [ '5.1.0-b1.20240517231100', '5.1.0-rc1' ],
            [ '5.1.0-b1.20240517221100', '5.1.0-b1.20240517231100' ],
            [ '5.1.0-rc1.20240517231100', '5.1.0-rc2' ],
            [ '5.1.0-rc1.20240517221100', '5.1.0-rc1.20240517231100' ],
        ];
    }
}
