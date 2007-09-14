class Loading
{
	function Loading( text:String )
	{
		if( _root.loading != undefined )
		{
			_root.removeMovieClip("loading");
		}
		
		var mc:MovieClip = _root.createEmptyMovieClip( "loading", _root.getNextHighestDepth() );
		mc.createTextField("txt", mc.getNextHighestDepth(), 5, 5, 100, 100 );
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
		
		var spin:MovieClip = mc.createEmptyMovieClip( "spinner", mc.getNextHighestDepth() );
		
		spin._x = mc.txt._width+40;
		spin._y = (mc.txt._height+10)/2;
		
		var radius:Number = 15;
		var dots:Number = 6;
		var colours:Array = [0xF0F0F0,0xD0D0D0,0xB0B0B0,0x909090,0x707070,0x505050,0x303030];
		
		for( var i=0; i<dots; i++ )
		{
			var deg = (360/dots)*i;
			var radians:Number = deg * (Math.PI/180);
			var x:Number = radius * Math.cos(radians);
			var y:Number = radius * Math.sin(radians);
			
			spin.fillCircle(
				x,
				y,
				4,
				3,
				colours[i]
				);
		}
		
		spin.onEnterFrame = function ()
    	{
			this._rotation += 2.4;
		}
		
		var dropShadow = new flash.filters.DropShadowFilter();
		dropShadow.blurX = 4;
		dropShadow.blurY = 4;
		dropShadow.distance = 4;
		dropShadow.angle = 45;
		dropShadow.quality = 2;
		dropShadow.alpha = 0.5;
		// apply shadow filter
		mc.filters = [dropShadow];
	}
	
	function done()
	{
		removeMovieClip("loading");
	}
}