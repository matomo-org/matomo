<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\DevicesDetection\Columns\DeviceType;
use DeviceDetector\Parser\Device\DeviceParserAbstract as DeviceParser;

class GetType extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new DeviceType();
        $this->name          = Piwik::translate('DevicesDetection_DeviceType');
        $this->documentation = ''; // TODO
        $this->order = 0;
        $this->widgetTitle  = 'DevicesDetection_DeviceType';
    }

    public function configureView(ViewDataTable $view)
    {
        $unknownTypeCount = 1;
        $view->requestConfig->filter_limit = $unknownTypeCount + count(DeviceParser::getAvailableDeviceTypeNames());
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("DevicesDetection_dataTableLabelTypes"));
    }

}
