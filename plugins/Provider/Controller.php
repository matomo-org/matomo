<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider;

use Piwik\ViewDataTable\Factory;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * Provider
     * @return string|void
     */
    public function getProvider()
    {
        return $this->renderReport(__FUNCTION__);
    }
}

