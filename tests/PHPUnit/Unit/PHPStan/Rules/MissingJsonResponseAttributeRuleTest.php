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
use Piwik\Tests\PHPStan\Rules\MissingJsonResponseAttributeRule;

/**
 * @group Core
 * @extends RuleTestCase<MissingJsonResponseAttributeRule>
 */
class MissingJsonResponseAttributeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MissingJsonResponseAttributeRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/JsonResponseController.php'], [
            [
                'Controller action unconditionalManualCall() calls Json::sendHeaderJSON() unconditionally;'
                . ' add the #[\Piwik\Http\JsonResponse] attribute to the method and remove the manual call'
                . ' so the JSON Content-Type cannot be overwritten by later output.',
                24,
            ],
        ]);
    }
}
