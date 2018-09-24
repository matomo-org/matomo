<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Transitions\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugin\ViewDataTable;

use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetPageTitleTransitions extends GetPageUrlTransitions
{
    protected function init()
    {
        parent::init();

        $this->order = 2;
        $this->name = Piwik::translate('Transitions_PageTitleTransitions');
    }

    public function configureView(ViewDataTable $view)
    {
        parent::configureView($view);
        $view->requestConfig->apiMethodToRequestDataTable = 'Actions.getPageTitles';
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('Transitions', 'getPageUrlTransitions'),
        );
    }

}
