<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Updates;

use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Updates;

/**
 */
class Updates_2_3_0_rc2 extends Updates
{
    public static function update()
    {
        ServerFilesGenerator::deleteHtAccessFiles();

        ServerFilesGenerator::createHtAccessFiles();
    }

}
