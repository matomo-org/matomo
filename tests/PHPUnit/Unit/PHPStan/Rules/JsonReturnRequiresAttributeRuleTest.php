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
use Piwik\Tests\PHPStan\Rules\JsonReturnRequiresAttributeRule;

/**
 * @group Core
 * @extends RuleTestCase<JsonReturnRequiresAttributeRule>
 */
class JsonReturnRequiresAttributeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new JsonReturnRequiresAttributeRule();
    }

    public function testRule(): void
    {
        $message = 'Controller action %s() returns a JSON response but is not marked'
            . ' #[\Piwik\Http\JsonResponse]. Add the attribute so Matomo applies the JSON'
            . ' Content-Type after the action; do not send the header manually.';

        $this->analyse([__DIR__ . '/data/JsonResponseController.php'], [
            [sprintf($message, 'undeclaredJsonEncodeReturn'), 106],
            [sprintf($message, 'undeclaredJsonEncodeCastReturn'), 111],
            [sprintf($message, 'undeclaredJsonLiteralReturn'), 116],
        ]);
    }
}
