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
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

    public static function install()
    {
        $annotation = "  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                         `idsite` INTEGER UNSIGNED NOT NULL,
					     `note` TEXT NOT NULL,
					     `date` DATETIME NOT NULL,
					     `starred` TINYINT(1) NOT NULL DEFAULT 0 ,
					     PRIMARY KEY ( `id` )
					     INDEX index_id_idsite_date (`id`, `idsite`, `date`)";
        DbHelper::createTable(self::$rawPrefix, $annotation);
    }

    public static function uninstall()
    {
        Db::dropTables(Common::prefixTable(self::$rawPrefix));
    }
}
