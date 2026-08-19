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

/**
 * Surfaces the plugin's own stored attributes in the visits log and the visitor profile.
 *
 * Matomo finds this class by its filename: `VisitorDetails.php` in the plugin root, extending
 * `Live\VisitorDetailsAbstract`. Nothing registers it, and nothing in the plugin's own code calls
 * it -- Live does. This is the opposite relationship to reading another plugin's data, which goes
 * through `Request::processRequest()`.
 *
 * The values shown here are the ones this plugin collected about an identified user, so they are
 * personal data appearing in visitor-level output. They belong in a subject-access export and must
 * disappear when that subject is deleted, which is what `Tracker/LogTable/` takes care of.
 */
class VisitorDetails extends VisitorDetailsAbstract
{
    /**
     * @param array<string, mixed> $visitor
     */
    public function extendVisitorDetails(&$visitor)
    {
        $userId = $this->details['user_id'] ?? '';

        if (empty($userId) || !is_string($userId)) {
            return;
        }

        // Live builds these instances with `new`, not through the container, so there is nothing to
        // inject a DAO through. One lookup per visit: a plugin whose visits log needs to stay fast
        // would instead join its table into the Live query, or cache the attributes for the page.
        $attributes = (new CustomUserLog())->getUserInformation($userId);

        if (empty($attributes)) {
            return;
        }

        $visitor['userGender'] = $attributes['gender'];
        $visitor['userGroup'] = $attributes['group_name'];
    }
}
