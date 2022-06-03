<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Url;
use Psr\Log\LoggerInterface;

class CORSHandler
{
    /**
     * @var array
     */
    protected $domains;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->domains = Url::getCorsHostsFromConfig();
        $this->logger = StaticContainer::get(LoggerInterface::class);
    }

    /**
     * This method implements the same-origin policy.
     * By default, it allows from all URLs (*).
     * To limit the request URL, please read FAQ https://matomo.org/faq/how-to/faq_18694/
     * For preFlight requests and requests from disallowed origins this method will exit
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        // set default header
        Common::sendHeader('Vary: Origin');
        Common::sendHeader('Access-Control-Allow-Credentials: true');
        Common::sendHeader('Access-Control-Allow-Origin: *');

        // when origin is set, response http origin as response
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            Common::sendHeader('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        }

        // check if http origin is not in the cors_domain list
        if (!empty($this->domains) && !empty($_SERVER['HTTP_ORIGIN'])) {
            if (!in_array('*', $this->domains) && !in_array($_SERVER['HTTP_ORIGIN'], $this->domains, true)) {
                Common::sendHeader('Access-Control-Allow-Origin: ' . $this->domains[0], true);
                if (self::isPreFlightCorsRequest()) {
                    Common::sendResponseCode(403);
                } else {
                    Common::sendResponseCode(401);
                }
                $this->logger->debug("Detected CORS request. Skipping...");
                exit;
            }
        }

        // check if is preFlight
        if (self::isPreFlightCorsRequest()) {
            Common::sendHeader('Access-Control-Allow-Methods: GET, POST');
            Common::sendHeader('Access-Control-Allow-Headers: *');
            Common::sendResponseCode(204);
            $this->logger->debug("Detected preflight CORS request. Skipping...");
            exit;
        }
    }

    /**
     * Check if the request is pre fight cors request
     * @return bool
     */
    public static function isPreFlightCorsRequest(): bool
    {
        if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
            return !empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']) || !empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']);
        }
        return false;
    }
}
