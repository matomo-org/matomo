<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
require_once 'UserCountry/functions.php';

class Test_Piwik_UserCountry extends PHPUnit_Framework_Testcase
{
    /**
     * 
     * @group Plugins
     * @group UserCountry
     */
    public function testGetFlagFromCode()
    {
        $flag = Piwik_getFlagFromCode("us");
        $this->assertEquals( basename($flag), "us.png" );
    }

    /**
     * 
     * @group Plugins
     * @group UserCountry
     */
    public function testGetFlagFromInvalidCode()
    {
        $flag = Piwik_getFlagFromCode("foo");
        $this->assertEquals( basename($flag), "xx.png" );
    }

    /**
     * 
     * @group Plugins
     * @group UserCountry
     */
    public function testFlagsAndContinents()
    {
        require PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/Countries.php';

        $continents = $GLOBALS['Piwik_ContinentList'];
        $countries = array_merge($GLOBALS['Piwik_CountryList'], $GLOBALS['Piwik_CountryList_Extras']);

        // Get list of existing flag icons
        $flags = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/UserCountry/flags/');

        // Get list of countries
        foreach($countries as $country => $continent)
        {
            // test continent
            $this->assertContains($continent, $continents);

            // test flag
            $this->assertContains($country . '.png', $flags);
        }

        foreach($flags as $filename)
        {
            if($filename == '.' || $filename == '..' || $filename == '.svn')
            {
                continue;
            }

            $country = substr($filename, 0, strpos($filename, '.png'));

            // test country
            $this->assertArrayHasKey($country, $countries, $filename);
        }
    }
}

