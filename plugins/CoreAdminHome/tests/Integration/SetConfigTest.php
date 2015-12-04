<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Config;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Url;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Integration
 */
class SetConfigTest extends ConsoleCommandTestCase
{
    public function test_Command_SucceedsWhenOptionsUsed()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'config:set',
            '--section' => 'MySection',
            '--key' => 'setting',
            '--value' => 'myvalue',
            '-vvv' => false,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        $config = Config::getInstance();
        $this->assertEquals(array('setting' => 'myvalue'), $config->MySection);

        $this->assertContains('Setting [MySection] setting = "myvalue"', $this->applicationTester->getDisplay());
    }

    /**
     * @dataProvider getInvalidArgumentsForTest
     */
    public function test_Command_FailsWhenInvalidArgumentsUsed($invalidArgument)
    {
        $code = $this->applicationTester->run(array(
            'command' => 'config:set',
            'assignment' => array($invalidArgument),
            '-vvv' => false,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains('Invalid assignment string', $this->applicationTester->getDisplay());
    }

    public function getInvalidArgumentsForTest()
    {
        return array(
            array("garbage"),
            array("ab&cd.ghi=23"),
            array("section.value = 34"),
            array("section.value = notjson"),
            array("section.array[0]=23"),
        );
    }

    public function test_Command_SucceedsWhenArgumentsUsed()
    {
        $config = Config::getInstance();
        $config->General['trusted_hosts'] = array('www.trustedhost.com');

        $code = $this->applicationTester->run(array(
            'command' => 'config:set',
            'assignment' => array(
                'General.action_url_category_delimiter="+"',
                'General.trusted_hosts[]="www.trustedhost2.com"',
                'MySection.array_value=["abc","def"]',
                'MySection.object_value={"abc":"def"}',
            ),
            '-vvv' => false,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        $config->clear();

        $this->assertEquals('+', $config->General['action_url_category_delimiter']);
        $this->assertEquals(array('www.trustedhost.com', 'www.trustedhost2.com'), $config->General['trusted_hosts']);
        $this->assertEquals(array('abc', 'def'), $config->MySection['array_value']);
        $this->assertEquals(array('abc' => 'def'), $config->MySection['object_value']);

        $this->assertContains("Done.", $this->applicationTester->getDisplay());
    }
}
