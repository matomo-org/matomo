<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Contents\Dimensions;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

abstract class Base extends Report
{
    protected function init()
    {
        $this->categoryId = 'General_Actions';
        $this->subcategoryId = 'Contents_Contents';
        $this->onlineGuideUrl = 'https://matomo.org/docs/content-tracking/';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widget = $factory->createWidget();

        $widgetsList->addToContainerWidget('Contents', $widget);
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->datatable_js_type   = 'ContentsDataTable';
        $view->config->datatable_css_class = 'ContentsDataTable';
        $view->config->show_table_all_columns = false;

        if (!empty($this->dimension)) {
            $view->config->addTranslations(array('label' => $this->dimension->getName()));
        }

        $view->config->columns_to_display = array_merge(
            array('label'),
            array_keys($this->getMetrics()),
            array_keys($this->getProcessedMetrics())
        );

        if (property_exists($view->config, 'selectable_columns')) {
            $view->config->selectable_columns = $this->metrics;
        }

        $view->requestConfig->filter_sort_column = 'nb_impressions';

        if ($this->hasSubtableId()) {
            $apiMethod = $view->requestConfig->getApiMethodToRequest();
            $label     = Dimensions::getSubtableLabelForApiMethod($apiMethod);
            $view->config->addTranslation('label', Piwik::translate($label));
        }
    }

    private function hasSubtableId()
    {
        $subtable = Common::getRequestVar('idSubtable', false, 'integer');

        return !empty($subtable);
    }
}
