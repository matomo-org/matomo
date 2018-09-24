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

use Piwik\Plugins\Transitions\Controller;
use Piwik\Plugins\Transitions\Visualizations\Transitions;
use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetPageUrlTransitions extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('Transitions_PageURLTransitions');
        $this->dimension     = null;
        $this->documentation = Piwik::translate('');
        $this->order = 1;
        $this->subcategoryId = 'Transitions';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->requestConfig->apiMethodToRequestDataTable = 'Actions.getPageUrls';
    }

    public function getDefaultTypeViewDataTable()
    {
        return Transitions::ID;
    }

    public function alwaysUseDefaultViewDataTable()
    {
        return true;
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('Transitions', 'getPageTitleTransitions'),
        );
    }
/*
    public function render()
    {
        $view = new View('@Transitions/getPageURLTransitions');
        $view->translations = Controller::getTranslations();
        return $view->render();
    }*/
}
