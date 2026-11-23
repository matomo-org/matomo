<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group AssetManager
 */
class ExceptionHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        StaticContainer::getContainer()->set('Piwik\Access', $this->getMockAccess());
    }

    /**
     * @dataProvider getSensitiveValuesData
     * @runInSeparateProcess This is necessary because SettingsPiwik holds the salt as a static value which means that
     * it won't be pulled from the config after the first time it's checked.
     * @param $configToken
     * @param $configSalt
     * @param $configDbUser
     * @param $configDbPass
     * @return void
     * @throws \Exception
     */
    public function testReplaceSensitiveValues($configToken, $configSalt, $configDbUser, $configDbPass)
    {
        $tokenAuth = $expectedToken = 'no auth token';
        $salt = $expectedSalt = 'no salt';
        $dbUser = $expectedDbUser = 'no db user';
        $dbPass = $expectedDbPass = 'no db password';

        StaticContainer::get('Piwik\Access')->setTokenAuth($configToken);
        Config::getInstance()->General['salt'] = $configSalt;
        Config::getInstance()->database['username'] = $configDbUser;
        Config::getInstance()->database['password'] = $configDbPass;

        if (!empty($configToken)) {
            $tokenAuth = $configToken;
            $expectedToken = 'tokenauth';
        }
        if (!empty($configSalt)) {
            $salt = $configSalt;
            $expectedSalt = 'generalSalt';
        }
        if (!empty($configDbUser)) {
            $dbUser = $configDbUser;
            $expectedDbUser = 'dbuser';
        }
        if (!empty($configDbPass)) {
            $dbPass = $configDbPass;
            $expectedDbPass = 'dbpass';
        }
        $testMessage = "Error message containing $tokenAuth, $salt, $dbUser, $dbPass, and hard-coded: zero (0)";
        $expectedMessage = "Error message containing $expectedToken, $expectedSalt, $expectedDbUser, $expectedDbPass, and hard-coded: zero (0)";
        $result = \Piwik\ExceptionHandler::replaceSensitiveValues($testMessage);
        $this->assertSame($expectedMessage, $result);
    }

    /**
     * @runInSeparateProcess SettingsPiwik holds the salt as a static value.
     */
    public function testReplaceSensitiveValuesCanLeaveCredentialsUntouched()
    {
        StaticContainer::get('Piwik\Access')->setTokenAuth('myTestToken');
        Config::getInstance()->General['salt'] = 'myTestSalt';
        Config::getInstance()->database['username'] = 'myTestDbUser';
        Config::getInstance()->database['password'] = 'myTestDbPass';
        Config::getInstance()->mail['username'] = 'myTestSmtpUser';
        Config::getInstance()->mail['password'] = 'myTestSmtpPass';

        $credentials = 'myTestDbUser, myTestDbPass, myTestSmtpUser, myTestSmtpPass';
        $testMessage = 'Error in ' . PIWIK_DOCUMENT_ROOT . "/index.php containing myTestToken, myTestSalt, $credentials";

        $this->assertSame(
            "Error in /index.php containing tokenauth, generalSalt, $credentials",
            \Piwik\ExceptionHandler::replaceSensitiveValues($testMessage, false)
        );
        $this->assertSame(
            'Error in /index.php containing tokenauth, generalSalt, dbuser, dbpass, smtpuser, smtppass',
            \Piwik\ExceptionHandler::replaceSensitiveValues($testMessage)
        );
    }

    /**
     * @runInSeparateProcess SettingsPiwik holds the salt as a static value.
     */
    public function testGetMessageForResponseRewritesTheDatabaseAccount()
    {
        Config::getInstance()->database['username'] = 'myTestDbUser';
        Config::getInstance()->database['password'] = 'myTestDbPass';

        $denied = "command denied to user 'myTestDbUser'@'localhost' for table 't'";
        $expected = "command denied to user 'dbuser'@'localhost' for table 't'";

        $this->assertSame($expected, \Piwik\ExceptionHandler::getMessageForResponse(new \Exception($denied)));
        $this->assertSame($expected, \Piwik\ExceptionHandler::getMessageForResponse(new \PDOException($denied)));
        $this->assertSame(
            "User dbuser already has more than 'max_user_connections' active connections",
            \Piwik\ExceptionHandler::getMessageForResponse(
                new \PDOException("User myTestDbUser already has more than 'max_user_connections' active connections")
            )
        );
        $this->assertSame(
            "User 'dbuser' has exceeded the 'max_questions' resource (current value: 100)",
            \Piwik\ExceptionHandler::getMessageForResponse(
                new \PDOException("User 'myTestDbUser' has exceeded the 'max_questions' resource (current value: 100)")
            )
        );
        $this->assertSame(
            "Something failed for 'myTestDbPass'",
            \Piwik\ExceptionHandler::getMessageForResponse(new \PDOException("Something failed for 'myTestDbPass'"))
        );
        $this->assertSame(
            'Something failed for myTestDbUser.',
            \Piwik\ExceptionHandler::getMessageForResponse(new \Exception('Something failed for myTestDbUser.'))
        );
    }

    /**
     * @dataProvider getMessagesForResponseData
     */
    public function testGetMessageForResponseOnlyRewritesDatabaseServerMessages(string $message, string $expected)
    {
        $this->assertSame($expected, \Piwik\ExceptionHandler::getMessageForResponse(new \Exception($message)));
    }

    public function getMessagesForResponseData(): array
    {
        return [
            'access denied' => [
                "SQLSTATE[HY000] [1045] Access denied for user 'some'@'localhost' (using password: YES)",
                "SQLSTATE[HY000] [1045] Access denied for user 'dbuser'@'localhost' (using password: YES)",
            ],
            'user name with a quote' => [
                "INSERT command denied to user 'o'brien'@'10.0.0.1' for table 't'",
                "INSERT command denied to user 'dbuser'@'10.0.0.1' for table 't'",
            ],
            'quoted value that looks like an account' => [
                "The value 'some'@'thing' is not valid.",
                "The value 'some'@'thing' is not valid.",
            ],
            'other text about a user and a limit' => [
                'User some already has more than five reports.',
                'User some already has more than five reports.',
            ],
            'other text about an exceeded limit' => [
                "User 'some' has exceeded the limit.",
                "User 'some' has exceeded the limit.",
            ],
        ];
    }

    public function getSensitiveValuesData(): array
    {
        return [
            [null, null, null, null],
            [null, '', '', ''],
            [0, '', '', ''],
            ['', '', '', ''],
            ['0', '', '', ''],
            ['myTestToken', 'myTestSalt', 'myTestDbUser', 'myTestDbPass'],
            ['myTestToken', '', '', ''],
            ['', 'myTestSalt', '', ''],
            ['', '', 'myTestDbUser', ''],
            ['', '', '', 'myTestDbPass'],
            ['myTestToken', 'myTestSalt', '', ''],
            ['', 'myTestSalt', 'myTestDbUser', ''],
            ['', '', 'myTestDbUser', 'myTestDbPass'],
            ['myTestToken', '', 'myTestDbUser', ''],
            ['', 'myTestSalt', '', 'myTestDbPass'],
            ['myTestToken', '', '', 'myTestDbPass'],
            ['myTestToken', 'myTestSalt', 'myTestDbUser', ''],
            ['', 'myTestSalt', 'myTestDbUser', 'myTestDbPass'],
            ['myTestToken', '', 'myTestDbUser', 'myTestDbPass'],
            ['myTestToken', 'myTestSalt', '', 'myTestDbPass'],
            ['otherTestToken', 'otherTestSalt', 'otherTestDbUser', 'otherTestDbPass'],
        ];
    }

    private function getMockAccess(): FakeAccess
    {
        return new class () extends FakeAccess {
            public function getTokenAuth()
            {
                return $this->token_auth;
            }

            public function setTokenAuth($tokenAuth): void
            {
                $this->token_auth = $tokenAuth;
            }
        };
    }
}
