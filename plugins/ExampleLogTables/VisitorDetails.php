<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables;

use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\View;

/**
 * Surfaces the plugin's own stored attributes in the visits log and the visitor profile.
 *
 * Matomo finds this class by its filename: `VisitorDetails.php` in the plugin root, extending
 * `Live\VisitorDetailsAbstract`. Nothing registers it, and nothing in the plugin's own code calls it
 * -- Live does. This is the opposite relationship to reading another plugin's data, which goes
 * through `Request::processRequest()`.
 *
 * It takes two methods, and they do different jobs:
 *
 * - `extendVisitorDetails()` adds the values to the Live **API payload**. Nothing in the visits log
 *   or the visitor profile renders that payload -- but it is not invisible either: segment value
 *   suggestions are read out of it *by segment name*, so `userPlan` here is the same string as the
 *   dimension's `$segmentName`, and that alone is what lets the segment editor suggest values.
 * - `renderVisitorDetails()` is what puts them in the **visits log UI** and, through the same block,
 *   in the per-visit list of the visitor profile: a block of HTML with a sort order. Implement one and
 *   not the other and the feature looks half-finished in whichever place you did not look.
 *
 * The values shown here are the ones this plugin collected about an identified user, so they are
 * personal data appearing in visitor-level output. They belong in a subject-access export and must
 * disappear when that subject is deleted, which is what `Tracker/LogTable/` takes care of.
 *
 * **Access control is Live's, with one exception worth knowing before you publish a key.** Every
 * entry point checks view access, and `Live\API` enforces the visits-log and visitor-profile site
 * settings -- except when the root API request is `API.getSuggestedValuesForSegment`, where that check
 * is deliberately skipped. `extendVisitorDetails()` still runs there, so whatever key it publishes is
 * readable as a segment suggestion even for a site whose visits log is switched off. A plan label is
 * fine; publish only what you are willing to have surface that way.
 */
class VisitorDetails extends VisitorDetailsAbstract
{
    /**
     * Attributes already looked up, keyed by user id.
     *
     * Live builds these instances with `new`, not through the container, so there is nothing to
     * inject a DAO through -- but it keeps one instance for the whole PHP process in its transient
     * cache and calls `setDetails()` on it once per visit. So the lookup key is re-derived per visit
     * while the cache survives, which turns a visits log of a hundred visits by five users into five
     * queries. It also means the cache never notices a row that changed after it was read: cache what
     * you only read, never what you also write.
     *
     * @var array<string, array<string, string>>
     */
    private array $attributesByUserId = [];

    /**
     * @param array<string, mixed> $visitor
     */
    public function extendVisitorDetails(&$visitor)
    {
        $attributes = $this->getAttributes($this->details['user_id'] ?? '');

        if (empty($attributes)) {
            return;
        }

        // Publish only what is actually stored. A key present but empty is indistinguishable from a
        // plugin that has an opinion and says "nothing", and it is the same restraint the write path
        // applies one layer down.
        if (!empty($attributes['plan'])) {
            // Same key as UserAttributePlan::$segmentName, deliberately.
            $visitor['userPlan'] = $attributes['plan'];
        }

        if (!empty($attributes['account_name'])) {
            // No segment declares this one, so the name is only a payload key.
            $visitor['userAccount'] = $attributes['account_name'];
        }
    }

    /**
     * @param \ArrayAccess<string, mixed>|array<string, mixed> $visitorDetails The visits log passes a
     *        `DataTable\Row`, not an array -- it happens to work because `Row` extends `ArrayObject`.
     *        Read it through array access only, and do not narrow this to `array`.
     * @return array<array{int, string}> sort order and rendered HTML, as Live expects
     */
    public function renderVisitorDetails($visitorDetails)
    {
        if (empty($visitorDetails['userPlan']) && empty($visitorDetails['userAccount'])) {
            return [];
        }

        $view = new View('@ExampleLogTables/_visitorDetails');
        $view->sendHeadersWhenRendering = false;
        $view->plan = $visitorDetails['userPlan'] ?? '';
        $view->account = $visitorDetails['userAccount'] ?? '';

        // The first element orders this block among every plugin's visits log block, and nothing
        // allocates it. Occupied today: 0 (Live), 10 (Referrers), 20 (Provider), 30
        // (MarketingCampaignsReporting), 40 (CustomDimensions), 50 (CustomVariables) -- the last
        // three ship separately from core. Survey with care: these numbers are per sink, and
        // `renderActionTooltip()` has a different set of its own, so a number that looks taken may
        // not be. Ties keep plugin load order rather than being arbitrary, but do not rely on that;
        // pick a gap and check it is still one when you upgrade.
        return [[45, $view->render()]];
    }

    /**
     * @return array<string, string>
     */
    private function getAttributes(mixed $userId): array
    {
        if (empty($userId) || !is_string($userId)) {
            return [];
        }

        if (!array_key_exists($userId, $this->attributesByUserId)) {
            $this->attributesByUserId[$userId] = (new CustomUserLog())->getUserInformation($userId);
        }

        return $this->attributesByUserId[$userId];
    }
}
