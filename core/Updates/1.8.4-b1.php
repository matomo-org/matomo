<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_8_4_b1 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public static function isMajorUpdate()
    {
        return true;
    }

    public function getMigrations(Updater $updater)
    {
        $action = Common::prefixTable('log_action');
        $duplicates = Common::prefixTable('log_action_duplicates');
        $visitAction = Common::prefixTable('log_link_visit_action');
        $conversion = Common::prefixTable('log_conversion');
        $visit = Common::prefixTable('log_visit');

        return array(

            $this->migration->db->addColumn('log_action', 'url_prefix', 'TINYINT(2) NULL', 'type'),

            // remove protocol and www and store information in url_prefix
            $this->migration->db->sql(
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
            "
            ),
            $this->migration->db->dropTable('log_action_duplicates'),
            $this->migration->db->createTable('log_action_duplicates', array(
                'before' => 'int(10) unsigned NOT NULL',
                'after' => 'int(10) unsigned NOT NULL',
            )),
            $this->migration->db->sql("ALTER TABLE $duplicates ADD KEY `mainkey` (`before`)"),

            // grouping by name only would be case-insensitive, so we GROUP BY name,hash
            // ON (action.type = 1 AND canonical.hash = action.hash) will use index (type, hash)
            $this->migration->db->sql(
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
            "
            ),

            // replace idaction in log_link_visit_action
            $this->migration->db->sql(
                "   UPDATE
                  `$visitAction` AS link
                LEFT JOIN
                  `$duplicates` AS duplicates_idaction_url
                  ON link.idaction_url = duplicates_idaction_url.before
                SET
                  link.idaction_url = duplicates_idaction_url.after
                WHERE
                  duplicates_idaction_url.after IS NOT NULL;
            "
            ),
            $this->migration->db->sql(
                "   UPDATE
                  `$visitAction` AS link
                LEFT JOIN
                  `$duplicates` AS duplicates_idaction_url_ref
                  ON link.idaction_url_ref = duplicates_idaction_url_ref.before
                SET
                  link.idaction_url_ref = duplicates_idaction_url_ref.after
                WHERE
                  duplicates_idaction_url_ref.after IS NOT NULL;
            "
            ),

            // replace idaction in log_conversion
            $this->migration->db->sql(
                "   UPDATE
                  `$conversion` AS conversion
                LEFT JOIN
                  `$duplicates` AS duplicates
                  ON conversion.idaction_url = duplicates.before
                SET
                  conversion.idaction_url = duplicates.after
                WHERE
                  duplicates.after IS NOT NULL;
            "
            ),

            // replace idaction in log_visit
            $this->migration->db->sql(
                "   UPDATE
                  `$visit` AS visit
                LEFT JOIN
                  `$duplicates` AS duplicates_entry
                  ON visit.visit_entry_idaction_url = duplicates_entry.before
                SET
                  visit.visit_entry_idaction_url = duplicates_entry.after
                WHERE
                  duplicates_entry.after IS NOT NULL;
            "
            ),
            $this->migration->db->sql(
                "   UPDATE
                  `$visit` AS visit
                LEFT JOIN
                  `$duplicates` AS duplicates_exit
                  ON visit.visit_exit_idaction_url = duplicates_exit.before
                SET
                  visit.visit_exit_idaction_url = duplicates_exit.after
                WHERE
                  duplicates_exit.after IS NOT NULL;
            "
            ),

            // remove duplicates from log_action
            $this->migration->db->sql(
                "   DELETE action FROM
                  `$action` AS action
                LEFT JOIN
                  `$duplicates` AS duplicates
                  ON action.idaction = duplicates.before
                WHERE
                  duplicates.after IS NOT NULL;
            "
            ),

            // remove the duplicates table
            $this->migration->db->dropTable('log_action_duplicates')
        );
    }

    public function doUpdate(Updater $updater)
    {
        try {
            self::enableMaintenanceMode();
            $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
            self::disableMaintenanceMode();
        } catch (\Exception $e) {
            self::disableMaintenanceMode();
            throw $e;
        }
    }
}
