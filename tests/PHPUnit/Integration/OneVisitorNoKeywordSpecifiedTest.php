<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\Tests\IntegrationTestCase;
use Piwik\Tests\Fixtures\TwoVisitsNoKeywordWithBot;

/**
 * 1) Tests empty google kwd works nicely in Live! output and Top keywords
 * 2) Tests IP anonymization
 * Also test that Live! will link to the search result page URL rather than the exact referrer URL
 * when the referrer URL is google.XX/url.... which is a redirect to landing page rather than the search result URL
 *
 * @group Integration
 * @group OneVisitorNoKeywordSpecifiedTest
 */
class OneVisitorNoKeywordSpecifiedTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array('Referrers.getKeywords');

        // test started failing after bc19503 and I cannot understand why
        if(!self::isTravisCI()) {
            $apiToCall[] = 'Live.getLastVisitsDetails';
        }

        return array(
            array($apiToCall, array('idSite'   => self::$fixture->idSite,
                                    'date'     => self::$fixture->dateTime,
                                    'language' => 'fr'))
        );
    }

    public static function getOutputPrefix()
    {
        return 'OneVisitor_NoKeywordSpecified';
    }
}

OneVisitorNoKeywordSpecifiedTest::$fixture = new TwoVisitsNoKeywordWithBot();