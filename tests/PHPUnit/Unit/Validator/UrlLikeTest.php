<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\UrlLike;

/**
 * @group Validator
 * @group UrlLike
 * @group UrlLikeTest
 */
class UrlLikeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidUrls
     */
    public function test_validate_successValueIsLikeUri($validUrl)
    {
        $this->validate($validUrl);
        $this->assertTrue(true);
    }

    public function getValidUrls()
    {
        return [
            array('http://piwik.org', true),
            array('http://www.piwik.org', true),
            array('https://piwik.org', true),
            array('https://piwik.org/dir/dir2/?oeajkgea7aega=&ge=a', true),
            array('ftp://www.pi-wik.org', true),
            array('news://www.pi-wik.org', true),
            array('https://www.tëteâ.org', true),
            array('http://汉语/漢語.cn', true), //chinese

            array('rtp://whatever.com', true),
            array('testhttp://test.com', true),
            array('cylon://3.hmn', true),
            array('://something.com', true),

            // valid network-path reference RFC3986
            array('//piwik.org', true),
            array('//piwik/hello?world=test&test', true),
            array('//piwik.org/hello?world=test&test', true),
        ];
    }

    /**
     * @dataProvider getFailedUrls
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage ValidatorErrorNotUrlLike
     */
    public function test_validate_failValueIsNotUrlLike($url)
    {
        $this->validate($url);
    }

    public function getFailedUrls()
    {
        return [
            array('it doesnt look like url'),
            array('/index?page=test'),
            array('http:/index?page=test'),
            array('http/index?page=test'),
            array('test.html'),
            array('/\/\/\/\/\/\\\http://test.com////'),
            array('jmleslangues.php'),
            array('http://'),
            array(' http://'),
            array('2fer://'),
        ];
    }

    private function validate($value)
    {
        $validator = new UrlLike();
        $validator->validate($value);
    }
}
