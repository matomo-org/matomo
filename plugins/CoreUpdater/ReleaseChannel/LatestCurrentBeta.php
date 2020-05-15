<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater\ReleaseChannel;

use Piwik\Piwik;
use Piwik\Plugins\CoreUpdater\ReleaseChannel;
use Piwik\Version;

class LatestCurrentBeta extends ReleaseChannel
{
    public function getId()
    {
        // NOTE: using Version::VERSION instead of Version::MAJOR_VERSION  since MAJOR_VERSION may not exist when
        // updating from pre 4.x to 4.x.
        return 'latest_'. ((int) Version::VERSION) . 'x_beta';
    }

    public function getName()
    {
        return Piwik::translate('CoreUpdater_LatestXBetaRelease', ((int) Version::VERSION) . '.X');
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