MovieClip.prototype.fillCircle = function(x, y, radius, accuracy, colour:Number )
{
    if (a < 3) a = 3;
    var span = Math.PI/accuracy;
    var controlRadius = radius/Math.cos(span);
    var anchorAngle=0, controlAngle=0;
    this.moveTo(x+Math.cos(anchorAngle)*radius, y+Math.sin(anchorAngle)*radius);
	
	this.beginFill( colour, 100 );
    for (var i=0; i<accuracy; ++i)
	{
		controlAngle = anchorAngle+span;
        anchorAngle = controlAngle+span;
        this.curveTo(
					 x + Math.cos(controlAngle)*controlRadius,
                     y + Math.sin(controlAngle)*controlRadius,
                     x + Math.cos(anchorAngle)*radius,
                     y + Math.sin(anchorAngle)*radius
        			 );
    }
	this.endFill();
}