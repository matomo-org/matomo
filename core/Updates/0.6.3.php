<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_6_3 extends Piwik_Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'ALTER TABLE `' . Piwik_Common::prefixTable('log_visit') . '`
				CHANGE `location_ip` `location_ip` INT UNSIGNED NOT NULL'                                                                 => false,
            'ALTER TABLE `' . Piwik_Common::prefixTable('logger_api_call') . '`
				CHANGE `caller_ip` `caller_ip` INT UNSIGNED' => false,
        );
    }

    static function update()
    {
        $config = Piwik_Config::getInstance();
        $dbInfos = $config->database;
        if (!isset($dbInfos['schema'])) {
            try {
                if (is_writable(Piwik_Config::getLocalConfigPath())) {
                    $dbInfos['schema'] = 'Myisam';
                    $config->database = $dbInfos;
                    $config->forceSave();
                } else {
                    throw new Exception('mandatory update failed');
                }
            } catch (Exception $e) {
                throw new Piwik_Updater_UpdateErrorException("Please edit your config/config.ini.php file and add below <code>[database]</code> the following line: <br /><code>schema = Myisam</code>");
            }
        }

        Piwik_Updater::updateDatabase(__FILE__, self::getSql());
    }
}
