<?php

/**
 * Set the title of a chart, make one of these and pass it into
 * open_flash_chart set_title
 */
class title
{
	function title( $text='' )
	{
		$this->text = $text;
	}
	
	/**
	 * A css string. Can optionally contain:
     * - font-size
     * - font-family
     * - font-weight
     * - color
     * - background-color
     * - text-align
     * - margin
     * - margin-left
     * - margin-right
     * - margin-top
     * - margin-bottom
     * - padding
     * - padding-left
     * - padding-right
     * - padding-top
     * - padding-bottom
     * just like the css we use all the time :-)
	 */
	function set_style( $css )
	{
		$this->style = $css;
		//"{font-size: 20px; color:#0000ff; font-family: Verdana; text-align: center;}";		
	}
}