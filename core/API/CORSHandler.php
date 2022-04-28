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
use Piwik\Url;

class CORSHandler
{
    /**
     * @var array
     */
    protected $domains;

    public function __construct()
    {
        $this->domains = Url::getCorsHostsFromConfig();
    }

    public function handle()
    {

        // set default header
        Common::sendHeader('Vary: Origin');
        Common::sendHeader('Access-Control-Allow-Origin: *');

        if (!empty($this->domains) && !in_array('*', $this->domains) && !in_array($_SERVER['HTTP_ORIGIN'],
            $this->domains, true)) {
            Common::stripHeader('Access-Control-Allow-Credentials');
        }

        // when origin is set, response http origin as response
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            Common::sendHeader('Access-Control-Allow-Credentials: true');
            Common::sendHeader('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        }

        if ($this->isPreFlightCorsRequest()) {
            Common::sendHeader('Access-Control-Allow-Methods: GET, POST');
            Common::sendHeader('Access-Control-Allow-Headers: *');
            $this->logger->debug("Tracker detected preflight CORS request. Skipping...");
            return false;
        }

        return true;
    }

    public function isPreFlightCorsRequest(): bool
    {
        if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
            return !empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']) || !empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']);
        }
        return false;
    }
}
