<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\Url;
use Psr\Log\LoggerInterface;

class CORSHandler
{
    /**
     * @var array
     */
    protected $domains;

    private $logger;

    public function __construct()
    {
        $this->domains = Url::getCorsHostsFromConfig();
        $this->logger = StaticContainer::get(LoggerInterface::class);

    }

    /**
     * This method implement the same-origin policy.
     * By default, it allows from all URL.
     * To limit the request URL, please read FAQ https://matomo.org/faq/how-to/faq_18694/
     * For preFlight request this method will exit
     * @throws \Exception
     */
    public function handle()
    {

        // set default header
        Common::sendHeader('Vary: Origin');
        Common::sendHeader('Access-Control-Allow-Credentials: true');
        Common::sendHeader('Access-Control-Allow-Origin: *');


        // when origin is set, response http origin as response
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            Common::sendHeader('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        }

        //check if http origin is not in the cor_domain list
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

        //check if is preFight
        if (self::isPreFlightCorsRequest()) {
            Common::sendHeader('Access-Control-Allow-Methods: GET, POST');
            Common::sendHeader('Access-Control-Allow-Headers: *');
            Common::sendResponseCode(204);
            $this->logger->debug("Tracker detected preflight CORS request. Skipping...");
            exit;
        }

    }

    public static function outputAccessControlHeaders()
    {
        if (Http::isHttpGetRequest()) {
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
            Common::sendHeader('Access-Control-Allow-Origin: ' . $origin);
            Common::sendHeader('Access-Control-Allow-Credentials: true');
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
