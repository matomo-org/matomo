<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\CronArchive;

use Piwik\Concurrency\DistributedList;

/**
 * Distributed list that stores the list of IDs of sites whose archives should be reprocessed.
 *
 * CronArchive will read this list of sites when archiving is being run, and make sure the sites
 * are re-archived.
 *
 * Any class/API method/command/etc. is allowed to add site IDs to this list.
 */
class SitesToReprocessDistributedList extends DistributedList
{
    const OPTION_INVALIDATED_IDSITES_TO_REPROCESS = 'InvalidatedOldReports_WebsiteIds';

    public function __construct()
    {
        parent::__construct(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS);
    }

    /**
     * @inheritdoc
     */
    public function setAll($items)
    {
        $items = array_unique($items, SORT_REGULAR);
        $items = array_values($items);

        parent::setAll($items);
    }
}
