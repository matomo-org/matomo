<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: OpenFlashChart.php 566 2008-07-21 00:34:43Z matt $
 *
 * @package Piwik_Visualization
 * @subpackage OFC
 */

require_once "iView.php";


/**
 * Original class provided by Open Flash Chart
 *
 * @package Piwik_Visualization
 */
abstract class Piwik_Visualization_OpenFlashChart implements Piwik_iView
{
	function __construct()
	{

		$this->data_sets = array();
		
		
		$this->data = array();
		$this->links = array();
		$this->width = 250;
		$this->height = 200;
		$this->js_path = 'js/';
		$this->swf_path = '';
		$this->x_labels = array();
		$this->y_min = '';
		$this->y_max = '';
		$this->x_min = '';
		$this->x_max = '';
		$this->y_steps = '';
		$this->title = '';
		$this->title_style = '';
		$this->occurence = 0;
		
		$this->x_offset = '';

		$this->x_tick_size = -1;

		$this->y2_max = '';
		$this->y2_min = '';

		// GRID styles:
		$this->x_axis_colour = '';
		$this->x_axis_3d = '';
		$this->x_grid_colour = '';
		$this->x_axis_steps = 1;
		$this->y_axis_colour = '';
		$this->y_grid_colour = '';
		$this->y2_axis_colour = '';
		
		// AXIS LABEL styles:         
		$this->x_label_style = '';
		$this->y_label_style = '';
		$this->y_label_style_right = '';
	
	
		// AXIS LEGEND styles:
		$this->x_legend = '';
		$this->x_legend_size = 20;
		$this->x_legend_colour = '#000000';
	
		$this->y_legend = '';
		$this->y_legend_right = '';
		//$this->y_legend_size = 20;
		//$this->y_legend_colour = '#000000';
	
		$this->lines = array();
		$this->line_default['type'] = 'line';
		$this->line_default['values'] = '3,#87421F';
		$this->js_line_default = 'so.addVariable("line","3,#87421F");';
		
		$this->bg_colour = '';
		$this->bg_image = '';
	
		$this->inner_bg_colour = '';
		$this->inner_bg_colour_2 = '';
		$this->inner_bg_angle = '';
		
		// PIE chart ------------
		$this->pie = '';
		$this->pie_values = '';
		$this->pie_colours = '';
		$this->pie_labels = '';
		
		$this->tool_tip = '';
		
		// which data lines are attached to the
		// right Y axis?
		$this->y2_lines = array();
		
		// Number formatting:
		$this->y_format='';
		$this->num_decimals='';
		$this->is_fixed_num_decimals_forced='';
		$this->is_decimal_separator_comma='';
		$this->is_thousand_separator_disabled='';
		
		$this->output_type = '';
		
		//
		// set some default value incase the user forgets
		// to set them, so at least they see *something*
		// even is it is only the axis and some ticks
		//
		$this->set_y_min( 0 );
		$this->set_y_max( 20 );
		$this->set_x_axis_steps( 1 );
		$this->y_label_steps( 5 );
	}

	/**
	* Set the unique_id to use for the flash object id.
	*/
	function set_unique_id()
	{
		$this->unique_id = uniqid(rand(), true);
	}
	
	/**
	* Get the flash object ID for the last rendered object.
	*/
	function get_unique_id()
	{
		return ($this->unique_id);
	}
	
	/**
	* Set the base path for the swfobject.js
	*
	* @param base_path a string argument.
	*   The path to the swfobject.js file
	*/
	function set_js_path($path)
	{
		$this->js_path = $path;
	}
	
	/**
	* Set the base path for the open-flash-chart.swf
	*
	* @param path a string argument.
	*   The path to the open-flash-chart.swf file
	*/
	function set_swf_path($path)
	{
		$this->swf_path = $path;
	}

	/**
	* Set the type of output data.
	*
	* @param type a string argument.
	*   The type of data.  Currently only type is js, or nothing.
	*/
	function set_output_type($type)
	{
		$this->output_type = $type;
	}

	/**
	* returns the next line label for multiple lines.
	*/
	function next_line()
	{
		$line_num = '';
		if( count( $this->lines ) > 0 )
			$line_num = '_'. (count( $this->lines )+1);

		return $line_num;
	}
	
	// escape commas (,)
	static function esc( $text )
	{
		// we replace the comma so it is not URL escaped
		// if it is, flash just thinks it is a comma
		// which is no good if we are splitting the
		// string on commas.
		$tmp = str_replace( ',', '#comma#', $text );
		//$tmp = utf8_encode( $tmp );
		// now we urlescape all dodgy characters (like & % $ etc..)
		return urlencode( $tmp );
	}

	/**
	* Format the text to the type of output.
	*/
	function format_output($function,$values)
	{
		if($this->output_type == 'js')
		{
			$tmp = 'so.addVariable("'. $function .'","'. $values . '");';
		}
		else
		{
			$tmp = '&'. $function .'='. $values .'&';
		}

		return $tmp;
	}

	/**
	* Set the text and style of the title.
	*
	* @param title a string argument.
	*   The text of the title.
	* @param style a string.
	*   CSS styling of the title.
	*/
	function set_title( $title, $style='' )
	{
		$this->title = $this->esc( $title );
		if( strlen( $style ) > 0 )
			$this->title_style = $style;
	}

