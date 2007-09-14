
// rectangle with rounded corners
//#include "rrectangle.as"
#include "prototype.drawCircle.as"
#include "prototype.fillCircle.as"
#include "String.prototype.replace.as"

MovieClip.prototype.rect = function( x:Number, y:Number, width:Number, height:Number, colour:Number, alpha:Number )
{

	this.beginFill( colour, 100 );
    this.moveTo( x, y );
    this.lineTo( x+width, y );
    this.lineTo( x+width, y+height );
    this.lineTo( x, y+height );
	this.lineTo( x, y );
    this.endFill();
	
	this._alpha = alpha;
	this._alpha_original = alpha;	// <-- remember our original alpha while tweening
	
}

function get_colour( col:String )
{
	if( col.substr(0,2) == '0x' )
		return Number(col);
		
	if( col.substr(0,1) == '#' )
		return Number( '0x'+col.substr(1,col.length) );
		
	if( col.length=6 )
		return Number( '0x'+col );
		
	// not recognised as a valid colour, so?
	return Number( col );
		
}

// why isn't this built into flash?
// make a number 1000 = 1,000
function format( i:Number )
{
	var s:String = '';
	if( i<0 )
		var num:Array = String(-i).split('.');
	else
		var num:Array = String(i).split('.');
	
	var x:String = num[0];
	var pos:Number=0;
	for(c=x.length-1;c>-1;c--)
	{
		if( pos%3==0 && s.length>0 )
		{
			s=','+s;
			pos=0;
		}
		pos++;
			
		s=x.substr(c,1)+s;
	}
	if( num[1] != undefined )
		s += '.'+ num[1].substr(0,2);
		
	if( i<0 )
		s = '-'+s;
		
	return s;
}

// added by NetVicious June 2007
function setContextualMenu()
{
	var contextual_menu:ContextMenu = new ContextMenu();
	var About:ContextMenuItem = new ContextMenuItem("About Open Flash Chart...");
	About.onSelect = function(obj, item) {
		// Go to project url
        getURL("javascript:popup=window.open('http://teethgrinder.co.uk/open-flash-chart/','ofc', 'toolbar=Yes,location=Yes,scrollbars=Yes,menubar=Yes,status=Yes,resizable=Yes,fullscreen=No'); popup.focus()");
	};
	/*
	If you want to remove default items of Flash (except conf and about) uncomment this line
	contextual_menu.hideBuiltInItems();
	*/
	contextual_menu.customItems.push(About);
	createClassObject
	_root.menu = contextual_menu;
}

function TxtFormat(size:Number,colour:Number)
{
	var fmt:TextFormat = new TextFormat();
	fmt.color = colour;
	fmt.font = "Verdana";
	fmt.size = size;
	fmt.align = "center";
	return fmt;
}



function FadeIn()
{
	this.onEnterFrame = function ()
    {

		_root.show_tip(
			this,
			this._x,
			this._y-20,
			this.tooltip
			);
		
        if( this._alpha < 100 )
        {
            this._alpha += 10;
        }
        else
        {
			this._alpha = 100;
            delete this.onEnterFrame;
        }
    }
}

function FadeOut()
{
	this.onEnterFrame = function ()
    {
			
        if( (this._alpha-5) > this._alpha_original )
        {
            this._alpha -= 5;
        }
        else
        {
			this._alpha = this._alpha_original;
			_root.hide_tip( this );
            delete this.onEnterFrame;
        }
    }
}


function hide_tip( owner:Object )
{
	if( _root.tooltip._owner == owner )
		removeMovieClip("tooltip");
}

