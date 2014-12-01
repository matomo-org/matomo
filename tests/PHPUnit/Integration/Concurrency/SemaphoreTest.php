<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Concurrency;

use Piwik\Common;
use Piwik\Concurrency\Semaphore;
use Piwik\Db;
use Piwik\Option;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class SemaphoreTest extends IntegrationTestCase
{
    const TEST_SEMAPHORE_NAME = "Tests.testSemaphore";

    /**
     * @var Semaphore
     */
    private $semaphore;

    public function setUp()
    {
        parent::setUp();

        $this->semaphore = new Semaphore(self::TEST_SEMAPHORE_NAME);
    }

    public function test_increment_AddsOneToValue()
    {
        $this->semaphore->set(0);

        $this->semaphore->increment();
        $this->assertEquals(1, $this->getSemaphoreValue());
        $this->assertEquals(1, $this->semaphore->get());

        $this->semaphore->increment();
        $this->assertEquals(2, $this->getSemaphoreValue());
        $this->assertEquals(2, $this->semaphore->get());
    }

    public function test_decrement_SubtractsOneToValue()
    {
        $this->semaphore->set(0);

        $this->semaphore->decrement();
        $this->assertEquals(-1, $this->getSemaphoreValue());
        $this->assertEquals(-1, $this->semaphore->get());

        $this->semaphore->set(10);
        $this->semaphore->decrement();
        $this->assertEquals(9, $this->getSemaphoreValue());
        $this->assertEquals(9, $this->semaphore->get());
    }

    public function test_advance_AddsAnyValueToStoredValue()
    {
        $this->semaphore->set(0);

        $this->semaphore->advance(10);
        $this->assertEquals(10, $this->getSemaphoreValue());
        $this->assertEquals(10, $this->semaphore->get());

        $this->semaphore->advance(5);
        $this->assertEquals(15, $this->getSemaphoreValue());
        $this->assertEquals(15, $this->semaphore->get());
    }

    public function test_deleteLike_CorrectlyDeletesStoredSemaphores()
    {
        Option::set("Tests.testOption", 1);

        $semaphore2 = new Semaphore("Tests.test3");
        $semaphore2->set(0);

        $semaphore3 = new Semaphore("Tests.notPrefixed");
        $semaphore3->set(0);

        $this->assertEquals(3, $this->getOptionsWithTestsCount());

        Semaphore::deleteLike("Tests.test%");

        $this->assertEquals(2, $this->getOptionsWithTestsCount());
    }

    private function getSemaphoreValue()
    {
        return Db::fetchOne("SELECT option_value FROM `".Common::prefixTable('option')."` WHERE option_name = ?",
            Semaphore::OPTION_NAME_PREFIX . self::TEST_SEMAPHORE_NAME);
    }

    private function getOptionsWithTestsCount()
    {
        return Db::fetchOne("SELECT COUNT(*) FROM `" . Common::prefixTable('option'). "` WHERE option_name LIKE '%Tests.%'");
    }
}