	/**
	 * Set the width of the chart.
	 *
	 * @param width an int argument.
	 *   The width of the chart frame.
	 */
	function set_width( $width )
	{
		$this->width = $width;
	}
	
	/**
	 * Set the height of the chart.
	 *
	 * @param height an int argument.
	 *   The height of the chart frame.
	 */
	function set_height( $height )
	{
		$this->height = $height;
	}

	/**
	 * Set the base path of the swfobject.
	 *
	 * @param base a string argument.
	 *   The base path of the swfobject.
	 */
	function set_base( $base='js/' )
	{
		$this->base = $base;
	}
	
	// Number formatting:
	function set_y_format( $val )
	{
		$this->y_format = $val;	
	}
	
	function set_num_decimals( $val )
	{
		$this->num_decimals = $val;
	}
	
	function set_is_fixed_num_decimals_forced( $val )
	{
		$this->is_fixed_num_decimals_forced = $val?'true':'false';
	}
	
	function set_is_decimal_separator_comma( $val )
	{
		$this->is_decimal_separator_comma = $val?'true':'false';
	}
	
	function set_is_thousand_separator_disabled( $val )
	{
		$this->is_thousand_separator_disabled = $val?'true':'false';
	}

	/**
	 * Set the data for the chart
	 * @param a an array argument.
	 *   An array of the data to add to the chart.
	 */
	function set_data( $a )
	{
		$this->data[] = implode(',',$a);
	}
	
	// UGH, these evil functions are making me fell ill
	function set_links( $links )
	{
		// TO DO escape commas:
		$this->links[] = implode(',',$links);
	}
	
	// $val is a boolean
	function set_x_offset( $val )
	{
		$this->x_offset = $val?'true':'false';
	}

	/**
	 * Set the tooltip to be displayed on each chart item.\n
	 * \n
	 * Replaceable tokens that can be used in the string include: \n
	 * #val# - The actual value of whatever the mouse is over. \n
	 * #key# - The key string. \n
	 * \<br>  - New line. \n
	 * #x_label# - The X label string. \n
	 * #x_legend# - The X axis legend text. \n
	 * Default string is: "#x_label#<br>#val#" \n
	 * 
	 * @param tip a string argument.
	 *   A formatted string to show as the tooltip.
	 */
	function set_tool_tip( $tip )
	{
		$this->tool_tip = $this->esc( $tip );
	}

	/**
	 * Set the x axis labels
	 *
	 * @param a an array argument.
	 *   An array of the x axis labels.
	 */
	function set_x_labels( $a )
	{
		$tmp = array();
		foreach( $a as $item )
			$tmp[] = $this->esc( $item );
		$this->x_labels = $tmp;
	}

	/**
	 * Set the look and feel of the x axis labels
	 *
	 * @param font_size an int argument.
	 *   The font size.
	 * @param colour a string argument.
	 *   The hex colour value.
	 * @param orientation an int argument.
	 *   The orientation of the x-axis text.
	 *   0 - Horizontal
	 *   1 - Vertical
	 *   2 - 45 degrees
	 * @param step an int argument.
	 *   Show the label on every $step label.
	 * @param grid_colour a string argument.
	 */
	function set_x_label_style( $size, $colour='', $orientation=0, $step=-1, $grid_colour='' )
	{
		$this->x_label_style = $size;
		
		if( strlen( $colour ) > 0 )
			$this->x_label_style .= ','. $colour;

		if( $orientation > -1 )
			$this->x_label_style .= ','. $orientation;

		if( $step > 0 )
			$this->x_label_style .= ','. $step;

		if( strlen( $grid_colour ) > 0 )
			$this->x_label_style .= ','. $grid_colour;
	}

	/**
	 * Set the background colour.
	 * @param colour a string argument.
	 *   The hex colour value.
	 */
	function set_bg_colour( $colour )
	{
		$this->bg_colour = $colour;
	}

	/**
	 * Set a background image.
	 * @param url a string argument.
	 *   The location of the image.
	 * @param x a string argument.
	 *   The x location of the image. 'Right', 'Left', 'Center'
	 * @param y a string argument.
	 *   The y location of the image. 'Top', 'Bottom', 'Middle'
	 */
	function set_bg_image( $url, $x='center', $y='center' )
	{
		$this->bg_image = $url;
		$this->bg_image_x = $x;
		$this->bg_image_y = $y;
	}

	/**
	 * Attach a set of data (a line, area or bar chart) to the right Y axis.
	 * @param data_number an int argument.
	 *   The numbered order the data was attached using set_data.
	 */
	function attach_to_y_right_axis( $data_number )
	{
		$this->y2_lines[] = $data_number;
	}

	/**
 	 * Set the background colour of the grid portion of the chart.
	 * @param col a string argument.
	 *   The hex colour value of the background.
	 * @param col2 a string argument.
	 *   The hex colour value of the second colour if you want a gradient.
	 * @param angle an int argument.
	 *   The angle in degrees to make the gradient.
	 */
	function set_inner_background( $col, $col2='', $angle=-1 )
	{
		$this->inner_bg_colour = $col;
		
		if( strlen($col2) > 0 )
			$this->inner_bg_colour_2 = $col2;
		
		if( $angle != -1 )
			$this->inner_bg_angle = $angle;
	}

