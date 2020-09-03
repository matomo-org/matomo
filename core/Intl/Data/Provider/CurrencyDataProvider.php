<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Intl\Data\Provider;

use Piwik\Config;

/**
 * Provides currency data.
 */
class CurrencyDataProvider
{
    private $currencyList;

    /**
     * Returns the list of all known currency symbols.
     *
     * @return array An array mapping currency codes to their respective currency symbols
     *               and a description, eg, `array('USD' => array('$', 'US dollar'))`.
     * @api
     */
    public function getCurrencyList()
    {
        if ($this->currencyList === null) {
            $this->currencyList = require __DIR__ . '/../Resources/currencies.php';

            $custom = Config::getInstance()->General['currencies'];
            foreach ($custom as $code => $name) {
                $this->currencyList[$code] = array($code, $name);
            }
        }

        return $this->currencyList;
    }
}
