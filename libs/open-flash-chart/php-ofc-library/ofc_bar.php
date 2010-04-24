<?php

include_once dirname(__FILE__) . '/ofc_bar_base.php';

class bar_value
{
	function bar_value( $top, $bottom=null )
	{
		$this->top = $top;
		
		if( isset( $bottom ) )
			$this->bottom = $bottom;
	}
	
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}
}

class bar extends bar_base
{
	function bar()
	{
		$this->type      = "bar";
		parent::bar_base();
	}
}

