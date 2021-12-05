<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Config;
use Piwik\Tests\Framework\Fixture;

/**
 * Add config disable archiving
 */
class DisablePluginArchive extends Fixture
{
    public $idSite = 1;
    public $dateTime = '2009-01-04 00:11:42';

    public $trackInvalidRequests = true;

    public function setUp(): void
    {
      $this->setUpConfig();
    }

    public function tearDown(): void
    {
        $this->removeConfig();
    }

    private function setUpConfig()
    {
        $config = Config::getInstance();
        $config->General['disable_archiving_segment_for_plugins'] = 'Referrers';
    }

    private function removeConfig()
    {
        $config = Config::getInstance();
        $config->General['disable_archiving_segment_for_plugins'] = '';
    }
}