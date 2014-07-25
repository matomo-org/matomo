<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_1_8_4_b1 extends Updates
{

    static function isMajorUpdate()
    {
        return true;
    }

    static function getSql()
    {
        $action = Common::prefixTable('log_action');
        $duplicates = Common::prefixTable('log_action_duplicates');
        $visitAction = Common::prefixTable('log_link_visit_action');
        $conversion = Common::prefixTable('log_conversion');
        $visit = Common::prefixTable('log_visit');

        return array(

            // add url_prefix column
            "   ALTER TABLE `$action`
		    	ADD `url_prefix` TINYINT(2) NULL AFTER `type`;
		    "                                                                                                     => 1060, // ignore error 1060 Duplicate column name 'url_prefix'

            // remove protocol and www and store information in url_prefix
            "   UPDATE `$action`
				SET
				  url_prefix = IF (
					LEFT(name, 11) = 'http://www.', 1, IF (
					  LEFT(name, 7) = 'http://', 0, IF (
						LEFT(name, 12) = 'https://www.', 3, IF (
						  LEFT(name, 8) = 'https://', 2, NULL
						)
					  )
					)
				  ),
				  name = IF (
					url_prefix = 0, SUBSTRING(name, 8), IF (
					  url_prefix = 1, SUBSTRING(name, 12), IF (
						url_prefix = 2, SUBSTRING(name, 9), IF (
						  url_prefix = 3, SUBSTRING(name, 13), name
						)
					  )
					)
				  ),
				  hash = CRC32(name)
				WHERE
				  type = 1 AND
				  url_prefix IS NULL;
			"                                                                      => false,

            // find duplicates
            "   DROP TABLE IF EXISTS `$duplicates`;
			"                                                    => false,
            "   CREATE TABLE `$duplicates` (
				 `before` int(10) unsigned NOT NULL,
				 `after` int(10) unsigned NOT NULL,
				 KEY `mainkey` (`before`)
				) ENGINE=InnoDB;
			"                                                            => false,

            // grouping by name only would be case-insensitive, so we GROUP BY name,hash
            // ON (action.type = 1 AND canonical.hash = action.hash) will use index (type, hash)
            "   INSERT INTO `$duplicates` (
				  SELECT
					action.idaction AS `before`,
					canonical.idaction AS `after`
				  FROM
					(
					  SELECT
						name,
						hash,
						MIN(idaction) AS idaction
					  FROM
						`$action` AS action_canonical_base
					  WHERE
						type = 1 AND
						url_prefix IS NOT NULL
					  GROUP BY name, hash
					  HAVING COUNT(idaction) > 1
					)
					AS canonical
				  LEFT JOIN
					`$action` AS action
					ON (action.type = 1 AND canonical.hash = action.hash)
					AND canonical.name = action.name
					AND canonical.idaction != action.idaction
				);
			" => false,

            // replace idaction in log_link_visit_action
            "   UPDATE
				  `$visitAction` AS link
				LEFT JOIN
				  `$duplicates` AS duplicates_idaction_url
				  ON link.idaction_url = duplicates_idaction_url.before
				SET
				  link.idaction_url = duplicates_idaction_url.after
				WHERE
				  duplicates_idaction_url.after IS NOT NULL;
			"                           => false,
            "   UPDATE
				  `$visitAction` AS link
				LEFT JOIN
				  `$duplicates` AS duplicates_idaction_url_ref
				  ON link.idaction_url_ref = duplicates_idaction_url_ref.before
				SET
				  link.idaction_url_ref = duplicates_idaction_url_ref.after
				WHERE
				  duplicates_idaction_url_ref.after IS NOT NULL;
			"                           => false,

            // replace idaction in log_conversion
            "   UPDATE
				  `$conversion` AS conversion
				LEFT JOIN
				  `$duplicates` AS duplicates
				  ON conversion.idaction_url = duplicates.before
				SET
				  conversion.idaction_url = duplicates.after
				WHERE
				  duplicates.after IS NOT NULL;
			"                            => false,

            // replace idaction in log_visit
            "   UPDATE
				  `$visit` AS visit
				LEFT JOIN
				  `$duplicates` AS duplicates_entry
				  ON visit.visit_entry_idaction_url = duplicates_entry.before
				SET
				  visit.visit_entry_idaction_url = duplicates_entry.after
				WHERE
				  duplicates_entry.after IS NOT NULL;
			"                                 => false,
            "   UPDATE
				  `$visit` AS visit
				LEFT JOIN
				  `$duplicates` AS duplicates_exit
				  ON visit.visit_exit_idaction_url = duplicates_exit.before
				SET
				  visit.visit_exit_idaction_url = duplicates_exit.after
				WHERE
				  duplicates_exit.after IS NOT NULL;
			"                                 => false,

            // remove duplicates from log_action
            "   DELETE action FROM
				  `$action` AS action
				LEFT JOIN
				  `$duplicates` AS duplicates
				  ON action.idaction = duplicates.before
				WHERE
				  duplicates.after IS NOT NULL;
			"                                => false,

            // remove the duplicates table
            "   DROP TABLE `$duplicates`;
			"                                                              => false
        );
    }

    static function update()
    {
        try {
            self::enableMaintenanceMode();
            Updater::updateDatabase(__FILE__, self::getSql());
            self::disableMaintenanceMode();
        } catch (\Exception $e) {
            self::disableMaintenanceMode();
            throw $e;
        }
    }
}
