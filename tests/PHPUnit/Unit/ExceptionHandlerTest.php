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
        Config::getInstance()->mail['oauth_client_secret'] = 'myTestOAuthSecret';
        Config::getInstance()->mail['oauth_private_key_password'] = 'myTestKeyPass';

        $credentials = 'myTestDbUser, myTestDbPass, myTestSmtpUser, myTestSmtpPass, myTestOAuthSecret, myTestKeyPass';
        $testMessage = 'Error in ' . PIWIK_DOCUMENT_ROOT . "/index.php containing myTestToken, myTestSalt, $credentials";

        $this->assertSame(
            "Error in /index.php containing tokenauth, generalSalt, $credentials",
            \Piwik\ExceptionHandler::replaceSensitiveValues($testMessage, false)
        );
        $this->assertSame(
            'Error in /index.php containing tokenauth, generalSalt, dbuser, dbpass, smtpuser, smtppass, '
                . 'smtpoauthclientsecret, smtpoauthprivatekeypassword',
            \Piwik\ExceptionHandler::replaceSensitiveValues($testMessage)
        );
    }

    /**
     * @dataProvider getMessagesForResponseData
     * @runInSeparateProcess SettingsPiwik holds the salt as a static value.
     */
    public function testGetMessageForResponse(string $message, string $expected)
    {
        StaticContainer::get('Piwik\Access')->setTokenAuth('myTestToken');
        Config::getInstance()->General['salt'] = 'myTestSalt';
        Config::getInstance()->database['username'] = 'myTestDbUser';
        Config::getInstance()->database['password'] = 'myTestDbPass';
        Config::getInstance()->mail['username'] = 'myTestSmtpUser';
        Config::getInstance()->mail['password'] = 'myTestSmtpPass';
        Config::getInstance()->mail['oauth_client_secret'] = 'myTestOAuthSecret';
        Config::getInstance()->mail['oauth_private_key_password'] = 'myTestKeyPass';

        $this->assertSame($expected, \Piwik\ExceptionHandler::getMessageForResponse(new \Exception($message)));
        $this->assertSame($expected, \Piwik\ExceptionHandler::getMessageForResponse(new \PDOException($message)));
    }

    public function getMessagesForResponseData(): array
    {
        return [
            'access denied' => [
                "SQLSTATE[HY000] [1045] Access denied for user 'some'@'localhost' (using password: YES)",
                "SQLSTATE[HY000] [1045] Access denied for user 'dbuser'@'localhost' (using password: YES)",
            ],
            'command denied' => [
                "SELECT command denied to user 'myTestDbUser'@'10.0.0.5' for table 't'",
                "SELECT command denied to user 'dbuser'@'10.0.0.5' for table 't'",
            ],
            'command denied in another language' => [
                "SQLSTATE[42000]: 1142 SELECT Befehl nicht erlaubt f\u{FC}r Benutzer 'some'@'localhost' auf Tabelle 't'",
                "SQLSTATE[42000]: 1142 SELECT Befehl nicht erlaubt f\u{FC}r Benutzer 'dbuser'@'localhost' auf Tabelle 't'",
            ],
            'token, salt and root path' => [
                'Error in ' . PIWIK_DOCUMENT_ROOT . '/index.php containing myTestToken and myTestSalt',
                'Error in /index.php containing tokenauth and generalSalt',
            ],
            'other text' => [
                "Something failed for 'myTestDbUser', myTestDbPass, myTestSmtpUser, myTestSmtpPass, "
                    . 'myTestOAuthSecret and myTestKeyPass.',
                "Something failed for 'myTestDbUser', myTestDbPass, myTestSmtpUser, myTestSmtpPass, "
                    . 'myTestOAuthSecret and myTestKeyPass.',
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
