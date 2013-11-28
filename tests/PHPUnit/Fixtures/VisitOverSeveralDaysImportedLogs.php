<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Import a same visitor, over three different days, in reverse chronological order
 * useful to test there are three visits are created for this visitor, as expected
 *
 */
class Test_Piwik_Fixture_VisitOverSeveralDaysImportedLogs extends Test_Piwik_BaseFixture
{
    public $dateTime = '2013-04-07 19:00:00';
    public $idSite = 1;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    public function setUpWebsitesAndGoals()
    {
        self::createWebsite($this->dateTime);
    }

    private function trackVisits()
    {
        $this->logFromLogFileReverseVisitOrder();
    }

    /**
     * Logs a couple visits for the site we created w/ all log importer options
     * enabled. Visits are for Aug 11 of 2012.
     */
    private function logFromLogFileReverseVisitOrder()
    {
        $logFile = PIWIK_INCLUDE_PATH . '/tests/resources/access-logs/fake_logs_visits_in_reverse_chronological_order.log';

        $opts = array('--idsite'                    => $this->idSite,
                      '--token-auth'                => self::getTokenAuth(),);

        self::executeLogImporter($logFile, $opts);
    }

}
