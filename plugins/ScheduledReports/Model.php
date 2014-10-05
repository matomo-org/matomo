<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\ReportRenderer;
use Piwik\Translate;

class Model
{
    private static $rawPrefix = 'report';
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

    public function deleteUserReportForSite($userLogin, $idSite)
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE login = ? and idsite = ?';
        $bind  = array($userLogin, $idSite);
        Db::query($query, $bind);
    }

    public function deleteAllReportForUser($userLogin)
    {
        Db::query('DELETE FROM ' . $this->table . ' WHERE login = ?', $userLogin);
    }

    public function updateReport($idReport, $report)
    {
        $idReport = (int) $idReport;

        $this->getDb()->update($this->table, $report, "idreport = " . $idReport);
    }

    public function createReport($report)
    {
        $nextId = $this->getNextReportId();
        $report['idreport'] = $nextId;

        $this->getDb()->insert($this->table, $report);

        return $nextId;
    }

    private function getNextReportId()
    {
        $db = $this->getDb();
        $idReport = $db->fetchOne("SELECT max(idreport) + 1 FROM " . $this->table);

        if ($idReport == false) {
            $idReport = 1;
        }

        return $idReport;
    }

    private function getDb()
    {
        return Db::get();
    }

    public static function install()
    {
        $reportTable = "`idreport` INT(11) NOT NULL AUTO_INCREMENT,
					    `idsite` INTEGER(11) NOT NULL,
					    `login` VARCHAR(100) NOT NULL,
					    `description` VARCHAR(255) NOT NULL,
					    `idsegment` INT(11),
					    `period` VARCHAR(10) NOT NULL,
					    `hour` tinyint NOT NULL default 0,
					    `type` VARCHAR(10) NOT NULL,
					    `format` VARCHAR(10) NOT NULL,
					    `reports` TEXT NOT NULL,
					    `parameters` TEXT NULL,
					    `ts_created` TIMESTAMP NULL,
					    `ts_last_sent` TIMESTAMP NULL,
					    `deleted` tinyint(4) NOT NULL default 0,
					    PRIMARY KEY (`idreport`)";

        DbHelper::createTable(self::$rawPrefix, $reportTable);
    }
}
