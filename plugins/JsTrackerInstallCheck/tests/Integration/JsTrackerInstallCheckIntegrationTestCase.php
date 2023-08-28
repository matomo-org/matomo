<?php

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Integration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class JsTrackerInstallCheckIntegrationTestCase extends IntegrationTestCase
{
    const TEST_URL1 = 'https://some-test-site.local';
    const TEST_URL2 = 'https://another-test-site.local';
    const TEST_URL3 = 'https://nonexistent-test-site.local';
    const TEST_NONCE1 = '1111111111';
    const TEST_NONCE2 = '2222222222';

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
