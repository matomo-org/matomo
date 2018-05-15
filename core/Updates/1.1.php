<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\Updates;
use Piwik\Updater;

/**
 */
class Updates_1_1 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $config = Config::getInstance();

        try {
            $superuser = $config->superuser;
        } catch (\Exception $e) {
            return;
        }

        if (empty($superuser['login'])) {
            return;
        }

        $rootLogin = $superuser['login'];
        try {
            // throws an exception if invalid
            Piwik::checkValidLoginString($rootLogin);
        } catch (\Exception $e) {
            throw new \Exception('Superuser login name "' . $rootLogin . '" is no longer a valid format. '
                . $e->getMessage()
                . ' Edit your config/config.ini.php to change it.');
        }
    }
}