	/**
	 * Internal function to build the y label style for y and y2
	 */
	function _set_y_label_style( $size, $colour )
	{
		$tmp = $size;
		
		if( strlen( $colour ) > 0 )
			$tmp .= ','. $colour;
		return $tmp;
	}

	/**
	 * Set the look and feel of the y axis labels
	 *
	 * @param font_size an int argument.
	 *   The font size.
	 * @param colour a string argument.
	 *   The hex colour value.
	 */
	function set_y_label_style( $size, $colour='' )
	{
		$this->y_label_style = $this->_set_y_label_style( $size, $colour );
	}

	/**
	 * Set the look and feel of the right y axis labels
	 *
	 * @param font_size an int argument.
	 *   The font size.
	 * @param colour a string argument.
	 *   The hex colour value.
	 */
	function set_y_right_label_style( $size, $colour='' )
	{
		$this->y_label_style_right = $this->_set_y_label_style( $size, $colour );
	}

	function set_x_max( $max )
	{
		$this->x_max = floatval( $max );
	}

	function set_x_min( $min )
	{
		$this->x_min = floatval( $min );
	}

	/**
	 * Set the maximum value of the y axis.
	 *
	 * @param max an float argument.
	 *   The maximum value.
	 */
	function set_y_max( $max )
	{
		$this->y_max = floatval( $max );
	}

	/**
	 * Set the minimum value of the y axis.
	 *
	 * @param min an float argument.
	 *   The minimum value.
	 */
	function set_y_min( $min )
	{
		$this->y_min = floatval( $min );
	}

	/**
	 * Set the maximum value of the right y axis.
	 *
	 * @param max an float argument.
	 *   The maximum value.
	 */  
	function set_y_right_max( $max )
	{
		$this->y2_max = floatval($max);
	}

	/**
	 * Set the minimum value of the right y axis.
	 *
	 * @param min an float argument.
	 *   The minimum value.
	 */
	function set_y_right_min( $min )
	{
		$this->y2_min = floatval($min);
	}

	/**
	 * Show the y label on every $step label.
	 *
	 * @param val an int argument.
	 *   Show the label on every $step label.
	 */
	function y_label_steps( $val )
	{
		 $this->y_steps = intval( $val );
	}
	
	function title( $title, $style='' )
	{
		 $this->title = $this->esc( $title );
		 if( strlen( $style ) > 0 )
				 $this->title_style = $style;
	}

	/**
	 * Set the parameters of the x legend.
	 *
	 * @param text a string argument.
	 *   The text of the x legend.
	 * @param font_size an int argument.
	 *   The font size of the x legend text.
	 * @param colour a string argument
	 *   The hex value of the font colour. 
	 */
	function set_x_legend( $text, $size=-1, $colour='' )
	{
		$this->x_legend = $this->esc( $text );
		if( $size > -1 )
			$this->x_legend_size = $size;
		
		if( strlen( $colour )>0 )
			$this->x_legend_colour = $colour;
	}

	/**
	 * Set the size of the x label ticks.
	 *
	 * @param size an int argument.
	 *   The size of the ticks in pixels.
	 */
	function set_x_tick_size( $size )
	{
		if( $size > 0 )
				$this->x_tick_size = $size;
	}

	/**
	 * Set how often you would like to show a tick on the x axis.
	 *
	 * @param steps an int argument.
	 *   Show a tick ever $steps.
	 */
	function set_x_axis_steps( $steps )
	{
		if ( $steps > 0 )
			$this->x_axis_steps = $steps;
	}

	/**
	 * Set the depth in pixels of the 3D X axis slab.
	 *
	 * @param size an int argument.
	 *   The depth in pixels of the 3D X axis.
	 */
	function set_x_axis_3d( $size )
	{
		if( $size > 0 )
			$this->x_axis_3d = intval($size);
	}
	
	/**
	 * The private method of building the y legend output.
	 */
	function _set_y_legend( $text, $size, $colour )
	{
		$tmp = $text;
	
		if( $size > -1 )
			$tmp .= ','. $size;

		if( strlen( $colour )>0 )
			$tmp .= ','. $colour;

		return $tmp;
		}

	/**
	 * Set the parameters of the y legend.
	 *
	 * @param text a string argument.
	 *   The text of the y legend.
	 * @param font_size an int argument.
	 *   The font size of the y legend text.
	 * @param colour a string argument
	 *   The hex colour value of the font colour. 
	 */
	function set_y_legend( $text, $size=-1, $colour='' )
	{
		$this->y_legend = $this->_set_y_legend( $text, $size, $colour );
	}

	/**
	 * Set the parameters of the right y legend.
	 *
	 * @param text a string argument.
	 *   The text of the right y legend.
	 * @param font_size an int argument.
	 *   The font size of the right y legend text.
	 * @param colour a string argument
	 *   The hex value of the font colour. 
	 */
	function set_y_right_legend( $text, $size=-1, $colour='' )
	{
		$this->y_legend_right = $this->_set_y_legend( $text, $size, $colour );
	}
	
