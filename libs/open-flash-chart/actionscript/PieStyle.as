import flash.external.ExternalInterface;


class PieStyle extends Style
{
	var TO_RADIANS:Number = Math.PI/180;
	var labels:Array;
	var links:Array;
	var colours:Array;
	
	var text_colour:Number;
	
	public var values:Array;
	
	private var pie_mcs:Array;
	public var name:String;
	
	private var gradientFill:String = 'true'; //toggle gradients
	private var border_width:Number = 1;
	
	public function PieStyle( lv:LoadVars, name:String )//, links:String )
	{
		this.labels = new Array();
		this.links = new Array();
		this.colours = new Array();
		
		this.name = name;
		
		this.parse( lv.pie );
		this.labels = lv.pie_labels.split(',');
		this.links = lv.links.split(',');
		
		var tmp:Array;
		if( lv.colours != undefined )
			tmp = lv.colours.split(',');
		
		// allow for both spellings fo colour.
		if( lv.colors != undefined )
			tmp = lv.colours.split(',');
			
			
		for( var i:Number=0; i<tmp.length; i++ )
			this.colours.push( _root.get_colour( tmp[i] ) );
			
	
		var tmp:Array = this.parseVals( lv.values );
		this.set_values( tmp );
	}
	
	public function parse( val:String ) : Void
	{
		var vals:Array = val.split(",");
		
		this.alpha = Number( vals[0] );
		this.colour = _root.get_colour( vals[1] );
		this.text_colour = _root.get_colour( vals[2] );
		
		if( vals.length > 3 )
			this.gradientFill = vals[3]; 
			
		if( vals.length > 4 )
			this.border_width = vals[4];
	}
	
	private function parseVals( val:String ):Array
	{
		var tmp:Array = Array();
		
		var vals:Array = val.split(",");
		for( var i:Number=0; i < vals.length; i++ )
		{
			tmp.push( vals[i] );
		}
		return tmp;
	}
	
	// override Style:set_values
	function set_values( v:Array )
	{
		super.set_values( v );
		
		// make an empty array to hold each bar MovieClip:
		this.pie_mcs = new Array( this.values.length );
		
		for( var i:Number=0; i < this.values.length; i++ )
		{
			var mc:MovieClip = _root.createEmptyMovieClip( this.name+'_'+i, _root.getNextHighestDepth() );

			mc.onRollOver = function() {ChartUtil.FadeIn(this, true); };
			mc.onRollOut = function() {ChartUtil.FadeOut(this); };
			
			if(this.links.length>i)
			{
				mc._ofc_link = this.links[i];
				mc.onRelease = function ():Void { trace(this._ofc_link); getURL(this._ofc_link); };
			}
			
			// this is used in FadeIn and FadeOut
			var tooltip:Object = {x_label:this.labels[i], value:this.values[i], key:'??'};
			mc.tooltip = tooltip;
			
			// add the MovieClip to our array:
			this.pie_mcs[i] = mc;
		}
		
		this.valPos();
	}
	
	private function valPos() : Void
	{
		this.ExPoints = new Array();
	
		var total:Number = 0;
		var slice_start:Number=0;
		for( var i:Number=0; i < this.values.length; i++)
		{
			total += Number(values[i]);
		}
		
		for( var i:Number=0; i < this.values.length; i++)
		{
			var slice_percent :Number = Number(this.values[i])*100/total; 
			
			this.ExPoints.push(
				new ExPoint(
					slice_start,					// x position of value
					0,						// center (not applicable for a bar)
					Number(this.values[i]), //y
					slice_percent,//width
					// min=-100 and max=100, use b.zero
					// min = 10 and max = 20, use b.bottom
					slice_start, //bar bottom
					//ChartUtil.format(slice_percent)+"%"+"\n"+ChartUtil.format(values[i]), //tooltip
					//_root.format(slice_percent)+"%"+"\n"+_root.format(values[i])
					slice_percent
					//,"#" //link
					)
				);
				
			slice_start += slice_percent;
		}
	}
	
	public function draw() : Void
	{
		
		//var colors_array:Array = [0xd01f3c,0x356aa0,0xC79810,0x73880A,0xD15600,0x6BBA70];
		
		for( var i:Number=0; i < this.ExPoints.length; i++ ) {
			//ignore non-positive values
			if( this.ExPoints[i].bar_width > 0)
			{
				this.draw_slice( this.ExPoints[i], i, this.colours[i%this.colours.length], this.labels[i], this.links[i] );
			}
		}	
	}
	
