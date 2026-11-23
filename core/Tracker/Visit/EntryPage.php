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
        // The answer cannot change within a request, and a dozen dimensions ask for it, so it is
        // reached once and kept. Keeping it also settles the ping: the action a ping carries is
        // dropped part way through the request, so the answer reached while it was still there is
        // the one every later caller gets.
        $answered = $request === null ? null : $request->getMetadata('CoreHome', self::REQUEST_METADATA);

        if (is_array($answered)) {
            return $answered[0];
        }

        $answer = self::determineForRequest($visitor, $action, $request);

        if ($request !== null) {
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
            // A visit can begin with something that records no page of its own - an event sent
            // before the first page view, say - and has no entry page until one arrives. The page
            // view arriving now becomes it, so the visitor is on it by definition. Anything that
            // records no page still leaves the question unanswerable.
            if ($movesTheVisitor && $interactions < 1) {
                return true;
            }

            return null;
        }

        if ($movesTheVisitor) {
            // the ids of the current action are resolved a few statements later, when it is
            // recorded. The lookup is cached on the action, so asking for them here costs the same
            // queries only sooner, and nothing in between changes the action they are derived from.
            $action->loadIdsFromLogActionTable();

            $idActionUrl = (int) $action->getIdActionUrlForEntryAndExitIds();

            if (!empty($idActionUrl)) {
                if ($entryIdActionUrl === $idActionUrl) {
                    return true;
                }

                // A visitor's consent keeps URL parameters a compliance policy strips, so the page
                // the visit arrived on may be stored under the other of its two names - before
                // consent was given, say. It is still the same page.
                if (null === $request) {
                    return false;
                }

                $pageNames = self::getPageNames($request);

                return count($pageNames) > 1
                    && in_array($entryIdActionUrl, self::lookUpPageIdActionUrls($pageNames), true);
            }

            // a site search deliberately records no page of its own, so it is placed by the page
            // it names, like everything else below
        }

        // What is left names no page that can be compared against the one the visit arrived on: a
        // download names the file, an outlink the link, an event its page but recorded as an event
        // rather than as a page, and a ping, a site search or a manual goal conversion no page at
        // all. Each still says which page it was sent from, so that is read from the request and
        // looked up as a page view would have stored it.
        $idActionUrls = $request === null ? [] : self::lookUpPageIdActionUrls(self::getPageNames($request));

        // nothing reaching here could have started a visit of its own either, so it never answers
        // that one should. Where this site has never recorded that page, the visitor cannot be
        // placed by it.
        return in_array($entryIdActionUrl, $idActionUrls, true) ? true : null;
    }

    /**
     * Looks up the page the request was sent from, as a page view would have recorded it.
     *
     * Deliberately a lookup and not a write: a page this site has never recorded is reported as
     * unknown rather than created, so that answering this question cannot add rows of its own.
     * Reached only while the visit is still on its first interaction, so what it costs does not
     * grow with the length of a visit.
     *
     * @param string[] $pageNames the names the page can be recorded under, see getPageNames()
     * @return int[] the ids the page is recorded under, empty where this site has not recorded it
     */
    private static function lookUpPageIdActionUrls(array $pageNames): array
    {
        $names = [];
        foreach ($pageNames as $name) {
            $names[] = ['name' => $name, 'type' => Action::TYPE_PAGE_URL];
        }

        $rows = empty($names) ? false : (new Model())->getIdsAction($names);

        if (empty($rows)) {
            return [];
        }

        $idActionUrls = [];

        foreach ($rows as $row) {
            if ((int) $row['type'] === Action::TYPE_PAGE_URL) {
                $idActionUrls[] = (int) $row['idaction'];
            }
        }

        return $idActionUrls;
    }

    /**
     * The names a page view of the page the request was sent from is recorded under.
     *
     * A page view with the visitor's consent keeps URL parameters a compliance policy strips from
     * one without it, so the same page can have been recorded under either of two names. Where the
     * two are the same - wherever no policy strips anything consent lifts - there is one.
     *
     * @return string[]
     */
    private static function getPageNames(Request $request): array
    {
        $url = $request->getParam('url');

        if (empty($url)) {
            return [];
        }

        $names = [];

        foreach ([false, true] as $hasConsent) {
            // the same cleaning a page view applies before storing the URL, so that what is looked
            // up here is the name a page view would have written
            $cleanUrl = PageUrl::excludeQueryParametersFromUrl($url, $request->getIdSite(), [], $hasConsent);
            $cleanUrl = PageUrl::getUrlIfLookValid($cleanUrl);

            if (!empty($cleanUrl)) {
                $names[] = PageUrl::normalizeUrl($cleanUrl)['url'];
            }
        }

        return array_values(array_unique($names));
    }
}
