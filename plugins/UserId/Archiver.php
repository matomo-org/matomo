<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId;

/**
 * Archiver that aggregates metrics per user ID (user_id field).
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    public const USERID_ARCHIVE_RECORD = "UserId_users";

    public const VISITOR_ID_FIELD = 'idvisitor';
    public const USER_ID_FIELD = 'user_id';
}