	function draw_slice( value:ExPoint, num:Number, color:Number, label:String, link:String ) : Void
	{
		//radius for the pie		
		var r1:Number = (Stage.width<Stage.height) ? Stage.width/2-60 : Stage.height/2-60;
		
		//the slice to be drawn
		//var pieSlice: MovieClip = _root.createEmptyMovieClip(name, _root.getNextHighestDepth());
		var pieSlice:MovieClip = this.pie_mcs[num];
		pieSlice.clear();
		 
		
	
		//line from center to edge
		pieSlice.lineStyle(this.border_width, this.colour, 100);

		//if the user selected the charts to be gradient filled do gradients
		if( this.gradientFill == 'true' )
		{
			//set gradient fill
			var colors:Array = [color, color];
			var alphas:Array = [100, 50];
			var ratios:Array = [100,255];
			var matrix:Object = {a:r1*2, b:0, c:50, d:0, e:r1*2, f:0, g:-3, h:3, i:1};
			pieSlice.beginGradientFill("radial", colors, alphas, ratios, matrix);
		}
		else
			pieSlice.beginFill(color, 100);
		
		pieSlice.moveTo(0, 0);
		pieSlice.lineTo(r1, 0);
	
		
		var angle:Number = 4;
		var a:Number = Math.tan((angle/2)*TO_RADIANS);
		
		var i:Number = 0;
		//draw curve segments spaced by angle 
		for( i=0; i+angle < value.bar_width*3.6; i+=angle) {
			var endx:Number = r1*Math.cos((i+angle)*TO_RADIANS);
			var endy:Number = r1*Math.sin((i+angle)*TO_RADIANS);
			var ax:Number = endx+r1*a*Math.cos(((i+angle)-90)*TO_RADIANS);
			var ay:Number = endy+r1*a*Math.sin(((i+angle)-90)*TO_RADIANS);
			pieSlice.curveTo(ax, ay, endx, endy);	
		}
		
		//when aproaching end of slice, refine angle interval
		var angle:Number = 0.08;
		var a:Number = Math.tan((angle/2)*TO_RADIANS);
		 
		for ( ; i+angle < value.bar_width*3.6; i+=angle) {
			var endx:Number = r1*Math.cos((i+angle)*TO_RADIANS);
			var endy:Number = r1*Math.sin((i+angle)*TO_RADIANS);
			var ax:Number = endx+r1*a*Math.cos(((i+angle)-90)*TO_RADIANS);
			var ay:Number = endy+r1*a*Math.sin(((i+angle)-90)*TO_RADIANS);
			pieSlice.curveTo(ax, ay, endx, endy);	
		}
		
		//close slice
		pieSlice.endFill();
		pieSlice.lineTo(0,0);

		//move slice to center
		pieSlice._x = Stage.width/2;//x;
		pieSlice._y = Stage.height/2;//y;
		
		//rotate slice to appropriate place in pie
		pieSlice._rotation = 3.6*value.bar_bottom;
		
		if( this.labels.length>0 )
		{
			var labelLineSize:Number = 1.1*r1;
			
			//draw line 
			pieSlice.lineStyle(1, this.colour, 100);
			//move to center of arc
			pieSlice.moveTo(r1*Math.cos(value.bar_width/2*3.6*TO_RADIANS), r1*Math.sin(value.bar_width/2*3.6*TO_RADIANS));
			
			//final line positions
			var lineEnd_x : Number = labelLineSize*Math.cos(value.bar_width/2*3.6*TO_RADIANS);
			var lineEnd_y : Number = labelLineSize*Math.sin(value.bar_width/2*3.6*TO_RADIANS);
			pieSlice.lineTo(lineEnd_x, lineEnd_y);
			
			//text field position
			var legend_x : Number = pieSlice._x+labelLineSize*Math.cos((value.bar_bottom+value.bar_width/2)*3.6*TO_RADIANS);
			var legend_y : Number = pieSlice._y+labelLineSize*Math.sin((value.bar_bottom+value.bar_width/2)*3.6*TO_RADIANS);
			
			//create legend text field
			if( _root["pie_text_"+num] != undefined )
				_root["pie_text_"+num].removeTextField();
			
			var legend_tf:TextField = _root.createTextField("pie_text_"+num, _root.getNextHighestDepth(), legend_x, legend_y, 10, 10);
			legend_tf.text = label;
			legend_tf.autoSize = true;
			legend_tf.rotation = 3.6*value.bar_bottom;
			
			//if legend stands to the right side of the pie
			if(legend_x<pieSlice._x)
				legend_tf._x -= legend_tf._width;
				
			//if legend stands on upper half of the pie
			if(legend_y<pieSlice._y)
				legend_tf._y -= legend_tf._height;
				
			var fmt:TextFormat = new TextFormat();
			fmt.color = this.text_colour;
			fmt.font = "Verdana";
			//fmt.size = this.size;
			fmt.align = "center";
			legend_tf.setTextFormat(fmt);
	
			//pieSlice.tool_tip_title = label;
			//pieSlice.tool_tip_value = value.tooltip;
		
			pieSlice._alpha = this.alpha;
			pieSlice._alpha_original = this.alpha;	// <-- remember our original alpha while tweening
		}
	}
}