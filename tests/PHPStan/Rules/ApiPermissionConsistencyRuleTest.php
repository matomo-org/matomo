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
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::missingDocblock has a #[Permission] attribute but is missing a matching @matomo-permission docblock tag.',
                    28,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::missingAttribute has a @matomo-permission tag but is missing a matching #[Permission] attribute.',
                    37,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::mismatchedMetadata has mismatched permission metadata: docblock declares someView but attribute declares superuser.',
                    45,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::mismatchedMetadata declares superuser but the direct body check is someView via Piwik::checkUserHasSomeViewAccess(...).',
                    48,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::missingBodyCheck declares someAdmin but does not contain a matching direct Piwik::checkUserHasSomeAdminAccess(...) permission check.',
                    54,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::wrongBodyMethod declares admin(idSite) but the direct body check is view(idSite) via Piwik::checkUserHasViewAccess(...).',
                    65,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::wrongBodyParameter declares superUserOrUser(userLogin) but the direct body check is superUserOrUser(otherLogin) via Piwik::checkUserHasSuperUserAccessOrIsTheUser(...).',
                    74,
                ],
                [
                    'API method Piwik\Tests\PHPStan\Rules\Fixture\FixtureApi::multipleAttributes declares multiple #[Permission] attributes; exactly one is allowed.',
                    80,
                ],
            ]
        );
    }
}
