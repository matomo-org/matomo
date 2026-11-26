<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Installation\tests\Unit;

use Piwik\Plugins\Installation\FormDatabaseSetup;

/**
 * @group Installation
 */
class FormDatabaseSetupSslTest extends \PHPUnit\Framework\TestCase
{
    private const NAMES = ['ENABLE_SSL', 'SSL_CA', 'SSL_CERT', 'SSL_KEY', 'SSL_CA_PATH', 'SSL_CIPHER', 'SSL_NO_VERIFY'];

    protected function tearDown(): void
    {
        foreach (self::NAMES as $name) {
            putenv('MATOMO_DATABASE_' . $name);
            putenv('DATABASE_' . $name);
        }

        parent::tearDown();
    }

    private function getSslSettings(): array
    {
        // Without its constructor: init() builds the form elements, which needs a running Matomo.
        $form = (new \ReflectionClass(FormDatabaseSetup::class))->newInstanceWithoutConstructor();

        return $form->getSslSettingsFromEnvironment();
    }

    public function testSslIsOffWithoutEnvironment()
    {
        $this->assertSame(['enable_ssl' => false], $this->getSslSettings());
    }

    public function testSslSettingsAreIgnoredWhenSslIsNotEnabled()
    {
        putenv('MATOMO_DATABASE_ENABLE_SSL=0');
        putenv('MATOMO_DATABASE_SSL_CA=/etc/mysql/ca.pem');

        $this->assertSame(['enable_ssl' => false], $this->getSslSettings());
    }

    public function testSslSettingsAreReadFromEnvironment()
    {
        putenv('MATOMO_DATABASE_ENABLE_SSL=1');
        putenv('MATOMO_DATABASE_SSL_CA=/etc/mysql/ca.pem');
        putenv('MATOMO_DATABASE_SSL_CERT=/etc/mysql/client-cert.pem');
        putenv('MATOMO_DATABASE_SSL_KEY=/etc/mysql/client-key.pem');
        putenv('MATOMO_DATABASE_SSL_NO_VERIFY=0');

        $this->assertSame(
            [
                'enable_ssl'    => true,
                'ssl_ca'        => '/etc/mysql/ca.pem',
                'ssl_cert'      => '/etc/mysql/client-cert.pem',
                'ssl_key'       => '/etc/mysql/client-key.pem',
                'ssl_no_verify' => false,
            ],
            $this->getSslSettings()
        );
    }

    public function testUnprefixedNamesAreReadAndPrefixedOnesWin()
    {
        putenv('DATABASE_ENABLE_SSL=true');
        putenv('DATABASE_SSL_CA=/unprefixed/ca.pem');
        putenv('DATABASE_SSL_CIPHER=ECDHE-RSA-AES256-GCM-SHA384');
        putenv('MATOMO_DATABASE_SSL_CA=/prefixed/ca.pem');

        $this->assertSame(
            [
                'enable_ssl' => true,
                'ssl_ca'     => '/prefixed/ca.pem',
                'ssl_cipher' => 'ECDHE-RSA-AES256-GCM-SHA384',
            ],
            $this->getSslSettings()
        );
    }
}
