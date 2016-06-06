<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_0_5_4 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_action') . '`
				 CHANGE `name` `name` TEXT' => false,
        );
    }

    public function doUpdate(Updater $updater)
    {
        $salt = Common::generateUniqId();
        $config = Config::getInstance();
        $superuser = $config->superuser;
        if (!isset($superuser['salt'])) {
            try {
                if (is_writable(Config::getLocalConfigPath())) {
                    $superuser['salt'] = $salt;
                    $config->superuser = $superuser;
                    $config->forceSave();
                } else {
                    throw new \Exception('mandatory update failed');
                }
            } catch (\Exception $e) {
                throw new \Piwik\UpdaterErrorException("Please edit your config/config.ini.php file and add below <code>[superuser]</code> the following line: <br /><code>salt = $salt</code>");
            }
        }

        $plugins = $config->Plugins;
        if (!in_array('MultiSites', $plugins)) {
            try {
                if (is_writable(Config::getLocalConfigPath())) {
                    $plugins[] = 'MultiSites';
                    $config->Plugins = $plugins;
                    $config->forceSave();
                } else {
                    throw new \Exception('optional update failed');
                }
            } catch (\Exception $e) {
                throw new \Exception("You can now enable the new MultiSites plugin in the Plugins screen in the Piwik admin!");
            }
        }

        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
