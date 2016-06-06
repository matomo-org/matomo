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
class Updates_2_15_0_b4 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        // added .ttf whitelisted file for apache webserver
        ServerFilesGenerator::deleteHtAccessFiles();
        ServerFilesGenerator::createHtAccessFiles();
    }
}
