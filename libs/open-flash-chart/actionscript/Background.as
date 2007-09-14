class Background
{
	private var colour:Number=0;
	private var mc:MovieClip;
	private var img_mc:MovieClip;
	private var img_x;
	private var img_y;

	// added by NetVicious, 05 July, 2007
	function positionize( mc:MovieClip, myX, myY, s:Square )
	{
		var newX:Number = 0;
		var newY:Number = 0;

		if ( isNaN(myX) ) {
			myX.toLowerCase()
			switch ( myX ) {
				case 'center':
					newX = (s.width / 2) - (mc._width / 2);
					break;
				case 'left':
					newX = s.left;
					break;
				case 'right':
					newX = s.right - mc._width;
					break;
				default:
					newX = 0;
			}
		} else if ( myX < 0 ) {
			newX = s.right - mc._width - myX;
		} else { newX = s.left + myX; }

		if ( isNaN(myY) ) {
			myY.toLowerCase();
			switch ( myY ) {
				case 'middle':
					newY = (s.height / 2) - (mc._height / 2);
					break;
				case 'top':
					newY = s.top;
					break;
				case 'bottom':
					newY = s.bottom - mc._height;
					break;
				default:
					newY = 0;
			}
		} else if ( myY < 0 ) {
			newY = s.bottom - mc._height - myY;
		} else { newY = s.top + myY; }

		mc._x = newX;
		mc._y = newY;
	}


	function Background( lv:LoadVars )
	{
		if( lv.bg_colour != undefined )
			this.colour = _root.get_colour( lv.bg_colour );
		else
			this.colour = 0xf8f8d8;		// <-- default to Ivory
			
		this.mc = _root.createEmptyMovieClip( "background", _root.getNextHighestDepth(), 0, 0, Stage.width, Stage.height );
	
		if( lv.bg_image != undefined )
		{
			this.img_mc = _root.createEmptyMovieClip( "background_img", _root.getNextHighestDepth(), 0, 0, Stage.width, Stage.height );
			//this.img_mc.cacheAsBitmap = true;
			//this.img_mc.opaqueBackground = 0xFFFFFF;
			// this.img_mc is replaced with the loaded image:
			
			// added by NetVicious, 05 July, 2007 ++++
			
			if( lv.bg_image_x != undefined )
				this.img_x = lv.bg_image_x;
				
			if( lv.bg_image_y != undefined )
				this.img_y = lv.bg_image_y;
				
			var ref = this; // This variable it's used for avoid the scope loss

			var loader:MovieClipLoader = new MovieClipLoader();
			loader.addListener({
				onLoadInit: function(mymc:MovieClip) {
					ref.positionize( mymc, ref.img_x, ref.img_y, new Square(0, 0, Stage.width, Stage.height) );				
					delete loader;
				}
			});
			
			loader.loadClip(lv.bg_image, this.img_mc);
			// ++++++++++++++++++++++++++++++++++++++++
			
//			loader = new MovieClipLoader()
//			//Give us status updates by firing events
//			loader.addListener(this)

			//loadMovie(lv.bg_image, this.img_mc );
		}
	}

	// the background doesn't 'move' but
	// it does re-size:
	function move()
	{
		this.mc.clear();
		this.mc.beginFill( this.colour, 100 );
    	this.mc.moveTo( 0, 0 );
		this.mc.lineTo( Stage.width, 0 );
		this.mc.lineTo( Stage.width, Stage.height );
		this.mc.lineTo( 0, Stage.height );
		this.mc.endFill(); 
		
		// do we have an image, and did it load:
		if(( this.img_mc != undefined ) and (this.img_mc._width != undefined))
		{
     		positionize( this.img_mc, this.img_x, this.img_y, new Square(0, 0, Stage.width, Stage.height) );
		}
	}
}