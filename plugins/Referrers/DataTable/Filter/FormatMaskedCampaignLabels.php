<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Referrers\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\Plugins\PrivacyManager\Settings\CampaignParameterValuesMasked;

/**
 * Renders campaign values that a compliance policy discarded.
 *
 * Discarded values are stored as an internal placeholder so that "discarded" stays distinguishable
 * from "no campaign". The placeholder is an implementation detail and must never reach a consumer:
 * Live, the visitor details and the GDPR export already run values through
 * CampaignParameterValuesMasked::formatValue(), and the campaign reports have to do the same, so
 * that the reporting API and the UI give the same answer.
 */
class FormatMaskedCampaignLabels extends BaseFilter
{
    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label');

            if (false !== $label) {
                $row->setColumn('label', CampaignParameterValuesMasked::formatValue($label));
            }

            $subtable = $row->getSubtable();
            if (!empty($subtable)) {
                $subtable->filter(static::class);
            }

            $comparisons = $row->getComparisons();
            if (!empty($comparisons)) {
                $comparisons->filter(static::class);
            }
        }

        $table->setLabelsHaveChanged();
    }
}
