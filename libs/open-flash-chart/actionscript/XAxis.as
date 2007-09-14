class XAxis
{
	private var tick:Number;
	private var grid_colour:Number;
	private var axis_colour:Number;
	//private var label_count:Number;
	private var grid_count:Number;
	private var mc:MovieClip;
	private var x_steps:Number;
	private var alt_axis_colour:Number;
	private var alt_axis_step:Number;
	private var three_d:Boolean;
	private var three_d_height:Number;
	
	function XAxis( tick:Number, lv:LoadVars, label_count:Number, steps:Number )
	{
		this.tick = tick;
		
		if( lv.x_grid_colour != undefined )
			this.grid_colour = _root.get_colour( lv.x_grid_colour );
		else
			this.grid_colour = 0xF5E1AA;
		
		if( lv.x_axis_colour != undefined )
			this.axis_colour = _root.get_colour( lv.x_axis_colour );
		else
			this.axis_colour = 0x784016;
			
		if( lv.x_axis_3d != undefined )
		{
			this.three_d = true;
			this.three_d_height = int( lv.x_axis_3d );
		}
		else
			this.three_d = false;

		// Path from Will Henry
		var style:Array = lv.x_label_style.split(',');
		if( style.length > 4 )
		{
			this.alt_axis_step = style[3];
			this.alt_axis_colour = _root.get_colour(style[4]);
		}
		
		//this.label_count = label_count;
		this.grid_count = label_count;
		if( steps == undefined )
			this.x_steps = 1;
		else
			this.x_steps = steps;

		this.mc = _root.createEmptyMovieClip( "x_axis", _root.getNextHighestDepth() );
		
	}
	
	function set_grid_count( val:Number )
	{
		this.grid_count = val;
	}
	
	function move( box:Box )
	{
		this.mc.clear();
		
		//
		// Grid lines
		//
		for( var i:Number=0; i < this.grid_count; i+=this.x_steps )
		{
			if( ( this.alt_axis_step > 1 ) && ( i % this.alt_axis_step == 0 ) )
			{
				this.mc.lineStyle(1,this.alt_axis_colour,100);
			}
			else
			{
				this.mc.lineStyle(1,this.grid_colour,100);
			}
			
			var x:Number = box.get_x_pos(i);
			this.mc.moveTo( x, box.bottom);
			this.mc.lineTo( x, box.top);
			
			this.mc.moveTo( x, box.bottom);
			this.mc.lineTo( x, box.top);
		}
		
		if( this.three_d )
			this.three_d_axis( mc, box );
		else
			this.two_d_axis( mc, box );
	}
		
	function three_d_axis( mc:MovieClip, box:Box )
	{
		
		// for 3D
		var h:Number = this.three_d_height;
		var offset:Number = 12;
		var x_axis_height:Number = h+offset;
		
		//
		// ticks
		var item_width:Number = box.width / this.grid_count;
	
		this.mc.lineStyle(1, this.axis_colour, 100);
		var w:Number = 1;
		for( var i:Number=0; i < this.grid_count; i+=this.x_steps )
		{
			//
			// uncommenting beginFill and endFill causes big bugs??!
			//
			//this.mc.beginFill(this.axis_colour,100);
			var pos:Number = box.get_x_tick_pos(i);
			
			this.mc.moveTo( pos, box.bottom+x_axis_height);
			this.mc.lineTo( pos+w, box.bottom+x_axis_height);
			this.mc.lineTo( pos+w, box.bottom+x_axis_height+this.tick);
			this.mc.lineTo( pos, box.bottom+x_axis_height+this.tick);
			this.mc.lineTo( pos, box.bottom+x_axis_height);
			//this.mc.endFill();
		}

		
		// turn off out lines:
		mc.lineStyle(0, 0, 0);
		
		var lighter:Number = ChartUtil.Lighten( this.axis_colour );
		
		// TOP
		var colors:Array = [this.axis_colour,lighter];
		var alphas:Array = [100,100];
		var ratios:Array = [0,255];
		var matrix:Object = { matrixType:"box", x:box.left-offset, y:box.bottom, w:box.width_(), h:offset, r:(270/180)*Math.PI };
		mc.beginGradientFill("linear", colors, alphas, ratios, matrix);
		this.mc.moveTo(box.left,box.bottom);
		this.mc.lineTo(box.right,box.bottom);
		this.mc.lineTo(box.right-offset,box.bottom+offset);
		this.mc.lineTo(box.left-offset,box.bottom+offset);
		this.mc.endFill();
	
		// front
		var colors:Array = [this.axis_colour,lighter];
		var alphas:Array = [100,100];
		var ratios:Array = [0,255];
		var matrix:Object = { matrixType:"box", x:box.left-offset, y:box.bottom+offset, w:box.width_(), h:h, r:(270/180)*Math.PI };
		mc.beginGradientFill("linear", colors, alphas, ratios, matrix);
		this.mc.moveTo(box.left-offset,box.bottom+offset);
		this.mc.lineTo(box.right-offset,box.bottom+offset);
		this.mc.lineTo(box.right-offset,box.bottom+offset+h);
		this.mc.lineTo(box.left-offset,box.bottom+offset+h);
		this.mc.endFill();
		
		// right side
		var colors:Array = [this.axis_colour,lighter];
		var alphas:Array = [100,100];
		var ratios:Array = [0,255];
		var matrix:Object = { matrixType:"box", x:box.left-offset, y:box.bottom+offset, w:box.width_(), h:h, r:(225/180)*Math.PI };
		mc.beginGradientFill("linear", colors, alphas, ratios, matrix);
		this.mc.moveTo(box.right,box.bottom);
		this.mc.lineTo(box.right,box.bottom+h);
		this.mc.lineTo(box.right-offset,box.bottom+offset+h);
		this.mc.lineTo(box.right-offset,box.bottom+offset);
		this.mc.endFill();
		
	}
	
	// 2D:
	function two_d_axis( mc:MovieClip, box:Box )
	{
		//
		// ticks
		var item_width:Number = box.width / this.grid_count;
		var left:Number = box.left+(item_width/2);
		//
		this.mc.lineStyle(2,this.axis_colour,100);
		for( var i:Number=0; i < this.grid_count; i+=this.x_steps )
		{
			this.mc.moveTo(left + (i*item_width),box.bottom);
			this.mc.lineTo(left + (i*item_width),box.bottom+this.tick);
		}
		
		// Axis line:
		this.mc.lineStyle(2,this.axis_colour,100);
		this.mc.moveTo(box.left,box.bottom);
		this.mc.lineTo(box.right,box.bottom);
			
	}
	
	function height_()
	{
		return 2 + this.tick;
	}
	
	function height()
	{
		if( this.three_d )
		{
			// 12 is the size of the slanty
			// 3D part of the X axis
			return this.three_d_height+12+this.tick;
		}
		else
			return this.tick;
	}
	
}