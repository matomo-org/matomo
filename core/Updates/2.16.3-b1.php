<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_2_16_3_b1 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        ServerFilesGenerator::createFilesForSecurity();
    }
}
