<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Reports;

use Piwik\Piwik;
use Piwik\Plugins\Events\Columns\EventName;

/**
 * Report metadata class for the Events.getNameFromActionId class.
 */
class GetNameFromActionId extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new EventName();
        $this->name          = Piwik::translate('Events_Names');
        $this->isSubtableReport = true;
    }
}