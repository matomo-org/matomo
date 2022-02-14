<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

use Piwik\Http;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Psr\Log\LoggerInterface;

/**
 * Retrieves Google PageRank.
 */
class Google implements MetricsProvider
{
    const SEARCH_URL = 'https://www.google.com/search?hl=en&q=site%3A';

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

        $logo = "plugins/Morpheus/icons/dist/SEO/google.com.png";

        $url = self::SEARCH_URL . urlencode($domain ?? '');
        $suffix = '';
        try {
            $response = str_replace('&nbsp;', ' ', Http::sendHttpRequest($url, $timeout = 10, @$_SERVER['HTTP_USER_AGENT']));

            if (preg_match('#([0-9,\.]+) results#i', $response, $p)) {
                $pageCount = NumberFormatter::getInstance()->formatNumber((int)str_replace(array(',', '.'), '', $p[1]));
                $suffix = 'General_Pages';
            } elseif (preg_match('#did not match any#i', $response, $p)) {
                $pageCount = Piwik::translate('General_ErrorTryAgain');
            } else {
                $pageCount = 0;
            }
        } catch (\Exception $e) {
            $this->logger->info('Error while getting Google search SEO stats: {message}', array('message' => $e->getMessage()));
            $pageCount = Piwik::translate('General_ErrorTryAgain');
        }

        return array(
            new Metric('google-index', 'SEO_Google_IndexedPages', $pageCount, $logo, null, null, $suffix),
        );
    }


}
