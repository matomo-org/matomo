<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\API;

use PHPUnit\Framework\TestCase;
use Piwik\API\Proxy;
use ReflectionMethod;

class ProxyTest extends TestCase
{
    public function testSkipsCompoundParametersWithoutPreventingOtherMethodsFromLoading(): void
    {
        $api = new class {
            public function compoundReport(int|string $label): void
            {
            }

            public function namedReport(?int $limit = null): void
            {
            }
        };

        $proxy = new Proxy();
        $loadMetadata = new ReflectionMethod(Proxy::class, 'loadMethodMetadata');
        $loadMetadata->invoke($proxy, $api::class, new ReflectionMethod($api, 'compoundReport'));
        $loadMetadata->invoke($proxy, $api::class, new ReflectionMethod($api, 'namedReport'));

        $this->assertArrayNotHasKey('compoundReport', $proxy->getMetadata()[$api::class]);
        $this->assertSame([
            'limit' => ['default' => null, 'type' => 'int', 'allowsNull' => true],
        ], $proxy->getParametersListWithTypes($api::class, 'namedReport'));
    }
}
