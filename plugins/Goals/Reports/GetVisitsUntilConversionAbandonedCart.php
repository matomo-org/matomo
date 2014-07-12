<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\Goals\Columns\VisitsUntilConversion;

class GetVisitsUntilConversionAbandonedCart extends BaseEcommerce
{
    protected function init()
    {
        parent::init();

        $this->action = 'getVisitsUntilConversion';
        $this->name = Piwik::translate('General_AbandonedCarts') . ' - ' . Piwik::translate('Goals_VisitsUntilConv');
        $this->dimension = new VisitsUntilConversion();
        $this->constantRowsCount = true;
        $this->processedMetrics = false;
        $this->metrics = array('nb_conversions');
        $this->order = 20;

        $this->parameters =  array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART);
    }

}
