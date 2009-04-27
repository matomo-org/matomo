<?php

include_once 'ofc_bar_base.php';

class bar_value
{
	/**
	 * @param $top as integer. The Y value of the top of the bar
	 * @param OPTIONAL $bottom as integer. The Y value of the bottom of the bar, defaults to Y min.
	 */
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

class bar_glass extends bar_base
{
	function bar_glass()
	{
		$this->type      = "bar_glass";
		parent::bar_base();
	}
}

class bar_cylinder extends bar_base
{
	function bar_cylinder()
	{
		$this->type      = "bar_cylinder";
		parent::bar_base();
	}
}

class bar_cylinder_outline extends bar_base
{
	function bar_cylinder_outline()
	{
		$this->type      = "bar_cylinder_outline";
		parent::bar_base();
	}
}

class bar_rounded_glass extends bar_base
{
	function bar_rounded_glass()
	{
		$this->type      = "bar_round_glass";
		parent::bar_base();
	}
}

class bar_round extends bar_base
{
	function bar_round()
	{
		$this->type      = "bar_round";
		parent::bar_base();
	}
}

class bar_dome extends bar_base
{
	function bar_dome()
	{
		$this->type      = "bar_dome";
		parent::bar_base();
	}
}

class bar_round3d extends bar_base
{
	function bar_round3d()
	{
		$this->type      = "bar_round3d";
		parent::bar_base();
	}
}

class bar_3d extends bar_base
{
	function bar_3d()
	{
		$this->type      = "bar_3d";
		parent::bar_base();
	}
}