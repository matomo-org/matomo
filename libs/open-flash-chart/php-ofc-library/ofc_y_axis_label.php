<?php

/**
 * y_axis_label see y_axis_labels
 */
class y_axis_label
{
	function y_axis_label( $y, $text)
	{
		$this->y = $y;
		$this->set_text( $text );
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
}