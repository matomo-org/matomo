<?php

/**
 * x_axis_label see x_axis_labels
 */
class x_axis_label
{
	function x_axis_label( $text, $colour, $size, $rotate )
	{
		$this->set_text( $text );
		$this->set_colour( $colour );
		$this->set_size( $size );
		$this->set_rotate( $rotate );
	}
	
	function set_text( $text )
	{
		$this->text = $text;
	}
	
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	function set_size( $size )
	{
		$this->size = $size;
	}
	
	function set_rotate( $rotate )
	{
		$this->rotate = $rotate;
	}
	
	function set_vertical()
	{
		$this->rotate = "vertical";
	}
	
	function set_visible()
	{
		$this->visible = true;
	}
}