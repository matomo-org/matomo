<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDimensions\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\CoreHome\Columns\UserId;

class RemoveUserIfNeeded extends BaseFilter
{
    private $idSite;
    private $period;
    private $date;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table, $idSite, $period, $date)
    {
        parent::__construct($table);
        $this->idSite = $idSite;
        $this->period = $period;
        $this->date   = $date;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $userId = new UserId();
        if (!$userId->hasDataTableUsers($table) &&
            !$userId->isUsedInAtLeastOneSite(array($this->idSite), $this->period, $this->date)) {
            $table->deleteColumn(Metrics::INDEX_NB_USERS);
        }
    }
}