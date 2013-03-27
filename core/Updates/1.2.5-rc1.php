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
class Piwik_Updates_1_2_5_rc1 extends Piwik_Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            'ALTER TABLE `' . Piwik_Common::prefixTable('goal') . '`
		    	ADD `allow_multiple` tinyint(4) NOT NULL AFTER case_sensitive'                                                         => false,
            'ALTER TABLE `' . Piwik_Common::prefixTable('log_conversion') . '`
				ADD buster int unsigned NOT NULL AFTER revenue,
				DROP PRIMARY KEY,
		    	ADD PRIMARY KEY (idvisit, idgoal, buster)' => false,
        );
    }

    static function update()
    {
        Piwik_Updater::updateDatabase(__FILE__, self::getSql());
    }
}

