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
 * Retrieves Google PageRank.
 */
class Google implements MetricsProvider
{
    const SEARCH_URL = 'http://www.google.com/search?hl=en&q=site%3A';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getMetrics($domain)
    {
        $pageCount = $this->fetchIndexedPagesCount($domain);

        $logo = "plugins/Morpheus/icons/dist/SEO/google.com.png";

        return array(
            new Metric('google-index', 'SEO_Google_IndexedPages', $pageCount, $logo, null, null, 'General_Pages'),
        );
    }

    public function fetchIndexedPagesCount($domain)
    {
        $url = self::SEARCH_URL . urlencode($domain);

        try {
            $response = str_replace('&nbsp;', ' ', Http::sendHttpRequest($url, $timeout = 10, @$_SERVER['HTTP_USER_AGENT']));

            if (preg_match('#([0-9,\.]+) results#i', $response, $p)) {
                return NumberFormatter::getInstance()->formatNumber((int)str_replace(array(',', '.'), '', $p[1]));
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            $this->logger->warning('Error while getting Google search SEO stats: {message}', array('message' => $e->getMessage()));
            return null;
        }
    }

}
