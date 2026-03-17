<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Intl\Data\Provider;

use PHPUnit\Framework\TestCase;
use Piwik\Config\GeneralConfig;
use Piwik\Intl\Data\Provider\CurrencyDataProvider;

class CurrencyDataProviderTest extends TestCase
{
    private $originalCurrenciesConfig;

    protected function setUp(): void
    {
        $this->originalCurrenciesConfig = GeneralConfig::getConfigValue('currencies');
    }

    protected function tearDown(): void
    {
        GeneralConfig::setConfigValue('currencies', $this->originalCurrenciesConfig);
    }

    public function testGetCurrencyListAddsCustomCurrenciesFromConfig(): void
    {
        GeneralConfig::setConfigValue('currencies', ['XTS' => 'Test Currency']);

        $provider = new CurrencyDataProvider();
        $currencyList = $provider->getCurrencyList();

        $this->assertSame(['XTS', 'Test Currency'], $currencyList['XTS']);
        $this->assertArrayHasKey('USD', $currencyList);
    }

    public function testGetCurrencyListIgnoresMisconfiguredCurrenciesConfig(): void
    {
        GeneralConfig::setConfigValue('currencies', 'invalid');

        $provider = new CurrencyDataProvider();
        $currencyList = $provider->getCurrencyList();

        $this->assertArrayHasKey('USD', $currencyList);
        $this->assertArrayNotHasKey('BTC', $currencyList);
    }

    public function testGetCurrencyListUsesDefaultConfiguredCurrenciesUnlessOverwritten(): void
    {
        $provider = new CurrencyDataProvider();
        $defaultCurrencyList = $provider->getCurrencyList();

        $this->assertSame(['BTC', 'Bitcoin'], $defaultCurrencyList['BTC']);

        GeneralConfig::setConfigValue('currencies', ['XTS' => 'Test Currency']);

        $overwrittenCurrencyList = (new CurrencyDataProvider())->getCurrencyList();

        $this->assertArrayNotHasKey('BTC', $overwrittenCurrencyList);
    }
}
