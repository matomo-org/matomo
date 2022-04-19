<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\Referrers\Columns\ReferrerType;
use Piwik\Widget\WidgetsList;
use Piwik\Report\ReportWidgetFactory;

class GetReferrerType extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new ReferrerType();
        $this->name          = Piwik::translate('Referrers_Type');
        $this->documentation = Piwik::translate('Referrers_TypeReportDocumentation') . '<br />'
                             . '<b>' . Piwik::translate('Referrers_DirectEntry') . ':</b> ' . Piwik::translate('Referrers_DirectEntryDocumentation') . '<br />'
                             . '<b>' . Piwik::translate('Referrers_SearchEngines') . ':</b> ' . Piwik::translate('Referrers_SearchEnginesDocumentation',
                                 array('<br />', '&quot;' . Piwik::translate('Referrers_SubmenuSearchEngines') . '&quot;')) . '<br />'
                             . '<b>' . Piwik::translate('Referrers_Websites') . ':</b> ' . Piwik::translate('Referrers_WebsitesDocumentation',
                                 array('<br />', '&quot;' . Piwik::translate('Referrers_SubmenuWebsitesOnly') . '&quot;')) . '<br />'
                             . '<b>' . Piwik::translate('Referrers_Campaigns') . ':</b> ' . Piwik::translate('Referrers_CampaignsDocumentation',
                                 array('<br />', '&quot;' . Piwik::translate('Referrers_Campaigns') . '&quot;'));
        $this->constantRowsCount = true;
        $this->hasGoalMetrics = true;
        $this->order = 1;
        $this->subcategoryId = 'Referrers_WidgetGetAll';
        $this->supportsFlatten = false;
    }

    public function getDefaultTypeViewDataTable()
    {
        return HtmlTable\AllColumns::ID;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                    ->setName('Referrers_ReferrerTypes')
                    ->setSubcategoryId('Referrers_WidgetGetAll')
        );

        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->setName('General_EvolutionOverPeriod')
                ->setSubcategoryId('General_Overview')
                ->setAction('getEvolutionGraph')
                ->setOrder(9)
                ->setIsNotWidgetizable()
                ->forceViewDataTable(Evolution::ID)
                ->addParameters(array(
                    'columns' => $defaultColumns = array('nb_visits'),
                ))
        );

        $widgetsList->addWidgetConfig(
            $factory->createCustomWidget('getSparklines')
                ->forceViewDataTable(Sparklines::ID)
                ->setIsNotWidgetizable()
                ->setName('Referrers_Type')
                ->setSubcategoryId('General_Overview')
                ->setOrder(10)
        );
    }

    public function configureView(ViewDataTable $view)
    {
        $idSubtable       = Common::getRequestVar('idSubtable', false);
        $labelColumnTitle = $this->name;

        switch ($idSubtable) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                $labelColumnTitle = Piwik::translate('General_ColumnKeyword');
                break;
            case Common::REFERRER_TYPE_SOCIAL_NETWORK:
                $labelColumnTitle = Piwik::translate('Referrers_ColumnSocial');
                break;
            case Common::REFERRER_TYPE_WEBSITE:
                $labelColumnTitle = Piwik::translate('Referrers_ColumnWebsite');
                break;
            case Common::REFERRER_TYPE_CAMPAIGN:
                $labelColumnTitle = Piwik::translate('Referrers_ColumnCampaign');
                break;
            default:
                break;
        }

        $view->config->show_search = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $labelColumnTitle);

        $view->requestConfig->filter_limit = 10;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }
        
        $this->configureFooterMessage($view);
    }

    protected function configureFooterMessage(ViewDataTable $view)
    {
        if ($this->isSubtableReport) {
            // no footer message for subtables
            return;
        }

        $out = '';
        Piwik::postEvent('Template.afterReferrerTypeReport', array(&$out));
        $view->config->show_footer_message = $out;
    }
}
