<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

/**
 * The consent state of a visit, summarised from the consent recorded on its actions.
 *
 * Consent belongs to the action, because each request carries its own decision - see
 * {@link ActionConsent}. This column exists so that segments and report filters can read a visit's
 * consent without joining to the much larger action table.
 *
 *   null  no consent signal was sent for this visit
 *   0     a signal was sent and said "not consented"
 *   1     every action of the visit is covered by consent
 *   2     consent was granted part way through, so some actions predate it
 *
 * null and 0 are stored apart only because writing nothing is the natural no-op for a site that
 * does not use consent at all; every read path treats them the same.
 */
class Consent extends VisitDimension
{
    public const COLUMN_TYPE = 'TINYINT(1) UNSIGNED NULL';

    public const NOT_CONSENTED = 0;
    public const CONSENTED = 1;
    public const MIXED = 2;

    protected $columnName = 'consent';
    protected $columnType = self::COLUMN_TYPE;

    /**
     * @param Action|null $action
     * @return int|false the state to store, or false to leave the column alone
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $consent = $request->getConsent();

        if (null === $consent) {
            return false;
        }

        return $consent;
    }

    /**
     * Folds this action's consent into the visit's summary.
     *
     * The summary only ever moves forwards. Once a visit has seen consent it keeps it, so a
     * withdrawal part way through does not make the visit claim that its consented actions never
     * consented. Recording the withdrawal itself is out of scope - the per-action values still
     * hold the detail.
     *
     * @param Action|null $action
     * @return int|false the state to store, or false to leave the column alone
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $consent = $request->getConsent();

        if (null === $consent) {
            return false;
        }

        $current = $visitor->getVisitorColumn($this->columnName);
        $current = (null === $current || false === $current) ? null : (int) $current;

        if (self::MIXED === $current) {
            return false;
        }

        if (self::CONSENTED === $consent) {
            // consent arriving on a visit that had not consented means the earlier actions of this
            // visit predate it, which is exactly what MIXED records
            return (self::CONSENTED === $current) ? false : self::MIXED;
        }

        // a later non-consented action never downgrades a visit that has already consented
        if (self::CONSENTED === $current) {
            return false;
        }

        return (self::NOT_CONSENTED === $current) ? false : self::NOT_CONSENTED;
    }
}
