<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Updates;

use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_2_0_rc1 extends Updates
{
    public static function update()
    {
        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('Morpheus');
        } catch(\Exception $e) {
        }
    }
}
