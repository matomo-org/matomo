<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Piwik;

/**
 * @group Core
 */
class EmailValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected function isValid($email)
    {
        return Piwik::isValidEmailString($email);
    }

    public function test_isValid_validStandard()
    {
        $this->assertTrue($this->isValid('test@example.com'));
    }

    public function test_isValid_unknownTld()
    {
        $this->assertTrue($this->isValid('test@example.unknown'));
    }

    public function test_isValid_validUpperCaseLocalPart()
    {
        $this->assertTrue($this->isValid('TEST@example.com'));
    }

    public function test_isValid_validNumericLocalPart()
    {
        $this->assertTrue($this->isValid('1234567890@example.com'));
    }

    public function test_isValid_validTaggedLocalPart()
    {
        $this->assertTrue($this->isValid('test+test@example.com'));
    }

    public function test_isValid_validQmailLocalPart()
    {
        $this->assertTrue($this->isValid('test-test@example.com'));
    }

    public function test_isValid_validUnusualCharactersInLocalPart()
    {
        $this->assertTrue($this->isValid('t*est@example.com'));
        $this->assertTrue($this->isValid('+1~1+@example.com'));
        $this->assertTrue($this->isValid('{_test_}@example.com'));
    }

    public function test_isValid_validQuotedLocalPart()
    {
        $this->assertTrue($this->isValid('"[[ test ]]"@example.com'));
    }

    public function test_isValid_validAtomisedLocalPart()
    {
        $this->assertTrue($this->isValid('test.test@example.com'));
    }

    public function test_isValid_validQuotedAtLocalPart()
    {
        $this->assertTrue($this->isValid('"test@test"@example.com'));
    }

    public function test_isValid_validMultipleLabelDomain()
    {
        $this->assertTrue($this->isValid('test@example.example.com'));
        $this->assertTrue($this->isValid('test@example.example.example.com'));
    }

    public function test_isValid_invalidTooLong()
    {
        $this->assertFalse($this->isValid('12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345@example.com'));
    }

    public function test_isValid_invalidTooShort()
    {
        $this->assertFalse($this->isValid('@a'));
    }

    public function test_isValid_invalidNoAtSymbol()
    {
        $this->assertFalse($this->isValid('test.example.com'));
    }

    public function test_isValid_invalidBlankAtomInLocalPart()
    {
        $this->assertFalse($this->isValid('test.@example.com'));
        $this->assertFalse($this->isValid('test..test@example.com'));
        $this->assertFalse($this->isValid('.test@example.com'));
    }

    public function test_isValid_invalidMultipleAtSymbols()
    {
        $this->assertFalse($this->isValid('test@test@example.com'));
        $this->assertFalse($this->isValid('test@@example.com'));
    }

    public function test_isValid_invalidInvalidCharactersInLocalPart()
    {
        $this->assertFalse($this->isValid('-- test --@example.com'));
        $this->assertFalse($this->isValid('[test]@example.com'));
        $this->assertFalse($this->isValid('"test"test"@example.com'));
        $this->assertFalse($this->isValid('()[]\;:,<>@example.com'));
    }

    public function test_isValid_invalidDomainLabelTooShort()
    {
        $this->assertFalse($this->isValid('test@.'));
        $this->assertFalse($this->isValid('test@example.'));
        $this->assertFalse($this->isValid('test@.org'));
    }

    public function test_isValid_invalidLocalPartTooLong()
    {
        $this->assertFalse($this->isValid('12345678901234567890123456789012345678901234567890123456789012345@example.com')); // 64 characters is maximum length for local part
    }

    public function test_isValid_invalidDomainLabelTooLong()
    {
        $this->assertFalse($this->isValid('test@123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012.com')); // 255 characters is maximum length for domain. This is 256.
    }

    public function test_isValid_invalidTooFewLabelsInDomain()
    {
        $this->assertFalse($this->isValid('test@example'));
    }

    public function test_isValid_invalidUnpartneredSquareBracketIp()
    {
        $this->assertFalse($this->isValid('test@[123.123.123.123'));
        $this->assertFalse($this->isValid('test@123.123.123.123]'));
    }
}
