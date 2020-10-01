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
        if (empty($this->domains)) {
            return;
        }
        
        Common::sendHeader('Vary: Origin');
        
        // allow Piwik to serve data to all domains
        if (in_array("*", $this->domains)) {
            
            Common::sendHeader('Access-Control-Allow-Credentials: true');
            
            if (!empty($_SERVER['HTTP_ORIGIN'])) {
                Common::sendHeader('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
                return;
            }
            
            Common::sendHeader('Access-Control-Allow-Origin: *');
            return;
        }

        // specifically allow if it is one of the allowlisted CORS domains
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            $origin = $_SERVER['HTTP_ORIGIN'];
            if (in_array($origin, $this->domains, true)) {
                Common::sendHeader('Access-Control-Allow-Credentials: true');
                Common::sendHeader('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            }
        }
    }
}
