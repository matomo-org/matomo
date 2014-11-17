<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Filesystem;

/**
 * @group Core
 */
class FilesystemTest extends \PHPUnit_Framework_TestCase
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

}