<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater\ReleaseChannel;

use Piwik\Piwik;
use Piwik\Plugins\CoreUpdater\ReleaseChannel;
use Piwik\Version;

class LatestCurrentStable extends ReleaseChannel
{
    public function getId()
    {
        return 'latest_'.Version::MAJOR_VERSION.'x_stable';
    }

    public function getName()
    {
        return Piwik::translate('CoreUpdater_LatestXStableRelease', Version::MAJOR_VERSION . '.X');
    }

    public function getDescription()
    {
        return Piwik::translate('CoreUpdater_LtsSupportVersion');
    }

    public function getOrder()
    {
        return 20;
    }
}