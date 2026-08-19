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
 * - `extendVisitorDetails()` adds the values to the Live **API payload**. On its own, that is all it
 *   does -- nothing appears in any user interface.
 * - `renderVisitorDetails()` is what puts them in the **visits log UI**, as a block of HTML with a
 *   sort order. Implement one and not the other and the feature looks half-finished in whichever
 *   place you did not look.
 *
 * The key `extendVisitorDetails()` writes is not free either: segment value suggestions are read out
 * of this payload *by segment name*, so `userGender` here is the same string as the dimension's
 * `$segmentName`. That is what makes the segment editor able to suggest values at all.
 *
 * The values shown here are the ones this plugin collected about an identified user, so they are
 * personal data appearing in visitor-level output. They belong in a subject-access export and must
 * disappear when that subject is deleted, which is what `Tracker/LogTable/` takes care of. Live
 * itself decides whether visitor-level output is allowed at all, so there is no gate to write here.
 */
class VisitorDetails extends VisitorDetailsAbstract
{
    /**
     * Attributes already looked up during this request, keyed by user id.
     *
     * Live builds these instances with `new`, not through the container, so there is nothing to
     * inject a DAO through -- but it does keep one instance per request, which makes an instance
     * cache safe and turns a visits log of a hundred visits by five users into five queries. A
     * plugin needing more than that would have to give up the per-visit lookup entirely.
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

        // Same key as UserAttributeGender::$segmentName, deliberately.
        $visitor['userGender'] = $attributes['gender'];
        // No segment declares this one, so the name is only a payload key.
        $visitor['userGroup'] = $attributes['group_name'];
    }

    /**
     * @param array<string, mixed> $visitorDetails
     * @return array<array{int, string}> sort order and rendered HTML, as Live expects
     */
    public function renderVisitorDetails($visitorDetails)
    {
        if (empty($visitorDetails['userGender']) && empty($visitorDetails['userGroup'])) {
            return [];
        }

        $view = new View('@ExampleLogTables/_visitorDetails');
        $view->sendHeadersWhenRendering = false;
        $view->gender = $visitorDetails['userGender'] ?? '';
        $view->group = $visitorDetails['userGroup'] ?? '';

        return [[40, $view->render()]];
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
