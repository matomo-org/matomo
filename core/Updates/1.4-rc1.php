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
use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Piwik_Updates_1_4_rc1 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('pdf') . '`
		    	ADD COLUMN `format` VARCHAR(10)'                                                => false,
            'UPDATE `' . Common::prefixTable('pdf') . '`
		    	SET format = "pdf"' => false,
        );
    }

    static function update()
    {
        try {
            Updater::updateDatabase(__FILE__, self::getSql());
        } catch (Exception $e) {
        }
    }
}
