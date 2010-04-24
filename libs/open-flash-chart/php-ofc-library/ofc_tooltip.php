<?php

include_once dirname(__FILE__) . '/ofc_bar_base.php';

class tooltip
{
	function tooltip(){}
	
	/**
	 * @param $shadow as boolean. Enable drop shadow.
	 */
	function set_shadow( $shadow )
	{
		$this->shadow = $shadow;
	}
	
	/**
	 * @param $stroke as integer, border width in pixels (e.g. 5 )
	 */
	function set_stroke( $stroke )
	{
		$this->stroke = $stroke;
	}
	
	/**
	 * @param $colour as string, HEX colour e.g. '#0000ff'
	 */
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	/**
	 * @param $bg as string, HEX colour e.g. '#0000ff'
	 */
	function set_background_colour( $bg )
	{
		$this->background = $bg;
	}
	
	/**
	 * @param $style as string. A css style.
	 */
	function set_title_style( $style )
	{
		$this->title = $style;
	}
	
	/**
	 * @param $style as string. A css style.
	 */
    function set_body_style( $style )
	{
		$this->body = $style;
	}
	
	function set_proximity()
	{
		$this->mouse = 1;
	}
	
	function set_hover()
	{
		$this->mouse = 2;
	}
}

