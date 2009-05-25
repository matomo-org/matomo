<?php

class scatter_line
{
	function scatter_line( $colour, $width  )
	{
		$this->type      = "scatter_line";
		$this->set_colour( $colour );
		$this->set_width( $width );
	}
	
	function set_default_dot_style( $style )
	{
		$tmp = 'dot-style';
		$this->$tmp = $style;	
	}
	
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	function set_width( $width )
	{
		$this->width = $width;
	}
	
	function set_values( $values )
	{
		$this->values = $values;
	}
	
	function set_step_horizontal()
	{
		$this->stepgraph = 'horizontal';
	}
	
	function set_step_vertical()
	{
		$this->stepgraph = 'vertical';
	}
	
	function set_key( $text, $font_size )
	{
		$this->text      = $text;
		$tmp = 'font-size';
		$this->$tmp = $font_size;
	}
}