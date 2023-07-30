<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck;

use Piwik\Date;
use Piwik\Nonce;
use Piwik\Option;
use Piwik\Piwik;


class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function index()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $nonceString = Nonce::getNonce(JsTrackerInstallCheck::NONCE_NAME);
        // Discard the nonce since we're storing it in the option table and won't need it in the session
        Nonce::discardNonce(JsTrackerInstallCheck::NONCE_NAME);
        Option::set(JsTrackerInstallCheck::OPTION_NAME_PREFIX . $this->idSite, json_encode([
            'nonce' => $nonceString,
            'time' => Date::getNowTimestamp(),
            'isSuccessful' => false
        ]));

        return $this->renderTemplate('index', [
            'nonce' => $nonceString,
        ]);
    }
}
