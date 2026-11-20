<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

require_once __DIR__ . '/PermissionDeclaration.php';
require_once __DIR__ . '/ApiPermissionConsistencyRule.php';

class ApiPermissionConsistencyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ApiPermissionConsistencyRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [__DIR__ . '/Fixture/ApiPermissionConsistencyRuleFixture.php'],
            [
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::mismatchedBodyCheck declares superuser but the direct body check is someView via Piwik::checkUserHasSomeViewAccess(...).',
                    25,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::missingBodyCheck declares someAdmin but does not contain a matching direct Piwik::checkUserHasSomeAdminAccess(...) permission check.',
                    28,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::wrongBodyMethod declares admin(idSite) but the direct body check is view(idSite) via Piwik::checkUserHasViewAccess(...).',
                    36,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::wrongBodyParameter declares superUserOrUser(userLogin) but the direct body check is superUserOrUser(otherLogin) via Piwik::checkUserHasSuperUserAccessOrIsTheUser(...).',
                    42,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::multipleAttributes declares multiple #[Permission] attributes; exactly one is allowed.',
                    45,
                ],
            ]
        );
    }
}
