<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

/**
 * The consent decision that applied when this action was tracked.
 *
 * Consent belongs here rather than on the visit, because each request carries its own decision and
 * a visitor can consent part way through a visit. {@link Consent} summarises these onto the visit
 * so that segments and report filters do not have to join to this table.
 *
 *   null  no consent signal was sent with the request
 *   0     the request said "not consented"
 *   1     the request said "consented"
 *
 * The value records what was decided, not what was collected. What the tracker actually did with
 * the action is already reflected in the action's other columns.
 */
class ActionConsent extends ActionDimension
{
    public const COLUMN_TYPE = 'TINYINT(1) UNSIGNED NULL';

    protected $columnName = 'consent';
    protected $columnType = self::COLUMN_TYPE;

    /**
     * @return int|false the decision to store, or false to leave the column alone
     */
    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        $consent = $request->getConsent();

        if (null === $consent) {
            return false;
        }

        return $consent;
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        // a sum or average over a consent flag is not meaningful
    }
}
