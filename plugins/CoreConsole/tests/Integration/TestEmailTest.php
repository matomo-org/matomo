<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\tests\Integration;

use Piwik\Config;
use Piwik\DI;
use Piwik\Mail\OAuth\AccessTokenProvider;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group CoreConsole
 * @group TestEmailTest
 */
class TestEmailTest extends ConsoleCommandTestCase
{
    /**
     * @var string[]
     */
    public static $accessTokenCalls = [];

    public function setUp(): void
    {
        parent::setUp();

        self::$accessTokenCalls = [];
    }

    /**
     * @dataProvider getAuthTypesAndAccessTokenCalls
     * @param string[] $expectedAccessTokenCalls
     */
    public function testRequestsANewAccessTokenWhenOAuthIsUsed(string $type, array $expectedAccessTokenCalls): void
    {
        Config::getInstance()->mail = [
            'transport' => 'smtp',
            'host'      => 'smtp.example.test',
            'type'      => $type,
            'username'  => 'reports@example.test',
        ] + Config::getInstance()->mail;

        $result = $this->applicationTester->run([
            'command'      => 'core:test-email',
            'emailAddress' => 'someone@example.test',
        ]);

        $this->assertSame(0, $result, $this->getCommandDisplayOutputErrorMessage());
        $this->assertSame($expectedAccessTokenCalls, self::$accessTokenCalls);
    }

    public function getAuthTypesAndAccessTokenCalls(): iterable
    {
        yield 'OAuth' => ['Xoauth2', ['forget', 'get']];
        yield 'password' => ['Login', []];
    }

    public function provideContainerConfig()
    {
        return [
            AccessTokenProvider::class => DI::factory(function () {
                return new class () implements AccessTokenProvider {
                    public function forgetAccessToken(): void
                    {
                        TestEmailTest::$accessTokenCalls[] = 'forget';
                    }

                    public function getAccessToken(): string
                    {
                        TestEmailTest::$accessTokenCalls[] = 'get';

                        return 'the-access-token';
                    }
                };
            }),
        ];
    }
}
