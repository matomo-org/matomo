<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\System;

use Piwik\Plugins\TwoFactorAuth\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Plugins\TwoFactorAuth\SystemSettings;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;

/**
 * @group TwoFactorAuth
 * @group TwoFactorAuthTest
 * @group Plugins
 */
class TwoFactorAuthTest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @var RecoveryCodeDao
     */
    private $dao;

    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var TwoFactorAuthentication
     */
    private $twoFa;

    public function setUp(): void
    {
        parent::setUp();

        $this->dao = StaticContainer::get(RecoveryCodeDao::class);
        $this->settings = new SystemSettings();
        $secretGenerator = new TwoFaSecretRandomGenerator();
        $this->twoFa = new TwoFactorAuthentication($this->settings, $this->dao, $secretGenerator);

        self::$fixture->loginAsSuperUser();
    }

    public function test_onRequestDispatchEnd_notRequired()
    {
        $this->settings->twoFactorAuthRequired->setValue(true);
        $html = '<html>' . Piwik::getCurrentUserTokenAuth() . '</html>';
        $expected = '<html>' . Piwik::getCurrentUserTokenAuth() . '</html>';
        Piwik::postEvent('Request.dispatch.end', array(&$html, 'module', 'action', array()));
        $this->assertSame($expected, $html);
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

TwoFactorAuthTest::$fixture = new SimpleFixtureTrackFewVisits();
