<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Reports;

use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Contents\Dimensions;

abstract class Base extends Report
{
    protected function init()
    {
        $this->category = 'General_Actions';
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

        if (!empty($this->dimension)) {
            $view->config->addTranslations(array('label' => $this->dimension->getName()));
        }

        $view->config->columns_to_display = array_merge(
            array('label'),
            array_keys($this->getMetrics()),
            array_keys($this->getProcessedMetrics())
        );

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
