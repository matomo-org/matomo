<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\UrlLike;

/**
 * @group Validator
 * @group UrlLike
 * @group UrlLikeTest
 */
class UrlLikeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getValidUrls
     */
    public function test_validate_successValueIsLikeUri($validUrl)
    {
        self::expectNotToPerformAssertions();

        $this->validate($validUrl);
    }

    public function getValidUrls()
    {
        return [
            array('http://piwik.org'),
            array('http://www.piwik.org'),
            array('https://piwik.org'),
            array('https://piwik.org/dir/dir2/?oeajkgea7aega=&ge=a'),
            array('ftp://www.pi-wik.org'),
            array('news://www.pi-wik.org'),
            array('https://www.tëteâ.org'),
            array('http://汉语/漢語.cn'), //chinese

            array('rtp://whatever.com'),
            array('testhttp://test.com'),
            array('cylon://3.hmn'),
            array('://something.com'),

            // valid network-path reference RFC3986
            array('//piwik.org'),
            array('//piwik/hello?world=test&test'),
            array('//piwik.org/hello?world=test&test'),
        ];
    }

    /**
     * @dataProvider getFailedUrls
     */
    public function test_validate_failValueIsNotUrlLike($url)
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('ValidatorErrorNotUrlLike');

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
