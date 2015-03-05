<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\Tasks;

use Piwik\Concurrency\DistributedList;
use Piwik\Option;

/**
 * TODO
 */
class ArchivesToPurgeDistributedList extends DistributedList
{
    const OPTION_INVALIDATED_DATES_SITES_TO_PURGE = 'InvalidatedOldReports_DatesWebsiteIds';

    /**
     * TODO
     */
    public function __construct()
    {
        parent::__construct(self::OPTION_INVALIDATED_DATES_SITES_TO_PURGE);
    }

    /**
     * TODO
     *
     * @param array $yearMonths
     */
    public function setAll($yearMonths)
    {
        $yearMonths = array_unique($yearMonths);
        parent::setAll($yearMonths);
    }
}