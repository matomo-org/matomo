<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\Plugin;

use PHPUnit\Framework\TestCase;
use Piwik\Plugin\ControllerAdmin;
use ReflectionMethod;

/**
 * @group Core
 */
class ControllerAdminTest extends TestCase
{
    /**
     * @dataProvider getPhpVersions
     */
    public function testIsUsingPhpVersionCompatibleWithNextPiwik(string $phpVersion, bool $expected): void
    {
        $this->assertSame(
            $expected,
            $this->invokeControllerAdminMethod('isUsingPhpVersionCompatibleWithNextPiwik', [$phpVersion])
        );
    }

    public function getPhpVersions(): iterable
    {
        yield 'PHP below Matomo 6 requirement' => ['8.0.30', false];
        yield 'PHP matching Matomo 6 requirement' => ['8.1.0', true];
        yield 'PHP above Matomo 6 requirement' => ['8.2.0', true];
    }

    /**
     * @dataProvider getDatabaseVersions
     */
    public function testIsUsingDatabaseVersionCompatibleWithNextPiwik(
        string $databaseType,
        string $databaseVersion,
        bool $expected
    ): void {
        $this->assertSame(
            $expected,
            $this->invokeControllerAdminMethod(
                'isUsingDatabaseVersionCompatibleWithNextPiwik',
                [$databaseType, $databaseVersion]
            )
        );
    }

    public function getDatabaseVersions(): iterable
    {
        yield 'MySQL below Matomo 6 requirement' => ['MySQL', '5.7.44', false];
        yield 'MySQL matching Matomo 6 requirement' => ['MySQL', '8.0.0', true];
        yield 'MySQL above Matomo 6 requirement' => ['MySQL', '8.4.0', true];
        yield 'MariaDB below Matomo 6 requirement' => ['MariaDB', '10.5.22-MariaDB', false];
        yield 'MariaDB matching Matomo 6 requirement' => ['MariaDB', '10.6.0-MariaDB', true];
        yield 'MariaDB above Matomo 6 requirement' => ['MariaDB', '11.4.0-MariaDB', true];
        yield 'MariaDB detected through MySQL schema version' => ['MySQL', '10.5.22-MariaDB', false];
        yield 'MariaDB detected with compatibility prefix' => ['MySQL', '5.5.5-10.6.12-MariaDB', true];
        yield 'Unknown database type is treated as compatible' => ['PostgreSQL', '15.2', true];
    }

    /**
     * @dataProvider getDatabaseRequirements
     */
    public function testGetNextRequiredMinimumDatabaseVersion(
        string $databaseType,
        string $databaseVersion,
        ?string $expected
    ): void {
        $this->assertSame(
            $expected,
            $this->invokeControllerAdminMethod(
                'getNextRequiredMinimumDatabaseVersion',
                [$databaseType, $databaseVersion]
            )
        );
    }

    public function getDatabaseRequirements(): iterable
    {
        yield 'MySQL' => ['MySQL', '5.7.44', '8.0'];
        yield 'MariaDB' => ['MariaDB', '10.5.22-MariaDB', '10.6'];
        yield 'MariaDB detected through version' => ['MySQL', '10.5.22-MariaDB', '10.6'];
        yield 'Unknown database type' => ['PostgreSQL', '15.2', null];
    }

    /**
     * @dataProvider getMailConfigs
     */
    public function testIsMailConfigUsingSmtpPasswordLogin(array $mail, bool $expected): void
    {
        $this->assertSame(
            $expected,
            $this->invokeControllerAdminMethod('isMailConfigUsingSmtpPasswordLogin', [$mail])
        );
    }

    public function getMailConfigs(): iterable
    {
        $smtp = ['transport' => 'smtp', 'host' => 'smtp.office365.com', 'type' => 'Login'];

        yield 'Microsoft 365 with a password login' => [$smtp, true];
        yield 'another server, as the host is not checked' => [['host' => 'smtp.gmail.com'] + $smtp, true];
        yield 'a server of your own' => [['host' => 'mail.example.org'] + $smtp, true];
        yield 'PLAIN login' => [['type' => 'Plain'] + $smtp, true];
        yield 'CRAM-MD5 login' => [['type' => 'Cram-md5'] + $smtp, true];
        yield 'mail() instead of SMTP' => [['transport' => ''] + $smtp, false];
        yield 'transport in upper case, which Transport does not treat as SMTP' => [['transport' => 'SMTP'] + $smtp, false];
        yield 'no transport set' => [['host' => 'smtp.office365.com', 'type' => 'Login'], false];
        yield 'no host' => [['host' => ''] + $smtp, false];
        yield 'whitespace-only host' => [['host' => '  '] + $smtp, false];
        yield 'no login' => [['type' => ''] + $smtp, false];
        yield 'OAuth 2.0' => [['type' => 'Xoauth2'] + $smtp, false];
        yield 'OAuth 2.0 in upper case' => [['type' => 'XOAUTH2'] + $smtp, false];
    }

    private function invokeControllerAdminMethod(string $methodName, array $arguments)
    {
        $method = new ReflectionMethod(ControllerAdmin::class, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs(null, $arguments);
    }
}
