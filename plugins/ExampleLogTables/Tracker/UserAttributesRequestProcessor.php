<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Tracker;

use Piwik\Common;
use Piwik\Plugins\ExampleLogTables\Dao\CustomGroupLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Writes the plugin's own log tables during tracking.
 *
 * A Dimension writes a column of a log table Matomo already owns. Writing rows into a table of your
 * own is what a RequestProcessor is for, and `recordLogs()` is the step to do it in: by then the visit
 * has been persisted and every earlier phase of every plugin has run. Other plugins' `recordLogs()`
 * run in the same loop, so half of them run after this one -- do not depend on their work here.
 *
 * `recordLogs()` runs once per tracking *request*, not once per visit, so a visit of ten pageviews
 * calls this ten times -- which is why the DAO upserts instead of inserting. It is skipped entirely
 * for a request that was aborted earlier (an excluded visit, a late ping) and for requests handled in
 * bot mode, so it is the right place to write data about a visit and the wrong place to count
 * requests.
 *
 * Matomo finds this class because it lives in the plugin's `Tracker/` directory and extends
 * `Piwik\Tracker\RequestProcessor` -- see `Piwik\Plugin\RequestProcessors`. It is instantiated
 * through the container, so its dependencies are injected. It is also shared between tracking
 * requests, so it must stay stateless.
 */
class UserAttributesRequestProcessor extends RequestProcessor
{
    /**
     * Tracking parameters this plugin reads. A plugin makes up its own; nothing registers them.
     */
    public const PARAM_GENDER = 'user_gender';
    public const PARAM_GROUP = 'user_group';
    public const PARAM_GROUP_IS_ADMIN = 'user_group_is_admin';

    private CustomUserLog $userLog;

    private CustomGroupLog $groupLog;

    public function __construct(CustomUserLog $userLog, CustomGroupLog $groupLog)
    {
        $this->userLog = $userLog;
        $this->groupLog = $groupLog;
    }

    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        // Read the user id Matomo persisted rather than the `uid` request parameter. By this point
        // the site's "disable user id" setting and PrivacyManager's user id pseudonymisation have
        // both been applied, and this table must not hold an identifier log_visit does not.
        $userId = $visitProperties->getProperty('user_id');

        if (empty($userId) || !is_string($userId)) {
            return;
        }

        $params = $request->getParams();

        // Note the empty string default. With a type argument, Common::getRequestVar() coerces the
        // default through settype(), so passing false here would still return '' and the familiar
        // `false === $value` check would never fire.
        $gender = Common::getRequestVar(self::PARAM_GENDER, '', 'string', $params);
        $group = Common::getRequestVar(self::PARAM_GROUP, '', 'string', $params);

        // A default of -1 distinguishes "the request said the group is not an admin group" from "the
        // request said nothing about it". Writing an invented default in the second case would
        // silently overwrite what an earlier request stored.
        $isAdmin = Common::getRequestVar(self::PARAM_GROUP_IS_ADMIN, -1, 'int', $params);

        // Collect only what this request actually carried. A request that mentions the gender but
        // not the group says nothing about the group, and writing a default for it would erase what
        // an earlier request stored.
        //
        // Clamp each value to the width of the column that holds it, the way
        // CoreHome\Columns\UserId does for the user id it persists. These arrive from a tracking
        // request, so their length is not yours to assume: the tracker connection keeps whatever
        // sql_mode the server gives it, which on a default install is strict, and an over-long
        // value there fails the whole tracking request rather than truncating. Clamping is lossy
        // in its own way -- two group names sharing a 30-character prefix become one row, and
        // `group_name` is the join key between the two tables -- so widen the column rather than
        // lean on the clamp if your values are genuinely free text.
        $attributes = [];

        if ('' !== $gender) {
            $attributes['gender'] = mb_substr($gender, 0, CustomUserLog::MAX_LENGTH_GENDER);
        }

        if ('' !== $group) {
            $group = mb_substr($group, 0, CustomGroupLog::MAX_LENGTH_GROUP_NAME);
            $attributes['group_name'] = $group;
        }

        if (empty($attributes)) {
            return; // nothing this plugin collects was sent, so nothing is stored
        }

        $this->userLog->addOrUpdateUserInformation($userId, $attributes);

        // The group row is written only when this request said something about the flag. A request
        // carrying a group name and nothing else stores the user's membership and leaves the group
        // table alone, so a user row can name a group that has no row of its own yet -- deliberately.
        // The group table is reference data about groups, not a foreign key the user table depends
        // on: a group nobody has described yet is simply a group with no known flag, and the
        // archived metric counts it as not-an-admin-group until a request says otherwise. An invalid
        // flag is treated exactly like an absent one, because a malformed value is not a statement.
        if ('' === $group || (0 !== $isAdmin && 1 !== $isAdmin)) {
            return;
        }

        // These values arrive in a tracking request, which anyone can send. `is_admin` is a
        // segmentation attribute describing the group, never an authorisation signal -- no access
        // decision anywhere in Matomo may read it.
        $this->groupLog->addOrUpdateGroupInformation($group, 1 === $isAdmin);
    }
}
