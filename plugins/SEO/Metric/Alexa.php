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
use Piwik\Plugins\Referrers\SearchEngine;
use Psr\Log\LoggerInterface;

/**
 * Retrieves the Alexa rank.
 */
class Alexa implements MetricsProvider
{
    const URL = 'http://data.alexa.com/data?cli=10&url=';
    const LINK = 'http://www.alexa.com/siteinfo/';

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
        try {
            $response = Http::sendHttpRequest(self::URL . urlencode($domain), $timeout = 10, @$_SERVER['HTTP_USER_AGENT']);

            $xml = @simplexml_load_string($response);
            $value = $xml ? NumberFormatter::getInstance()->formatNumber((int)$xml->SD->POPULARITY['TEXT']) : null;
        } catch (\Exception $e) {
            $this->logger->warning('Error while getting Alexa SEO stats: {message}', array('message' => $e->getMessage()));
            $value = null;
        }

        $logo = SearchEngine::getInstance()->getLogoFromUrl('http://alexa.com');
        $link = self::LINK . urlencode($domain);

        return array(
            new Metric('alexa', 'SEO_AlexaRank', $value, $logo, $link)
        );
    }
}
