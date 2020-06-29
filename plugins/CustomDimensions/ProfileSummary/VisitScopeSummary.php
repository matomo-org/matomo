<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\ProfileSummary;

use Piwik\Piwik;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\Live\ProfileSummary\ProfileSummaryAbstract;
use Piwik\View;

/**
 * Class VisitScopeSummary
 */
class VisitScopeSummary extends ProfileSummaryAbstract
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Piwik::translate('CustomDimensions_CustomDimensions') . ' ' . Piwik::translate('General_TrackingScopeVisit');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (empty($this->profile['customDimensions']) || empty($this->profile['customDimensions'][CustomDimensions::SCOPE_VISIT])) {
            return '';
        }

        $view              = new View('@CustomDimensions/_profileSummary.twig');
        $view->visitorData = $this->profile;
        $view->scopeName   = Piwik::translate('General_TrackingScopeVisit');
        $view->dimensions  = $this->profile['customDimensions'][CustomDimensions::SCOPE_VISIT];

        return $view->render();
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 10;
    }
}