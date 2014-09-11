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
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_1_9_b19 extends Updates
{
    static function getSql()
    {
        return array(
            'ALTER TABLE  `' . Common::prefixTable('log_link_visit_action') . '`
			CHANGE `idaction_url_ref` `idaction_url_ref` INT( 10 ) UNSIGNED NULL DEFAULT 0'
            => false,
            'ALTER TABLE  `' . Common::prefixTable('log_visit') . '`
			CHANGE `visit_exit_idaction_url` `visit_exit_idaction_url` INT( 10 ) UNSIGNED NULL DEFAULT 0'
            => false
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());

        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('Transitions');
        } catch (\Exception $e) {
        }
    }
}

