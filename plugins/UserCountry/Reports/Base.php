<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\UserCountry\UserCountry;
use Piwik\Url;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->categoryId = 'General_Visitors';
    }

    protected function getGeoIPReportDocSuffix()
    {
        return Piwik::translate('UserCountry_GeoIPDocumentationSuffix',
            array('<a rel="noreferrer noopener" target="_blank" href="http://www.maxmind.com/?rId=piwik">',
                '</a>',
                '<a rel="noreferrer noopener" target="_blank" href="http://www.maxmind.com/en/city_accuracy?rId=piwik">',
                '</a>')
        );
    }

    /**
     * Checks if a datatable for a view is empty and if so, displays a message in the footer
     * telling users to configure GeoIP.
     */
    protected function checkIfNoDataForGeoIpReport(ViewDataTable $view)
    {
        $view->config->filters[] = function ($dataTable) use ($view) {
            // if there's only one row whose label is 'Unknown', display a message saying there's no data
            if ($dataTable->getRowsCount() == 1
                && $dataTable->getFirstRow()->getColumn('label') == Piwik::translate('General_Unknown')
            ) {
                $footerMessage = Piwik::translate('UserCountry_NoDataForGeoIPReport1');

                $userCountry = new UserCountry();
                // if GeoIP is working, don't display this part of the message
                if (!$userCountry->isGeoIPWorking()) {
                    $params = array('module' => 'UserCountry', 'action' => 'adminIndex');
                    $footerMessage .= ' ' . Piwik::translate('UserCountry_NoDataForGeoIPReport2',
                            array('<a target="_blank" href="' . Url::getCurrentQueryStringWithParametersModified($params) . '">',
                                '</a>',
                                '<a rel="noreferrer noopener" target="_blank" href="https://db-ip.com/?refid=mtm">',
                                '</a>'));
                } else {
                    $footerMessage .= ' ' . Piwik::translate('UserCountry_ToGeolocateOldVisits',
                            array('<a rel="noreferrer noopener" target="_blank" href="https://matomo.org/faq/how-to/faq_167">', '</a>'));
                }

                $view->config->show_footer_message = $footerMessage;
            }
        };
    }
}