	/**
	 * Set the colour of the x axis line and grid.
	 *
	 * @param axis a string argument.
	 *   The hex colour value of the x axis line.
	 * @param grid a string argument.
	 *   The hex colour value of the x axis grid. 
	 */
	function x_axis_colour( $axis, $grid='' )
	{
		$this->x_axis_colour = $axis;
		$this->x_grid_colour = $grid;
	}

	/**
	 * Set the colour of the y axis line and grid.
	 *
	 * @param axis a string argument.
	 *   The hex colour value of the y axis line.
	 * @param grid a string argument.
	 *   The hex colour value of the y axis grid. 
	 */
	function y_axis_colour( $axis, $grid='' )
	{
		$this->y_axis_colour = $axis;

		if( strlen( $grid ) > 0 )
			$this->y_grid_colour = $grid;
	}

	/**
	 * Set the colour of the right y axis line.
	 *
	 * @param colour a string argument.
	 *   The hex colour value of the right y axis line.
	 */
	function y_right_axis_colour( $colour )
	{
		 $this->y2_axis_colour = $colour;
	}

	/**
	 * Draw a line without markers on values.
	 *
	 * @param width an int argument.
	 *   The width of the line in pixels.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label
	 * @param circles an int argument
	 *   Need to find out.
	 */
	function line( $width, $colour='', $text='', $size=-1, $circles=-1 )
	{
		$type = 'line'. $this->next_line();

		$description = '';
		if( $width > 0 )
		{
			$description .= $width;
			$description .= ','. $colour;
		}

		if( strlen( $text ) > 0 )
		{
			$description.= ','. $text;
			$description .= ','. $size;
		}

		if( $circles > 0 ) 
			$description .= ','. $circles;

		$this->lines[$type] = $description;
	}

	/**
	 * Draw a line with solid dot markers on values.
	 *
	 * @param width an int argument.
	 *   The width of the line in pixels.
	 * @param dot_size an int argument.
	 *   Size in pixels of the dot.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label.
	 */
	function line_dot( $width, $dot_size, $colour, $text='', $font_size='' )
	{
		$type = 'line_dot'. $this->next_line();

		$description = "$width,$colour,$text";

		if( strlen( $font_size ) > 0 )
			$description .= ",$font_size,$dot_size";

		$this->lines[$type] = $description;
	}

	/**
	 * Draw a line with hollow dot markers on values.
	 *
	 * @param width an int argument.
	 *   The width of the line in pixels.
	 * @param dot_size an int argument.
	 *   Size in pixels of the dot.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label.
	 */
	function line_hollow( $width, $dot_size, $colour, $text='', $font_size='' )
	{
		$type = 'line_hollow'. $this->next_line();

		$description = "$width,$colour,$text";

		if( strlen( $font_size ) > 0 )
			$description .= ",$font_size,$dot_size";

		$this->lines[$type] = $description;
	}

	/**
	 * Draw an area chart.
	 *
	 * @param width an int argument.
	 *   The width of the line in pixels.
	 * @param dot_size an int argument.
	 *   Size in pixels of the dot.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param alpha an int argument.
	 *   The percentage of transparency of the fill colour.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label.
	 * @param fill_colour a string argument.
	 *   The hex colour value of the fill colour.
	 */
	function area_hollow( $width, $dot_size, $colour, $alpha, $text='', $font_size='', $fill_colour='' )
	{
		$type = 'area_hollow'. $this->next_line();

		$description = "$width,$dot_size,$colour,$alpha";

		if( strlen( $text ) > 0 )
			$description .= ",$text,$font_size";
	
		if( strlen( $fill_colour ) > 0 )
			$description .= ','. $fill_colour;

		$this->lines[$type] = $description;
	}

	/**
	 * Draw a bar chart.
	 *
	 * @param alpha an int argument.
	 *   The percentage of transparency of the bar colour.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label.
	 */
	function bar( $alpha, $colour='', $text='', $size=-1 )
	{
		$type = 'bar'. $this->next_line();

		$description = $alpha .','. $colour .','. $text .','. $size;

		$this->lines[$type] = $description;
	}

	/**
	 * Draw a bar chart with an outline.
	 *
	 * @param alpha an int argument.
	 *   The percentage of transparency of the bar colour.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param colour_outline a strng argument.
	 *   The hex colour value of the outline.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label.
	 */
	function bar_filled( $alpha, $colour, $colour_outline, $text='', $size=-1 )
	{
		$type = 'filled_bar'. $this->next_line();

		$description = "$alpha,$colour,$colour_outline,$text,$size";

		$this->lines[$type] = $description;
	}

	function bar_sketch( $alpha, $offset, $colour, $colour_outline, $text='', $size=-1 )
	{
		$type = 'bar_sketch'. $this->next_line();

		$description = "$alpha,$offset,$colour,$colour_outline,$text,$size";

		$this->lines[$type] = $description;
	}

	/**
	 * Draw a 3D bar chart.
	 *
	 * @param alpha an int argument.
	 *   The percentage of transparency of the bar colour.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label.
	 */
	function bar_3D( $alpha, $colour='', $text='', $size=-1 )
	{
		$type = 'bar_3d'. $this->next_line();

		$description = $alpha .','. $colour .','. $text .','. $size;

		$this->lines[$type] = $description;
	}

