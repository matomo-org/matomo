<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\PluginTrial;

use Piwik\Plugins\Marketplace\PluginTrial\UnsubscribeToken;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group PluginTrial
 * @group Plugins
 */
class UnsubscribeTokenTest extends IntegrationTestCase
{
    public function testTokenIsValidOnlyForTheLoginItWasGeneratedFor()
    {
        $token = new UnsubscribeToken();

        self::assertTrue($token->isValid('alice', $token->generate('alice')));
        self::assertFalse($token->isValid('bob', $token->generate('alice')));
        self::assertFalse($token->isValid('alice', ''));
        self::assertFalse($token->isValid('', $token->generate('')));
    }
}
