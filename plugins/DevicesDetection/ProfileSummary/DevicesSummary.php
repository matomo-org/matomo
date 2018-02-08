<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection\ProfileSummary;

use Piwik\Piwik;
use Piwik\Plugins\Live\ProfileSummary\ProfileSummaryAbstract;
use Piwik\View;

/**
 * Class DevicesSummary
 *
 * @api
 */
class DevicesSummary extends ProfileSummaryAbstract
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Piwik::translate('DevicesDetection_Devices');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (empty($this->profile['devices'])) {
            return '';
        }

        $view              = new View('@DevicesDetection/_profileSummary.twig');
        $view->visitorData = $this->profile;
        return $view->render();
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 50;
    }
}