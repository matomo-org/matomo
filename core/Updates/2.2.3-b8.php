<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Updates;

use Faker\Provider\File;
use Piwik\Filesystem;
use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Updates;

class Updates_2_2_3_b8 extends Updates
{
    public static function update()
    {
        try {
            \Piwik\Plugin\Manager::getInstance()->deactivatePlugin('Morpheus');
            \Piwik\Plugin\Manager::getInstance()->deactivatePlugin('Zeitgeist');
        } catch(\Exception $e) {
        }
    }
}
