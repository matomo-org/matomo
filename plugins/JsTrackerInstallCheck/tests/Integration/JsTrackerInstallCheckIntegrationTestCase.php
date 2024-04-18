<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Integration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class JsTrackerInstallCheckIntegrationTestCase extends IntegrationTestCase
{
    public const TEST_URL1 = 'https://some-test-site.local';
    public const TEST_URL2 = 'https://another-test-site.local';
    public const TEST_URL3 = 'https://nonexistent-test-site.local';
    public const TEST_NONCE1 = '7fa8282ad93047a4d6fe6111c93b308a';
    public const TEST_NONCE2 = '79d886010186eb60e3611cd4a5d0bcae';

    /**
     * @var int
     */
    protected $idSite1;

    /**
     * @var int
     */
    protected $idSite2;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite1 = 1;
        $this->idSite2 = 2;
    }
}
