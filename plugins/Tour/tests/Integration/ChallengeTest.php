<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour\tests\Integration;

use Piwik\Plugins\Tour\Engagement\Challenge;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CustomTestChallenge extends Challenge
{
    public function getId()
    {
        return 'test_challenge';
    }
    public function getName()
    {
        return 'Test Challenge';
    }
}

class CustomTest2Challenge extends Challenge
{
    public function getId()
    {
        return 'test_challenge2';
    }
    public function getName()
    {
        return 'Test Challenge2';
    }
}

/**
 * @group Tour
 * @group ChallengeTest
 * @group Plugins
 */
class ChallengeTest extends IntegrationTestCase
{
    /**
     * @var Challenge
     */
    private $challenge;

    /**
     * @var Challenge
     */
    private $challenge2;

    public function setUp(): void
    {
        parent::setUp();

        $this->challenge = new CustomTestChallenge();
        $this->challenge2 = new CustomTest2Challenge();
    }

    public function tearDown(): void
    {
        Challenge::clearCache();
        parent::tearDown();
    }

    public function test_skip()
    {
        $login = 'foo';
        $this->assertFalse($this->challenge->isSkipped($login));
        $this->assertFalse($this->challenge2->isSkipped($login));
        $this->assertFalse($this->challenge->isCompleted($login));
        $this->assertFalse($this->challenge2->isCompleted($login));

        $this->challenge2->skipChallenge($login);

        $this->assertFalse($this->challenge->isSkipped($login));
        $this->assertTrue($this->challenge2->isSkipped($login));
        $this->assertFalse($this->challenge2->isSkipped('barbaz'));
        $this->assertFalse($this->challenge->isCompleted($login));
        $this->assertFalse($this->challenge2->isCompleted($login));
    }

    public function test_complete()
    {
        $login = 'foo';
        $this->assertFalse($this->challenge->isSkipped($login));
        $this->assertFalse($this->challenge2->isSkipped($login));
        $this->assertFalse($this->challenge->isCompleted($login));
        $this->assertFalse($this->challenge2->isCompleted($login));

        $this->challenge->setCompleted($login);

        $this->assertFalse($this->challenge->isSkipped($login));
        $this->assertFalse($this->challenge2->isSkipped($login));
        $this->assertTrue($this->challenge->isCompleted($login));
        $this->assertFalse($this->challenge->isCompleted('barbaz'));
        $this->assertFalse($this->challenge2->isCompleted($login));
    }
}
