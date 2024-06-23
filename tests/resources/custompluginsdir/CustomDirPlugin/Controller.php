<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin;

use Piwik\Config;
use Piwik\Container\StaticContainer;

class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        return $this->renderTemplate('index', array(
            'answerToLife' => 42,
            'diTest' => StaticContainer::get('customDirPluginTest')
        ));
    }
}
