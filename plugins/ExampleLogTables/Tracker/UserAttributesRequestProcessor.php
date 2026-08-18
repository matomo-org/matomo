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
 * own is what a RequestProcessor is for, and `recordLogs()` is the step to do it in: by then the
 * visit has been persisted and every other plugin has had its say.
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

        if ('' === $gender && '' === $group) {
            return; // nothing this plugin collects was sent, so nothing is stored
        }

        $this->userLog->addOrUpdateUserInformation($userId, $group, $gender);

        if ('' === $group) {
            return;
        }

        // A default of -1 distinguishes "the request said the group is not an admin group" from
        // "the request said nothing about it". Writing an invented default in the second case
        // would silently overwrite what an earlier request stored.
        $isAdmin = Common::getRequestVar(self::PARAM_GROUP_IS_ADMIN, -1, 'int', $params);

        if ($isAdmin < 0) {
            return;
        }

        // These values arrive in a tracking request, which anyone can send. `is_admin` is a
        // segmentation attribute describing the group, never an authorisation signal -- no access
        // decision anywhere in Matomo may read it.
        $this->groupLog->addOrUpdateGroupInformation($group, 1 === $isAdmin);
    }
}
