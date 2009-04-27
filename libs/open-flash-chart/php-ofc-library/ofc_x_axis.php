<?php

class x_axis
{
	function x_axis(){}

	/**
	 * @param $stroke as integer, with of the line and ticks
	 */
	function set_stroke( $stroke )
	{
		$this->stroke = $stroke;	
	}
	
	/**
	 *@param $colour as string HEX colour
	 *@param $grid_colour as string HEX colour
	 */
	function set_colours( $colour, $grid_colour )
	{
		$this->set_colour( $colour );
		$this->set_grid_colour( $grid_colour );
	}
	
	/**
	 *@param $colour as string HEX colour
	 */
	function set_colour( $colour )
	{
		$this->colour = $colour;	
	}
	
	function set_tick_height( $height )
	{
		$tmp = 'tick-height';
		$this->$tmp      		= $height;
	}
	
	function set_grid_colour( $colour )
	{
		$tmp = 'grid-colour';
		$this->$tmp = $colour;
	}
	
	/**
	 * @param $o is a boolean. If true, the X axis start half a step in
	 * This defaults to True
	 */
	function set_offset( $o )
	{
		$this->offset = $o?true:false;	
	}
	
	/**
	 * @param $steps as integer. Which ticks are visible.
	 */
	function set_steps( $steps )
	{
		$this->steps = $steps;
	}
	
	/**
	 * @param $val as an integer, the height in pixels of the 3D bar. Mostly
	 * used for the 3D bar chart.
	 */
	function set_3d( $val )
	{
		$tmp = '3d';
		$this->$tmp				= $val;		
	}
	
	/**
	 * @param $x_axis_labels as an x_axis_labels object
	 * Use this to customize the labels (colour, font, etc...)
	 */
	function set_labels( $x_axis_labels )
	{
		//$this->labels = $v;
		$this->labels = $x_axis_labels;
	}
	
	/**
	 * Sugar syntax: helper function to make the examples simpler.
	 * @param $a is an array of labels
	 */
	function set_labels_from_array( $a )
	{
		$x_axis_labels = new x_axis_labels();
		$x_axis_labels->set_labels( $a );
		$this->labels = $x_axis_labels;
		
		if( isset( $this->steps ) )
			$x_axis_labels->set_steps( $this->steps );
	}
	
	/**
	 * min and max.
	 */
	function set_range( $min, $max )
	{
		$this->min = $min;
		$this->max = $max;
	}
}