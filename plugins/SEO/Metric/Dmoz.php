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
 * Retrieves the number of Dmoz.org entries.
 */
class Dmoz implements MetricsProvider
{
    const URL = 'http://www.dmoz.org/search?q=';

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
        try {
            $response = Http::sendHttpRequest(self::URL . urlencode($domain), $timeout = 10, @$_SERVER['HTTP_USER_AGENT']);

            preg_match('#DMOZ Sites[^\(]+\([0-9]-[0-9]+ of ([0-9]+)\)#', $response, $p);
            if (!empty($p[1])) {
                $value = NumberFormatter::getInstance()->formatNumber((int)$p[1]);
            } else {
                $value = 0;
            }

            // Add DMOZ only if > 0 entries found
            if ($value == 0) {
                return array();
            }
        } catch (\Exception $e) {
            $this->logger->warning('Error while getting Dmoz SEO stats: {message}', array('message' => $e->getMessage()));
            $value = null;
        }

        $logo = \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl('http://dmoz.org');

        return array(
            new Metric('dmoz', 'SEO_Dmoz', $value, $logo)
        );
    }
}
