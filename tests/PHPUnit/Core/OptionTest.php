<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Common;
use Piwik\Option;
use Piwik\Db;

require_once "Option.php";

class OptionTest extends DatabaseTestCase
{
    /**
     * @group Core
     * @group Option
     */
    public function testGet()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Option::getInstance()->get('anonymous_defaultReport'));

        // populate table, expect '1' (i.e., found)
        Db::query("INSERT INTO " . Common::prefixTable('option') . " VALUES ('anonymous_defaultReport', '1', false)");
        $this->assertSame('1', Option::getInstance()->get('anonymous_defaultReport'));

        // delete row (bypassing API), expect '1' (i.e., from cache)
        Db::query("DELETE FROM " . Common::prefixTable('option') . " WHERE option_name = ?", array('anonymous_defaultReport'));
        $this->assertSame('1', Option::getInstance()->get('anonymous_defaultReport'));

        // force cache reload, expect false (i.e., not found)
        Option::getInstance()->clearCache();
        $this->assertFalse(Option::getInstance()->get('anonymous_defaultReport'));
    }

    /**
     * @group Core
     * @group Option
     */
    public function testGetOption()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Piwik_GetOption('anonymous_defaultReport'));

        // populate table, expect '1' (i.e., found)
        Db::query("INSERT INTO " . Common::prefixTable('option') . " VALUES ('anonymous_defaultReport', '1',true)");
        $this->assertSame('1', Piwik_GetOption('anonymous_defaultReport'));

        // delete row (bypassing API), expect '1' (i.e., from cache)
        Db::query("DELETE FROM " . Common::prefixTable('option') . " WHERE option_name = ?", array('anonymous_defaultReport'));
        $this->assertSame('1', Piwik_GetOption('anonymous_defaultReport'));

        // force cache reload, expect false (i.e., not found)
        Option::getInstance()->clearCache();
        $this->assertFalse(Piwik_GetOption('anonymous_defaultReport'));
    }

    /**
     * @group Core
     * @group Option
     */
    public function testSet()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Piwik_GetOption('anonymous_defaultReport'));

        // populate table, expect '1'
        Option::getInstance()->set('anonymous_defaultReport', '1', true);
        $this->assertSame('1', Option::getInstance()->get('anonymous_defaultReport'));
    }

    /**
     * @group Core
     * @group Option
     */
    public function testSetOption()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Piwik_GetOption('anonymous_defaultReport'));

        // populate table, expect '1'
        Piwik_SetOption('anonymous_defaultReport', '1', false);
        $this->assertSame('1', Option::getInstance()->get('anonymous_defaultReport'));
    }

    /**
     * @group Core
     * @group Option
     */
    public function testDelete()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Piwik_GetOption('anonymous_defaultReport'));
        $this->assertFalse(Piwik_GetOption('admin_defaultReport'));

        // populate table, expect '1'
        Piwik_SetOption('anonymous_defaultReport', '1', true);
        Option::getInstance()->delete('_defaultReport');
        $this->assertSame('1', Option::getInstance()->get('anonymous_defaultReport'));

        // populate table, expect '2'
        Piwik_SetOption('admin_defaultReport', '2', false);
        Option::getInstance()->delete('_defaultReport');
        $this->assertSame('2', Option::getInstance()->get('admin_defaultReport'));

        // delete with non-matching value, expect '1'
        Option::getInstance()->delete('anonymous_defaultReport', '2');
        $this->assertSame('1', Option::getInstance()->get('anonymous_defaultReport'));

        // delete with matching value, expect false
        Option::getInstance()->delete('anonymous_defaultReport', '1');
        $this->assertFalse(Option::getInstance()->get('anonymous_defaultReport'));

        // this shouldn't have been deleted, expect '2'
        $this->assertSame('2', Option::getInstance()->get('admin_defaultReport'));

        // deleted, expect false
        Option::getInstance()->delete('admin_defaultReport');
        $this->assertFalse(Option::getInstance()->get('admin_defaultReport'));
    }

    /**
     * @group Core
     * @group Option
     */
    public function testDeleteLike()
    {
        // empty table, expect false (i.e., not found)
        $this->assertFalse(Piwik_GetOption('anonymous_defaultReport'));
        $this->assertFalse(Piwik_GetOption('admin_defaultReport'));
        $this->assertFalse(Piwik_GetOption('visitor_defaultReport'));

        // insert guard - to test unescaped underscore
        Piwik_SetOption('adefaultReport', '0', true);
        $this->assertTrue(Piwik_GetOption('adefaultReport') === '0');

        // populate table, expect '1'
        Piwik_SetOption('anonymous_defaultReport', '1', true);
        Option::getInstance()->deleteLike('\_defaultReport');
        $this->assertSame('1', Option::getInstance()->get('anonymous_defaultReport'));
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // populate table, expect '2'
        Piwik_SetOption('admin_defaultReport', '2', false);
        Option::getInstance()->deleteLike('\_defaultReport');
        $this->assertSame('2', Option::getInstance()->get('admin_defaultReport'));
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // populate table, expect '3'
        Piwik_SetOption('visitor_defaultReport', '3', false);
        Option::getInstance()->deleteLike('\_defaultReport');
        $this->assertSame('3', Option::getInstance()->get('visitor_defaultReport'));
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // delete with non-matching value, expect '1'
        Option::getInstance()->deleteLike('%\_defaultReport', '4');
        $this->assertSame('1', Option::getInstance()->get('anonymous_defaultReport'));
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // delete with matching pattern, expect false
        Option::getInstance()->deleteLike('%\_defaultReport', '1');
        $this->assertFalse(Option::getInstance()->get('anonymous_defaultReport'));
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // this shouldn't have been deleted, expect '2' and '3'
        $this->assertSame('2', Option::getInstance()->get('admin_defaultReport'));
        $this->assertSame('3', Option::getInstance()->get('visitor_defaultReport'));
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // deleted, expect false (except for the guard)
        Option::getInstance()->deleteLike('%\_defaultReport');
        $this->assertFalse(Option::getInstance()->get('admin_defaultReport'));
        $this->assertFalse(Option::getInstance()->get('visitor_defaultReport'));

        // unescaped backslash (single quotes)
        Option::getInstance()->deleteLike('%\_defaultReport');
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // escaped backslash (single quotes)
        Option::getInstance()->deleteLike('%\\_defaultReport');
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // unescaped backslash (double quotes)
        Option::getInstance()->deleteLike("%\_defaultReport");
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));

        // escaped backslash (double quotes)
        Option::getInstance()->deleteLike("%\\_defaultReport");
        $this->assertSame('0', Piwik_GetOption('adefaultReport'));
    }
}
