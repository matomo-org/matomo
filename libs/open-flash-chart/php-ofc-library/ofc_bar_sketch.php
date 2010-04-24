<?php

include_once dirname(__FILE__) . '/ofc_bar_base.php';

class bar_sketch extends bar_base
{
	/**
	 * @param $colour as string, HEX colour e.g. '#00ff00'
	 * @param $outline_colour as string, HEX colour e.g. '#ff0000'
	 * @param $fun_factor as integer, range 0 to 10. 0,1 and 2 are pretty boring.
	 * 4 to 6 is a bit fun, 7 and above is lots of fun. 
	 */
	function bar_sketch( $colour, $outline_colour, $fun_factor )
	{
		$this->type      = "bar_sketch";
		parent::bar_base();
		
		$this->set_colour( $colour );
		$this->set_outline_colour( $outline_colour );
		$this->offset = $fun_factor;
	}
	
	function set_outline_colour( $outline_colour )
	{
		$tmp = 'outline-colour';
		$this->$tmp = $outline_colour;	
	}
}

