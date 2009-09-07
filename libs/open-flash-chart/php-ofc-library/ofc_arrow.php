<?php

class ofc_arrow
{
	/**
	 *@param $x as number. Start x position
	 *@param $y as number. Start y position
	 *@param $a as number. End x position
	 *@param $b as number. End y position
	 *@param $colour as string.
	 *@param $barb_length as number. Length of the barbs in pixels.
	 */
	function ofc_arrow($x, $y, $a, $b, $colour, $barb_length=10)
	{
		$this->type     = "arrow";
		$this->start	= array("x"=>$x, "y"=>$y);
		$this->end		= array("x"=>$a, "y"=>$b);
		$this->colour($colour);
		$this->{"barb-length"} = $barb_length;
	}
	
	function colour( $colour )
	{
		$this->colour = $colour;
		return $this;
	}
}
