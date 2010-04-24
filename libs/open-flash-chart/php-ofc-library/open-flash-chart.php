<?php

// var_dump(debug_backtrace());

//
// Omar Kilani's php C extension for encoding JSON has been incorporated in stock PHP since 5.2.0
// http://www.aurore.net/projects/php-json/
//
// -- Marcus Engene
//
if (! function_exists('json_encode'))
{
	include_once dirname(__FILE__) . '/JSON.php';
}

include_once dirname(__FILE__) . '/json_format.php';

// ofc classes
include_once dirname(__FILE__) . '/ofc_title.php';
include_once dirname(__FILE__) . '/ofc_y_axis_base.php';
include_once dirname(__FILE__) . '/ofc_y_axis.php';
include_once dirname(__FILE__) . '/ofc_y_axis_right.php';
include_once dirname(__FILE__) . '/ofc_y_axis_labels.php';
include_once dirname(__FILE__) . '/ofc_y_axis_label.php';
include_once dirname(__FILE__) . '/ofc_x_axis.php';


include_once dirname(__FILE__) . '/ofc_pie.php';
//include_once dirname(__FILE__) . '/ofc_bar.php';
include_once dirname(__FILE__) . '/ofc_bar_glass.php';
include_once dirname(__FILE__) . '/ofc_bar_filled.php';
include_once dirname(__FILE__) . '/ofc_bar_stack.php';
//include_once dirname(__FILE__) . '/ofc_bar_3d.php';
include_once dirname(__FILE__) . '/ofc_hbar.php';
include_once dirname(__FILE__) . '/ofc_line_base.php';
include_once dirname(__FILE__) . '/ofc_line.php';
//include_once dirname(__FILE__) . '/ofc_line_dot.php';
//include_once dirname(__FILE__) . '/ofc_line_hollow.php';
include_once dirname(__FILE__) . '/ofc_candle.php';
include_once dirname(__FILE__) . '/ofc_area_base.php';
include_once dirname(__FILE__) . '/ofc_tags.php';
include_once dirname(__FILE__) . '/ofc_arrow.php';
//include_once dirname(__FILE__) . '/ofc_area_hollow.php';
//include_once dirname(__FILE__) . '/ofc_area_line.php';

include_once dirname(__FILE__) . '/ofc_x_legend.php';
include_once dirname(__FILE__) . '/ofc_y_legend.php';
include_once dirname(__FILE__) . '/ofc_bar_sketch.php';
include_once dirname(__FILE__) . '/ofc_scatter.php';
include_once dirname(__FILE__) . '/ofc_scatter_line.php';
include_once dirname(__FILE__) . '/ofc_x_axis_labels.php';
include_once dirname(__FILE__) . '/ofc_x_axis_label.php';
include_once dirname(__FILE__) . '/ofc_tooltip.php';
include_once dirname(__FILE__) . '/ofc_shape.php';
include_once dirname(__FILE__) . '/ofc_radar_axis.php';
include_once dirname(__FILE__) . '/ofc_radar_axis_labels.php';
include_once dirname(__FILE__) . '/ofc_radar_spoke_labels.php';
include_once dirname(__FILE__) . '/ofc_line_style.php';

include_once dirname(__FILE__) . '/dot_base.php';
include_once dirname(__FILE__) . '/ofc_menu.php';

class open_flash_chart
{
	function open_flash_chart()
	{
		//$this->title = new title( "Many data lines" );
		$this->elements = array();
	}
	
	function set_title( $t )
	{
		$this->title = $t;
	}
	
	function set_x_axis( $x )
	{
		$this->x_axis = $x;	
	}
	
	function set_y_axis( $y )
	{
		$this->y_axis = $y;
	}
	
	function add_y_axis( $y )
	{
		$this->y_axis = $y;
	}

	function set_y_axis_right( $y )
	{
		$this->y_axis_right = $y;
	}
	
	function add_element( $e )
	{
		$this->elements[] = $e;
	}
	
	function set_x_legend( $x )
	{
		$this->x_legend = $x;
	}

	function set_y_legend( $y )
	{
		$this->y_legend = $y;
	}
	
	function set_bg_colour( $colour )
	{
		$this->bg_colour = $colour;	
	}
	
	function set_radar_axis( $radar )
	{
		$this->radar_axis = $radar;
	}
	
	function set_tooltip( $tooltip )
	{
		$this->tooltip = $tooltip;	
	}
	
	/**
	 * This is a bit funky :(
	 *
	 * @param $num_decimals as integer. Truncate the decimals to $num_decimals, e.g. set it
	 * to 5 and 3.333333333 will display as 3.33333. 2.0 will display as 2 (or 2.00000 - see below)
	 * @param $is_fixed_num_decimals_forced as boolean. If true it will pad the decimals.
	 * @param $is_decimal_separator_comma as boolean
	 * @param $is_thousand_separator_disabled as boolean
	 *
	 * This needs a bit of love and attention
	 */
	function set_number_format($num_decimals, $is_fixed_num_decimals_forced, $is_decimal_separator_comma, $is_thousand_separator_disabled )
	{
		$this->num_decimals = $num_decimals;
		$this->is_fixed_num_decimals_forced = $is_fixed_num_decimals_forced;
		$this->is_decimal_separator_comma = $is_decimal_separator_comma;
		$this->is_thousand_separator_disabled = $is_thousand_separator_disabled;
	}
	
	/**
	 * This is experimental and will change as we make it work
	 * 
	 * @param $m as ofc_menu
	 */
	function set_menu($m)
	{
		$this->menu = $m;
	}
	
	function toString()
	{
		if (function_exists('json_encode'))
		{
			return json_encode($this);
		}
		else
		{
			$json = new Services_JSON();
			return $json->encode( $this );
		}
	}
	
	function toPrettyString()
	{
		return json_format( $this->toString() );
	}
}



//
// there is no PHP end tag so we don't mess the headers up!
//
