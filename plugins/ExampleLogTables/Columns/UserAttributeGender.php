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
 * Found by location: any class in `Columns/` extending `Dimension`. Three things about it are easy to
 * get wrong when copying:
 *
 * - **No `$columnType`.** Declaring one asks Matomo to create and migrate the column for you, which
 *   is right for a column on a core log table and wrong here -- the DAO owns this schema. A
 *   `$columnType` on a dimension over a plugin-owned table would be a second, competing definition
 *   of the same column.
 * - **`$segmentName` is also the payload key.** Segment value suggestions are read out of the visits
 *   log by segment name, so `VisitorDetails` writes this value under `userGender`, the same string as
 *   below. Name the two differently and the segment editor answers "there was no data to suggest".
 * - **`$allowAnonymous = false`**, because this is personal data about an identified user. Core sets
 *   the same flag on exactly its identifying dimensions -- user id, IP, visitor id, fingerprint --
 *   and the group flag next door deliberately does not set it, because a group is not a person.
 *
 * Declaring a table, a column and a name also registers an archived metric for this dimension
 * automatically, through `Dimension::configureMetrics()`. It is not a side effect worth fighting, but
 * it is worth knowing that the plugin exposes more than the one metric it archives explicitly.
 */
class UserAttributeGender extends Dimension
{
    protected $dbTableName   = CustomUserLog::TABLE_NAME;
    protected $category      = 'General_Visitors';
    protected $type          = self::TYPE_TEXT;
    protected $columnName    = 'gender';
    protected $segmentName   = 'userGender';
    protected $nameSingular  = 'ExampleLogTables_UserGender';
    protected $acceptValues  = 'men, women';
    protected $allowAnonymous = false;
}
