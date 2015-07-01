<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class EmailValidatorTest extends IntegrationTestCase
{
    protected function isValid($email)
    {
        return Piwik::isValidEmailString($email);
    }

    public function testValidStandard()
    {
        $this->assertTrue($this->isValid('test@example.com'));
    }

    public function testValidUpperCaseLocalPart()
    {
        $this->assertTrue($this->isValid('TEST@example.com'));
    }

    public function testValidNumericLocalPart()
    {
        $this->assertTrue($this->isValid('1234567890@example.com'));
    }

    public function testValidTaggedLocalPart()
    {
        $this->assertTrue($this->isValid('test+test@example.com'));
    }

    public function testValidQmailLocalPart()
    {
        $this->assertTrue($this->isValid('test-test@example.com'));
    }

    public function testValidUnusualCharactersInLocalPart()
    {
        $this->assertTrue($this->isValid('t*est@example.com'));
        $this->assertTrue($this->isValid('+1~1+@example.com'));
        $this->assertTrue($this->isValid('{_test_}@example.com'));
    }

    public function testValidQuotedLocalPart()
    {
        $this->assertTrue($this->isValid('"[[ test ]]"@example.com'));
    }

    public function testValidAtomisedLocalPart()
    {
        $this->assertTrue($this->isValid('test.test@example.com'));
    }

    public function testValidQuotedAtLocalPart()
    {
        $this->assertTrue($this->isValid('"test@test"@example.com'));
    }

    public function testValidMultipleLabelDomain()
    {
        $this->assertTrue($this->isValid('test@example.example.com'));
        $this->assertTrue($this->isValid('test@example.example.example.com'));
    }

    public function testInvalidTooLong()
    {
        $this->assertFalse($this->isValid('12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345@example.com'));
    }

    public function testInvalidTooShort()
    {
        $this->assertFalse($this->isValid('@a'));
    }

    public function testInvalidNoAtSymbol()
    {
        $this->assertFalse($this->isValid('test.example.com'));
    }

    public function testInvalidBlankAtomInLocalPart()
    {
        $this->assertFalse($this->isValid('test.@example.com'));
        $this->assertFalse($this->isValid('test..test@example.com'));
        $this->assertFalse($this->isValid('.test@example.com'));
    }

    public function testInvalidMultipleAtSymbols()
    {
        $this->assertFalse($this->isValid('test@test@example.com'));
        $this->assertFalse($this->isValid('test@@example.com'));
    }

    public function testInvalidInvalidCharactersInLocalPart()
    {
        $this->assertFalse($this->isValid('-- test --@example.com'));
        $this->assertFalse($this->isValid('[test]@example.com'));
        $this->assertFalse($this->isValid('"test"test"@example.com'));
        $this->assertFalse($this->isValid('()[]\;:,<>@example.com'));
    }

    public function testInvalidDomainLabelTooShort()
    {
        $this->assertFalse($this->isValid('test@.'));
        $this->assertFalse($this->isValid('test@example.'));
        $this->assertFalse($this->isValid('test@.org'));
    }

    public function testInvalidLocalPartTooLong()
    {
        $this->assertFalse($this->isValid('12345678901234567890123456789012345678901234567890123456789012345@example.com')); // 64 characters is maximum length for local part
    }

    public function testInvalidDomainLabelTooLong()
    {
        $this->assertFalse($this->isValid('test@123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012.com')); // 255 characters is maximum length for domain. This is 256.
    }

    public function testInvalidTooFewLabelsInDomain()
    {
        $this->assertFalse($this->isValid('test@example'));
    }

    public function testInvalidUnpartneredSquareBracketIp()
    {
        $this->assertFalse($this->isValid('test@[123.123.123.123'));
        $this->assertFalse($this->isValid('test@123.123.123.123]'));
    }
}
