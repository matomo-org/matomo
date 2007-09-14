class YAxisLabels
{
	public var labelNames:Array;
	private var steps:Number;
	
	function YAxisLabels( y_label_style:YLabelStyle, min:Number, max:Number, steps:Number, nr:Number, lv:LoadVars )
	{
		this.steps = steps;
		this.labelNames = [];
		var name:String = '';
		
		if(nr == 1)
		{
			// are the Y Labels visible?
			if( !y_label_style.show_labels )
				return;
			
			name = 'y_label_';
		}
		else if (nr = 2)
		{
			
			// are the Y Labels visible?
			if( !lv.show_y2 )
				return;
			
			name = 'y_label_2_';
		}
			
		// labels
		var every:Number = (max-min)/this.steps;
		
		for( var i:Number=min; i<=max; i+=every )
		{
			this.yAxisLabel( i, name+String(i), y_label_style, nr );
			this.labelNames.push( name+String(i) );
		}
	}

	
	
	function yAxisLabel( title:Number, name:String, y_label_style:YLabelStyle )
	{
		// does _root already have this textFiled defined?
		// this happens when we do an AJAX reload()
		// these have to be deleted by hand or else flash goes wonky.
		// In an ideal world we would put this code in the object
		// distructor method, but I don't think actionscript has these :-(
		if( _root[name] != undefined )
			_root[name].removeTextField();
											
		var tf:TextField = _root.createTextField(name, _root.getNextHighestDepth(), 0, 0, 100, 100);
		//tf.border = true;
		tf.text = _root.format(title);
		var fmt:TextFormat = new TextFormat();
		fmt.color = y_label_style.colour;
		fmt.font = "Verdana";
		fmt.size = y_label_style.size;
		fmt.align = "right";
		tf.setTextFormat(fmt);
		tf.autoSize="right";
	}

	// move y axis labels to the correct x pos
	function move( left:Number, box:Box )
	{
		var maxWidth:Number = this.width();
		
		for( var i in this.labelNames )
		{
			// right align
			_root[this.labelNames[i]]._x = left - _root[this.labelNames[i]]._width + maxWidth;
		}
		
		// now move it to the correct Y, vertical center align
		var tick:Number = box.height/this.steps;
		
		var count:Number = 0;
		for( var i in this.labelNames )
		{
			_root[this.labelNames[i]]._y = box.top + (tick*count) - (_root[this.labelNames[i]]._height/2);
			count+=1;
		}
	}


	function width()
	{
		var max:Number = 0;
		for( var x in this.labelNames )
		{
			max = Math.max( max, _root[this.labelNames[x]]._width );
		}
		return max;
	}
}