<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Integration\Security;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Container\StaticContainer;
use Piwik\DI;
use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UsersManager\Model as UsersManagerModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group LoginFromAnotherCountryTest
 */
class LoginFromAnotherCountryTest extends IntegrationTestCase
{
    private const IP_FRANCE = '194.57.91.215';

    private const IP_USA = '99.99.99.99';

    private const IP_UNKNOWN = '127.0.0.1';

    private const LOGIN = 'user1';

    /**
     * @var string|null
     */
    private $loginCountry;

    /** @var UsersManagerModel */
    private $userModel;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2010-01-01 05:00:00');
        Fixture::loadAllTranslations();

        $this->loginCountry = null;

        $this->userModel = new UsersManagerModel();
        $this->userModel->addUser(
            self::LOGIN,
            'a98732d98732',
            'user1@example.com',
            '2019-03-03'
        );

        FakeAccess::$identity = self::LOGIN;
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [1];

        // select GeoIP provider
        LocationProvider::setCurrentProvider(GeoIp2\Php::ID);
    }

    public function testNoEmailIsSentWhenLoggingInFirstTimeAfterRelease()
    {
        // start in France
        $_SERVER['REMOTE_ADDR'] = self::IP_FRANCE;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);
    }

    public function testNoEmailIsSentWhenLoggingInFromTheSameCountryAsBefore()
    {
        // start in USA
        $_SERVER['REMOTE_ADDR'] = self::IP_USA;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);

        // continue in USA
        $_SERVER['REMOTE_ADDR'] = self::IP_USA;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);
    }

    public function testEmailIsSentWhenLoggingInFromADifferentCountry()
    {
        // start in France
        $_SERVER['REMOTE_ADDR'] = self::IP_FRANCE;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);

        // continue in USA
        $_SERVER['REMOTE_ADDR'] = self::IP_USA;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEquals(Piwik::translate('Intl_Country_US'), $this->loginCountry);
    }

    public function testEmailIsSentWhenLoggingInFromAnUnknownCountry()
    {
        // start in France
        $_SERVER['REMOTE_ADDR'] = self::IP_FRANCE;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);

        // continue in an unknown country
        $_SERVER['REMOTE_ADDR'] = self::IP_UNKNOWN;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEquals(Piwik::translate('General_Unknown'), $this->loginCountry);

        // continue in the USA again
        $_SERVER['REMOTE_ADDR'] = self::IP_USA;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEquals(Piwik::translate('Intl_Country_US'), $this->loginCountry);
    }

    public function testEmailIsNotSentWhenGeoIpNotAvailable()
    {
        LocationProvider::setCurrentProvider(LocationProvider\DisabledProvider::ID);

        // start in France
        $_SERVER['REMOTE_ADDR'] = self::IP_FRANCE;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);

        // continue in USA
        $_SERVER['REMOTE_ADDR'] = self::IP_USA;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);
    }

    public function testEmailIsNotSentWhenUsingDefaultGeoIpProvider()
    {
        LocationProvider::setCurrentProvider(LocationProvider\DefaultProvider::ID);

        // start in France
        $_SERVER['REMOTE_ADDR'] = self::IP_FRANCE;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);

        // continue in USA
        $_SERVER['REMOTE_ADDR'] = self::IP_USA;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);
    }

    public function testEmailIsNotSentWhenUsingGeoIpProviderIsActivateButNotAvailable()
    {
        StaticContainer::getContainer()->set('path.geoip2', '/tmp/invalid/geoip2/path');

        // start in France
        $_SERVER['REMOTE_ADDR'] = self::IP_FRANCE;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);

        // continue in USA
        $_SERVER['REMOTE_ADDR'] = self::IP_USA;
        Piwik::postEvent('Login.authenticate.processSuccessfulSession.end', [self::LOGIN]);
        $this->assertEmpty($this->loginCountry);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
            'observers.global' => DI::add([
                ['Test.Mail.send', DI::value(function (PHPMailer $mail) {
                    $subjectNotification = Piwik::translate('Login_LoginFromDifferentCountryEmailSubject');

                    if ($subjectNotification === $mail->Subject) {
                        $body = $mail->createBody();
                        $body = preg_replace("/=[\r\n]+/", '', $body);

                        preg_match('/<li>Country: ([a-zA-Z0-9=\s]+)<\/li>/', $body, $matches);
                        $this->assertNotEmpty($matches[1]);
                        $country = $matches[1];
                        $this->loginCountry = $country;
                    }
                })],
            ]),
        ];
    }
}
