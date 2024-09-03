<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class OptionTest extends IntegrationTestCase
{
    public function testGet()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Option::get('anonymous_defaultReport'));

        // populate table, expect '1' (i.e., found)
        Db::query("INSERT INTO `" . Common::prefixTable('option') . "` VALUES ('anonymous_defaultReport', '1', false)");
        // force cache reload, so value is reloaded
        Option::clearCache();
        $this->assertSame('1', Option::get('anonymous_defaultReport'));

        // delete row (bypassing API), expect '1' (i.e., from cache)
        Db::query("DELETE FROM `" . Common::prefixTable('option') . "` WHERE option_name = ?", array('anonymous_defaultReport'));
        $this->assertSame('1', Option::get('anonymous_defaultReport'));

        // force cache reload, expect false (i.e., not found)
        Option::clearCache();
        $this->assertFalse(Option::get('anonymous_defaultReport'));
    }

    public function testGetOption()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Option::get('anonymous_defaultReport'));

        // populate table, expect '1' (i.e., found)
        Db::query("INSERT INTO `" . Common::prefixTable('option') . "` VALUES ('anonymous_defaultReport', '1',true)");
        // force cache reload, so value is reloaded
        Option::clearCache();
        $this->assertSame('1', Option::get('anonymous_defaultReport'));

        // delete row (bypassing API), expect '1' (i.e., from cache)
        Db::query("DELETE FROM `" . Common::prefixTable('option') . "` WHERE option_name = ?", array('anonymous_defaultReport'));
        $this->assertSame('1', Option::get('anonymous_defaultReport'));

        // force cache reload, expect false (i.e., not found)
        Option::clearCache();
        $this->assertFalse(Option::get('anonymous_defaultReport'));
    }

    public function testSet()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Option::get('anonymous_defaultReport'));

        // populate table, expect '1'
        Option::set('anonymous_defaultReport', '1', true);
        $this->assertSame('1', Option::get('anonymous_defaultReport'));
    }

    public function testSetOption()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Option::get('anonymous_defaultReport'));

        // populate table, expect '1'
        Option::set('anonymous_defaultReport', '1', false);
        $this->assertSame('1', Option::get('anonymous_defaultReport'));
    }

    public function testDelete()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Option::get('anonymous_defaultReport'));
        $this->assertFalse(Option::get('admin_defaultReport'));

        // populate table, expect '1'
        Option::set('anonymous_defaultReport', '1', true);
        Option::delete('_defaultReport');
        $this->assertSame('1', Option::get('anonymous_defaultReport'));

        // populate table, expect '2'
        Option::set('admin_defaultReport', '2', false);
        Option::delete('_defaultReport');
        $this->assertSame('2', Option::get('admin_defaultReport'));

        // delete with non-matching value, expect '1'
        Option::delete('anonymous_defaultReport', '2');
        $this->assertSame('1', Option::get('anonymous_defaultReport'));

        // delete with matching value, expect false
        Option::delete('anonymous_defaultReport', '1');
        $this->assertFalse(Option::get('anonymous_defaultReport'));

        // this shouldn't have been deleted, expect '2'
        $this->assertSame('2', Option::get('admin_defaultReport'));

        // deleted, expect false
        Option::delete('admin_defaultReport');
        $this->assertFalse(Option::get('admin_defaultReport'));
    }

    public function testDeleteLike()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Option::get('anonymous_defaultReport'));
        $this->assertFalse(Option::get('admin_defaultReport'));
        $this->assertFalse(Option::get('visitor_defaultReport'));

        // insert guard - to test unescaped underscore
        Option::set('adefaultReport', '0', true);
        $this->assertTrue(Option::get('adefaultReport') === '0');

        // populate table, expect '1'
        Option::set('anonymous_defaultReport', '1', true);
        Option::deleteLike('\_defaultReport');
        $this->assertSame('1', Option::get('anonymous_defaultReport'));
        $this->assertSame('0', Option::get('adefaultReport'));

        // populate table, expect '2'
        Option::set('admin_defaultReport', '2', false);
        Option::deleteLike('\_defaultReport');
        $this->assertSame('2', Option::get('admin_defaultReport'));
        $this->assertSame('0', Option::get('adefaultReport'));

        // populate table, expect '3'
        Option::set('visitor_defaultReport', '3', false);
        Option::deleteLike('\_defaultReport');
        $this->assertSame('3', Option::get('visitor_defaultReport'));
        $this->assertSame('0', Option::get('adefaultReport'));

        // delete with non-matching value, expect '1'
        Option::deleteLike('%\_defaultReport', '4');
        $this->assertSame('1', Option::get('anonymous_defaultReport'));
        $this->assertSame('0', Option::get('adefaultReport'));

        // delete with matching pattern, expect false
        Option::deleteLike('%\_defaultReport', '1');
        $this->assertFalse(Option::get('anonymous_defaultReport'));
        $this->assertSame('0', Option::get('adefaultReport'));

        // this shouldn't have been deleted, expect '2' and '3'
        $this->assertSame('2', Option::get('admin_defaultReport'));
        $this->assertSame('3', Option::get('visitor_defaultReport'));
        $this->assertSame('0', Option::get('adefaultReport'));

        // deleted, expect false (except for the guard)
        Option::deleteLike('%\_defaultReport');
        $this->assertFalse(Option::get('admin_defaultReport'));
        $this->assertFalse(Option::get('visitor_defaultReport'));

        // unescaped backslash (single quotes)
        Option::deleteLike('%\_defaultReport');
        $this->assertSame('0', Option::get('adefaultReport'));

        // escaped backslash (single quotes)
        Option::deleteLike('%\\_defaultReport');
        $this->assertSame('0', Option::get('adefaultReport'));

        // unescaped backslash (double quotes)
        Option::deleteLike("%\_defaultReport");
        $this->assertSame('0', Option::get('adefaultReport'));

        // escaped backslash (double quotes)
        Option::deleteLike("%\\_defaultReport");
        $this->assertSame('0', Option::get('adefaultReport'));
    }

    public function testDeleteLikeUnderscoreNotWildcard()
    {
        // insert guard - to test unescaped underscore
        Option::set('adefaultReport', '1', true);

        Option::deleteLike("adefaul_Report"); // the underscore should not match a character
        $this->assertSame('1', Option::get('adefaultReport'));
    }

    public function testGetLike()
    {
        Option::set('adefaultReport', '1', true);
        Option::set('adefaultRepo', '1', true);
        Option::set('adefaultRepppppppport', '1', true);

        $values = Option::getLike("adefaultRepo%"); // the underscore should not match a character
        $this->assertSame(array(
            'adefaultRepo' => '1',
            'adefaultReport' => '1'
        ), $values);
    }

    public function testGetLikeUnderscoreNotWildcard()
    {
        // insert guard - to test unescaped underscore
        Option::set('adefaultReport', '1', true);

        $values = Option::getLike("adefaul_Report"); // the underscore should not match a character
        $this->assertSame(array(), $values);
        $values = Option::getLike("adefaul%Report");
        $this->assertSame(array('adefaultReport' => '1'), $values);
    }
}
