<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tracker\Visit;

use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

/**
 * Whether a tracking request is still on the page the visit arrived on.
 *
 * Several things need this and cannot be decided on a guess, so the answer is deliberately three
 * valued: true, false, or null where it cannot be told.
 *
 * For use while tracking a request. It is deliberately not part of the public API: the shape of
 * the answer is still settling, and resolving it has the side effect noted below.
 */
class EntryPage
{
    /**
     * Resolves the current action's ids when it records a page, a few statements earlier than the
     * request would otherwise, so calling this is not free of side effects.
     *
     * @param Action|null $action the action this request records, if it records one
     * @param Request|null $request omitting it skips the ping check, which changes the answer for
     *                              a ping, so pass it wherever it is available
     * @return bool|null null where the answer cannot be determined
     */
    public static function isRequestOnEntryPage(Visitor $visitor, $action, ?Request $request = null): ?bool
    {
        // The immutable value is the visit as stored, before this request changes it. Interactions
        // count page views and site searches and nothing else, so an event recorded on the landing
        // page - a consent banner impression, say - does not close the window, and they only ever
        // rise, so returning to the page the visit arrived on does not reopen it.
        $interactions = (int) $visitor->getImmutableVisitorColumn('visit_total_interactions');

        // a ping still carries an action at this point - it is dropped later in the request - and
        // records none of it, so it leaves the visitor wherever the visit already was
        if (empty($action) || ($request !== null && $request->getParam('ping') == 1)) {
            return 1 === $interactions ? true : null;
        }

        if ($interactions > 1) {
            return false;
        }

        // the ids of the current action are resolved a few statements later, when it is recorded.
        // The lookup is cached on the action, so asking for them here costs the same queries only
        // sooner, and nothing in between changes the action they are derived from.
        $action->loadIdsFromLogActionTable();
        $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();

        if (empty($idActionUrl)) {
            // an event, download or outlink: it records no page of its own, so like a ping it does
            // not move the visitor
            return 1 === $interactions ? true : null;
        }

        $entryIdActionUrl = (int) $visitor->getImmutableVisitorColumn('visit_entry_idaction_url');

        if (empty($entryIdActionUrl)) {
            return null;
        }

        return $entryIdActionUrl === (int) $idActionUrl;
    }
}
