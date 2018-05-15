<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\tests\Integration;

use Piwik\DataTable\Renderer;
use Piwik\Plugins\SEO\API;
use Exception;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group SEO
 * @group SEOTest
 * @group Plugins
 */
class SEOTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3, 4));

        //finally we set the user as a Super User by default
        FakeAccess::$superUser = true;

        $user_agents = array(
            'Mozilla/6.0 (Macintosh; I; Intel Mac OS X 11_7_9; de-LI; rv:1.9b4) Gecko/2012010317 Firefox/10.0a4',
            'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0',
            'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/18.6.872.0 Safari/535.2 UNTRUSTED/1.0 3gpp-gba UNTRUSTED/1.0',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
        );

        $_SERVER['HTTP_USER_AGENT'] = $user_agents[mt_rand(0, count($user_agents) - 1)];
    }

    /**
     * tell us when the API is broken
     */
    public function test_API()
    {
        try {
            $dataTable = API::getInstance()->getRank('http://www.microsoft.com/');
        } catch(Exception $e) {
            $this->markTestSkipped('A SEO http request failed, Skipping this test for now. Error was: '.$e->getMessage());
        }
        $renderer = Renderer::factory('php');
        $renderer->setSerialize(false);
        $ranks = $renderer->render($dataTable);
        foreach ($ranks as $rank) {
            $message = $rank['id'] . ' expected non-zero rank, got [' . $rank['rank'] . ']';
            if(empty($rank['rank'])) {
                $this->markTestSkipped("Skipped to avoid random build failure: " . $message);
            }
            $this->assertNotEmpty($rank['rank'], $message);
        }
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
