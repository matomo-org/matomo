<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
class PeriodTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group Period
     */
    public function testGetId()
    {
        $period = new Piwik_Period_Day( Piwik_Date::today() );
        $this->assertNotEquals( 0,  $period->getId() );
        $period = new Piwik_Period_Week( Piwik_Date::today() );
        $this->assertNotEquals( 0,  $period->getId() );
        $period = new Piwik_Period_Month( Piwik_Date::today() );
        $this->assertNotEquals( 0,  $period->getId() );
        $period = new Piwik_Period_Year( Piwik_Date::today() );
        $this->assertNotEquals( 0,  $period->getId() );
    }
    
    /**
     * @group Core
     * @group Period
     */
    public function testGetLabel()
    {
        $period = new Piwik_Period_Day( Piwik_Date::today() );
        $label = $period->getLabel();
        $this->assertInternalType( 'string', $label);
        $this->assertNotEmpty($label);
        $period = new Piwik_Period_Week( Piwik_Date::today() );
        $label = $period->getLabel();
        $this->assertInternalType( 'string', $label);
        $this->assertNotEmpty($label);
        $period = new Piwik_Period_Month( Piwik_Date::today() );
        $label = $period->getLabel();
        $this->assertInternalType( 'string', $label);
        $this->assertNotEmpty($label);
        $period = new Piwik_Period_Year( Piwik_Date::today() );
        $label = $period->getLabel();
        $this->assertInternalType( 'string', $label);
        $this->assertNotEmpty($label);
    }
}