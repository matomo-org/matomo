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

class Latest3XBeta extends ReleaseChannel
{
    public function getId()
    {
        return 'latest_3x_beta';
    }

    public function getName()
    {
        return Piwik::translate('CoreUpdater_LatestXBetaRelease', '3.X');
    }

    public function getDescription()
    {
        return Piwik::translate('CoreUpdater_LtsSupportVersion');
    }

    public function doesPreferStable()
    {
        return false;
    }

    public function getOrder()
    {
        return 21;
    }
}