<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Ecommerce\ProfileSummary;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Live\ProfileSummary\ProfileSummaryAbstract;
use Piwik\View;

/**
 * Class EcommerceSummary
 */
class EcommerceSummary extends ProfileSummaryAbstract
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Piwik::translate('Goals_Ecommerce');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (empty($this->profile['totalEcommerceRevenue'])) {
            return '';
        }

        $view              = new View('@Ecommerce/_profileSummary.twig');
        $view->idSite      = Common::getRequestVar('idSite', null, 'int');
        $view->visitorData = $this->profile;
        return $view->render();
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 20;
    }
}