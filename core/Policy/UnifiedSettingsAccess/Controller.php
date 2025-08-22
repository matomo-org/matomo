<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UnifiedSettingsAccess;

use Piwik\Plugin\ControllerAdmin;

/**
 *
 */
class Controller extends ControllerAdmin
{
    public function index()
    {
        $usa = new UnifiedSettingsAccess();
        var_dump([
            $usa->getSetting('General.proxy_client_headers', [], UnifiedSettingsAccess::TYPE_ARRAY),
            $usa->getSetting('General.enabled_periods_UI', null, UnifiedSettingsAccess::TYPE_STRING),
        ]);
    }
}
