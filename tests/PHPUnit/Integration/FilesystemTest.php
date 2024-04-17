<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Filesystem;

/**
 * @group Core
 */
class FilesystemTest extends \PHPUnit\Framework\TestCase
{
    public function test_getFileSize_ShouldRecognizeLowerUnits()
    {
        $size = Filesystem::getFileSize(__FILE__, 'b');

        $this->assertGreaterThan(400, $size);
        $this->assertLessThan(400000, $size);
    }

    public function test_getFileSize_ShouldReturnNull_IfFileDoesNotExists()
    {
        $size = Filesystem::getFileSize(PIWIK_INCLUDE_PATH . '/tests/NotExisting.File');

        $this->assertNull($size);
    }

    public function test_removeFile_shouldRemoveFile()
    {
        $tmpFile = StaticContainer::get('path.tmp') . '/filesystem-test-file';
        touch($tmpFile);

        Filesystem::remove($tmpFile);

        $this->assertFileNotExists($tmpFile);
    }

    public function test_removeNonExistingFile_shouldNotThrowException()
    {
        self::expectNotToPerformAssertions();

        Filesystem::remove('foo');
    }
}
