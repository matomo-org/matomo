<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Columns;

use Piwik\Columns\Dimension;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;

/**
 * Describes one column of the plugin's own user table, and gives it a segment.
 *
 * Found by location: any class in `Columns/` extending `Dimension`.
 *
 * **Extending `Dimension` rather than `VisitDimension` is the first decision, and it is unusual.**
 * Almost every dimension in core extends one of the three scope classes -- `VisitDimension`,
 * `ActionDimension`, `ConversionDimension` -- because almost every dimension describes a column of a
 * core log table. Those subclasses default `$dbTableName` to the table of their scope and take over
 * creating and migrating the column, which is exactly what must not happen here: this column belongs to
 * a table the plugin's own DAO owns. The base class is the right one for a dimension over your own
 * table, and it comes with one consequence worth knowing -- only the scope subclasses supply a default
 * `$category`, so a base `Dimension` that omits it gets no segment at all, with no error.
 *
 * Three more things are easy to get wrong when copying:
 *
 * - **No `$columnType`.** On a scope dimension it is the column's schema definition and
 *   `Columns\Updater` applies it. On a base `Dimension` nothing reads it: that class enumerates only
 *   `VisitDimension`, `ActionDimension` and `ConversionDimension`, so a `$columnType` here would be a
 *   second definition of a column the DAO already owns, which never runs and can only drift from it.
 * - **`$segmentName` is also the payload key.** Segment value suggestions are read out of the visits
 *   log by segment name, so `VisitorDetails` writes this value under `userPlan`, the same string as
 *   below. Name the two differently and the editor offers no suggestions and reports nothing: the
 *   visits are found, this column is not among them, and an empty list is what a segment with no data
 *   yet looks like too.
 * - **`$allowAnonymous = false`**, because this is personal data about an identified user. Core sets
 *   the flag on its five visitor-identifying dimensions -- user id, visitor id, visit id, IP and
 *   fingerprint -- and leaves it alone on identifiers that identify something other than a person,
 *   such as Ecommerce's `idorder`. The test is whether the value describes a person, not whether the
 *   column is an id, which is why an attribute *about* an identified user takes the flag as well. The
 *   paying flag next door deliberately does not, because an account is not a person.
 *
 * Declaring a table, a column and a name also puts an `ArchivedMetric` in `MetricsList` for this
 * dimension through `Dimension::configureMetrics()` -- `nb_uniq_…` for a text column, `sum_…` for a
 * boolean. Nothing in core archives it and no API surfaces it: it is a SQL fragment for
 * report-building plugins to consume, so it is neither broken nor exposed, only latent. Worth knowing
 * so that you do not go looking for a metric that was never meant to appear.
 */
class UserAttributePlan extends Dimension
{
    protected $dbTableName   = CustomUserLog::TABLE_NAME;
    protected $category      = 'General_Visitors';
    protected $type          = self::TYPE_TEXT;
    protected $columnName    = 'plan';
    protected $segmentName   = 'userPlan';
    protected $nameSingular  = 'ExampleLogTables_UserPlan';
    protected $acceptValues  = 'free, pro, enterprise, etc.';
    protected $allowAnonymous = false;
}
