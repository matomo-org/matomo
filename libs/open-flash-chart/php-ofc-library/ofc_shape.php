<?php

class shape_point
{
	function shape_point( $x, $y )
	{
		$this->x = $x;
		$this->y = $y;
	}
}

class shape
{
	function shape( $colour )
	{
		$this->type		= "shape";
		$this->colour	= $colour;
		$this->values	= array();
	}
	
	function append_value( $p )
	{
		$this->values[] = $p;	
	}
}