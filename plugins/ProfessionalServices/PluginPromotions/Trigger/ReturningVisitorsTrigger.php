<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\DataTable;

/**
 * Triggers when enough visitors are coming back for groups of them to be worth comparing
 * over time.
 */
class ReturningVisitorsTrigger extends ReportBackedTrigger
{
    public const NAME = 'returning_visitors';

    public const MINIMUM_RETURNING_VISITORS = 500;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getRequiredArchives(): array
    {
        return ['VisitFrequency'];
    }

    protected function getApiMethod(): string
    {
        return 'VisitFrequency.get';
    }

    protected function deriveContext(DataTable $report): ?array
    {
        // A summary rather than a list, so everything is on one row.
        $row = $report->getFirstRow();

        if (empty($row)) {
            return null;
        }

        // Distinct visitors rather than visits: the promotion compares groups of people
        // over time and its copy says "returning visitors", so someone coming back three
        // times is one of them and not three.
        $returning = (int) $row->getColumn('nb_uniq_visitors_returning');

        return $returning < self::MINIMUM_RETURNING_VISITORS ? null : ['count' => $returning];
    }
}
