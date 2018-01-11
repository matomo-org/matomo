<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Columns\ClickedUrl;

class GetOutlinks extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new ClickedUrl();
        $this->name          = Piwik::translate('General_Outlinks');
        $this->documentation = Piwik::translate('Actions_OutlinksReportDocumentation') . ' '
                             . Piwik::translate('Actions_OutlinkDocumentation') . '<br />'
                             . Piwik::translate('General_UsePlusMinusIconsDocumentation');

        $this->metrics = array('nb_visits', 'nb_hits');
        $this->order   = 8;

        $this->actionToLoadSubTables = $this->action;

        $this->subcategoryId = 'General_Outlinks';
    }

    public function getMetrics()
    {
        return array(
            'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicks'),
            'nb_hits'   => Piwik::translate('Actions_ColumnClicks')
        );
    }

    protected function getMetricsDocumentation()
    {
        return array(
            'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicksDocumentation'),
            'nb_hits'   => Piwik::translate('Actions_ColumnClicksDocumentation')
        );
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslations(array('label' => $this->dimension->getName()));

        $view->config->columns_to_display          = array('label', 'nb_visits', 'nb_hits');
        $view->config->show_exclude_low_population = false;

        $this->addBaseDisplayProperties($view);
    }
}
