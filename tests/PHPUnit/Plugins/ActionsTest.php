<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Tracker\Action;
use Piwik\Translate;

require_once 'Actions/Actions.php';

class ActionsTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Translate::loadEnglishTranslation();
    }

    public function tearDown()
    {
        Translate::unloadEnglishTranslation();
    }

    public function getActionNameTestData()
    {
        return array(
            array(
                'params'   => array('name' => 'http://example.org/', 'type' => Action::TYPE_PAGE_URL, 'urlPrefix' => null),
                'expected' => array('/index'),
            ),
            array(
                'params'   => array('name' => 'example.org/', 'type' => Action::TYPE_PAGE_URL, 'urlPrefix' => 1),
                'expected' => array('/index'),
            ),
            array(
                'params'   => array('name' => 'example.org/', 'type' => Action::TYPE_PAGE_URL, 'urlPrefix' => 2),
                'expected' => array('/index'),
            ),
            array(
                'params'   => array('name' => 'example.org/', 'type' => Action::TYPE_PAGE_URL, 'urlPrefix' => 3),
                'expected' => array('/index'),
            ),
            array(
                'params'   => array('name' => 'example.org/', 'type' => Action::TYPE_PAGE_URL, 'urlPrefix' => 4),
                'expected' => array('/index'),
            ),
            array(
                'params'   => array('name' => 'example.org/path/', 'type' => Action::TYPE_PAGE_URL, 'urlPrefix' => 4),
                'expected' => array('path', '/index'),
            ),
            array(
                'params'   => array('name' => 'example.org/test/path', 'type' => Action::TYPE_PAGE_URL, 'urlPrefix' => 1),
                'expected' => array('test', '/path'),
            ),
            array(
                'params'   => array('name' => 'http://example.org/path/', 'type' => Action::TYPE_PAGE_URL),
                'expected' => array('path', '/index'),
            ),
            array(
                'params'   => array('name' => 'example.org/test/path', 'type' => Action::TYPE_PAGE_URL, 'urlPrefix' => 1),
                'expected' => array('test', '/path'),
            ),
            array(
                'params'   => array('name' => 'Test / Path', 'type' => Action::TYPE_PAGE_URL),
                'expected' => array('Test', '/Path'),
            ),
            array(
                'params'   => array('name' => '    Test trim   ', 'type' => Action::TYPE_PAGE_URL),
                'expected' => array('/Test trim'),
            ),
            array(
                'params'   => array('name' => 'Category / Subcategory', 'type' => Action::TYPE_PAGE_TITLE),
                'expected' => array('Category', ' Subcategory'),
            ),
            array(
                'params'   => array('name' => '/path/index.php?var=test', 'type' => Action::TYPE_PAGE_TITLE),
                'expected' => array('path', ' index.php?var=test'),
            ),
            array(
                'params'   => array('name' => 'http://example.org/path/Default.aspx#anchor', 'type' => Action::TYPE_PAGE_TITLE),
                'expected' => array('path', ' Default.aspx#anchor'),
            ),
            array(
                'params'   => array('name' => '', 'type' => Action::TYPE_PAGE_TITLE),
                'expected' => array('Page Name not defined'),
            ),
            array(
                'params'   => array('name' => '', 'type' => Action::TYPE_PAGE_URL),
                'expected' => array('Page URL not defined'),
            ),
            array(
                'params'   => array('name' => 'http://example.org/download.zip', 'type' => Action::TYPE_DOWNLOAD),
                'expected' => array('example.org', '/download.zip'),
            ),
            array(
                'params'   => array('name' => 'http://example.org/download/1/', 'type' => Action::TYPE_DOWNLOAD),
                'expected' => array('example.org', '/download/1/'),
            ),
            array(
                'params'   => array('name' => 'http://example.org/link', 'type' => Action::TYPE_OUTLINK),
                'expected' => array('example.org', '/link'),
            ),
            array(
                'params'   => array('name' => 'http://example.org/some/path/', 'type' => Action::TYPE_OUTLINK),
                'expected' => array('example.org', '/some/path/'),
            ),
        );
    }

    /**
     * @dataProvider getActionNameTestData
     * @group        Plugins
     */
    public function testGetActionExplodedNames($params, $expected)
    {
        ArchivingHelper::reloadConfig();
        $processed = ArchivingHelper::getActionExplodedNames($params['name'], $params['type'], (isset($params['urlPrefix']) ? $params['urlPrefix'] : null));
        $this->assertEquals($expected, $processed);
    }
}

