<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Marketplace\Input;
use Piwik\Common;
use Piwik\Plugins\Marketplace\Consumer;

/**
 */
class PurchaseType
{
    const TYPE_FREE = 'free';
    const TYPE_PAID = 'paid';
    const TYPE_ALL  = '';
    
    public function getPurchaseType()
    {
        $defaultType = static::TYPE_ALL;

        $type = Common::getRequestVar('type', $defaultType, 'string');

        return $type;
    }

}
