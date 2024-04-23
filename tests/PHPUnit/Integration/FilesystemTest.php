<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Filesystem;

/**
 * @group Core
 */
class FilesystemTest extends \PHPUnit\Framework\TestCase
{
    public function testGetFileSizeShouldRecognizeLowerUnits()
    {
        $size = Filesystem::getFileSize(__FILE__, 'b');

        $this->assertGreaterThan(400, $size);
        $this->assertLessThan(400000, $size);
    }

    public function testGetFileSizeShouldReturnNullIfFileDoesNotExists()
    {
        $size = Filesystem::getFileSize(PIWIK_INCLUDE_PATH . '/tests/NotExisting.File');

        $this->assertNull($size);
    }

    public function testRemoveFileShouldRemoveFile()
    {
        $tmpFile = StaticContainer::get('path.tmp') . '/filesystem-test-file';
        touch($tmpFile);

        Filesystem::remove($tmpFile);

        $this->assertFileNotExists($tmpFile);
    }

    public function testRemoveNonExistingFileShouldNotThrowException()
    {
        self::expectNotToPerformAssertions();

        Filesystem::remove('foo');
    }
}
