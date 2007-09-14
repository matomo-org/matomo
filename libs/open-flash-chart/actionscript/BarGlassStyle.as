class BarGlassStyle extends BarStyle
{
	public var is_bar:Boolean = true;
	public var outline_colour:Number = 0x000000;

	public function BarGlassStyle( val:String, name:String )
	{
		this.name = name;
		this.parse( val );
	}
	
	public function parse( val:String )
	{
		var vals:Array = val.split(",");
		
		this.alpha = Number( vals[0] );
		this.colour = _root.get_colour( vals[1] );
		this.outline_colour = _root.get_colour( vals[2] );
		
		if( vals.length > 3 )
			this.key = vals[3];
			
		if( vals.length > 4 )
			this.font_size = Number( vals[4] );
		
	}
	
	private function glass( mc:MovieClip, val:ExPoint )
	{
		var x:Number = 3;
		var y:Number = x;
		var width:Number = (val.bar_width/2)-x;
		var height:Number = val.bar_bottom-val.y-y-1;
		
		//set gradient fill
		var colors:Array = [0xFFFFFF,0xFFFFFF];
		var alphas:Array = [30, 70];
		var ratios:Array = [0,255];
		var matrix:Object = { matrixType:"box", x:x, y:y, w:width, h:height, r:(180/180)*Math.PI };
		mc.beginGradientFill("linear", colors, alphas, ratios, matrix);
		
		
		mc.lineStyle(0, 0, 0);
		
		var rad:Number = 3;
		var w:Number = width;
		var h:Number = height;
		
		//this.beginFill(this.shine_colour, 100);
		mc.moveTo(x+rad, y);
		mc.lineTo(x+w, y);
		mc.lineTo(x+w, y+h);
		mc.lineTo(x+rad, y+h);
		mc.curveTo(x, y+h, x, y+h-rad);
		mc.lineTo(x, y+rad);
		mc.endFill();

	}
	
	private function bg( mc:MovieClip, val:ExPoint )
	{
		//
		var w:Number = val.bar_width;
		var h:Number = val.bar_bottom-val.y;
		var x:Number = val.left;
		var y:Number = val.y;
		var rad:Number = 7;
		
		mc.lineStyle(0, this.outline_colour, 100);
		mc.beginFill(this.colour, 100);
		mc.moveTo(0+rad, 0);
		mc.lineTo(w-rad, 0);
		mc.curveTo(w, 0, w, rad);
		mc.lineTo(w, h);
		mc.lineTo(0, h);
		mc.lineTo(0, 0+rad);
		mc.curveTo(0, 0, 0+rad, 0);
		mc.endFill();
		mc._x = x;
		mc._y = y;
	};
	
	public function draw_bar( val:ExPoint, i:Number )
	{
		var mc:MovieClip = this.bar_mcs[i];
		
		mc.clear();
		this.bg( mc, val );
		this.glass( mc, val );

		var dropShadow = new flash.filters.DropShadowFilter();
		dropShadow.blurX = 5;
		dropShadow.blurY = 5;
		dropShadow.distance = 3;
		dropShadow.angle = 45;
		dropShadow.quality = 2;
		dropShadow.alpha = 0.4;
		mc.filters = [dropShadow];
		
		mc._alpha = this.alpha;
		mc._alpha_original = this.alpha;	// <-- remember our original alpha while tweening
		
		// this is used in _root.FadeIn and _root.FadeOut
		//mc.val = val;
	}
}