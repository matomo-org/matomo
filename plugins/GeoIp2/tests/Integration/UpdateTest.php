<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\GeoIp2\tests\Integration;

/**
 * @group GeoIp2
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testEnsureFileForUpdateIsPresent()
    {
        // In update Updates_3_5_1_b1 the file `plugins/GeoIp2/data/isoRegionNames.php` is used to check file system
        // case sensitivity. Therefor we need to ensure this file is present unless the update script isn't changed
        $this->assertFileExists(PIWIK_INCLUDE_PATH . '/plugins/GeoIp2/data/isoRegionNames.php');
    }
}
