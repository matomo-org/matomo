<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


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
        Config::getInstance()->General['salt'] = null;
        Config::getInstance()->database['username'] = '';
        Config::getInstance()->database['password'] = '';
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
     * @throws Exception
     */
    public function testReplaceSensitiveValues($configToken, $configSalt, $configDbUser, $configDbPass)
    {
        $tokenAuth = $expectedToken = 'no auth token';
        $salt = $expectedSalt = 'no salt';
        $dbUser = $expectedDbUser = 'no db user';
        $dbPass = $expectedDbPass = 'no db password';

        if (!empty($configToken)) {
            $tokenAuth = $configToken;
            $expectedToken = 'tokenauth';
            StaticContainer::get('Piwik\Access')->setTokenAuth($configToken);
        }
        if (!empty($configSalt)) {
            $salt = $configSalt;
            $expectedSalt = 'generalSalt';
            Config::getInstance()->General['salt'] = $configSalt;
        }
        if (!empty($configDbUser)) {
            $dbUser = $configDbUser;
            $expectedDbUser = 'dbuser';
            Config::getInstance()->database['username'] = $configDbUser;
        }
        if (!empty($configDbPass)) {
            $dbPass = $configDbPass;
            $expectedDbPass = 'dbpass';
            Config::getInstance()->database['password'] = $configDbPass;
        }
        $testMessage = "Error message containing $tokenAuth, $salt, $dbUser, $dbPass";
        $expectedMessage = "Error message containing $expectedToken, $expectedSalt, $expectedDbUser, $expectedDbPass";
        $result = \Piwik\ExceptionHandler::replaceSensitiveValues($testMessage);
        $this->assertSame($expectedMessage, $result);
    }

    public function getSensitiveValuesData(): array
    {
        return [
            [null, null, null, null],
            [0, 0, 0, 0],
            ['', '', '', ''],
            ['0', '0', '0', '0'],
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
        return new class() extends FakeAccess {
            public function getTokenAuth()
            {
                return $this->token_auth;
            }

            public function setTokenAuth(string $tokenAuth): void
            {
                $this->token_auth = $tokenAuth;
            }
        };
    }
}