	/**
	 * Draw a 3D bar chart that looks like glass.
	 *
	 * @param alpha an int argument.
	 *   The percentage of transparency of the bar colour.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param outline_colour a string argument.
	 *   The hex colour value of the outline.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label.
	 */
	function bar_glass( $alpha, $colour, $outline_colour, $text='', $size=-1 )
	{
		$type = 'bar_glass'. $this->next_line();

		$description = $alpha .','. $colour .','. $outline_colour .','. $text .','. $size;

		$this->lines[$type] = $description;
	}

	/**
	 * Draw a faded bar chart.
	 *
	 * @param alpha an int argument.
	 *   The percentage of transparency of the bar colour.
	 * @param colour a string argument.
	 *   The hex colour value of the line.
	 * @param text a string argument.
	 *   The label of the line.
	 * @param font_size an int argument.
	 *   Font size of the label.
	 */
	function bar_fade( $alpha, $colour='', $text='', $size=-1 )
	{
		$type = 'bar_fade'. $this->next_line();

		$description = $alpha .','. $colour .','. $text .','. $size;

		$this->lines[$type] = $description;
	}
	
	function candle( $data, $alpha, $line_width, $colour, $text='', $size=-1 )
	{
		$type = 'candle'. $this->next_line();

		$description = $alpha .','. $line_width .','. $colour .','. $text .','. $size;

		$this->lines[$type] = $description;
		
		$a = array();
		foreach( $data as $can )
			$a[] = $can->toString();
			
		$this->data[] = implode(',',$a);
	}
	
	function hlc( $data, $alpha, $line_width, $colour, $text='', $size=-1 )
	{
		$type = 'hlc'. $this->next_line();

		$description = $alpha .','. $line_width .','. $colour .','. $text .','. $size;

		$this->lines[$type] = $description;
		
		$a = array();
		foreach( $data as $can )
			$a[] = $can->toString();
			
		$this->data[] = implode(',',$a);
	}

	function scatter( $data, $line_width, $colour, $text='', $size=-1 )
	{
		$type = 'scatter'. $this->next_line();

		$description = $line_width .','. $colour .','. $text .','. $size;

		$this->lines[$type] = $description;
		
		$a = array();
		foreach( $data as $can )
			$a[] = $can->toString();
			
		$this->data[] = implode(',',$a);
	}


	//
	// Patch by, Jeremy Miller (14th Nov, 2007)
	//
	/**
	 * Draw a pie chart.
	 *
	 * @param alpha an int argument.
	 *   The percentage of transparency of the pie colour.
	 * @param $style a string argument.
	 *   CSS style string
	 * @param label_colour a string argument.
	 *   The hex colour value of the label.
	 * @param gradient a boolean argument.
	 *   Use a gradient true or false.
	 * @param border_size an int argument.
	 *   Size of the border in pixels.
	 */
	function pie( $alpha, $line_colour, $style, $gradient = true, $border_size = false )
	{
		$this->pie = $alpha.','.$line_colour.','.$style;
		if( !$gradient )
		{
			$this->pie .= ','.!$gradient;
		}
		if ($border_size)
		{
			if ($gradient === false)
			{
				$this->pie .= ',';
			}
			$this->pie .= ','.$border_size;
		}
	}

	/**
	 * Set the values of the pie chart.
	 *
	 * @param values an array argument.
	 *   An array of the values for the pie chart.
	 * @param labels an array argument.
	 *   An array of the labels for the pie pieces.
	 * @param links an array argument.
	 *   An array of the links to the pie pieces.
	 */	
	function pie_values( $values, $labels=array(), $links=array() )
	{
		$this->pie_values = implode(',',$values);
		$this->pie_labels = implode(',',$labels);
		$this->pie_links  = implode(",",$links);
	}

	/**
	 * Set the pie slice colours.
	 *
	 * @param colours an array argument.
	 *   The hex colour values of the pie pieces.
	 */
	function pie_slice_colours( $colours )
	{
		$this->pie_colours = implode(',',$colours);
	}
	

