<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Unit\Dimension;

use Piwik\Plugins\CustomDimensions\Dimension\Active;

/**
 * @group CustomDimensions
 * @group ActiveTest
 * @group Active
 * @group Plugins
 */
class ActiveTest extends \PHPUnit\Framework\TestCase
{
    public function test_check_shouldFailWhenActiveIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid value '' for 'active' specified. Allowed values: '0' or '1'");

        $this->buildActive('')->check();
    }

    public function test_check_shouldFailWhenActiveIsNotValid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid value 'anyValUe' for 'active' specified. Allowed values: '0' or '1'");

        $this->buildActive('anyValUe')->check();
    }

    public function test_check_shouldFailWhenActiveIsNumericButNot0or1()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid value '2'");

        $this->buildActive('2')->check();
    }

    public function test_check_shouldNotFailWhenActiveIsValid()
    {
        self::expectNotToPerformAssertions();

        $this->buildActive(true)->check();
        $this->buildActive(false)->check();
        $this->buildActive(0)->check();
        $this->buildActive(1)->check();
        $this->buildActive('0')->check();
        $this->buildActive('1')->check();
    }

    private function buildActive($active)
    {
        return new Active($active);
    }
}
