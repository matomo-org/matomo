<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Mail;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Piwik\Config;
use Piwik\DI;
use Piwik\Mail;
use Piwik\Mail\OAuth\AccessTokenProvider;
use Piwik\Mail\OAuth\TokenRequestFailedException;
use Piwik\Mail\OAuth\XOAuth2Authenticator;
use Piwik\Mail\Transport;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group Mail
 */
class TransportTest extends IntegrationTestCase
{
    private const ACCESS_TOKEN = 'the-access-token';

    /**
     * @var PHPMailer|null
     */
    private $phpMailer;

    /**
     * @var int
     */
    private $accessTokenRequests = 0;

    /**
     * @var int
     */
    private $forgottenAccessTokens = 0;

    /**
     * @var bool
     */
    private $failSending = false;

    public function testAuthenticatesWithThePasswordForPasswordBasedTypes(): void
    {
        $this->setSmtpConfig(['type' => 'Login', 'password' => 'the-password']);

        $this->sendMail();

        $this->assertSame('smtp', $this->phpMailer->Mailer);
        $this->assertTrue($this->phpMailer->SMTPAuth);
        $this->assertSame('LOGIN', $this->phpMailer->AuthType);
        $this->assertSame('reports@example.test', $this->phpMailer->Username);
        $this->assertSame('the-password', $this->phpMailer->Password);
        $this->assertNotInstanceOf(XOAuth2Authenticator::class, $this->phpMailer->getOAuth());
        $this->assertSame(0, $this->accessTokenRequests);
    }

    public function testAuthenticatesWithAnAccessTokenForXoauth2(): void
    {
        $this->setSmtpConfig(['type' => 'Xoauth2', 'password' => 'an-old-password']);

        $this->sendMail();

        $this->assertTrue($this->phpMailer->SMTPAuth);
        $this->assertSame('XOAUTH2', $this->phpMailer->AuthType);
        $this->assertSame('', $this->phpMailer->Password);
        $this->assertSame(1, $this->accessTokenRequests);

        $authenticator = $this->phpMailer->getOAuth();
        $this->assertInstanceOf(XOAuth2Authenticator::class, $authenticator);
        $this->assertSame(
            "user=reports@example.test\001auth=Bearer " . self::ACCESS_TOKEN . "\001\001",
            base64_decode($authenticator->getOauth64(), true)
        );
    }

    public function testFailsForXoauth2WithoutAUsername(): void
    {
        $this->setSmtpConfig(['type' => 'Xoauth2', 'username' => '']);

        try {
            $this->sendMail();
            $this->fail('Expected exception was not thrown');
        } catch (TokenRequestFailedException $e) {
            $this->assertStringContainsString('Please set username', $e->getMessage());
            $this->assertSame(0, $this->accessTokenRequests);
        }
    }

    /**
     * @dataProvider getAuthTypesAndForgottenTokens
     */
    public function testForgetsTheAccessTokenWhenSendingFailsForXoauth2(string $type, int $expectedForgottenTokens): void
    {
        $this->setSmtpConfig(['type' => $type]);
        $this->failSending = true;

        try {
            $this->sendMail();
            $this->fail('Expected exception was not thrown');
        } catch (PHPMailerException $e) {
            $this->assertSame($expectedForgottenTokens, $this->forgottenAccessTokens);
        }
    }

    public function getAuthTypesAndForgottenTokens(): iterable
    {
        yield 'OAuth' => ['Xoauth2', 1];
        yield 'password' => ['Login', 0];
    }

    public function testHidesTheOAuthCredentialsInTheSmtpDebugOutput(): void
    {
        $this->setSmtpConfig(['type' => 'Xoauth2']);

        $this->sendMail(true);

        $this->assertSame(SMTP::DEBUG_SERVER, $this->phpMailer->SMTPDebug);

        $credentials = $this->phpMailer->getOAuth()->getOauth64();

        ob_start();
        ($this->phpMailer->Debugoutput)('CLIENT -> SERVER: AUTH XOAUTH2 ' . $credentials, SMTP::DEBUG_SERVER);
        // credentials too long for the AUTH command are sent on a line of their own
        ($this->phpMailer->Debugoutput)('CLIENT -> SERVER: ' . $credentials, SMTP::DEBUG_SERVER);
        $output = ob_get_clean();

        $this->assertStringNotContainsString($credentials, $output);
        $this->assertStringContainsString("CLIENT -> SERVER: AUTH XOAUTH2 [credentials hidden]\n", $output);
        $this->assertStringContainsString("CLIENT -> SERVER: [credentials hidden]\n", $output);
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => DI::add([
                ['Test.Mail.send', DI::value(function (PHPMailer $phpMailer) {
                    $this->phpMailer = $phpMailer;

                    if ($this->failSending) {
                        throw new PHPMailerException('SMTP Error: Could not authenticate.');
                    }
                })],
            ]),
            AccessTokenProvider::class => DI::factory(function () {
                return new class ($this->accessTokenRequests, $this->forgottenAccessTokens) implements AccessTokenProvider {
                    /**
                     * @var int
                     */
                    private $requests;

                    /**
                     * @var int
                     */
                    private $forgotten;

                    public function __construct(int &$requests, int &$forgotten)
                    {
                        $this->requests = &$requests;
                        $this->forgotten = &$forgotten;
                    }

                    public function forgetAccessToken(): void
                    {
                        $this->forgotten++;
                    }

                    public function getAccessToken(): string
                    {
                        $this->requests++;

                        return 'the-access-token';
                    }
                };
            }),
        ];
    }

    private function setSmtpConfig(array $settings): void
    {
        Config::getInstance()->mail = $settings + [
            'transport' => 'smtp',
            'host'      => 'smtp.example.test',
            'port'      => '587',
            'username'  => 'reports@example.test',
        ] + Config::getInstance()->mail;
    }

    private function sendMail(bool $smtpDebug = false): void
    {
        $mail = new Mail();
        $mail->setFrom('noreply@example.test');
        $mail->addTo('someone@example.test');
        $mail->setSubject('A subject');
        $mail->setBodyText('A body');
        $mail->setSmtpDebug($smtpDebug);

        (new Transport())->send($mail);
    }
}
