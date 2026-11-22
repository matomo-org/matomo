<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

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
            [$this->expectedMessage('missingSelfCheck'), 28],
            [$this->expectedMessage('selfCheckNotFirst'), 34],
            [$this->expectedMessage('selfCheckWithFunctionName'), 43],
            [$this->expectedMessage('selfCheckWithOtherArguments'), 51],
            ['The default value of parameter $period of API method rejectedDefault() does not pass its validator attributes.', 69],
        ]);
    }

    private function expectedMessage(string $methodName): string
    {
        return 'API method ' . $methodName . '() has parameter validator attributes, so its first statement must be'
            . ' $this->checkParameters(__METHOD__, get_defined_vars()); otherwise the validators never run.';
    }
}
