<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Access;
use Piwik\AuthResult;
use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class PiwikTest extends IntegrationTestCase
{
    /**
     * Dataprovider for testIsNumericValid
     */
    public function getValidNumeric()
    {
        $valid = array(
            -1, 0, 1, 1.5, -1.5, 21111, 89898, 99999999999, -4565656,
            (float)-1, (float)0, (float)1, (float)1.5, (float)-1.5, (float)21111, (float)89898, (float)99999999999, (float)-4565656,
            (int)-1, (int)0, (int)1, (int)1.5, (int)-1.5, (int)21111, (int)89898, (int)99999999999, (int)-4565656,
            '-1', '0', '1', '1.5', '-1.5', '21111', '89898', '99999999999', '-4565656',
            '1e3', 0x123, "-1e-2",
        );

        foreach ($valid as $key => $value) {
            $valid[$key] = array($value);
        }
        return $valid;
    }

    /**
     * @dataProvider getValidNumeric
     */
    public function testIsNumericValid($toTest)
    {
        $this->assertTrue(is_numeric($toTest), $toTest . " not valid but should!");
    }

    /**
     * Dataprovider for testIsNumericNotValid
     */
    public function getInvalidNumeric()
    {
        $notValid = array(
            '-1.0.0', '1,2', '--1', '-.', '- 1', '1-',
        );
        foreach ($notValid as $key => $value) {
            $notValid[$key] = array($value);
        }
        return $notValid;
    }

    /**
     * @dataProvider getInvalidNumeric
     */
    public function testIsNumericNotValid($toTest)
    {
        $this->assertFalse(is_numeric($toTest), $toTest . " valid but shouldn't!");
    }

    public function testSecureDiv()
    {
        $this->assertSame(3, Piwik::secureDiv(9, 3));
        $this->assertSame(0, Piwik::secureDiv(9, 0));
        $this->assertSame(10, Piwik::secureDiv(10, 1));
        $this->assertSame(10.0, Piwik::secureDiv(10.0, 1.0));
        $this->assertSame(5.5, Piwik::secureDiv(11.0, 2));
        $this->assertSame(0, Piwik::secureDiv(11.0, 'a'));
    }

    /**
     * Dataprovider for testCheckValidLoginString
     */
    public function getInvalidLoginStringData()
    {
        $notValid = array(
            '',
            '   ',
            'a',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'alpha/beta',
            'alpha:beta',
            'alpha;beta',
            'alpha<beta',
            'alpha=beta',
            'alpha>beta',
            'alpha?beta',
        );
        foreach ($notValid as $key => $value) {
            $notValid[$key] = array($value);
        }
        return $notValid;
    }

    /**
     * @dataProvider getInvalidLoginStringData
     */
    public function testCheckInvalidLoginString($toTest)
    {
        $this->expectException(\Exception::class);
        Piwik::checkValidLoginString($toTest);
    }

    /**
     * Dataprovider for testCheckValidLoginString
     */
    public function getValidLoginStringData()
    {
        $valid = array(
            'aa',
            'aaa',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'äÄüÜöÖß',
            'shoot_puck@the-goal.com',
        );
        foreach ($valid as $key => $value) {
            $valid[$key] = array($value);
        }
        return $valid;
    }

    /**
     * @dataProvider getValidLoginStringData
     */
    public function testCheckValidLoginString($toTest)
    {
        $this->assertNull(Piwik::checkValidLoginString($toTest));
    }

    /**
     * Data provider for testIsAssociativeArray.
     */
    public function getIsAssociativeArrayTestCases()
    {
        return array(
            array(array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd', 4 => 'e', 5 => 'f'), false),
            array(array(-1 => 'a', 0 => 'a', 1 => 'a', 2 => 'a', 3 => 'a'), true),
            array(array(4 => 'a', 5 => 'a', 6 => 'a', 7 => 'a', 8 => 'a'), true),
            array(array(0 => 'a', 2 => 'a', 3 => 'a', 4 => 'a', 5 => 'a'), true),
            array(array('abc' => 'a', 0 => 'b', 'sdfds' => 'd'), true),
            array(array('abc' => 'def'), true)
        );
    }

    /**
     * @dataProvider getIsAssociativeArrayTestCases
     */
    public function testIsAssociativeArray($array, $expected)
    {
        $this->assertEquals($expected, Piwik::isAssociativeArray($array));
    }


    public function test_isUserIsAnonymous_shouldReturnTrueWhenLoginIsAnonymous()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'anonymous', 'token')));

        $mock->expects($this->any())->method('getName')->will($this->returnValue("test name"));

        $this->assertTrue(Access::getInstance()->reloadAccess($mock));
        $this->assertTrue(Piwik::isUserIsAnonymous());
    }

    public function test_isUserIsAnonymous_shouldReturnTrueWhenThereIsNoLogin()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::FAILURE, null, null)));

        $mock->expects($this->any())->method('getName')->will($this->returnValue("test name"));

        $this->assertFalse(Access::getInstance()->reloadAccess($mock));
        $this->assertTrue(Piwik::isUserIsAnonymous());
    }

    public function test_isUserIsAnonymous_shouldReturnFalseWhenLoginIsNotAnonymous()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'someuser', 'token')));

        $mock->expects($this->any())->method('getName')->will($this->returnValue("test name"));

        $this->assertTrue(Access::getInstance()->reloadAccess($mock));
        $this->assertFalse(Piwik::isUserIsAnonymous());
    }

    public function test_isUserIsAnonymous_shouldReturnFalseWhenLoginIsAnonymousButHasSuperUserAccess()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, 'anonymous', 'token')));

        $mock->expects($this->any())->method('getName')->will($this->returnValue("test name"));

        $this->assertTrue(Access::getInstance()->reloadAccess($mock));
        $this->assertFalse(Piwik::isUserIsAnonymous());
    }

    public function test_isUserIsAnonymous_shouldReturnFalseWhenLoginIsAnonymousButSetSuperUserAccessUsed()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::FAILURE, null, null)));

        $mock->expects($this->any())->method('getName')->will($this->returnValue("test name"));

        $this->assertFalse(Access::getInstance()->reloadAccess($mock));
        $this->assertTrue(Piwik::isUserIsAnonymous());

        Access::doAsSuperUser(function () {
            $this->assertFalse(Piwik::isUserIsAnonymous());
        });
    }

    private function createPiwikAuthMockInstance()
    {
        return $this->getMockBuilder('Piwik\\Auth')
            ->onlyMethods(array('authenticate', 'getName', 'getTokenAuthSecret', 'getLogin', 'setTokenAuth', 'setLogin',
                'setPassword', 'setPasswordHash'))
            ->getMock();
    }
}