	/**
	 * Render the output.
	 */
	function render()
	{
		$tmp = array();
		
		//echo headers_sent() ?'yes':'no';
		if( !headers_sent() )
			header('content-type: text; charset: utf-8');

		if($this->output_type == 'js')
		{
			$this->set_unique_id();
		
			$tmp[] = '<div id="' . $this->unique_id . '"></div>';
			$tmp[] = '<script type="text/javascript" src="' . $this->js_path . 'swfobject.js"></script>';
			$tmp[] = '<script type="text/javascript">';
			$tmp[] = 'var so = new SWFObject("' . $this->swf_path . 'open-flash-chart.swf", "ofc", "'. $this->width . '", "' . $this->height . '", "9", "#FFFFFF");';
			$tmp[] = 'so.addVariable("variables","true");';
		}

		if( strlen( $this->title ) > 0 )
		{
			$values = $this->title;
			$values .= ','. $this->title_style;
			$tmp[] = $this->format_output('title',$values);
		}

		if( strlen( $this->x_legend ) > 0 )
		{
			$values = $this->x_legend;
			$values .= ','. $this->x_legend_size;
			$values .= ','. $this->x_legend_colour;
			$tmp[] = $this->format_output('x_legend',$values);
		}
	
		if( strlen( $this->x_label_style ) > 0 )
			$tmp[] = $this->format_output('x_label_style',$this->x_label_style);
	
		if( $this->x_tick_size > 0 )
			$tmp[] = $this->format_output('x_ticks',$this->x_tick_size);

		if( $this->x_axis_steps > 0 )
			$tmp[] = $this->format_output('x_axis_steps',$this->x_axis_steps);

		if( strlen( $this->x_axis_3d ) > 0 )
			$tmp[] = $this->format_output('x_axis_3d',$this->x_axis_3d);
		
		if( strlen( $this->y_legend ) > 0 )
			$tmp[] = $this->format_output('y_legend',$this->y_legend);
		
		if( strlen( $this->y_legend_right ) > 0 )
			$tmp[] = $this->format_output('y2_legend',$this->y_legend_right);

		if( strlen( $this->y_label_style ) > 0 )
			$tmp[] = $this->format_output('y_label_style',$this->y_label_style);

		$values = '5,10,'. $this->y_steps;
		$tmp[] = $this->format_output('y_ticks',$values);

		if( count( $this->lines ) == 0 && count($this->data_sets)==0 )
		{
			$tmp[] = $this->format_output($this->line_default['type'],$this->line_default['values']);	
		}
		else
		{
			foreach( $this->lines as $type=>$description )
				$tmp[] = $this->format_output($type,$description);	
		}
	
		$num = 1;
		foreach( $this->data as $data )
		{
			if( $num==1 )
			{
				$tmp[] = $this->format_output( 'values', $data);
			}
			else
			{
				$tmp[] = $this->format_output('values_'. $num, $data);
			}
		
			$num++;
		}
		
		$num = 1;
		foreach( $this->links as $link )
		{
			if( $num==1 )
			{
				$tmp[] = $this->format_output( 'links', $link);
			}
			else
			{
				$tmp[] = $this->format_output('links_'. $num, $link);
			}
		
			$num++;
		}

		if( count( $this->y2_lines ) > 0 )
		{
			$tmp[] = $this->format_output('y2_lines',implode( ',', $this->y2_lines ));
			//
			// Should this be an option? I think so...
			//
			$tmp[] = $this->format_output('show_y2','true');
		}

		if( count( $this->x_labels ) > 0 )
			$tmp[] = $this->format_output('x_labels',implode(',',$this->x_labels));
		else
		{
			if( strlen($this->x_min) > 0 )
				$tmp[] = $this->format_output('x_min',$this->x_min);
				
			if( strlen($this->x_max) > 0 )
				$tmp[] = $this->format_output('x_max',$this->x_max);			
		}
		
		$tmp[] = $this->format_output('y_min',$this->y_min);
		$tmp[] = $this->format_output('y_max',$this->y_max);

		if( strlen($this->y2_min) > 0 )
			$tmp[] = $this->format_output('y2_min',$this->y2_min);
			
		if( strlen($this->y2_max) > 0 )
			$tmp[] = $this->format_output('y2_max',$this->y2_max);
		
		if( strlen( $this->bg_colour ) > 0 )
			$tmp[] = $this->format_output('bg_colour',$this->bg_colour);

		if( strlen( $this->bg_image ) > 0 )
		{
			$tmp[] = $this->format_output('bg_image',$this->bg_image);
			$tmp[] = $this->format_output('bg_image_x',$this->bg_image_x);
			$tmp[] = $this->format_output('bg_image_y',$this->bg_image_y);
		}

		if( strlen( $this->x_axis_colour ) > 0 )
		{
			$tmp[] = $this->format_output('x_axis_colour',$this->x_axis_colour);
			$tmp[] = $this->format_output('x_grid_colour',$this->x_grid_colour);
		}

		if( strlen( $this->y_axis_colour ) > 0 )
			$tmp[] = $this->format_output('y_axis_colour',$this->y_axis_colour);

		if( strlen( $this->y_grid_colour ) > 0 )
			$tmp[] = $this->format_output('y_grid_colour',$this->y_grid_colour);
  
		if( strlen( $this->y2_axis_colour ) > 0 )
			$tmp[] = $this->format_output('y2_axis_colour',$this->y2_axis_colour);
		
		if( strlen( $this->x_offset ) > 0 )
			$tmp[] = $this->format_output('x_offset',$this->x_offset);

		if( strlen( $this->inner_bg_colour ) > 0 )
		{
			$values = $this->inner_bg_colour;
			if( strlen( $this->inner_bg_colour_2 ) > 0 )
			{
				$values .= ','. $this->inner_bg_colour_2;
				$values .= ','. $this->inner_bg_angle;
			}
			$tmp[] = $this->format_output('inner_background',$values);
		}
	
		if( strlen( $this->pie ) > 0 )
		{
			$tmp[] = $this->format_output('pie',$this->pie);
			$tmp[] = $this->format_output('values',$this->pie_values);
			$tmp[] = $this->format_output('pie_labels',$this->pie_labels);
			$tmp[] = $this->format_output('colours',$this->pie_colours);
			$tmp[] = $this->format_output('links',$this->pie_links);
		}

		if( strlen( $this->tool_tip ) > 0 )
			$tmp[] = $this->format_output('tool_tip',$this->tool_tip);
			
			
		
		if( strlen( $this->y_format ) > 0 )
			$tmp[] = $this->format_output('y_format',$this->y_format);
			
		if( strlen( $this->num_decimals ) > 0 )
			$tmp[] = $this->format_output('num_decimals',$this->num_decimals);
			
		if( strlen( $this->is_fixed_num_decimals_forced ) > 0 )
			$tmp[] = $this->format_output('is_fixed_num_decimals_forced',$this->is_fixed_num_decimals_forced);
			
		if( strlen( $this->is_decimal_separator_comma ) > 0 )
			$tmp[] = $this->format_output('is_decimal_separator_comma',$this->is_decimal_separator_comma);
			
		if( strlen( $this->is_thousand_separator_disabled ) > 0 )
			$tmp[] = $this->format_output('is_thousand_separator_disabled',$this->is_thousand_separator_disabled);


		$count = 1;
		foreach( $this->data_sets as $set )
		{
			$tmp[] = $set->toString( $this->output_type, $count>1?'_'.$count:'' );
			$count++;
		}
		
		if($this->output_type == 'js')
		{
			$tmp[] = 'so.write("' . $this->unique_id . '");';
			$tmp[] = '</script>';
		}
		
		return implode("\r\n",$tmp);
	}
}

