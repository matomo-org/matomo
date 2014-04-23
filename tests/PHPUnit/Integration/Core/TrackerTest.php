<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * @group Core
 */
class Core_TrackerTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        \Piwik\Piwik::setUserHasSuperUserAccess(true);
        Fixture::createWebsite('2014-02-04');
    }

    protected function configureFixture()
    {
        $this->fixture->createSuperUser = true;
    }

    /**
     * Test the Bulk tracking API as documented in: http://developer.piwik.org/api-reference/tracking-api#bulk-tracking
     *
     * With invalid token_auth the request would still work
     */
    public function test_trackingApiWithBulkRequests_viaCurl_withWrongTokenAuth()
    {
        $token_auth = '33dc3f2536d3025974cccb4b4d2d98f4';
        $this->issueBulkTrackingRequest($token_auth, $expectTrackingToSucceed = true);
    }

    public function test_trackingApiWithBulkRequests_viaCurl_withCorrectTokenAuth()
    {
        $token_auth = Fixture::getTokenAuth();
        \Piwik\Filesystem::deleteAllCacheOnUpdate();
        $this->issueBulkTrackingRequest($token_auth, $expectTrackingToSucceed = true);
    }

    protected function issueBulkTrackingRequest($token_auth, $expectTrackingToSucceed)
    {
        $piwikHost = Fixture::getRootUrl() . 'tests/PHPUnit/proxy/piwik.php';

        $command = 'curl -s -X POST -d \'{"requests":["?idsite=1&url=http://example.org&action_name=Test bulk log Pageview&rec=1","?idsite=1&url=http://example.net/test.htm&action_name=Another bulk page view&rec=1"],"token_auth":"' . $token_auth . '"}\' ' . $piwikHost;

        exec($command, $output, $result);
        $output = implode("", $output);
        if ($result !== 0) {
            throw new Exception("tracking bulk failed: " . implode("\n", $output) . "\n\ncommand used: $command");
        }
        $this->assertStringStartsWith('{"status":', $output);

        if($expectTrackingToSucceed) {
            $this->assertNotContains('error', $output);
            $this->assertContains('success', $output);
        } else {
            $this->assertContains('error', $output);
            $this->assertNotContains('success', $output);
        }

    }
}
