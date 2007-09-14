class XAxisLabels
{
	private var mcs:Array;
	private var style:XLabelStyle;
	public var labels:Array;

	function XAxisLabels( lv:LoadVars, style:XLabelStyle )
	{
		
		if( lv.x_labels == undefined )
			return;
			
		var labels:Array = lv.x_labels.split(',');
		
		this.style = style;
		
		// what if there are more values than labels?
		
		//this.count = this.labels.length;
		this.labels = [];

		this.mcs = Array();
		for( var i:Number=0; i < labels.length; i++ )
		{
			this.add( labels[i] );
			
		}
	}
	
	function add( label:String )
	{
		this.labels.push( label );
		
		if( ( (this.labels.length-1) % style.step ) ==0 )
			this.show_label( label, 'x_label_'+String(this.labels.length) );
	}
	
	function del()
	{
		this.labels.shift();

		// delete all the MovieClips, and recreate them
		// we have to do this because of the 'step' value
		//
		// I expect there is a better way of doing this...
		//
		for( var i:Number=0; i<this.mcs.length; i++ )
			removeMovieClip(this.mcs[i]._name);
			
		this.mcs = [];
		
		// now we have deleted all the labels, re-create them
		// note we use the step value so only create *some*
		for( var i:Number=0; i<this.labels.length; i++ )
			if( ( i % style.step ) == 0 )
				this.show_label( this.labels[i], 'x_label_'+i );
			
	}
	
	function show_label( label:String, name:String )
	{
		// we create the text in its own movie clip, so when
		// we rotate it, we can move the regestration point
		var mc:MovieClip = _root.createEmptyMovieClip(name, _root.getNextHighestDepth() );		
		mc.createTextField('txt', _root.getNextHighestDepth(), 0, 0, 100, 80);
		mc.txt.text = label;
			
		if( style.vertical || style.diag )
		{
			// so we can rotate the text
			mc.txt.embedFonts = true;
		}

		var fmt:TextFormat = new TextFormat();
		fmt.color = style.colour;

		if( style.vertical )
			fmt.font = "Verdana_embed";
		else
			fmt.font = "Verdana";
		
		fmt.size = style.size;
		fmt.align = "left";
		mc.txt.setTextFormat(fmt);
		mc.txt.autoSize = "left";
		
		if( style.vertical )
		{
			mc.txt._rotation = 270;
			// LOOK: move registration point:
			mc.txt._y = mc._height;
			mc.txt._x = -(mc.txt._width/2)
		}
		else if( style.diag )
		{
			// shift the text so when we rotate the movie clip it rotates
			// arround the last letter
			mc.txt._x = -mc.txt._width;
			mc.txt._y = -(mc.txt._height/2);
			mc._rotation = -45;
		}
		else
		{
			mc.txt._x = -(mc.txt._width/2);
		}
		
		this.mcs.push( mc );
		// we don't know the x & y locations yet...
	}
	
	function count()
	{
		return this.labels.length;
	}
	
	function height()
	{
		var height:Number = 0;
		for( var i:Number=0; i<this.mcs.length; i++ )
			height = Math.max( height, this.mcs[i]._height );
			
		return height;
	}
	
	function move( yPos:Number, count:Number, b:Box )
	{
		var i:Number = 0;
		for( var pos:Number=0; pos < this.mcs.length; pos++ )
		{
			this.mcs[pos]._x = b.get_x_tick_pos(i);
			this.mcs[pos]._y = yPos;
			i+=this.style.step;
		}
	}
	
	//
	// to help Box calculate the correct width:
	//
	function last_label_width()
	{
		// is the last label shown?
		if( ( (this.labels.length-1) % style.step ) != 0 )
			return 0;
			
		// get the width of the right most label
		// because it may stick out past the end of the graph
		// and we don't want to truncate it.
		return this.mcs[(this.mcs.length-1)]._width;
	}
	
	// see above comments
	function first_label_width()
	{
		return this.mcs[0]._width;
	}
}