<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Validator;

use Piwik\Validators\IpRanges;

/**
 * @group Validator
 * @group IpRanges
 * @group IpRangesTest
 */
class IpRangesTest extends \PHPUnit\Framework\TestCase
{
    public function test_validate_success()
    {
        self::expectNotToPerformAssertions();

        $this->validate(array(
            '12.12.12.12/32',
            '14.14.14.14',
            '15.15.15.*',
            '2001:db8::/48',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->validate(false);
        $this->validate('');
        $this->validate(null);
    }

    public function test_validate_failNotValidIpRange()
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('SitesManager_ExceptionInvalidIPFormat');
        $this->validate(array('127.0.0.1', 'foo'));
    }

    private function validate($value)
    {
        $validator = new IpRanges();
        $validator->validate($value);
    }
}
