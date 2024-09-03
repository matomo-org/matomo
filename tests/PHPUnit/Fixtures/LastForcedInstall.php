<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

class LastForcedInstall extends LatestStableInstall
{
    protected function getDownloadUrl()
    {
        return 'http://builds.matomo.org/matomo-4.15.0.zip';
    }
}
