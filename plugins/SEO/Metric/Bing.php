<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

use Piwik\Http;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\SEO\tests\Integration\SEOTest;

/**
 * Fetches the number of pages indexed in Bing.
 */
class Bing implements MetricsProvider
{
    public const URL = 'https://www.bing.com/search?setlang=en-US&rdr=1&q=site%3A';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function getBingResponse(string $domain): string
    {
        $url = self::URL . urlencode($domain);

        $response = str_replace('&nbsp;', ' ', Http::sendHttpRequest($url, $timeout = 10, @$_SERVER['HTTP_USER_AGENT']));
        $response = str_replace('&#160;', '', $response); // number uses nbsp as the thousand separator

        return $response;
    }

    public function getMetrics($domain)
    {
        $logo = "plugins/Morpheus/icons/dist/SEO/bing.com.png";
        $suffix = '';
        $pageCount = Piwik::translate('General_ErrorTryAgain');

        if ($domain) {
            for ($i = 1; $i <= 3; $i++) {
                try {
                    $response = $this->getBingResponse($domain);

                    if (preg_match('#([0-9,\.]+) results#i', $response, $p)) {
                        $pageCount = NumberFormatter::getInstance()->formatNumber((int)str_replace([',', '.'], '', $p[1]));
                        $suffix = 'General_Pages';

                        break;
                    } else {
                        SEOTest::randomiseUserAgent();
                        sleep(10);
                    }
                } catch (\Exception $e) {
                    $this->logger->info('Error while getting Bing SEO stats: {message}', ['message' => $e->getMessage()]);
                }

            }
        }

        return array(
            new Metric('bing-index', 'SEO_Bing_IndexedPages', $pageCount, $logo, null, null, $suffix)
        );
    }
}
