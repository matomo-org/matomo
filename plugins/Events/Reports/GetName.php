<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Events\Columns\EventName;

class GetName extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new EventName();
        $this->name          = Piwik::translate('Events_EventNames');
        $this->documentation = Piwik::translate('Events_EventNamesReportDocumentation');
        $this->metrics       = array('nb_events', 'sum_event_value', 'min_event_value', 'max_event_value', 'nb_events_with_value');
        if (Common::getRequestVar('secondaryDimension', false) == 'eventCategory') {
            $this->actionToLoadSubTables = 'getCategoryFromNameId';
        } else {
            $this->actionToLoadSubTables = 'getActionFromNameId';
        }
        $this->order = 2;
    }
}
