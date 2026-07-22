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
use Piwik\Tests\PHPStan\Rules\JsonResponseMustNotEmitOutputRule;

/**
 * @group Core
 * @extends RuleTestCase<JsonResponseMustNotEmitOutputRule>
 */
class JsonResponseMustNotEmitOutputRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new JsonResponseMustNotEmitOutputRule();
    }

    public function testRule(): void
    {
        $message = 'Controller action %s() is marked #[\Piwik\Http\JsonResponse] but emits output'
            . ' (echo/print/flush) before returning; this commits the response headers early and can'
            . ' prevent the JSON Content-Type from being applied. Return the JSON string instead.';

        // wrongReturnType() and exitsEarly() (defined earlier for other rules) also echo, so they
        // are flagged here too; emitsOutput() has both an echo and a flush().
        $this->analyse([__DIR__ . '/data/JsonResponseController.php'], [
            [sprintf($message, 'wrongReturnType'), 67],
            [sprintf($message, 'exitsEarly'), 79],
            [sprintf($message, 'emitsOutput'), 197],
            [sprintf($message, 'emitsOutput'), 198],
        ]);
    }
}
