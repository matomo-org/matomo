<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Intl\Data\Provider;

use Piwik\Config\GeneralConfig;

/**
 * Provides currency data.
 */
class CurrencyDataProvider
{
    /**
     * @var array<string, array{0: string, 1: string}>|null
     */
    private $currencyList;

    /**
     * Returns the list of all known currency symbols.
     *
     * @return array An array mapping currency codes to their respective currency symbols
     *               and a description, eg, `array('USD' => array('$', 'US dollar'))`.
     * @phpstan-return array<string, array{0: string, 1: string}>
     * @api
     */
    public function getCurrencyList()
    {
        if ($this->currencyList === null) {
            $this->currencyList = require __DIR__ . '/../Resources/currencies.php';

            /** @var array<string, string>|null $custom */
            $custom = GeneralConfig::getConfigValue('currencies');
            if (is_array($custom)) {
                foreach ($custom as $code => $name) {
                    $this->currencyList[$code] = [$code, $name];
                }
            }
        }

        return $this->currencyList;
    }
}
