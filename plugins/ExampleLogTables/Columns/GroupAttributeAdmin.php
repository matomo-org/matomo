<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Columns;

use Piwik\Columns\Dimension;
use Piwik\Plugins\ExampleLogTables\Dao\CustomGroupLog;

/**
 * Describes a column of the plugin's group table -- the table that is two joins away from `log_visit`.
 *
 * See {@see UserAttributeGender} for the conventions shared by both dimensions. The difference worth
 * noticing is that nothing here says how far away the table is: the dimension names its table and its
 * column, and the join comes from `Tracker/LogTable/`. That is what makes `groupIsAdmin==1` work as a
 * segment on visit reports at all.
 *
 * `$allowAnonymous` is left at its default, unlike the gender dimension: a flag describing a group is
 * not personal data about a data subject.
 *
 * It also carries the other half of the segment-suggestion story. `VisitorDetails` publishes no
 * `groupIsAdmin` key, because the flag describes a group rather than the visit, so the route that
 * suggests values for `userGender` cannot work here: core would look for that column in the visits log,
 * not find it, and return an empty list without saying why. A `$suggestedValuesCallback` is the way
 * out, and it short-circuits before the visits log is consulted at all.
 * `plugins/CoreHome/Columns/Profilable.php` is core's precedent -- the only other boolean segment in
 * the same position -- and it is worth knowing that nothing derives `0`/`1` from `TYPE_BOOL` on your
 * behalf.
 */
class GroupAttributeAdmin extends Dimension
{
    protected $dbTableName  = CustomGroupLog::TABLE_NAME;
    protected $category     = 'General_Visitors';
    protected $type         = self::TYPE_BOOL;
    protected $columnName   = 'is_admin';
    protected $segmentName  = 'groupIsAdmin';
    protected $nameSingular = 'ExampleLogTables_GroupHasAdminPrivileges';
    protected $acceptValues = '0, 1';

    public function __construct()
    {
        $this->suggestedValuesCallback = function ($idSite, $maxValuesToReturn) {
            return ['0', '1'];
        };
    }
}