class line
{
	var $line_width;
	var $colour;
	var $_key;
	var $key;
	var $key_size;
	// hold the data
	var $data;
	// extra tool tip info:
	var $tips;
	
	function line( $line_width, $colour )
	{
		$this->var = 'line';
		
		$this->line_width = $line_width;
		$this->colour = $colour;
		$this->data = array();
		$this->links = array();
		$this->tips = array();
		$this->_key = false;
	}


	function key( $key, $size )
	{
		$this->_key = true;
		$this->key = graph::esc( $key );
		$this->key_size = $size;
	}
	
	function add( $data )
	{
		$this->data[] = $data;
	}
	
	function add_link( $data, $link )
	{
		$this->data[] = $data;
		$this->links[] = graph::esc( $link );
	}
	
	function add_data_tip( $data, $tip )
	{
		$this->data[] = $data;
		$this->tips[] = graph::esc( $tip );
	}
	
	function add_data_link_tip( $data, $link, $tip )
	{
		$this->data[] = $data;
		$this->links[] = graph::esc( $link );
		$this->tips[] = graph::esc( $tip );
	}
	
	// return the variables for this chart
	function _get_variable_list()
	{
		$values = array();
		$values[] = $this->line_width;
		$values[] = $this->colour;
		
		if( $this->_key )
		{
			$values[] = $this->key;
			$values[] = $this->key_size;
		}
		
		return $values;
	}
	
	function toString( $output_type, $set_num )
	{
		$values = implode( ',', $this->_get_variable_list() );
		
		$tmp = array();
		
		if( $output_type == 'js' )
		{
			$tmp[] = 'so.addVariable("'. $this->var.$set_num .'","'. $values . '");'; 

			$tmp[] = 'so.addVariable("values'. $set_num .'","'. implode( ',', $this->data ) .'");';
			
			if( count( $this->links ) > 0 )
				$tmp[] = 'so.addVariable("links'. $set_num .'","'. implode( ',', $this->links ) .'");';
				
			if( count( $this->tips ) > 0 )
				$tmp[] = 'so.addVariable("tool_tips_set'. $set_num .'","'. implode( ',', $this->tips ) .'");';

		}
		else
		{
			$tmp[]  = '&'. $this->var. $set_num .'='. $values .'&';
			$tmp[] = '&values'. $set_num .'='. implode( ',', $this->data ) .'&';
			
			if( count( $this->links ) > 0 )
				$tmp[] = '&links'. $set_num .'='. implode( ',', $this->links ) .'&';
				
			if( count( $this->tips ) > 0 )
				$tmp[] = '&tool_tips_set'. $set_num .'='. implode( ',', $this->tips ) .'&';	
		}

		return implode( "\r\n", $tmp );
	}
}

class line_hollow extends line
{
	var $dot_size;
	
	function line_hollow( $line_width, $dot_size, $colour )
	{
		parent::line( $line_width, $colour );
		$this->var = 'line_hollow';
		$this->dot_size = $dot_size;
	}
	
	// return the variables for this chart
	function _get_variable_list()
	{
		$values = array();
		$values[] = $this->line_width;
		$values[] = $this->colour;
		
		if( $this->_key )
		{
			$values[] = $this->key;
			$values[] = $this->key_size;
		}
		else
		{
			$values[] = '';
			$values[] = '';
		}
		$values[] = $this->dot_size;
		
		return $values;
	}
}

class line_dot extends line_hollow
{
	function line_dot( $line_width, $dot_size, $colour )
	{
		parent::line_hollow( $line_width, $dot_size,$colour );
		$this->var = 'line_dot';
	}
}

class bar
{
	var $colour;
	var $alpha;
	var $data;
	var $links;
	var $_key;
	var $key;
	var $key_size;
	var $var;
	// extra tool tip info:
	var $tips;
	
	function bar( $alpha, $colour )
	{
		$this->var = 'bar';
		
		$this->alpha = $alpha;
		$this->colour = $colour;
		$this->data = array();
		$this->links = array();
		$this->tips = array();
		$this->_key = false;
	}

