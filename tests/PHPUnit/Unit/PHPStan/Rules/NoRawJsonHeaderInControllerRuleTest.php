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
use Piwik\Tests\PHPStan\Rules\NoRawJsonHeaderInControllerRule;

/**
 * @group Core
 * @extends RuleTestCase<NoRawJsonHeaderInControllerRule>
 */
class NoRawJsonHeaderInControllerRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoRawJsonHeaderInControllerRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/JsonResponseController.php'], [
            [
                'Controller action rawJsonContentTypeHeader() sets a JSON Content-Type via'
                . ' Common::sendHeader(); use Json::sendHeaderJSON() (or the #[\Piwik\Http\JsonResponse]'
                . ' attribute for an always-JSON action) instead, so the JSON response convention and'
                . ' its checks apply.',
                99,
            ],
        ]);
    }
}
