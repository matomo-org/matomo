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
use Piwik\Plugins\Resolution\Columns\Resolution;

class GetResolution extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Resolution();
        $this->name          = Piwik::translate('Resolution_WidgetResolutions');
        $this->documentation = ''; // TODO
        $this->order = 0;
        $this->widgetTitle  = 'Resolution_WidgetResolutions';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicResolutionDisplayProperties($view);

        $view->config->addTranslation('label', $this->dimension->getName());
    }

    public function getRelatedReports()
    {
        return array(
            self::factory('Resolution', 'getConfiguration'),
        );
    }
}