function show_tip( owner:Object, x:Number, y:Number, tip_obj:Object )
{
	if( ( _root.tooltip != undefined ) )
	{
		if(_root.tooltip._owner==owner)
			return;	// <-- it's our tooltip and it is showing
		else
			removeMovieClip("tooltip");	// <-- it is someone elses tootlip - remove it
	}
	
	var tmp:String;
	var lines:Array = [];
	//
	// Dirty hack. Takes a tool_tip_wrapper, and replaces the #val# with the
	// tool_tip text, so noew you can do: "My Val = $#val#%", which turns into:
	// "My Val = $12.00%"
	//
	if( _root.tool_tip_wrapper != undefined )
	{
		tmp = _root.tool_tip_wrapper.replace('#val#',tip_obj.value);
		tmp = tmp.replace('#key#',tip_obj.key);
		tmp = tmp.replace('#x_label#',tip_obj.x_label);
		
		if( _root._x_legend != undefined )
			tmp = tmp.replace('#x_legend#',_root._x_legend.get_legend());
	}
	else
	{
		if( tip_obj.x_label == undefined )
			tmp = tip_obj.value;
		else
			tmp = tip_obj.x_label+'<br>'+tip_obj.value;
	}
		
	lines = tmp.split( '<br>' );
	
	var tooltip = _root.createEmptyMovieClip( "tooltip", this.getNextHighestDepth() );
		
	// let the tooltip know who owns it, else we get weird race conditions where one
	// bar has onRollOver fired, then another has onRollOut and deletes the tooltip
	tooltip._owner = owner;

	tooltip.createTextField( "txt_title", tooltip.getNextHighestDepth(), 5, 5, 100, 100);
	if( lines.length > 1 )
		tooltip.txt_title.text = lines.shift();

	var fmt:TextFormat = new TextFormat();
	fmt.color = 0x0000F0;
	fmt.font = "Verdana";
	
	// this needs to be an option:
	fmt.bold = true;
	fmt.size = 12;
	fmt.align = "right";
	tooltip.txt_title.setTextFormat(fmt);
	tooltip.txt_title.autoSize="left";
	
	tooltip.createTextField( "txt", tooltip.getNextHighestDepth(), 5, tooltip.txt_title._height, 100, 100);
	
	tooltip.txt.text = lines.join( '\n' );
	
	var fmt2:TextFormat = new TextFormat();
	fmt2.color = 0x000000;
	fmt2.font = "Verdana";
	fmt2.size = 12;
	fmt2.align = "left";
	tooltip.txt.setTextFormat(fmt2);
	tooltip.txt.autoSize="left";

	var max_width:Number = Math.max( tooltip.txt_title._width, tooltip.txt._width );
	var y_pos:Number = y - tooltip.txt_title._height - tooltip.txt._height;
	
	if( y_pos < 0 )
	{
		// the tooltip has drifted off the top of the screen, move it down:
		y_pos = y + tooltip.txt_title._height + tooltip.txt._height;
	}
	
	var cstroke = {width:2, color:0x808080, alpha:100};
	var ccolor = {color:0xf0f0f0, alpha:100};

	ChartUtil.rrectangle(
		tooltip,
		max_width+10,
		tooltip.txt_title._height + tooltip.txt._height + 5,
		6,
		((x+max_width+16) > Stage.width ) ? (Stage.width-max_width-16) : x,
		y_pos,
		cstroke,
		ccolor);

	// NetVicious, June, 2007
	// create shadow filter
	var dropShadow = new flash.filters.DropShadowFilter();
	dropShadow.blurX = 4;
	dropShadow.blurY = 4;
	dropShadow.distance = 4;
	dropShadow.angle = 45;
	dropShadow.quality = 2;
	dropShadow.alpha = 0.5;
	// apply shadow filter
	tooltip.filters = [dropShadow];

}


function hide_oops()
{
	removeMovieClip("oops");
}

function oops( text:String )
{
	if( _root.oops != undefined )
	{
		hide_oops();
	}
	
	var mc:MovieClip = _root.createEmptyMovieClip( "oops", this.getNextHighestDepth() );
	mc.createTextField("txt", this.getNextHighestDepth(), 5, 5, 100, 100 );
	mc.txt.text = text;
	
	var fmt:TextFormat = new TextFormat();
	fmt.color = 0x000000;
	fmt.font = "Verdana";
	fmt.size = 12;
	fmt.align = "center";
	mc.txt.setTextFormat(fmt);
	mc.txt.autoSize="left";
	
	mc.txt.setTextFormat(fmt);
	
	var cstroke = {width:2, color:0x808080, alpha:100};
	var ccolor = {color:0xf0f0f0, alpha:100};
	
	ChartUtil.rrectangle(
		mc,
		mc.txt._width+10,
		mc.txt._height+10,
		6,
		(Stage.width/2)-((mc.txt._width+10)/2),
		(Stage.height/2)-((mc.txt._height+10)/2),
		cstroke,
		ccolor);
	
	var dropShadow = new flash.filters.DropShadowFilter();
	dropShadow.blurX = 4;
	dropShadow.blurY = 4;
	dropShadow.distance = 4;
	dropShadow.angle = 45;
	dropShadow.quality = 2;
	dropShadow.alpha = 0.5;
	// apply shadow filter
	//mc.filters = [dropShadow];
}

function make_pie()
{
	_root._pie = new PieStyle( this, 'pie' );//!=undefined ? lv['values'] : "", lv['links']);
	_root._title = new Title( this );
}


