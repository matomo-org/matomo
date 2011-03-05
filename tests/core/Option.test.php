<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once 'Database.test.php';
require_once "Option.php";

class Test_Piwik_Option extends Test_Database
{
	public function test_get()
	{
		// empty table, expect false (i.e., not found)
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === false );

		// populate table, expect '1' (i.e., found)
		Piwik_Query("INSERT INTO ".Piwik_Common::prefixTable('option')." VALUES ('anonymous_defaultReport', '1', false)");
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === '1' );

		// delete row (bypassing API), expect '1' (i.e., from cache)
		Piwik_Query("DELETE FROM ".Piwik_Common::prefixTable('option')." WHERE option_name = ?", array('anonymous_defaultReport'));
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === '1' );

		// force cache reload, expect false (i.e., not found)
		Piwik_Option::getInstance()->clearCache();
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === false );
	}

	public function test_getOption()
	{
		// empty table, expect false (i.e., not found)
		$this->assertTrue( Piwik_GetOption('anonymous_defaultReport') === false );

		// populate table, expect '1' (i.e., found)
		Piwik_Query("INSERT INTO ".Piwik_Common::prefixTable('option')." VALUES ('anonymous_defaultReport', '1',true)");
		$this->assertTrue( Piwik_GetOption('anonymous_defaultReport') === '1' );

		// delete row (bypassing API), expect '1' (i.e., from cache)
		Piwik_Query("DELETE FROM ".Piwik_Common::prefixTable('option')." WHERE option_name = ?", array('anonymous_defaultReport'));
		$this->assertTrue( Piwik_GetOption('anonymous_defaultReport') === '1' );

		// force cache reload, expect false (i.e., not found)
		Piwik_Option::getInstance()->clearCache();
		$this->assertTrue( Piwik_GetOption('anonymous_defaultReport') === false );
	}

	public function test_set()
	{
		// empty table, expect false (i.e., not found)
		$this->assertTrue( Piwik_GetOption('anonymous_defaultReport') === false );

		// populate table, expect '1'
		Piwik_Option::getInstance()->set('anonymous_defaultReport', '1', true);
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === '1' );
	}

	public function test_setOption()
	{
		// empty table, expect false (i.e., not found)
		$this->assertTrue( Piwik_GetOption('anonymous_defaultReport') === false );

		// populate table, expect '1'
		Piwik_SetOption('anonymous_defaultReport', '1', false);
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === '1' );
	}

	public function test_delete()
	{
		// empty table, expect false (i.e., not found)
		$this->assertTrue( Piwik_GetOption('anonymous_defaultReport') === false );
		$this->assertTrue( Piwik_GetOption('admin_defaultReport') === false );

		// populate table, expect '1'
		Piwik_SetOption('anonymous_defaultReport', '1', true);
		Piwik_Option::getInstance()->delete('_defaultReport');
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === '1' );

		// populate table, expect '2'
		Piwik_SetOption('admin_defaultReport', '2', false);
		Piwik_Option::getInstance()->delete('_defaultReport');
		$this->assertTrue( Piwik_Option::getInstance()->get('admin_defaultReport') === '2' );

		// delete with non-matching value, expect '1'
		Piwik_Option::getInstance()->delete('anonymous_defaultReport', '2');
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === '1' );

		// delete with matching value, expect false
		Piwik_Option::getInstance()->delete('anonymous_defaultReport', '1');
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === false );

		// this shouldn't have been deleted, expect '2'
		$this->assertTrue( Piwik_Option::getInstance()->get('admin_defaultReport') === '2' );

		// deleted, expect false
		Piwik_Option::getInstance()->delete('admin_defaultReport');
		$this->assertTrue( Piwik_Option::getInstance()->get('admin_defaultReport') === false );
	}

	public function test_deleteLike()
	{
		// empty table, expect false (i.e., not found)
		$this->assertTrue( Piwik_GetOption('anonymous_defaultReport') === false );
		$this->assertTrue( Piwik_GetOption('admin_defaultReport') === false );
		$this->assertTrue( Piwik_GetOption('visitor_defaultReport') === false );

		// insert guard - to test unescaped underscore
		Piwik_SetOption('adefaultReport', '0', true);
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// populate table, expect '1'
		Piwik_SetOption('anonymous_defaultReport', '1', true);
		Piwik_Option::getInstance()->deleteLike('\_defaultReport');
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === '1' );
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// populate table, expect '2'
		Piwik_SetOption('admin_defaultReport', '2', false);
		Piwik_Option::getInstance()->deleteLike('\_defaultReport');
		$this->assertTrue( Piwik_Option::getInstance()->get('admin_defaultReport') === '2' );
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// populate table, expect '3'
		Piwik_SetOption('visitor_defaultReport', '3', false);
		Piwik_Option::getInstance()->deleteLike('\_defaultReport');
		$this->assertTrue( Piwik_Option::getInstance()->get('visitor_defaultReport') === '3' );
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// delete with non-matching value, expect '1'
		Piwik_Option::getInstance()->deleteLike('%\_defaultReport', '4');
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === '1' );
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// delete with matching pattern, expect false
		Piwik_Option::getInstance()->deleteLike('%\_defaultReport', '1');
		$this->assertTrue( Piwik_Option::getInstance()->get('anonymous_defaultReport') === false );
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// this shouldn't have been deleted, expect '2' and '3'
		$this->assertTrue( Piwik_Option::getInstance()->get('admin_defaultReport') === '2' );
		$this->assertTrue( Piwik_Option::getInstance()->get('visitor_defaultReport') === '3' );
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// deleted, expect false (except for the guard)
		Piwik_Option::getInstance()->deleteLike('%\_defaultReport');
		$this->assertTrue( Piwik_Option::getInstance()->get('admin_defaultReport') === false );
		$this->assertTrue( Piwik_Option::getInstance()->get('visitor_defaultReport') === false );

		// unescaped backslash (single quotes)
		Piwik_Option::getInstance()->deleteLike('%\_defaultReport');
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// escaped backslash (single quotes)
		Piwik_Option::getInstance()->deleteLike('%\\_defaultReport');
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// unescaped backslash (double quotes)
		Piwik_Option::getInstance()->deleteLike("%\_defaultReport");
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );

		// escaped backslash (double quotes)
		Piwik_Option::getInstance()->deleteLike("%\\_defaultReport");
		$this->assertTrue( Piwik_GetOption('adefaultReport') === '0' );
	}
}
