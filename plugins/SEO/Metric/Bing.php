<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

use Piwik\Http;
use Piwik\NumberFormatter;
use Psr\Log\LoggerInterface;

/**
 * Fetches the number of pages indexed in Bing.
 */
class Bing implements MetricsProvider
{
    const URL = 'http://www.bing.com/search?mkt=en-US&q=site%3A';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getMetrics($domain)
    {
        $url = self::URL . urlencode($domain);

        try {
            $response = str_replace('&nbsp;', ' ', Http::sendHttpRequest($url, $timeout = 10, @$_SERVER['HTTP_USER_AGENT']));

            if (preg_match('#([0-9\,]+) results#i', $response, $p)) {
                $pageCount = NumberFormatter::getInstance()->formatNumber((int)str_replace(',', '', $p[1]));
            } else {
                $pageCount = 0;
            }
        } catch (\Exception $e) {
            $this->logger->warning('Error while getting Bing SEO stats: {message}', array('message' => $e->getMessage()));
            $pageCount = null;
        }

        $logo = \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://bing.com');

        return array(
            new Metric('bing-index', 'SEO_Bing_IndexedPages', $pageCount, $logo, null, null, 'General_Pages')
        );
    }
}
