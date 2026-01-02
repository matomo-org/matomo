<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Auth;

use Piwik\Auth\PasswordStrength;

/**
 * @group Core
 */
class PasswordStrengthTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRulesFeatureDisabled()
    {
        $passwordStrength = new PasswordStrength($featureEnabled = false);
        $rules = $passwordStrength->getRules();

        $this->assertEmpty($rules);
    }

    public function testGetRulesFeatureEnabled()
    {
        $passwordStrength = new PasswordStrength($featureEnabled = true);
        $rules = $passwordStrength->getRules();

        $this->assertNotEmpty($rules);
        foreach ($rules as $rule) {
            $this->assertArrayHasKey('validationRegex', $rule);
            $this->assertNotEmpty($rule['validationRegex']);
            $this->assertArrayHasKey('ruleText', $rule);
            $this->assertNotEmpty($rule['ruleText']);
        }
    }

    /**
     * @dataProvider passwordProvider
     */
    public function testPasswordStrengthFeatureDisabled($password, $expected)
    {
        $passwordStrength = new PasswordStrength($featureEnabled = false);
        $brokenRules = $passwordStrength->validatePasswordStrength($password);

        $this->assertEmpty($brokenRules);
    }

    /**
     * @dataProvider passwordProvider
     */
    public function testPasswordStrengthRulesFeatureEnabled($password, $expected)
    {
        $passwordStrength = new PasswordStrength($featureEnabled = true);
        $brokenRules = $passwordStrength->validatePasswordStrength($password);

        $this->assertSame($expected, $brokenRules);
    }

    public function passwordProvider()
    {
        yield ['Testpassword1!', []];
        yield ['Testpassword1"', []];
        yield ['Testpassword1#', []];
        yield ['Testpassword1$', []];
        yield ['Testpassword1%', []];
        yield ['Testpassword1&', []];
        yield ["Testpassword1'", []];
        yield ['Testpassword1(', []];
        yield ['Testpassword1)', []];
        yield ['Testpassword1*', []];
        yield ['Testpassword1+', []];
        yield ['Testpassword1,', []];
        yield ['Testpassword1-', []];
        yield ['Testpassword1.', []];
        yield ['Testpassword1/', []];
        yield ['Testpassword1:', []];
        yield ['Testpassword1;', []];
        yield ['Testpassword1<', []];
        yield ['Testpassword1=', []];
        yield ['Testpassword1>', []];
        yield ['Testpassword1?', []];
        yield ['Testpassword1@', []];
        yield ['Testpassword1[', []];
        yield ['Testpassword1\\', []];
        yield ['Testpassword1]', []];
        yield ['Testpassword1^', []];
        yield ['Testpassword1_', []];
        yield ['Testpassword1`', []];
        yield ['Testpassword1{', []];
        yield ['Testpassword1|', []];
        yield ['Testpassword1}', []];
        yield ['Testpassword1~', []];
        yield ['Testword1!', ['General_PasswordStrengthValidationLength']];
        yield ['TESTPASSWORD1!', ['General_PasswordStrengthValidationLowercase']];
        yield ['testpassword1!', ['General_PasswordStrengthValidationUppercase']];
        yield ['Testpassword!', ['General_PasswordStrengthValidationNumber']];
        yield ['Testpassword1', ['General_PasswordStrengthValidationSpecialChar']];
        yield ['testpassword1', [
            'General_PasswordStrengthValidationUppercase',
            'General_PasswordStrengthValidationSpecialChar',
        ]];
        yield ['TESTWORD!', [
            'General_PasswordStrengthValidationLength',
            'General_PasswordStrengthValidationLowercase',
            'General_PasswordStrengthValidationNumber',
        ]];
        yield ['', [
                'General_PasswordStrengthValidationLength',
                'General_PasswordStrengthValidationLowercase',
                'General_PasswordStrengthValidationUppercase',
                'General_PasswordStrengthValidationNumber',
                'General_PasswordStrengthValidationSpecialChar',
            ]];
    }
}
