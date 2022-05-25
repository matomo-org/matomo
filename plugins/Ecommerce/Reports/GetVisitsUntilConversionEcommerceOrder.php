<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Reports;

use Piwik\Piwik;
use Piwik\Plugins\Goals\Columns\VisitsUntilConversion;

class GetVisitsUntilConversionEcommerceOrder extends Base
{
    protected function init()
    {
        parent::init();

        $this->action = 'getVisitsUntilConversion';
        $this->name = Piwik::translate('General_EcommerceOrders') . ' - ' . Piwik::translate('Goals_VisitsUntilConv');
        $this->dimension = new VisitsUntilConversion();
        $this->constantRowsCount = true;
        $this->processedMetrics = array();
        $this->metrics = array('nb_conversions');
        $this->order = 11;

        $this->parameters =  array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);
    }

}
