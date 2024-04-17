<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests;

use Piwik\Plugins\Referrers\Social;

/**
 * @group Social
 * @group Plugins
 */
class SocialTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        // inject definitions to avoid database usage
        $yml = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . Social::DEFINITION_FILE);
        Social::getInstance()->loadYmlData($yml);
        parent::setUpBeforeClass();
    }

    public function isSocialUrlTestData()
    {
        return array(
            array('http://www.facebook.com', 'Facebook', true),
            array('http://www.facebook.com', 'Twitter', false),
            array('http://m.facebook.com', false, true),
            array('http://lastfm.com.tr', 'Last.fm', true),
            array('http://asdfasdf.org/test', false, false),
            array('http://asdfasdf.com/test', 'Facebook', false),
        );
    }

    /**
     * @dataProvider isSocialUrlTestData
     */
    public function testIsSocialUrl($url, $assumedSocial, $expected)
    {
        $this->assertEquals($expected, Social::getInstance()->isSocialUrl($url, $assumedSocial));
    }


    /**
     * Dataprovider for getSocialNetworkFromDomainTestData
     */
    public function getSocialNetworkFromDomainTestData()
    {
        return array(
            array('http://www.facebook.com', 'Facebook'),
            array('http://www.facebook.com/piwik', 'Facebook'),
            array('http://m.facebook.com', 'Facebook'),
            array('https://m.facebook.com', 'Facebook'),
            array('m.facebook.com', 'Facebook'),
            array('http://lastfm.com.tr', 'Last.fm'),
            array('http://t.co/test', 'Twitter'),
            array('http://xxt.co/test', \Piwik\Piwik::translate('General_Unknown')),
            array('asdfasdfadsf.com', \Piwik\Piwik::translate('General_Unknown')),
            array('http://xwayn.com', \Piwik\Piwik::translate('General_Unknown')),
            array('http://live.com/test', \Piwik\Piwik::translate('General_Unknown')),
        );
    }

    /**
     * @dataProvider getSocialNetworkFromDomainTestData
     */
    public function testGetSocialNetworkFromDomain($url, $expected)
    {
        $this->assertEquals($expected, Social::getInstance()->getSocialNetworkFromDomain($url));
    }

    public function getLogoFromUrlTestData()
    {
        return array(
            array('http://www.facebook.com', 'facebook.com.png'),
            array('www.facebook.com', 'facebook.com.png',),
            array('http://lastfm.com.tr', 'last.fm.png'),
            array('http://asdfasdf.org/test', 'xx.png'),
            array('http://www.google.com', 'xx.png'),
        );
    }

    /**
     * @group Plugins
     *
     * @dataProvider getLogoFromUrlTestData
     */
    public function testGetLogoFromUrl($url, $expected)
    {
        self::assertStringContainsString($expected, Social::getInstance()->getLogoFromUrl($url));
    }
}
