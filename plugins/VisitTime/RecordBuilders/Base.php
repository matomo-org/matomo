<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitTime\RecordBuilders;

use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\DataTable;
use Piwik\Metrics;

abstract class Base extends RecordBuilder
{
    protected function ensureAllHoursAreSet(DataTable $record): void
    {
        $emptyRow = [
            Metrics::INDEX_NB_UNIQ_VISITORS => 0,
            Metrics::INDEX_NB_VISITS => 0,
            Metrics::INDEX_NB_ACTIONS => 0,
            Metrics::INDEX_NB_USERS => 0,
            Metrics::INDEX_MAX_ACTIONS => 0,
            Metrics::INDEX_SUM_VISIT_LENGTH => 0,
            Metrics::INDEX_BOUNCE_COUNT => 0,
            Metrics::INDEX_NB_VISITS_CONVERTED => 0,
        ];

        for ($i = 0; $i <= 23; $i++) {
            if (!$record->getRowFromLabel($i)) {
                $record->addRowFromSimpleArray(['label' => $i] + $emptyRow);
            }
        }
    }
}
