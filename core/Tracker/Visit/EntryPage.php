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
 * For use while tracking a request, by core and by plugins that record their own dimensions and
 * need the same answer core decides on. Resolving it has the side effect noted below.
 */
class EntryPage
{
    /**
     * Where the answer is kept for the rest of the request, under the CoreHome namespace.
     */
    private const REQUEST_METADATA = 'isRequestOnEntryPage';

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
        // The action a ping carries is dropped part way through the request, so a later caller
        // would have nothing left to place the visitor by and would fall back to the interaction
        // count - which, for a page the visit never recorded, reads as the arrival page. The
        // answer cannot change within a request, so the one reached while the action was still
        // there is the one everybody gets.
        $answered = $request === null ? null : $request->getMetadata('CoreHome', self::REQUEST_METADATA);

        if (is_array($answered)) {
            return $answered[0];
        }

        $answer = self::determineForRequest($visitor, $action, $request);

        if ($request !== null && !empty($action)) {
            $request->setMetadata('CoreHome', self::REQUEST_METADATA, [$answer]);
        }

        return $answer;
    }

    /**
     * @param Action|null $action
     */
    private static function determineForRequest(Visitor $visitor, $action, ?Request $request): ?bool
    {
        // The immutable value is the visit as stored, before this request changes it. Interactions
        // count page views and site searches and nothing else, so an event recorded on the landing
        // page - a consent banner impression, say - does not close the window, and they only ever
        // rise, so returning to the page the visit arrived on does not reopen it.
        $interactions = (int) $visitor->getImmutableVisitorColumn('visit_total_interactions');

        // A ping records no page, so it leaves the visitor wherever the visit already was - but
        // it still carries an action here, dropped later in the request, and that says which
        // page it was sent from. Where that page can be read it is compared like any other;
        // where it cannot, the answer is unknown rather than assumed from the count alone.
        $isPing = $request !== null && $request->getParam('ping') == 1;

        if (empty($action)) {
            return 1 === $interactions ? true : null;
        }

        if ($interactions > 1) {
            // a ping cannot start a visit of its own, so it never answers that one should
            return $isPing ? null : false;
        }

        // the ids of the current action are resolved a few statements later, when it is recorded.
        // The lookup is cached on the action, so asking for them here costs the same queries only
        // sooner, and nothing in between changes the action they are derived from.
        $action->loadIdsFromLogActionTable();
        $idActionUrl = $action->getIdActionUrlForEntryAndExitIds();

        if (empty($idActionUrl)) {
            // A ping names the page it was sent from, so one whose page cannot be placed is a
            // page this visit has no record of - the visitor is somewhere it has not seen, and
            // the answer is unknown rather than the arrival page by default.
            if ($isPing) {
                return null;
            }

            // an event, download or outlink: it records no page of its own, so it does not move
            // the visitor and says nothing about where they are beyond what the count already did
            return 1 === $interactions ? true : null;
        }

        $entryIdActionUrl = (int) $visitor->getImmutableVisitorColumn('visit_entry_idaction_url');

        if (empty($entryIdActionUrl)) {
            return null;
        }

        if ($entryIdActionUrl === (int) $idActionUrl) {
            return true;
        }

        return $isPing ? null : false;
    }
}
