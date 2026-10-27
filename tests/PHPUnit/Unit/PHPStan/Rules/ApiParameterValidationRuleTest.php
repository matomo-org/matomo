<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Piwik\Tests\PHPStan\Rules\ApiParameterValidationRule;

/**
 * @group Core
 * @extends RuleTestCase<ApiParameterValidationRule>
 */
class ApiParameterValidationRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ApiParameterValidationRule($this->createReflectionProvider());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/ApiParameterValidationApi.php'], [
            [$this->expectedMessage('missingSelfCheck'), 25],
            [$this->expectedMessage('selfCheckNotFirst'), 31],
            [$this->expectedMessage('selfCheckWithFunctionName'), 40],
            [$this->expectedMessage('selfCheckWithOtherArguments'), 48],
        ]);
    }

    private function expectedMessage(string $methodName): string
    {
        return 'API method ' . $methodName . '() has parameter validator attributes, so its first statement must be'
            . ' $this->checkParameters(__METHOD__, get_defined_vars()); otherwise the validators never run.';
    }
}
