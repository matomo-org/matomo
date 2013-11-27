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
class Updates_2_0_b10 extends Updates
{
    static function update()
    {
        parent::deletePluginFromConfigFile('Referers');
        parent::deletePluginFromConfigFile('PDFReports');
    }
}
