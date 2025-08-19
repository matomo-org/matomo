<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */

namespace Piwik\Plugins\Annotations;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;

class Model
{
    private static $rawPrefix = 'annotations';

    public static function install()
    {
        $table = Common::prefixTable(self::$rawPrefix);

        if (!DbHelper::tableExists($table)) {
            $annotation = "  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                             `idsite` INTEGER UNSIGNED NOT NULL,
                             `date` DATETIME NOT NULL,
                             `note` TEXT NOT NULL,
                             `starred` TINYINT(1) NOT NULL DEFAULT 0 ,
                             PRIMARY KEY ( `id` )";
            DbHelper::createTable(self::$rawPrefix, $annotation);
        }

        if (!DbHelper::tableHasIndex($table, 'index_id_idsite_date')) {
            Db::exec(sprintf(
                'ALTER TABLE %s ADD INDEX index_id_idsite_date (`id`, `idsite`, `date`)',
                $table
            ));
        }
    }

    public static function uninstall()
    {
        Db::dropTables(Common::prefixTable(self::$rawPrefix));
    }
}
