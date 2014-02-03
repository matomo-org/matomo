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
    /**
     * Test the Bulk tracking API as documented in: http://developer.piwik.org/api-reference/tracking-api#bulk-tracking
     *
     * @throws Exception
     */
    public function test_trackingApiWithBulkRequests_viaCurl()
    {
        $piwikHost = Test_Piwik_BaseFixture::getRootUrl() . 'piwik.php';
        $command = 'curl -X POST -d \'{"requests":["?idsite=1&url=http://example.org&action_name=Test bulk log Pageview&rec=1","?idsite=1&url=http://example.net/test.htm&action_name=Another bulk page view&rec=1"],"token_auth":"33dc3f2536d3025974cccb4b4d2d98f4"}\' ' . $piwikHost;

        exec($command, $output, $result);
        $output = implode("", $output);
        if ($result !== 0) {
            throw new Exception("tracking bulk failed: " . implode("\n", $output) . "\n\ncommand used: $command");
        }
        $this->assertNotContains('error', $output);
        $this->assertStringStartsWith('{"status":', $output);
        $this->assertContains('success', $output);
    }
}
