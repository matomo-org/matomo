<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Version;

class FirstPreviousMajorInstall extends LatestStableInstall
{
    protected function getDownloadUrl()
    {
        return sprintf('http://builds.matomo.org/matomo-%s.0.0.zip', Version::MAJOR_VERSION-1);
    }
}
