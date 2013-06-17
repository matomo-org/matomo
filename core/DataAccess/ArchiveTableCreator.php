<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

class Piwik_DataAccess_ArchiveTableCreator
{
    static public function getNumericTable(Piwik_Date $date)
    {
        return self::getTable($date, "numeric");
    }
    static public function getBlobTable(Piwik_Date $date)
    {
        return self::getTable($date, "blob");
    }

    static protected function getTable(Piwik_Date $date, $type)
    {
        Piwik_TablePartitioning_Monthly::createArchiveTablesIfAbsent($date);

        return Piwik_Common::prefixTable("archive_" . $type . "_" . $date->toString('Y_m'));
    }
}