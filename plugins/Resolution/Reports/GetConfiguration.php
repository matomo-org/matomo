<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Resolution\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Resolution\Columns\Configuration;

class GetConfiguration extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Configuration();
        $this->name          = Piwik::translate('Resolution_WidgetGlobalVisitors');
        $this->documentation = Piwik::translate('Resolution_WidgetGlobalVisitorsDocumentation', '<br />');
        $this->order = 7;
        $this->widgetTitle  = 'Resolution_WidgetGlobalVisitors';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicResolutionDisplayProperties($view);

        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_limit = 3;
    }

    public function getRelatedReports()
    {
        return array(
            self::factory('Resolution', 'getResolution'),
        );
    }
}
