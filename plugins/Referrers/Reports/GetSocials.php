<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\Plugins\Referrers\Columns\SocialNetwork;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class GetSocials extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new SocialNetwork();
        $this->name          = Piwik::translate('Referrers_Socials');
        $this->documentation = Piwik::translate('Referrers_WebsitesReportDocumentation', '<br />');
        $this->actionToLoadSubTables = 'getUrlsForSocial';
        $this->order = 11;

        $this->subcategoryId = 'Referrers_Socials';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widget = $factory->createWidget()->setName('Referrers_Socials');
        $widgetsList->addWidgetConfig($widget);
    }

    public function getDefaultTypeViewDataTable()
    {
        return Pie::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_pivot_by_subtable = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_goals = true;
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 10;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }
    }

}
