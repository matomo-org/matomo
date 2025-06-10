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
    public function testGetRulesFeatureEnabled()
    {
        $PasswordStrength = new PasswordStrength($featureEnabled = true);
        $rules = $PasswordStrength->getRules();

        $this->assertCount(5, $rules);
        $this->assertSame([
            [
                'validationRegex' => '/^.{12,}$/',
                'ruleText' => 'General_PasswordStrengthValidationLength'
            ],
            [
                'validationRegex' => '/^.*[a-z].*$/',
                'ruleText' => 'General_PasswordStrengthValidationLowercase'
            ],
            [
                'validationRegex' => '/^.*[A-Z].*$/',
                'ruleText' => 'General_PasswordStrengthValidationUppercase'
            ],
            [
                'validationRegex' => '/^.*[0-9].*$/',
                'ruleText' => 'General_PasswordStrengthValidationNumber'
            ],
            [
                'validationRegex' => '/^.*[!@#$%^&*(){}[\]\'\`\\\|\"\~].*$/',
                'ruleText' => 'General_PasswordStrengthValidationSpecialChar'
            ],
        ], $rules);
    }

    public function testGetRulesFeatureDisabled()
    {
        $passwordStrength = new PasswordStrength($featureEnabled = false);
        $rules = $passwordStrength->getRules();

        $this->assertEmpty($rules);
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
        return array(
            array('Testpassword1!', []),
            array('Testword1!', ['General_PasswordStrengthValidationLength']),
            array('TESTPASSWORD1!', ['General_PasswordStrengthValidationLowercase']),
            array('testpassword1!', ['General_PasswordStrengthValidationUppercase']),
            array('Testpassword!', ['General_PasswordStrengthValidationNumber']),
            array('Testpassword1', ['General_PasswordStrengthValidationSpecialChar']),
            array('', [
                'General_PasswordStrengthValidationLength',
                'General_PasswordStrengthValidationLowercase',
                'General_PasswordStrengthValidationUppercase',
                'General_PasswordStrengthValidationNumber',
                'General_PasswordStrengthValidationSpecialChar'
            ])
        );
    }
}
