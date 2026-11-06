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
use Piwik\Tracker\Model;
use Piwik\Tracker\PageUrl;
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
     * @param Request|null $request a request that records no page of its own is placed by the
     *                              page it names, which is read from here, as is the ping check,
     *                              so omitting it leaves both unanswered - pass it wherever it
     *                              is available
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

        // A ping records no page of its own, so it leaves the visitor wherever the visit already was
        $isPing = $request !== null && $request->getParam('ping') == 1;

        // Only an action that records a page of its own moves the visitor to it, and only those
        // answer this with anything other than false - the same rule that decides whether the
        // visit's entry and exit ids follow the action at all. A manual goal conversion records
        // no action whatsoever and so moves nobody either.
        $movesTheVisitor = !empty($action)
            && !$isPing
            && false !== $action->getIdActionUrlForEntryAndExitIds();

        if ($interactions > 1) {
            // Nothing that leaves the visitor where they are can start a visit of its own, so it
            // never answers that one should. For a ping that is not merely a question of the
            // visit being wrong: the request is abandoned once it turns out to be a new visit, so
            // the ping would be dropped rather than recorded at all.
            return $movesTheVisitor ? false : null;
        }

        $entryIdActionUrl = (int) $visitor->getImmutableVisitorColumn('visit_entry_idaction_url');

        if (empty($entryIdActionUrl)) {
            return null;
        }

        if ($movesTheVisitor) {
            // the ids of the current action are resolved a few statements later, when it is
            // recorded. The lookup is cached on the action, so asking for them here costs the same
            // queries only sooner, and nothing in between changes the action they are derived from.
            $action->loadIdsFromLogActionTable();

            $idActionUrl = (int) $action->getIdActionUrlForEntryAndExitIds();

            if (!empty($idActionUrl)) {
                return $entryIdActionUrl === $idActionUrl;
            }

            // a site search deliberately records no page of its own, so it is placed by the page
            // it names, like everything else below
        }

        // What is left names no page that can be compared against the one the visit arrived on: a
        // download names the file, an outlink the link, an event its page but recorded as an event
        // rather than as a page, and a ping, a site search or a manual goal conversion no page at
        // all. Each still says which page it was sent from, so that is read from the request and
        // looked up as a page view would have stored it.
        $idActionUrl = $request === null ? null : self::lookUpPageIdActionUrl($request);

        if (null === $idActionUrl) {
            // this site has never recorded that page, so the visitor cannot be placed by it
            return null;
        }

        // nothing reaching here could have started a visit of its own either, so it never answers
        // that one should
        return $entryIdActionUrl === $idActionUrl ? true : null;
    }

    /**
     * Looks up the page the request was sent from, as a page view would have recorded it.
     *
     * Deliberately a lookup and not a write: a page this site has never recorded is reported as
     * unknown rather than created, so that answering this question cannot add rows of its own.
     * Reached only by a request that records no page of its own, and only while the visit is
     * still on its first interaction, so what it costs does not grow with the length of a visit.
     *
     * @return int|null null where the page is not one this site has recorded
     */
    private static function lookUpPageIdActionUrl(Request $request): ?int
    {
        $url = $request->getParam('url');

        if (empty($url)) {
            return null;
        }

        // the same cleaning a page view applies before storing the URL, so that what is looked up
        // here is the name a page view would have written
        $url = PageUrl::excludeQueryParametersFromUrl($url, $request->getIdSite());
        $url = PageUrl::getUrlIfLookValid($url);

        if (empty($url)) {
            return null;
        }

        $url = PageUrl::normalizeUrl($url);

        $rows = (new Model())->getIdsAction([
            ['name' => $url['url'], 'type' => Action::TYPE_PAGE_URL],
        ]);

        if (empty($rows)) {
            return null;
        }

        foreach ($rows as $row) {
            if ((int) $row['type'] === Action::TYPE_PAGE_URL) {
                return (int) $row['idaction'];
            }
        }

        return null;
    }
}
