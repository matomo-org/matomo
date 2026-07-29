<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\SitesManager\SiteContentDetection\Cloudflare;
use Piwik\Plugins\SitesManager\SiteContentDetection\Osano;
use Piwik\Plugins\SitesManager\SiteContentDetection\ReactJs;
use Piwik\Plugins\SitesManager\SiteContentDetection\WordPress;
use Piwik\SiteContentDetector;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * @group Core
 * @group SiteContentDetectorTest
*/
class SiteContentDetectorTest extends IntegrationTestCase
{
    public function testSiteWithMultipleDetections()
    {
        $scd = new SiteContentDetector();
        $scd->detectContent([], null, [
            'data' => "<html lang='en'>
                        <head>
                            <title>A site</title>
                            <script src='https://localhost.com/js/react.min.js'></script>
                            <script src='https://osano.com/uhs9879874hthg.js'></script>
                            <script>Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });</script>
                        </head>
                        <body>A site<img src='/wp-content/uploads/images.gif'</body>
                       </html>",
            'headers' => [
                'CF-RAY' => 'test',
            ],
        ]);

        self::assertTrue($scd->wasDetected(Osano::getId()));
        self::assertTrue($scd->wasDetected(WordPress::getId()));
        self::assertTrue($scd->wasDetected(ReactJs::getId()));
        self::assertTrue($scd->wasDetected(Cloudflare::getId()));
        self::assertContains(Osano::getId(), $scd->connectedConsentManagers);
    }

    public function testSiteOnPrivateAddressLogsRefusalAtTheDefaultLogLevel()
    {
        $general = Config::getInstance()->General;
        $general['enable_internet_features'] = 1;
        Config::getInstance()->General = $general;

        $idSite = Fixture::createWebsite('2014-01-01 00:00:00', 0, 'intranet', 'http://10.0.0.1/');

        $logger = new class extends AbstractLogger implements LoggerInterface {
            /** @var array<int, array{0: string, 1: string}> */
            public $records = array();

            public function log($level, $message, array $context = array())
            {
                $this->records[] = array((string) $level, (string) $message);
            }
        };
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);

        (new SiteContentDetector())->detectContent([], $idSite);

        // an intranet site is refused by default now, so the reason must clear the default WARN level
        $refusals = array_filter($logger->records, function (array $record) {
            return $record[0] === LogLevel::WARNING && strpos($record[1], 'was refused') !== false;
        });

        self::assertCount(1, $refusals, 'expected one WARNING refusal, got: ' . var_export($logger->records, true));
    }
}
