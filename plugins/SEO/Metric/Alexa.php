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
use Psr\Log\LoggerInterface;

/**
 * Retrieves the Alexa rank.
 */
class Alexa implements MetricsProvider
{
    const URL = 'https://www.alexa.com/minisiteinfo/';
    const LINK = 'https://www.alexa.com/siteinfo/';

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
        $value = null;
        try {
            $response = Http::sendHttpRequest(self::URL . urlencode($domain), $timeout = 10, @$_SERVER['HTTP_USER_AGENT']);
            $dom = new \DomDocument();
            $dom->loadHTML($response);
            $nodes = (new \DomXPath($dom))->query("//div[contains(@class, 'data')]");
            if (isset($nodes[0]->nodeValue)) {
                $globalRanking = (int) str_replace(array(',', '.'), '', $nodes[0]->nodeValue);
                $value = NumberFormatter::getInstance()->formatNumber($globalRanking);
            }
        } catch (\Exception $e) {
            $this->logger->warning('Error while getting Alexa SEO stats via fallback method: {message}', array('message' => $e->getMessage()));
        }

        $logo = "plugins/Morpheus/icons/dist/SEO/alexa.com.png";
        $link = self::LINK . urlencode($domain);

        return array(
            new Metric('alexa', 'SEO_AlexaRank', $value, $logo, $link)
        );
    }
}
