class YAxis
{
	private var _width:Number=0;
	private var ticks:YTicks;
	private var grid_colour:Number;
	private var axis_colour:Number;
	//private var count:Number;
	private var mc:MovieClip;
	private var line_width:Number = 2;
	
	private var min:Number;
	private var max:Number;
	private var steps:Number;
	
	function YAxis( y_ticks:YTicks, lv:LoadVars, min:Number, max:Number, steps:Number, nr:Number )
	{
		// ticks: thin and wide ticks
		this.ticks = y_ticks;
		
		if( lv.y_grid_colour != undefined )
			this.grid_colour = _root.get_colour( lv.y_grid_colour );
		else
			this.grid_colour = 0xF5E1AA;
		
		if(nr != 2) {
			if( lv.y_axis_colour != undefined )
				this.axis_colour = _root.get_colour( lv.y_axis_colour );
			else
				this.axis_colour = 0x784016;
		}else{
			if( lv.y2_axis_colour != undefined )
				this.axis_colour = _root.get_colour( lv.y2_axis_colour );
			else
				this.axis_colour = 0x784016;
		}
	
		//this.count = count;
		this.min = min;
		this.max = max;
		this.steps = steps;
		
		if(nr == 1) 
			this.mc = _root.createEmptyMovieClip( "y_axis", _root.getNextHighestDepth() );
	    else if(nr==2) 
			this.mc = _root.createEmptyMovieClip( "y_axis2", _root.getNextHighestDepth() );
	
		this._width = this.line_width + Math.max( this.ticks.small, this.ticks.big );
	}
	
	function move( box:Box, nr:Number )
	{
		if( nr == 2 )
		{
			if( !_root.lv.show_y2 )
				return;
			
			// Create the new axel
			this.mc.clear();
			this.mc.lineStyle(this.line_width,this.axis_colour,100);
				
			this.mc.moveTo( box.right, box.top );
			this.mc.lineTo( box.right, box.bottom );	
				
			// create new ticks.. 
			var every:Number = (this.max-this.min)/this.steps;
			for( var i:Number=this.min; i<=this.max; i+=every )
			{
				
				// start at the bottom and work up:
				var y:Number = box.getY(i);
				this.mc.moveTo( box.right, y );
				if( i % this.ticks.steps == 0 )
					this.mc.lineTo( box.right+this.ticks.big, y );
				else
					this.mc.lineTo( box.right+this.ticks.small, y );
					
			}
			return;	
		}
		
		// this should be an option:
		this.mc.clear();
		// Ticks
		//var tick:Number = box.height/(this.count-1);
		
		// Grid lines
		this.mc.lineStyle(1,this.grid_colour,100);
//		for( var i:Number=0; i < this.count; i++)
//		{
//			var y:Number = box.top+(i*tick);
//			this.mc.moveTo( box.left, y );
//			this.mc.lineTo( box.right, y );
//		}

		// y axel grid lines
		var every:Number = (this.max-this.min)/this.steps;
		// Set opacity for the first line to 0 (otherwise it overlaps the x-axel line)
		//
		// Bug? Does this work on graphs with minus values?
		//
		var i2:Number=0;
		for( var i:Number=this.min; i<=this.max; i+=every )
		{
			var y:Number = box.getY(i);
			if(i2 == 0) 
				this.mc.lineStyle(1,this.grid_colour,0);
				
			this.mc.moveTo( box.left, y );
			this.mc.lineTo( box.right, y );

			if(i2 == 0) 
				this.mc.lineStyle(1,this.grid_colour,100);
			i2 +=1;
		}
		
		
		this.mc.lineStyle(this.line_width,this.axis_colour,100);
			
		this.mc.moveTo( box.left, box.top );
		this.mc.lineTo( box.left, box.bottom );	
		
		// ticks..
		var every:Number = (this.max-this.min)/this.steps;
		for( var i:Number=this.min; i<=this.max; i+=every )
		{
			// start at the bottom and work up:
			var y:Number = box.getY(i);
			this.mc.moveTo( box.left, y );
			if( i % this.ticks.steps == 0 )
				this.mc.lineTo( box.left-this.ticks.big, y );
			else
				this.mc.lineTo( box.left-this.ticks.small, y );
				
		}
	}
	
	function width()
	{
		return this._width;
	}
	
}