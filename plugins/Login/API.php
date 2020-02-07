<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login;

use Piwik\Piwik;
use Piwik\Plugins\Login\Security\BruteForceDetection;

/**
 * API for plugin Login
 *
 * @method static \Piwik\Plugins\Login\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var BruteForceDetection
     */
    private $bruteForceDetection;

    public function __construct(BruteForceDetection $bruteForceDetection)
    {
        $this->bruteForceDetection = $bruteForceDetection;
    }

    public function unblockBruteForceIPs()
    {
        Piwik::checkUserHasSuperUserAccess();

        $ips = $this->bruteForceDetection->getCurrentlyBlockedIps();
        if (!empty($ips)) {
            foreach ($ips as $ip) {
                $this->bruteForceDetection->unblockIp($ip);
            }
        }
    }
}
