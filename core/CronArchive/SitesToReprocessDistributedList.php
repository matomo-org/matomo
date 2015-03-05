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
 * Keeps track of which reports were invalidated via CoreAdminHome.invalidateArchivedReports API.
 *
 * This is used by:
 *
 * 1. core:archive command to know which websites should be reprocessed
 *
 * 2. scheduled task purgeInvalidatedArchives to know which websites/months should be purged
 *
 * TODO: modify
 */
class SitesToReprocessDistributedList extends DistributedList
{
    const OPTION_INVALIDATED_IDSITES_TO_REPROCESS = 'InvalidatedOldReports_WebsiteIds';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(self::OPTION_INVALIDATED_IDSITES_TO_REPROCESS);
    }

    /**
     * @inheritdoc
     */
    public function setAll($items)
    {
        $items = array_unique($items);
        $items = array_values($items);

        parent::setAll($items);
    }
}