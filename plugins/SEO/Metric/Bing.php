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
 * Fetches the number of pages indexed in Bing.
 */
class Bing implements MetricsProvider
{
    const URL = 'https://www.bing.com/search?setlang=en-US&rdr=1&q=site%3A';

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
        $url = self::URL . urlencode($domain ?? '');
        $suffix = '';
        try {
            $response = str_replace('&nbsp;', ' ', Http::sendHttpRequest($url, $timeout = 10, @$_SERVER['HTTP_USER_AGENT']));
            $response = str_replace('&#160;', '', $response); // number uses nbsp as thousand separator

            if (preg_match('#([0-9,\.]+) results#i', $response, $p)) {
                $pageCount = NumberFormatter::getInstance()->formatNumber((int)str_replace(array(',', '.'), '', $p[1]));
                $suffix = 'General_Pages';
            } elseif (preg_match('#There are no results#i', $response, $p)) {
                $pageCount = Piwik::translate('General_ErrorTryAgain');
            } else {
                $pageCount = 0;
            }
        } catch (\Exception $e) {
            $this->logger->info('Error while getting Bing SEO stats: {message}', array('message' => $e->getMessage()));
            $pageCount = Piwik::translate('General_ErrorTryAgain');
        }

        $logo = "plugins/Morpheus/icons/dist/SEO/bing.com.png";

        return array(
            new Metric('bing-index', 'SEO_Bing_IndexedPages', $pageCount, $logo, null, null, $suffix)
        );
    }
}
