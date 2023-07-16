<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\Complianz;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class ComplianzTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testRunSiteDetectionByContent($expected, $isConnected, $data, $headers)
    {
        $detection = new Complianz();
        self::assertSame($expected, $detection->runSiteDetectionByContent($data, $headers));
        self::assertSame($isConnected, $detection->checkIsConnected($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            false,
            '',
            []
        ];

        yield 'no complianz content' => [
            false,
            false,
            "<html lang=\"en\"><head><title>A site</title><script>console.log('abc');</script></head><body>A site</body></html>",
            []
        ];

        yield 'complianz-gdpr content found' => [
            true,
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script>complianz-gdpr</script></head><body>A site</body></html>",
            []
        ];

        yield 'complianz-gdpr connected' => [
            true,
            true,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script>// complianz-gdpr; 
// if (!cmplz_in_array( 'statistics', consentedCategories )) {
		_paq.push(['forgetCookieConsentGiven']);</script></head><body>A site</body></html>",
            []
        ];
    }
}
