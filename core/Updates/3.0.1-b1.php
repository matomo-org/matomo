<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_3_0_1_b1 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        // Allow IIS to serve .woff files (https://github.com/piwik/piwik/pull/11091).
        // Re-generate .htaccess without 'Options -Indexes' because it does not always work on some servers
        ServerFilesGenerator::createFilesForSecurity();
    }
}
