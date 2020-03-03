<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Http;
use Piwik\Piwik;
use Piwik\Session;
use Piwik\Tests\Framework\Fixture;

/**
 * @group Core
 * @group Session
 * @group SessionTest
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
	public function test_session_should_not_be_started_if_it_was_already_started()
	{
        $url = Fixture::getRootUrl() . '/tests/resources/sessionStarter.php';
	    $result = Http::sendHttpRequest($url, 5);
	    $this->assertSame('ok', trim($result));
	}

}
