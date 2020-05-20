<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Config;
use Piwik\Date;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tracker\Action;
use Piwik\Tracker\FingerprintSalt;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\Request;
use Piwik\Translate;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group ActionTest
 */
class FingerprintSaltTest extends IntegrationTestCase
{
    /**
     * @var FingerprintSalt
     */
    private $fingerprintSalt;

    public function setUp()
    {
        parent::setUp();

        $this->fingerprintSalt = new FingerprintSalt();
    }

    public function test_generateSalt()
    {
        $salt = $this->fingerprintSalt->generateSalt();
        $this->assertEquals(32, strlen($salt));
        $this->assertTrue(ctype_alnum($salt));
    }

    public function test_generateSalt_isRandom()
    {
        $this->assertNotSame($this->fingerprintSalt->generateSalt(), $this->fingerprintSalt->generateSalt());
    }

    public function test_getDateString()
    {
        $date = Date::factory('2020-05-05 14:04:05');
        $this->assertSame('2020-05-06',$this->fingerprintSalt->getDateString($date, 'Pacific/Auckland'));
        $this->assertSame('2020-05-05',$this->fingerprintSalt->getDateString($date, 'Europe/Berlin'));
    }

    public function test_getSalt_remembersSaltPerSite()
    {
        $salt05_1 = $this->fingerprintSalt->getSalt('2020-05-05', $idSite = 1);
        $salt06_1 = $this->fingerprintSalt->getSalt('2020-05-06', $idSite = 1);
        $salt05_2 = $this->fingerprintSalt->getSalt('2020-05-05', $idSite = 2);
        $salt06_2 = $this->fingerprintSalt->getSalt('2020-05-06', $idSite = 2);

        $this->assertNotSame($salt05_1, $salt06_1);
        $this->assertNotSame($salt05_2, $salt06_2);
        $this->assertNotSame($salt06_1, $salt06_2);

        $this->assertSame($salt05_1, $this->fingerprintSalt->getSalt('2020-05-05', $idSite = 1));
        $this->assertSame($salt06_1, $this->fingerprintSalt->getSalt('2020-05-06', $idSite = 1));
        $this->assertSame($salt05_2, $this->fingerprintSalt->getSalt('2020-05-05', $idSite = 2));
    }

    public function test_deleteOldSalts_whenNothingToDelete()
    {
        $this->fingerprintSalt->getSalt('2020-05-05', $idSite = 1);
        $this->fingerprintSalt->getSalt('2020-05-06', $idSite = 1);

        Date::$now = time() - FingerprintSalt::DELETE_FINGERPRINT_OLDER_THAN_SECONDS + 30;// they would expire in 30 seconds
        $this->fingerprintSalt->getSalt('2020-05-05', $idSite = 2);
        $this->fingerprintSalt->getSalt('2020-05-06', $idSite = 2);

        Date::$now = time();
        $this->assertSame(array(), $this->fingerprintSalt->deleteOldSalts());
    }

    public function test_deleteOldSalts_someToBeDeleted()
    {
        $this->fingerprintSalt->getSalt('2020-05-05', $idSite = 1);

        Date::$now = time() - FingerprintSalt::DELETE_FINGERPRINT_OLDER_THAN_SECONDS - 30; // these entries should be expired
        $this->fingerprintSalt->getSalt('2020-05-06', $idSite = 1);
        $this->fingerprintSalt->getSalt('2020-05-05', $idSite = 2);
        $this->fingerprintSalt->getSalt('2020-05-06', $idSite = 2);

        Date::$now = time();
        $this->assertSame(array(
            'fingerprint_salt_1_2020-05-06',
            'fingerprint_salt_2_2020-05-05',
            'fingerprint_salt_2_2020-05-06'
        ), $this->fingerprintSalt->deleteOldSalts());

        // executing it again wont delete anything
        $this->assertSame(array(), $this->fingerprintSalt->deleteOldSalts());
    }

}
