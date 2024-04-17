<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\tests\Fixtures;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\ScheduledReports\API;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Tests\Framework\Fixture;

class ReportSubscription extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;

    public function setUp(): void
    {
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        Fixture::createSuperUser(false);

        $reportType   = 'email';
        $reportFormat = 'pdf';
        $reports      = array('VisitsSummary_get', 'UserCountry_getCountry');
        $parameters   = [
            ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY,
            ScheduledReports::EMAIL_ME_PARAMETER => true,
            ScheduledReports::ADDITIONAL_EMAILS_PARAMETER => ['any@matomo.org']
        ];

        API::getInstance()->addReport($this->idSite, 'description', 'day', 3, $reportType, $reportFormat, $reports, $parameters);

        Db::query("INSERT INTO " . Common::prefixTable('report_subscriptions') . "(idreport, token, email) VALUES (1, 'mycustomtoken', 'any@matomo.org')");
    }
}
