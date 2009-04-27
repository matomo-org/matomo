<?php

class radar_axis
{
	function radar_axis( $max )
	{
		$this->set_max( $max );
	}
	
	function set_max( $max )
	{
		$this->max = $max;
	}
	
	function set_steps( $steps )
	{
		$this->steps = $steps;
	}
	
	function set_stroke( $s )
	{
		$this->stroke = $s;
	}
    
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	function set_grid_colour( $colour )
	{
		$tmp = 'grid-colour';
		$this->$tmp = $colour;
	}
	
	function set_labels( $labels )
	{
		$this->labels = $labels;
	}
	
	function set_spoke_labels( $labels )
	{
		$tmp = 'spoke-labels';
		$this->$tmp = $labels;
	}
}

