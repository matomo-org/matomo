class Keys
{
	private var _height:Number = 0;
	public var left:Number = 0;
	public var top:Number = 0;
	
	private var count:Number = 0;
	private var key_mcs:Array;
	
	function Keys( left:Number, top:Number, styles:Array )
	{
		this.left = left;
		this.top = top;
		
		this.key_mcs = Array();
		var key:Number = 0;
		for( var i=0; i<styles.length; i++ )
		{
			// some lines may not have a key
			if( (styles[i].font_size > 0) and (styles[i].key != '' ) ) 
			{
				this.key_mcs.push( this.make_key( styles[i], key ) );
				key++;
			}
		}
		
		this.count = key;
		
		this.move();
	}
	
	//
	// this should be in the destructor, but
	// actionscript does not support them :-(
	//
	function del()
	{
		for( var i=0; i<this.key_mcs.length; i++ )
			removeMovieClip(this.key_mcs[i]);
	}
	
	// each key is a MovieClip with text on it
	function make_key( st:Style, c:Number )
	{
		var name2:String = "_key_block"+c;
		var mc:MovieClip = _root.createEmptyMovieClip( name2, _root.getNextHighestDepth() );
		
		var tf:TextField = mc.createTextField( 'txt', _root.getNextHighestDepth(), 10, 0, 100, 100 );
		
		tf.text = st.key;
		var fmt:TextFormat = new TextFormat();
		fmt.color = st.colour;
		fmt.font = "Verdana";
		fmt.size = st.font_size;
		fmt.align = "left";
		
		tf.setTextFormat(fmt);
		tf.autoSize="left";
		
		var y:Number = (mc.txt._height/2) - (st.line_width/2);
		
		mc.beginFill( st.colour, 100 );
		mc.moveTo( 0, y );
		mc.lineTo( 10, y );
		mc.lineTo( 10, y+st.line_width );
		mc.lineTo( 0, y+st.line_width );
		mc.endFill();
		
		mc._height = mc.txt._height;
		
		return mc;
	}
	

	// shuffle the keys into place, keeping note of the total
	// height the key block has taken up
	function move()
	{
		
		if( this.count == 0 )
			return;
			
		var height:Number = 0;
		var x:Number = left;
		var top:Number = this.top;
		
		for( var i=0; i<this.key_mcs.length; i++ )
		{
			var width:Number = this.key_mcs[i]._width;
			
			if( ( x + width ) > Stage.width )
			{
				// it is past the edge of the stage, so move it down a line
				x = left;
				top += this.key_mcs[i]._height;
				height += this.key_mcs[i]._height;
			}
				
			this.key_mcs[i]._x = x;
			this.key_mcs[i]._y = top;
			
			// move next key to the left + some padding between keys
			x += width + 10;
		}
		
		// Ugly code:
		height += this.key_mcs[0]._height;
		this._height = height;
	}
	
	function height()
	{
		return this._height;
	}

	
}