class InnerBackground
{
	private var colour:Number=0;
	private var colour_2:Number=-1;
	private var angle:Number = 90;
	private var mc:MovieClip;
	
	function InnerBackground( lv:LoadVars )
	{
		if( lv.inner_background == undefined )
			return;
			
		var vals:Array = lv.inner_background.split(",");
		
		this.colour = _root.get_colour( vals[0] );
		
		trace( this.colour)
		
		if( vals.length > 1 )
			this.colour_2 = _root.get_colour( vals[1] ); 
			
		if( vals.length > 2 )
			this.angle = Number( vals[2] );

		this.mc = _root.createEmptyMovieClip( "inner_background", _root.getNextHighestDepth() );
		
		// create shadow filter
		var dropShadow = new flash.filters.DropShadowFilter();
		dropShadow.blurX = 5;
		dropShadow.blurY = 5;
		dropShadow.distance = 5;
		dropShadow.angle = 45;
		dropShadow.quality = 2;
		dropShadow.alpha = 0.5;
		// apply shadow filter
		
		// disabled for now...
		//this.mc.filters = [dropShadow];
		
	}
	
	function move( box:Box )
	{
		if( this.mc == undefined )
			return;
		
		this.mc.clear();
		this.mc.lineStyle(1, 0xFFFFFF, 0);
		
		if( this.colour_2 > -1 )
		{
			// Gradients: http://www.lukamaras.com/tutorials/actionscript/gradient-colored-movie-background-actionscript.html
			var fillType:String = "linear";
			var colors:Array = [this.colour, this.colour_2];
			var alphas:Array = [100, 100];
			var ratios:Array = [0, 255];
			var matrix = {matrixType:"box", x:0, y:0, w:box.width, h:box.height, r:this.angle/180*Math.PI};
			this.mc.beginGradientFill(fillType, colors, alphas, ratios, matrix);
		}
		else
			this.mc.beginFill( this.colour, 100);
			
			
		this.mc.moveTo(0, 0);
		this.mc.lineTo(box.width, 0);
		this.mc.lineTo(box.width, box.height);
		this.mc.lineTo(0, box.height);
		this.mc.lineTo(0, 0);
		this.mc.endFill();

		this.mc._x = box.left;
		this.mc._y = box.top;
	}
	
}