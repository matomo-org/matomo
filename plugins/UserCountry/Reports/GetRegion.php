<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\UserCountry\Columns\Region;

class GetRegion extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension      = new Region();
        $this->name           = Piwik::translate('UserCountry_Region');
        $this->documentation  = Piwik::translate('UserCountry_getRegionDocumentation') . '<br/>' . $this->getGeoIPReportDocSuffix();
        $this->metrics        = array('nb_visits', 'nb_uniq_visitors', 'nb_actions');
        $this->hasGoalMetrics = true;
        $this->order = 7;
        $this->widgetTitle = Piwik::translate('UserCountry_WidgetLocation')
                           . ' (' . Piwik::translate('UserCountry_Region') . ')';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_exclude_low_population = false;
        $view->config->documentation = $this->documentation;
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 5;

        $this->checkIfNoDataForGeoIpReport($view);
    }

}
