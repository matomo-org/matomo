<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Provider\Columns\Provider;

class GetProvider extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category      = 'General_Visitors';
        $this->dimension     = new Provider();
        $this->name          = Piwik::translate('Provider_ColumnProvider');
        $this->documentation = Piwik::translate('Provider_ProviderReportDocumentation', '<br />');
        $this->order = 50;
        $this->widgetTitle  = 'Provider_WidgetProviders';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->requestConfig->filter_limit = 5;
        $view->config->addTranslation('label', $this->dimension->getName());
    }

}
