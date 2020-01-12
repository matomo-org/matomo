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

class Latest3XStable extends ReleaseChannel
{
    public function getId()
    {
        return 'latest_3x_stable';
    }

    public function getName()
    {
        return Piwik::translate('CoreUpdater_LatestXStableRelease', '3.X');
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