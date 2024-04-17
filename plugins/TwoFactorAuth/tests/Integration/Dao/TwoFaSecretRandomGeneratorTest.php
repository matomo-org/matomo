<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\Integration\Dao;

use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TwoFactorAuth
 * @group TwoFaSecretRandomGeneratorTest
 * @group Plugins
 */
class TwoFaSecretRandomGeneratorTest extends IntegrationTestCase
{
    /**
     * @var TwoFaSecretRandomGenerator
     */
    private $generator;

    public function setUp(): void
    {
        parent::setUp();

        $this->generator = new TwoFaSecretRandomGenerator();
    }

    public function test_generatorCode_length()
    {
        $this->assertSame(16, mb_strlen($this->generator->generateSecret()));
    }

    public function test_generatorCode_alwaysDifferent()
    {
        $this->assertNotEquals($this->generator->generateSecret(), $this->generator->generateSecret());
    }
}
