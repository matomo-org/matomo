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
use Piwik\Updater;

/**
 */
class Updates_2_4_0_b2 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        ServerFilesGenerator::deleteWebConfigFiles();
        ServerFilesGenerator::createWebConfigFiles();

        ServerFilesGenerator::deleteHtAccessFiles();
        ServerFilesGenerator::createHtAccessFiles();
    }
}
