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
use Piwik\Db;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_1_8_3_b1 extends Updates
{

    static function getSql()
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('site') . '`
				CHANGE `excluded_parameters` `excluded_parameters` TEXT NOT NULL'                      => false,

            'CREATE TABLE `' . Common::prefixTable('report') . '` (
					`idreport` INT(11) NOT NULL AUTO_INCREMENT,
					`idsite` INTEGER(11) NOT NULL,
					`login` VARCHAR(100) NOT NULL,
					`description` VARCHAR(255) NOT NULL,
					`period` VARCHAR(10) NOT NULL,
					`type` VARCHAR(10) NOT NULL,
					`format` VARCHAR(10) NOT NULL,
					`reports` TEXT NOT NULL,
					`parameters` TEXT NULL,
					`ts_created` TIMESTAMP NULL,
					`ts_last_sent` TIMESTAMP NULL,
					`deleted` tinyint(4) NOT NULL default 0,
					PRIMARY KEY (`idreport`)
				) DEFAULT CHARSET=utf8' => 1050,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginLoaded('ScheduledReports')) {
            return;
        }

        try {

            // Common::prefixTable('pdf') has been heavily refactored to be more generic
            // The following actions are taken in this update script :
            // - create the new generic report table Common::prefixTable('report')
            // - migrate previous reports, if any, from Common::prefixTable('pdf') to Common::prefixTable('report')
            // - delete Common::prefixTable('pdf')

            $reports = Db::fetchAll('SELECT * FROM `' . Common::prefixTable('pdf') . '`');
            foreach ($reports as $report) {

                $idreport = $report['idreport'];
                $idsite = $report['idsite'];
                $login = $report['login'];
                $description = $report['description'];
                $period = $report['period'];
                $format = $report['format'];
                $display_format = $report['display_format'];
                $email_me = $report['email_me'];
                $additional_emails = $report['additional_emails'];
                $reports = $report['reports'];
                $ts_created = $report['ts_created'];
                $ts_last_sent = $report['ts_last_sent'];
                $deleted = $report['deleted'];

                $parameters = array();

                if (!is_null($additional_emails)) {
                    $parameters[ScheduledReports::ADDITIONAL_EMAILS_PARAMETER] = preg_split('/,/', $additional_emails);
                }

                $parameters[ScheduledReports::EMAIL_ME_PARAMETER] = is_null($email_me) ? ScheduledReports::EMAIL_ME_PARAMETER_DEFAULT_VALUE : (bool)$email_me;
                $parameters[ScheduledReports::DISPLAY_FORMAT_PARAMETER] = $display_format;

                Db::query(
                    'INSERT INTO `' . Common::prefixTable('report') . '` SET
					idreport = ?, idsite = ?, login = ?, description = ?, period = ?,
					type = ?, format = ?, reports = ?, parameters = ?, ts_created = ?,
					ts_last_sent = ?, deleted = ?',
                    array(
                         $idreport,
                         $idsite,
                         $login,
                         $description,
                         is_null($period) ? ScheduledReports::DEFAULT_PERIOD : $period,
                         ScheduledReports::EMAIL_TYPE,
                         is_null($format) ? ScheduledReports::DEFAULT_REPORT_FORMAT : $format,
                         Common::json_encode(preg_split('/,/', $reports)),
                         Common::json_encode($parameters),
                         $ts_created,
                         $ts_last_sent,
                         $deleted
                    )
                );
            }

            Db::query('DROP TABLE `' . Common::prefixTable('pdf') . '`');
        } catch (\Exception $e) {
        }

    }
}
