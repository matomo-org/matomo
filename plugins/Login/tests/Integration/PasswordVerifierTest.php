<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Date;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Login\PasswordVerifier;

class CustomPasswordVerifier extends PasswordVerifier
{
    public function hasBeenVerifiedAndHalfTimeValid()
    {
        return parent::hasBeenVerifiedAndHalfTimeValid();
    }
}

class PasswordVerifierTest extends IntegrationTestCase
{
    /**
     * @var CustomPasswordVerifier
     */
    private $verifier;

    public function setUp(): void
    {
        parent::setUp();

        \Zend_Session::$_unitTestEnabled = true;

        $this->verifier = new CustomPasswordVerifier();
        $this->verifier->setDisableRedirect();
    }

    public function testHasBeenVerifiedByDefaultNotVerified()
    {
        $this->assertFalse($this->verifier->hasBeenVerified());
    }

    public function testHasBeenVerifiedAndHalfTimeValidByDefaultNotVerified()
    {
        $this->assertFalse($this->verifier->hasBeenVerifiedAndHalfTimeValid());
    }

    public function testHasPasswordVerifyBeenRequestedByDefaultNotRequested()
    {
        $this->assertFalse($this->verifier->hasPasswordVerifyBeenRequested());
    }

    public function testRequirePasswordVerifiedRecently()
    {
        $this->assertNull($this->requirePasswordVerify());
        $this->assertTrue($this->verifier->hasPasswordVerifyBeenRequested());
        $this->assertFalse($this->verifier->hasBeenVerified());
        $this->assertFalse($this->verifier->hasBeenVerifiedAndHalfTimeValid());
    }

    public function testSetPasswordVerifiedCorrectly()
    {
        $this->assertNull($this->requirePasswordVerify());
        $this->assertFalse($this->verifier->hasBeenVerified());
        $this->assertFalse($this->verifier->hasBeenVerifiedAndHalfTimeValid());

        $this->verifier->setPasswordVerifiedCorrectly();

        $this->assertTrue($this->verifier->hasBeenVerified());
        $this->assertTrue($this->verifier->hasBeenVerifiedAndHalfTimeValid());
        $this->assertTrue($this->requirePasswordVerify()); // no need to redirect
    }

    public function testSetPasswordVerifiedCorrectlyRequiresAPasswordToBeRequestedToBeValid()
    {
        $this->verifier->setPasswordVerifiedCorrectly();

        $this->assertFalse($this->verifier->hasBeenVerified());
        $this->assertFalse($this->verifier->hasBeenVerifiedAndHalfTimeValid());
        $this->assertNull($this->requirePasswordVerify());
    }

    public function testSetPasswordVerifiedCorrectlyExpiresAfter15Min()
    {
        $this->assertNull($this->requirePasswordVerify());
        $this->assertFalse($this->verifier->hasBeenVerified());
        $this->assertFalse($this->verifier->hasBeenVerifiedAndHalfTimeValid());

        $this->verifier->setPasswordVerifiedCorrectly();

        $this->assertTrue($this->verifier->hasBeenVerified());
        $this->assertTrue($this->verifier->hasBeenVerifiedAndHalfTimeValid());
        $this->assertTrue($this->requirePasswordVerify()); // no need to redirect

        $this->verifier->setNow(Date::now()->addPeriod(PasswordVerifier::VERIFY_REVALIDATE_X_MINUTES_LEFT - 1, 'minutes'));

        $this->assertTrue($this->verifier->hasBeenVerified());
        $this->assertTrue($this->verifier->hasBeenVerifiedAndHalfTimeValid());
        $this->assertTrue($this->requirePasswordVerify()); // no need to redirect

        $this->verifier->setNow(Date::now()->addPeriod(PasswordVerifier::VERIFY_REVALIDATE_X_MINUTES_LEFT + 1, 'minutes'));

        $this->assertTrue($this->verifier->hasBeenVerified()); // it was verified recently
        $this->assertFalse($this->verifier->hasBeenVerifiedAndHalfTimeValid());
        $this->assertNull($this->requirePasswordVerify()); // no need to redirect

        $this->verifier->setNow(Date::now()->addPeriod(PasswordVerifier::VERIFY_VALID_FOR_MINUTES + 1, 'minutes'));

        $this->assertFalse($this->verifier->hasBeenVerified()); // it was verified recently
        $this->assertFalse($this->verifier->hasBeenVerifiedAndHalfTimeValid());
        $this->assertNull($this->requirePasswordVerify()); // no need to redirect
    }

    public function testForgetVerifiedPassword()
    {
        $this->requirePasswordVerify();
        $this->verifier->setPasswordVerifiedCorrectly();
        $this->assertTrue($this->verifier->hasBeenVerified());
        $this->assertTrue($this->requirePasswordVerify()); // no need to redirect

        $this->verifier->forgetVerifiedPassword();

        $this->assertNull($this->requirePasswordVerify());
        $this->assertFalse($this->verifier->hasBeenVerified());
    }

    private function requirePasswordVerify()
    {
        return $this->verifier->requirePasswordVerifiedRecently(array('module' => 'Login', 'action' => 'test'));
    }
}
