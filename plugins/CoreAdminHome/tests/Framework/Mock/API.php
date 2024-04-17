<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Framework\Mock;

class API extends \Piwik\Plugins\CoreAdminHome\API
{
    private $invalidatedReports = array();

    public function invalidateArchivedReports(
        $idSites,
        $dates,
        $period = false,
        $segment = false,
        $cascadeDown = false,
        $_forceInvalidateNonexistent = false
    ) {
        $this->invalidatedReports[] = func_get_args();
    }

    public function getInvalidatedReports()
    {
        return $this->invalidatedReports;
    }
}