function make_chart()
{
	//
	// the order that these are built determines their Z order:
	//
	_root._inner_background = new InnerBackground( this );

	_root._min_max = new MinMax( this );

	// we build the graph from top to bottom 
	_root._title = new Title( this );
	_root._x_legend = new XLegend( this );
	_root._y_legend = new YLegend( this , 1);
	
	if(_root.lv.show_y2) 
		_root._y2_legend = new YLegend( this , 2);
	
	var xTicks = 5;
	if( this.x_ticks != undefined )
		xTicks = Number( this.x_ticks );

	// size, colour
	var x_label_style:XLabelStyle = new XLabelStyle( this );
	var y_label_style:YLabelStyle = new YLabelStyle( this, 1 );
	
	if(_root.lv.show_y2) 
		var y_label_style2:YLabelStyle = new YLabelStyle( this, 2 );
		
	
	// create X labels and measure the height:	
	_root._x_axis_labels = new XAxisLabels( this, x_label_style );

	var xSteps = 1;
	if( this.x_axis_steps != undefined )
		xStep = Number( this.x_axis_steps );

	_root._x_axis = new XAxis(
		xTicks,									// <-- tick size
		this,
		_root._x_axis_labels.count(),
		xStep
		);

	_root._y_ticks = new YTicks( this );
	
	_root._y_axis_labels = new YAxisLabels(
		y_label_style,
		_root._min_max.y_min,
		_root._min_max.y_max,
		_root._y_ticks.steps,
		1,
		this
		);

	if(_root.lv.show_y2)
	{
		_root._y_axis_labels2 = new YAxisLabels(
			y_label_style2,
			_root._min_max.y2_min,
			_root._min_max.y2_max,
			_root._y_ticks.steps,
			2,
			this
			);
	}

	_root._y_axis = new YAxis(
		_root._y_ticks,
		this,
		_root._min_max.y_min,
		_root._min_max.y_max,
		_root._y_ticks.steps,
		1
		);

	if(_root.lv.show_y2)
	{
		_root._y_axis2 = new YAxis(
			_root._y_ticks,
			this,
			_root._min_max.y_min,
			_root._min_max.y_max,
			_root._y_ticks.steps,
			2
			);
	}

	// The chart values are defined last and are on TOP of every thing else
	_root.chartValues = new Values( this, _root._background.colour, _root._x_axis_labels.labels );
	
	// tell the x axis where the grid lines are:
	_root._x_axis.set_grid_count( _root.chartValues.length() );
	
	if( _root._keys != undefined )
	{
		//_root._keys.del();
		//_root.oops('deleted');
	}
		
	_root._keys = new Keys(
		(_root._y_legend.width()+_root._y_axis_labels.width()+_root._y_axis.width()),		// <-- from left
		_root._title.height(),											// <-- from top
		_root.chartValues.styles );

}

var lv:LoadVars = new LoadVars();

//lv.onLoad = function( success )
lv.onLoad = LoadVarsOnLoad;
lv.make_chart = make_chart;
lv.make_pie = make_pie;

function LoadVarsOnLoad( success )
{
	if( !success )
	{
		_root.loading.done();
		//_root.oops('Open Flash Chart: Error opening data file URL\n'+_root.data);
		_root.oops(_root.data);
		return;
	}

	// remove loading data... message
	if( _root.oops != undefined )
		removeMovieClip("oops");	
	
	_root.css = new Css('margin-top: 30;margin-right: 40;');

	//
	// Now we build the objects, the order in which we
	// build them determins their Z position.
	//
	_root._background = new Background( this );
	
	
	//
	// if we have a pie chart, we don't build the
	// axis, grid and stuff..
	//
	if( this.pie != undefined )
		this.make_pie();
	else
		this.make_chart();

	
	if( this.tool_tip != undefined )
	{
		_root.tool_tip_wrapper = this.tool_tip;
	}
	
	_root.loading.done();
	_root.move();
}

