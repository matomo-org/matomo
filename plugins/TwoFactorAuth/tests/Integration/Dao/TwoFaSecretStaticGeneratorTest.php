<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\Integration\Dao;

use Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretStaticGenerator;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TwoFactorAuth
 * @group TwoFaSecretStaticGeneratorTest
 * @group Plugins
 */
class TwoFaSecretStaticGeneratorTest extends IntegrationTestCase
{
    /**
     * @var TwoFaSecretStaticGenerator
     */
    private $generator;

    public function setUp(): void
    {
        parent::setUp();

        $this->generator = new TwoFaSecretStaticGenerator();
    }

    public function testGeneratorCodeAlwaysSame()
    {
        $this->assertSame($this->generator->generateSecret(), $this->generator->generateSecret());
    }

    public function testGeneratorCodeIncreases()
    {
        $this->assertSame('1111111111111111', $this->generator->generateSecret());
    }
}
