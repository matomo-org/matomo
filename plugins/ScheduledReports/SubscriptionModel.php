<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Piwik;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;

class SubscriptionModel
{
    private static $rawPrefix = 'report_subscriptions';
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

    public function unsubscribe($token)
    {
        $details = $this->getSubscription($token);

        if (empty($details)) {
            return false;
        }

        $email = $details['email'];

        $report = Access::doAsSuperUser(function() use ($details) {
            $reports = Request::processRequest('ScheduledReports.getReports', array(
                'idReport'    => $details['idreport'],
            ));
            return reset($reports);
        });

        if (empty($report)) {
            // if the report isn't found, remove subscription as it isn't active anymore
            $this->removeSubscription($token);
            return false;
        }

        $reportParameters = $report['parameters'];

        $emailFound = false;

        if (!empty($reportParameters['additionalEmails'])) {
            $additionalEmails = $reportParameters['additionalEmails'];
            $filteredEmails = [];
            foreach ($additionalEmails as $additionalEmail) {
                if ($additionalEmail == $email) {
                    $emailFound = true;
                    continue;
                }
                $filteredEmails[] = $additionalEmail;
            }
            if ($emailFound) {
                $report['parameters']['additionalEmails'] = $filteredEmails;
            }
        }

        if ($reportParameters['emailMe']) {
            $login = $report['login'];

            $userModel = new \Piwik\Plugins\UsersManager\Model();
            $userData = $userModel->getUser($login);

            if ($userData['email'] == $email) {
                $emailFound = true;
                $report['parameters']['emailMe'] = false;
            }
        }

        if ($emailFound) {
            $reportModel = new Model();
            $reportModel->updateReport($report['idreport'], array(
                'parameters' => json_encode($report['parameters'])
            ));
            // Reset the cache manually since we didn't call the API method which would do it for us
            APIScheduledReports::$cache = array();

            Piwik::postEvent('Report.unsubscribe', [$report['idreport'], $email]);

            $this->removeSubscription($token);
        }

        return $emailFound;
    }

    public function getReportSubscriptions($idReport, $includeUnsubscribed = false)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE idreport = ?';

        if (!$includeUnsubscribed) {
            $query .= ' AND ts_unsubscribed IS NULL';
        }

        return $this->getDb()->fetchAll($query, [$idReport]);
    }

    public function getSubscription($token)
    {
        return $this->getDb()->fetchRow('SELECT * FROM ' . $this->table . ' WHERE token = ?', [$token]);
    }

    public function updateReportSubscriptions($idReport, $emails)
    {
        $availableSubscriptions = $this->getReportSubscriptions($idReport);
        $availableEmails = array_column($availableSubscriptions, 'email');

        // remove available subscriptions that aren't present anymore
        foreach ($availableSubscriptions as $availableSubscription) {
            if (!in_array($availableSubscription['email'], $emails) && !empty($availableSubscription['token'])) {
                $this->removeSubscription($availableSubscription['token']);
            }
        }

        $emails = array_unique($emails);

        // add new subscriptions
        foreach ($emails as $email) {
            while($token = $this->generateToken($email)) {
                if (!$this->tokenExists($token)) {
                    break;
                }
            }

            if (!in_array($email, $availableEmails)) {
                $subscription = [
                    'idreport' => $idReport,
                    'token' => $token,
                    'email' => $email
                ];
                // remove possible "unsubscribe" entry
                $this->getDb()->query('DELETE FROM ' . $this->table . ' WHERE idreport = ? AND email = ?', [$idReport, $email]);
                $this->getDb()->insert($this->table, $subscription);
            }
        }

    }

    private function removeSubscription($token)
    {
        $this->getDb()->query('UPDATE ' . $this->table . ' SET token = NULL, ts_unsubscribed = NOW() WHERE token = ?', [$token]);
    }

    private function generateToken($email)
    {
        return substr(Common::hash($email . time() . Common::getRandomString(5)), 0, 100);
    }

    private function tokenExists($token)
    {
        return !!$this->getDb()->fetchOne('SELECT token FROM ' . $this->table . ' WHERE token = ?', [$token]);
    }

    private function getDb()
    {
        return Db::get();
    }

    public static function install()
    {
        $reportTable = "`idreport` INT(11) NOT NULL,
					    `token` VARCHAR(100) NULL,
					    `email` VARCHAR(100) NOT NULL,
					    `ts_subscribed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					    `ts_unsubscribed` TIMESTAMP NULL,
					    PRIMARY KEY (`idreport`, `email`),
					    UNIQUE INDEX `unique_token` (`token`)";

        DbHelper::createTable(self::$rawPrefix, $reportTable);
    }
}
