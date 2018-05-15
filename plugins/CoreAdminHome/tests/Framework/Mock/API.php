<?php
/**
* Piwik - free/libre analytics platform
*
* @link http://piwik.org
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

namespace Piwik\Plugins\CoreAdminHome\tests\Framework\Mock;

use Piwik\Tracker;

class API extends \Piwik\Plugins\CoreAdminHome\API
{
    private $invalidatedReports = array();

    public function invalidateArchivedReports($idSites, $dates, $period = false, $segment = false, $cascadeDown = false)
    {
        $this->invalidatedReports[] = func_get_args();
    }

    public function getInvalidatedReports()
    {
        return $this->invalidatedReports;
    }
}