function move()
{
	if( _root._pie != undefined )
	{
		_root._background.move();
		_root._title.move();
		_root._pie.draw();
		return;
	}
	
	//
	// move items that may resize themselves:
	//
	_root._keys.move();

	//
	// measure the box:
	//
	var top:Number = _root._title.height()+_root._keys.height();
	var left:Number = _root._y_legend.width()+_root._y_axis_labels.width()+_root._y_axis.width();
	var right:Number = Stage.width;
	// do we jiggle the box smaller because the last X Axis label
	// is hanging off the end of the screen?
	var jiggle:Boolean = true;
	
	if(_root.lv.show_y2)
	{
		right -= _root._y2_legend.width()+_root._y_axis_labels2.width()+_root._y_axis2.width();
		// no need to shrink the box:
		jiggle = false;
	}
	
	var bottom:Number = Stage.height-(_root._x_axis_labels.height()+_root._x_legend.height()+_root._x_axis.height())
 	
	var b:Box = new Box(
		top, left, right, bottom,
		_root._min_max,					// <-- scale everything between min/max
		_root._x_axis_labels.first_label_width(),
		_root._x_axis_labels.last_label_width(),
		 _root.chartValues.length(),
		jiggle,
		_root._x_axis.three_d
		);
		

	//
	// tell everything else to move, the order in
	// which we .move() things doesn't matter because
	// they allready have their z-order assigned.
	//
	_root._background.move();
	_root._inner_background.move( b );
	_root._title.move();
	_root._x_legend.move();
	_root._y_legend.move(1);

	
	if(_root.lv.show_y2)
		_root._y2_legend.move(2);
	
	_root._y_axis_labels.move( _root._y_legend.width(), b );

	// position of second y axel labels..
	if(_root.lv.show_y2)
		_root._y_axis_labels2.move( Stage.width-(_root._y2_legend.width()+_root._y_axis_labels2.width()), b );

	_root._x_axis.move( b );
	
	// move x labels
	_root._x_axis_labels.move(
		Stage.height-(_root._x_legend.height()+_root._x_axis_labels.height()),	// <-- up from the bottom
		_root.chartValues.styles[0].values.length,
		b );
	
	_root._y_axis.move( b , 1);
	
	if(_root.lv.show_y2)	
		_root._y_axis2.move( b , 2 );
	
	_root.chartValues.move(
		b,
		_root._min_max.y_min,
		_root._min_max.y_max,					// <-- scale everything between min/max
		_root._min_max.y2_min,
		_root._min_max.y2_max
		);
}

//
// test JS to flash coms
//
import flash.external.*;
ExternalInterface.addCallback("set_title", null, setTitle);
function setTitle(str:String):Void
{
	if( _root._title != undefined )
	{
		_root._title.build( str );
		_root.move();
	}
	// for debuggig:
	//_root.oops(str);
}

ExternalInterface.addCallback("push_value", null, pushValue);
function pushValue( val:String, label:String ):Void
{
	_root.chartValues.styles[0].add( Number( val ), label );
	_root._x_axis_labels.add(label);
	// tell the x axis where the grid lines are:
	_root._x_axis.set_grid_count( _root.chartValues.length() );
	_root.move();
}

ExternalInterface.addCallback("delete_value", null, deleteValue);
function deleteValue():Void
{
	_root.chartValues.styles[0].del();
	_root._x_axis_labels.del();
	// tell the x axis where the grid lines are:
	_root._x_axis.set_grid_count( _root.chartValues.length() );
	_root.move();
}

ExternalInterface.addCallback("show_message", null, show_message);
function show_message( msg:String ):Void
{
	_root.oops(msg);
}

ExternalInterface.addCallback("hide_message", null, hide_message);
function hide_message():Void
{
	hide_oops();
}

ExternalInterface.addCallback("reload", null, reload);
function reload( u:String ):Void
{
	// inform the user we are reloading data:
	_root.loading = new Loading('Loading data...');
	
	var lv:LoadVars = new LoadVars();
	
	// ugh!:
	lv.onLoad = LoadVarsOnLoad;
	lv.make_chart = make_chart;
	lv.make_pie = make_pie;
	// 

	var url:String = '';
	
	if( _root.data != undefined )
		url = _root.data;
	
	if( u != undefined )
	{
		if( u.length > 0 )
		{
			url = u;
		}
	}
	//setTitle( u );
	lv.load(url);
}


//
//
//
// ********************************************************************************

//_root.loading = new Loading('Loading data...');
//
// variables from the url:
if( _root.width == undefined )
	_root.width = 400; // <-- default width
	
if( _root.height == undefined )
	_root.height = 300; // <-- default width
	
_root.loading = new Loading('Loading data...');	

// so we can rotate text:
this.embedFonts = true;

_root.chartValues = new Array();

// -------------------------------------------------------------+
//
// tell flash to align top left, and not to scale
// anything (we do that in the code)
//
Stage.align = "LT";
//
// ----- RESIZE ----
//
// noScale: now we can pick up resize events
Stage.scaleMode = "noScale";
//
var stageListener:Object = new Object();
stageListener.onResize = function()
{
	//trace("w:"+Stage.width+", h:"+Stage.height);
	_root.move();
};
Stage.addListener(stageListener);
//
// ------ END RESIZE ----
//
//


// NetVicious, June 2007
// Right click menu:
setContextualMenu();

// from URL
if( _root.data == undefined )
	_root.data="C:\\Users\\John\\Documents\\flash\\svn\\data-files\\data-23.txt";
	//_root.data="http://www.stelteronline.de/index.php?option=com_joomleague&func=showStats_GetChartData&p=1";
	
lv.load(_root.data);

stop();