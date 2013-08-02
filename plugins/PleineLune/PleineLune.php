<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package PleineLune
 */
namespace Piwik\Plugins\PleineLune;

use Piwik\AssetManager;

/**
 *
 * @package PleineLune
 */
class PleineLune extends \Piwik\Plugin
{
    public function getListHooksRegistered()
    {
        return array(
            AssetManager::CSS_IMPORT_EVENT => 'getCssFiles',
        );
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/PleineLune/stylesheets/theme.less";
    }
}
