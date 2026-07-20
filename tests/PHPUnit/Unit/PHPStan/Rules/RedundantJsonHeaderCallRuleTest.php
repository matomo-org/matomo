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
use Piwik\Tests\PHPStan\Rules\RedundantJsonHeaderCallRule;

/**
 * @group Core
 * @extends RuleTestCase<RedundantJsonHeaderCallRule>
 */
class RedundantJsonHeaderCallRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RedundantJsonHeaderCallRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/JsonResponseController.php'], [
            [
                'Method redundantManualCall() carries #[\Piwik\Http\JsonResponse], which already sends the'
                . ' JSON header; the manual Json::sendHeaderJSON() call is redundant and should be removed.',
                48,
            ],
        ]);
    }
}
