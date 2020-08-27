<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Marketplace\Input;
use Piwik\Common;
use Piwik\Plugin;
use Exception;

/**
 */
class PluginName
{
    private $requestParam = '';

    public function __construct($requestParam = 'pluginName')
    {
        $this->requestParam = $requestParam;
    }

    public function getPluginName()
    {
        $pluginName = Common::getRequestVar($this->requestParam, null, 'string');

        $this->dieIfPluginNameIsInvalid($pluginName);

        return $pluginName;
    }

    private function dieIfPluginNameIsInvalid($pluginName)
    {
        if (!Plugin\Manager::getInstance()->isValidPluginName($pluginName)){
            throw new Exception('Invalid plugin name given');
        }
    }

}
