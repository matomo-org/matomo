<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Provider\Columns\Provider;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class GetProvider extends Report
{
    protected function init()
    {
        $this->categoryId      = 'General_Visitors';
        $this->dimension     = new Provider();
        $this->name          = Piwik::translate('Provider_ColumnProvider');
        $this->documentation = Piwik::translate('Provider_ProviderReportDocumentation', '<br />');
        $this->order = 50;

        $this->subcategoryId = 'UserCountry_SubmenuLocations';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widget = $factory->createWidget()->setName('Provider_WidgetProviders');
        $widgetsList->addWidgetConfig($widget);
    }

    public function configureView(ViewDataTable $view)
    {
        $view->requestConfig->filter_limit = 5;
        $view->config->addTranslation('label', $this->dimension->getName());

        $message = Piwik::translate("General_Note") . ': ' . Piwik::translate('Provider_ProviderReportFooter', '');
        if (! Common::getRequestVar('disableLink', 0, 'int')) {
            $message .= ' ' . Piwik::translate(
                    'General_SeeThisFaq',
                    array('<a href="https://matomo.org/faq/general/faq_52/" rel="noreferrer"  target="_blank">', '</a>')
                );
        }
        $view->config->show_footer_message = $message;
    }
}
