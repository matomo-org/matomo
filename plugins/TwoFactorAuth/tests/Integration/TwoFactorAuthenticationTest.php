<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Plugins\TwoFactorAuth\SystemSettings;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;
use Piwik\Plugins\UsersManager\API;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TwoFactorAuth
 * @group TwoFactorAuthenticationTest
 * @group Plugins
 */
class TwoFactorAuthenticationTest extends IntegrationTestCase
{
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

        foreach (['mylogin', 'mylogin1', 'mylogin2'] as $user) {
            API::getInstance()->addUser($user, '123abcDk3_l3', $user . '@matomo.org');
        }

        $this->dao = StaticContainer::get(RecoveryCodeDao::class);
        $this->settings = new SystemSettings();
        $secretGenerator = new TwoFaSecretRandomGenerator();
        $this->twoFa = new TwoFactorAuthentication($this->settings, $this->dao, $secretGenerator);
    }

    public function testGenerateSecret()
    {
        $this->assertSame(16, mb_strlen($this->twoFa->generateSecret()));
    }

    public function testIsUserRequiredToHaveTwoFactorEnabledNotByDefault()
    {
        $this->assertFalse($this->twoFa->isUserRequiredToHaveTwoFactorEnabled());
    }

    public function testIsUserRequiredToHaveTwoFactorEnabled()
    {
        $this->settings->twoFactorAuthRequired->setValue(1);
        $this->assertTrue($this->twoFa->isUserRequiredToHaveTwoFactorEnabled());
    }

    public function testSaveSecretDisable2FAforUserIsUserUsingTwoFactorAuthentication()
    {
        $this->dao->createRecoveryCodesForLogin('mylogin');

        $this->assertFalse(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication('mylogin'));
        $this->twoFa->saveSecret('mylogin', '123456');

        $this->assertTrue(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication('mylogin'));
        $this->assertFalse(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication('mylogin2'));

        $this->twoFa->disable2FAforUser('mylogin');

        $this->assertFalse(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication('mylogin'));
    }

    public function testDisable2FAforUserRemovesAllRecoveryCodes()
    {
        $this->dao->createRecoveryCodesForLogin('mylogin');
        $this->assertNotEmpty($this->dao->getAllRecoveryCodesForLogin('mylogin'));
        $this->twoFa->disable2FAforUser('mylogin');
        $this->assertEquals([], $this->dao->getAllRecoveryCodesForLogin('mylogin'));
    }

    public function testSaveSecretNeverWorksForAnonymous()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Anonymous cannot use');

        $this->twoFa->saveSecret('anonymous', '123456');
    }

    public function testSaveSecretNotWorksWhenNoRecoveryCodesCreated()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('no recovery codes have been created');

        $this->twoFa->saveSecret('not', '123456');
    }

    public function testIsUserUsingTwoFactorAuthenticationNeverWorksForAnonymous()
    {
        $this->assertFalse(TwoFactorAuthentication::isUserUsingTwoFactorAuthentication('anonymous'));
    }

    public function testValidateAuthCodeDuringSetup()
    {
        $secret = '789123';
        $this->assertFalse($this->twoFa->validateAuthCodeDuringSetup('123456', $secret));

        $authCode = $this->generateValidAuthCode($secret);

        $this->assertTrue($this->twoFa->validateAuthCodeDuringSetup($authCode, $secret));
    }

    public function testValidateAuthCodeUserIsNotUsingTwoFa()
    {
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin', '123456'));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin', false));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin', null));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin', ''));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin', 0));
    }

    public function testValidateAuthCodeUserIsUsingTwoFaAuthenticatesThroughApp()
    {
        $secret1 = '123456';
        $secret2 = '654321';
        $this->dao->createRecoveryCodesForLogin('mylogin1');
        $this->dao->createRecoveryCodesForLogin('mylogin2');
        $this->twoFa->saveSecret('mylogin1', $secret1);
        $this->twoFa->saveSecret('mylogin2', $secret2);

        $authCode1 = $this->generateValidAuthCode($secret1);
        $authCode2 = $this->generateValidAuthCode($secret2);

        $this->assertTrue($this->twoFa->validateAuthCode('mylogin1', $authCode1));
        $this->assertTrue($this->twoFa->validateAuthCode('mylogin2', $authCode2));

        $this->assertFalse($this->twoFa->validateAuthCode('mylogin2', $authCode1));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin1', $authCode2));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin1', false));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin2', null));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin2', ''));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin1', 0));
    }

    public function testValidateAuthCodeUserIsUsingTwoFaSameCodeCannotBeUsedTwice()
    {
        $secret1 = '654321';
        $secret2 = '654321';
        $this->dao->createRecoveryCodesForLogin('mylogin1');
        $this->dao->createRecoveryCodesForLogin('mylogin2');
        $this->twoFa->saveSecret('mylogin1', $secret1);
        $this->twoFa->saveSecret('mylogin2', $secret2);

        $authCode1 = $this->generateValidAuthCode($secret1);
        $authCode2 = $this->generateValidAuthCode($secret2);

        $options = Option::getLike(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . '%');
        $this->assertEquals(array(), $options); // no token used yet

        $this->assertTrue($this->twoFa->validateAuthCode('mylogin2', $authCode2));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin2', $authCode2));

        // can be used by different user though
        $this->assertTrue($this->twoFa->validateAuthCode('mylogin1', $authCode1));
        $this->assertFalse($this->twoFa->validateAuthCode('mylogin1', $authCode1));

        $options = Option::getLike(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . '%');
        $this->assertCount(2, $options);
    }

    public function testValidateAuthCodeUserIsUsingTwoFaAuthenticatesThroughRecoveryCode()
    {
        $this->dao->createRecoveryCodesForLogin('mylogin1');
        $this->dao->createRecoveryCodesForLogin('mylogin2');
        $this->twoFa->saveSecret('mylogin1', '123456');
        $this->twoFa->saveSecret('mylogin2', '654321');

        $codesLogin1 = $this->dao->getAllRecoveryCodesForLogin('mylogin1');
        $codesLogin2 = $this->dao->getAllRecoveryCodesForLogin('mylogin2');
        $this->assertNotEmpty($codesLogin1);
        $this->assertNotEmpty($codesLogin2);

        foreach ($codesLogin1 as $code) {
            // doesn't work cause belong to different user
            $this->assertFalse($this->twoFa->validateAuthCode('mylogin2', $code));
        }

        foreach ($codesLogin1 as $code) {
            $this->assertTrue($this->twoFa->validateAuthCode('mylogin1', $code));
        }

        foreach ($codesLogin1 as $code) {
            // no code can be used twice
            $this->assertFalse($this->twoFa->validateAuthCode('mylogin1', $code));
        }
    }

    public function testCleanupTwoFaCodesUsedRecently()
    {
        $this->twoFa->cleanupTwoFaCodesUsedRecently();

        Option::set(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . 'test1', time());
        Option::set(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . 'test2', time() - 100);

        // those two should be deleted because they were used more than 10min ago
        Option::set(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . 'test3', time() - 1000);
        Option::set(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . 'test4', time() - 5000);

        $options = Option::getLike(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . '%');
        $this->assertCount(4, $options);

        $this->twoFa->cleanupTwoFaCodesUsedRecently();

        $options = Option::getLike(TwoFactorAuthentication::OPTION_PREFIX_TWO_FA_CODE_USED . '%');
        $this->assertEquals(['twofa_codes_used_test1', 'twofa_codes_used_test2'], array_keys($options));
    }

    private function generateValidAuthCode($secret)
    {
        $code = new \TwoFactorAuthenticator();
        return $code->getCode($secret);
    }
}
