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
use Piwik\Tests\PHPStan\Rules\JsonResponseMustNotExitRule;

/**
 * @group Core
 * @extends RuleTestCase<JsonResponseMustNotExitRule>
 */
class JsonResponseMustNotExitRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new JsonResponseMustNotExitRule();
    }

    public function testRule(): void
    {
        $message = 'Controller action %s() is marked #[\Piwik\Http\JsonResponse] but calls exit/die;'
            . ' this bypasses the JSON header handling in FrontController, so the attribute has no'
            . ' effect. Return the JSON string instead, or drop the attribute and send the header'
            . ' manually.';

        $this->analyse([__DIR__ . '/data/JsonResponseController.php'], [
            [sprintf($message, 'exitsEarly'), 80],
            [sprintf($message, 'iifeExit'), 187],
        ]);
    }
}
