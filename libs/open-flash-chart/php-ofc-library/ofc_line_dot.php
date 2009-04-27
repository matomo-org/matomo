<?php

class dot_value
{
	function dot_value( $value, $colour )
	{
		$this->value = $value;
		$this->colour = $colour;
	}
	
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	function set_size( $size )
	{
		$this->size = $size;
	}
	
	function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}
}

class line_dot extends line_base
{
	function line_dot()
	{
		$this->type      = "line_dot";
	}
}