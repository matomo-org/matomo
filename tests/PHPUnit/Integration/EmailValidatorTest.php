<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Piwik;

/**
 * @group Core
 */
class EmailValidatorTest extends \PHPUnit\Framework\TestCase
{
    protected function isValid($email)
    {
        return Piwik::isValidEmailString($email);
    }

    private function getAllTlds()
    {
        /** @var array $response */
        $response = \Piwik\Http::sendHttpRequest("http://data.iana.org/TLD/tlds-alpha-by-domain.txt", 60, null, null, null, null, null, true);

        $this->assertEquals("200", $response['status']);

        $tlds = explode("\n", $response['data']);
        foreach ($tlds as $key => $tld) {
            if (strpos($tld, '#') !== false || $tld == "") {
                unset($tlds[$key]);
            }
        }
        $minimumTlds = 1200;
        $this->assertGreaterThan($minimumTlds, count($tlds), "expected to download at least $minimumTlds domain names");
        return $tlds;
    }

    private function skipTestIfIdnNotAvailable()
    {
        if (!function_exists('idn_to_utf8')) {
            $this->markTestSkipped("Function idn_to_utf8 does not exist, skip test");
        }
    }

    public function test_allCurrentTlds()
    {
        $this->skipTestIfIdnNotAvailable();

        $tlds = $this->getAllTlds();
        if (count($tlds) === 0) {
            $this->markTestSkipped("Couldn't get TLD list");
        }

        $errors = array();
        foreach ($tlds as $key => $tld) {
            if (strpos(mb_strtolower($tld), 'xn--') !== 0) {
                $tld = mb_strtolower($tld);
            }
            $domainNameExtension = idn_to_ascii($tld, 0, INTL_IDNA_VARIANT_UTS46);
            $email = 'test@example.' . $domainNameExtension;

            if(!$this->isValid($email)) {
                $errors[] = $domainNameExtension;
            }
        }

        // only fail when at least 10 domains are failing the test, so it does not fail every time IANA adds a new domain extension...
        if(count($errors) > 5) {
            $out = '';
            foreach($errors as $domainNameExtension) {
                $out .= "\t'$domainNameExtension' => array(1 => self::VALID_UNICODE_DOMAIN),\n";
            }
            $this->fail("Some email extensions are not supported yet, you can add these domain extensions in libs/Zend/Validate/Hostname.php: \n\n" . $out);
        }
    }

    public function test_invalidTld()
    {
        $this->skipTestIfIdnNotAvailable();

        $tlds = [
            strval(bin2hex(openssl_random_pseudo_bytes(64))), //generates 128 bit length string
            '-tld-cannot-start-from-hypen',
            'ąęśćżźł-there-is-no-such-idn',
            'xn--fd67as67fdsa', //no such idn punycode
            '!@#-inavlid-chars-in-tld',
            'no spaces in tld allowed',
            'no--double--hypens--allowed'
        ];
        if (count($tlds) === 0) {
            $this->markTestSkipped("Couldn't get TLD list");
        }

        foreach ($tlds as $key => $tld) {
            if (strpos(mb_strtolower($tld), 'xn--') !== 0) {
                $tld = mb_strtolower($tld);
            }
            $this->assertFalse(
                $this->isValid('test@example.' . idn_to_utf8($tld, 0, INTL_IDNA_VARIANT_UTS46))
            );
        }
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