	function key( $key, $size )
	{
		$this->_key = true;
		$this->key = graph::esc( $key );
		$this->key_size = $size;
	}
	
	function add( $data )
	{
		$this->data[] = $data;
	}

	function add_link( $data, $link )
	{
		$this->data[] = $data;
		$this->links[] = graph::esc( $link );
	}
	
	function add_data_tip( $data, $tip )
	{
		$this->data[] = $data;
		$this->tips[] = graph::esc( $tip );
	}
	
	// return the variables for this
	// bar chart
	function _get_variable_list()
	{
		$values = array();
		$values[] = $this->alpha;
		$values[] = $this->colour;
		
		if( $this->_key )
		{
			$values[] = $this->key;
			$values[] = $this->key_size;
		}
		
		return $values;
	}
	
	function toString( $output_type, $set_num )
	{
		$values = implode( ',', $this->_get_variable_list() );
		
		$tmp = array();
		
		if( $output_type == 'js' )
		{
			$tmp[] = 'so.addVariable("'. $this->var.$set_num .'","'. $values . '");'; 

			$tmp[] = 'so.addVariable("values'. $set_num .'","'. implode( ',', $this->data ) .'");';
			
			if( count( $this->links ) > 0 )
				$tmp[] = 'so.addVariable("links'. $set_num .'","'. implode( ',', $this->links ) .'");';
				
			if( count( $this->tips ) > 0 )
				$tmp[] = 'so.addVariable("tool_tips_set'. $set_num .'","'. implode( ',', $this->tips ) .'");';

		}
		else
		{
			$tmp[]  = '&'. $this->var. $set_num .'='. $values .'&';
			$tmp[] = '&values'. $set_num .'='. implode( ',', $this->data ) .'&';
			
			if( count( $this->links ) > 0 )
				$tmp[] = '&links'. $set_num .'='. implode( ',', $this->links ) .'&';
				
			if( count( $this->tips ) > 0 )
				$tmp[] = '&tool_tips_set'. $set_num .'='. implode( ',', $this->tips ) .'&';	
		}

		return implode( "\r\n", $tmp );
	}
	
}

class bar_3d extends bar
{
	function bar_3d( $alpha, $colour )
	{
		parent::bar( $alpha, $colour );
		$this->var = 'bar_3d';
	}
}

class bar_fade extends bar
{
	function bar_fade( $alpha, $colour )
	{
		parent::bar( $alpha, $colour );
		$this->var = 'bar_fade';
	}
}

class bar_outline extends bar
{
	var $outline_colour;
	
	function bar_outline( $alpha, $colour, $outline_colour )
	{
		parent::bar( $alpha, $colour );
		$this->var = 'filled_bar';
		$this->outline_colour = $outline_colour;
	}
	
	// override the base method
	function _get_variable_list()
	{
		$values = array();
		$values[] = $this->alpha;
		$values[] = $this->colour;
		$values[] = $this->outline_colour;
		
		if( $this->_key )
		{
			$values[] = $this->key;
			$values[] = $this->key_size;
		}
		
		return $values;
	}
}

class bar_glass extends bar_outline
{
	function bar_glass( $alpha, $colour, $outline_colour )
	{
		parent::bar_outline( $alpha, $colour, $outline_colour );
		$this->var = 'bar_glass';
	}
}

//
// this has an outline colour and a 'jiggle' parameter
// called offset
//
class bar_sketch extends bar_outline
{
	var $offset;
	
	function bar_sketch( $alpha, $offset, $colour, $outline_colour )
	{
		parent::bar_outline( $alpha, $colour, $outline_colour );
		$this->var = 'bar_sketch';
		$this->offset = $offset;
	}
	
	// override the base method
	function _get_variable_list()
	{
		$values = array();
		$values[] = $this->alpha;
		$values[] = $this->offset;
		$values[] = $this->colour;
		$values[] = $this->outline_colour;
		
		if( $this->_key )
		{
			$values[] = $this->key;
			$values[] = $this->key_size;
		}
		
		return $values;
	}
}

class candle
{
	var $out;
	
	function candle( $high, $open, $close, $low )
	{
		$this->out = array();
		$this->out[] = $high;
		$this->out[] = $open;
		$this->out[] = $close;
		$this->out[] = $low;
	}
	
	function toString()
	{
		return '['. implode( ',', $this->out ) .']';
	}
}

class hlc
{
	var $out;
	
	function hlc( $high, $low, $close )
	{
		$this->out = array();
		$this->out[] = $high;
		$this->out[] = $low;
		$this->out[] = $close;
	}
	
	function toString()
	{
		return '['. implode( ',', $this->out ) .']';
	}
}

class point
{
	var $out;
	
	function point( $x, $y, $size_px )
	{
		$this->out = array();
		$this->out[] = $x;
		$this->out[] = $y;
		$this->out[] = $size_px;
	}
	
	function toString()
	{
		return '['. implode( ',', $this->out ) .']';
	}
}

// PIWIK SPECIAL ALIAS HACK - when updating Open Flash Chart, leave this line unchanged
class graph extends Piwik_Visualization_OpenFlashChart {}