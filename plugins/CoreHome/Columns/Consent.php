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
 * Whether the visitor had given their consent during this visit.
 *
 * Consent is reported per request, but it describes the visitor rather than the request, so it is
 * recorded once for the visit and kept. A visit with no value never reported consent, which is not
 * the same as refusing it: a site that does not signal consent at all looks exactly the same.
 */
class Consent extends VisitDimension
{
    public const COLUMN_TYPE = 'TINYINT(1) UNSIGNED NULL';
    public const CONSENT_GIVEN = 1;

    protected $columnName = 'consent';
    protected $columnType = self::COLUMN_TYPE;

    /**
     * @param Action|null $action
     * @return int|false
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $request->hasConsent() ? self::CONSENT_GIVEN : false;
    }

    /**
     * @param Action|null $action
     * @return int|false
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        if (!$request->hasConsent() || self::hasVisitConsented($visitor)) {
            return false;
        }

        // consent given part way through a visit applies to the visit, so later requests do not
        // have to repeat the flag for it to be known
        return self::CONSENT_GIVEN;
    }

    /**
     * Whether consent was already recorded for this visit, as stored before the current request.
     */
    public static function hasVisitConsented(Visitor $visitor): bool
    {
        return self::CONSENT_GIVEN === (int) $visitor->getImmutableVisitorColumn('consent');
    }
}
