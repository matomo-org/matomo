<?php
 /*
     pDraw - class extension with drawing methods

     Version     : 2.1.4
     Made by     : Jean-Damien POGOLOTTI
     Last Update : 19/01/2014

     This file can be distributed under the license you can find at :

                       http://www.pchart.net/license

     You can find the whole class documentation on the pChart web site.
 */

 define("DIRECTION_VERTICAL"		, 690001);
 define("DIRECTION_HORIZONTAL"		, 690002);

 define("SCALE_POS_LEFTRIGHT"		, 690101);
 define("SCALE_POS_TOPBOTTOM"		, 690102);

 define("SCALE_MODE_FLOATING"		, 690201);
 define("SCALE_MODE_START0"		, 690202);
 define("SCALE_MODE_ADDALL"		, 690203);
 define("SCALE_MODE_ADDALL_START0"	, 690204);
 define("SCALE_MODE_MANUAL"		, 690205);

 define("SCALE_SKIP_NONE"		, 690301);
 define("SCALE_SKIP_SAME"		, 690302);
 define("SCALE_SKIP_NUMBERS"		, 690303);

 define("TEXT_ALIGN_TOPLEFT"		, 690401);
 define("TEXT_ALIGN_TOPMIDDLE"		, 690402);
 define("TEXT_ALIGN_TOPRIGHT"		, 690403);
 define("TEXT_ALIGN_MIDDLELEFT"		, 690404);
 define("TEXT_ALIGN_MIDDLEMIDDLE"	, 690405);
 define("TEXT_ALIGN_MIDDLERIGHT"	, 690406);
 define("TEXT_ALIGN_BOTTOMLEFT"		, 690407);
 define("TEXT_ALIGN_BOTTOMMIDDLE"	, 690408);
 define("TEXT_ALIGN_BOTTOMRIGHT"	, 690409);

 define("POSITION_TOP"                  , 690501);
 define("POSITION_BOTTOM"               , 690502);

 define("LABEL_POS_LEFT"		, 690601);
 define("LABEL_POS_CENTER"		, 690602);
 define("LABEL_POS_RIGHT"		, 690603);
 define("LABEL_POS_TOP"			, 690604);
 define("LABEL_POS_BOTTOM"		, 690605);
 define("LABEL_POS_INSIDE"		, 690606);
 define("LABEL_POS_OUTSIDE"		, 690607);

 define("ORIENTATION_HORIZONTAL"	, 690701);
 define("ORIENTATION_VERTICAL"		, 690702);
 define("ORIENTATION_AUTO"		, 690703);

 define("LEGEND_NOBORDER"		, 690800);
 define("LEGEND_BOX"			, 690801);
 define("LEGEND_ROUND"			, 690802);

 define("LEGEND_VERTICAL"		, 690901);
 define("LEGEND_HORIZONTAL"		, 690902);

 define("LEGEND_FAMILY_BOX"		, 691051);
 define("LEGEND_FAMILY_CIRCLE"		, 691052);
 define("LEGEND_FAMILY_LINE"		, 691053);

 define("DISPLAY_AUTO"			, 691001);
 define("DISPLAY_MANUAL"		, 691002);

 define("LABELING_ALL"			, 691011);
 define("LABELING_DIFFERENT"		, 691012);

 define("BOUND_MIN"			, 691021);
 define("BOUND_MAX"			, 691022);
 define("BOUND_BOTH"			, 691023);

 define("BOUND_LABEL_POS_TOP"		, 691031);
 define("BOUND_LABEL_POS_BOTTOM"	, 691032);
 define("BOUND_LABEL_POS_AUTO"		, 691033);

 define("CAPTION_LEFT_TOP"		, 691041);
 define("CAPTION_RIGHT_BOTTOM"		, 691042);

 define("GRADIENT_SIMPLE"		, 691051);
 define("GRADIENT_EFFECT_CAN"		, 691052);

 define("LABEL_TITLE_NOBACKGROUND"	, 691061);
 define("LABEL_TITLE_BACKGROUND"	, 691062);

 define("LABEL_POINT_NONE"		, 691071);
 define("LABEL_POINT_CIRCLE"		, 691072);
 define("LABEL_POINT_BOX"		, 691073);

 define("ZONE_NAME_ANGLE_AUTO"		, 691081);

 define("PI"		, 3.14159265);
 define("ALL"		, 69);
 define("NONE"		, 31);
 define("AUTO"		, 690000);
 define("OUT_OF_SIGHT"	, -10000000000000);

 class pDraw
  {
   /* Returns the number of drawable series */
   function countDrawableSeries()
    {
     $Results = 0;
     $Data = $this->DataSet->getData();

     foreach($Data["Series"] as $SerieName => $Serie)
      { if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] ) { $Results++; } }

     return($Results);
    }

   /* Fix box coordinates */
   function fixBoxCoordinates($Xa,$Ya,$Xb,$Yb)
    {
     $X1 = min($Xa,$Xb); $Y1 = min($Ya,$Yb);
     $X2 = max($Xa,$Xb); $Y2 = max($Ya,$Yb);

     return(array($X1,$Y1,$X2,$Y2));
    }

   /* Draw a polygon */
   function drawPolygon($Points,$Format="")
    {
     $R			= isset($Format["R"]) ? $Format["R"] : 0;
     $G			= isset($Format["G"]) ? $Format["G"] : 0;
     $B			= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $NoFill		= isset($Format["NoFill"]) ? $Format["NoFill"] : FALSE;
     $NoBorder		= isset($Format["NoBorder"]) ? $Format["NoBorder"] : FALSE;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : $R;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : $G;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : $B;
     $BorderAlpha 	= isset($Format["Alpha"]) ? $Format["Alpha"] : $Alpha / 2;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $SkipX		= isset($Format["SkipX"]) ? $Format["SkipX"] : OUT_OF_SIGHT;
     $SkipY		= isset($Format["SkipY"]) ? $Format["SkipY"] : OUT_OF_SIGHT;

     /* Calling the ImageFilledPolygon() function over the $Points array will round it */ 	
     $Backup = $Points;

     if ( $Surrounding != NULL ) { $BorderR = $R+$Surrounding; $BorderG = $G+$Surrounding; $BorderB = $B+$Surrounding; }

     if ( $SkipX != OUT_OF_SIGHT ) { $SkipX = floor($SkipX); }
     if ( $SkipY != OUT_OF_SIGHT ) { $SkipY = floor($SkipY); }

     $RestoreShadow = $this->Shadow;
     if ( !$NoFill )
      {
       if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
        {
         $this->Shadow = FALSE;
         for($i=0;$i<=count($Points)-1;$i=$i+2)
          { $Shadow[] = $Points[$i] + $this->ShadowX; $Shadow[] = $Points[$i+1] + $this->ShadowY; }
         $this->drawPolygon($Shadow,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa,"NoBorder"=>TRUE));
        }

       $FillColor = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);

       if ( count($Points) >= 6 )
        { ImageFilledPolygon($this->Picture,$Points,count($Points)/2,$FillColor); }
      }

     if ( !$NoBorder )
      {
       $Points = $Backup;

       if ( $NoFill )
        $BorderSettings = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha);
       else
        $BorderSettings = array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha);

       for($i=0;$i<=count($Points)-1;$i=$i+2)
        {
         if ( isset($Points[$i+2]) )
          {
           if ( !($Points[$i] == $Points[$i+2] && $Points[$i] == $SkipX ) && !($Points[$i+1] == $Points[$i+3] && $Points[$i+1] == $SkipY ) )
            $this->drawLine($Points[$i],$Points[$i+1],$Points[$i+2],$Points[$i+3],$BorderSettings);
          }
         else
          {
           if ( !($Points[$i] == $Points[0] && $Points[$i] == $SkipX ) && !($Points[$i+1] == $Points[1] && $Points[$i+1] == $SkipY ) )
            $this->drawLine($Points[$i],$Points[$i+1],$Points[0],$Points[1],$BorderSettings);
          }
        }
      }

     $this->Shadow = $RestoreShadow;
    }

   /* Apply AALias correction to the rounded box boundaries */
   function offsetCorrection($Value,$Mode)
    {
     $Value = round($Value,1);

     if ( $Value == 0 && $Mode == 1 ) { return(.9); }
     if ( $Value == 0 ) { return(0); }

     if ( $Mode == 1) 
      { if ( $Value == 1 ) { return(.9); }; if ( $Value == .1 ) { return(.9); }; if ( $Value == .2 ) { return(.8); }; if ( $Value == .3 ) { return(.8); }; if ( $Value == .4 ) { return(.7); }; if ( $Value == .5 ) { return(.5); }; if ( $Value == .6 ) { return(.8); }; if ( $Value == .7 ) { return(.7); }; if ( $Value == .8 ) { return(.6); }; if ( $Value == .9 ) { return(.9); }; }

     if ( $Mode == 2) 
      { if ( $Value == 1 ) { return(.9); }; if ( $Value == .1 ) { return(.1); }; if ( $Value == .2 ) { return(.2); }; if ( $Value == .3 ) { return(.3); }; if ( $Value == .4 ) { return(.4); }; if ( $Value == .5 ) { return(.5); }; if ( $Value == .6 ) { return(.8); }; if ( $Value == .7 ) { return(.7); }; if ( $Value == .8 ) { return(.8); }; if ( $Value == .9 ) { return(.9); }; }

     if ( $Mode == 3) 
      { if ( $Value == 1 ) { return(.1); }; if ( $Value == .1 ) { return(.1); }; if ( $Value == .2 ) { return(.2); }; if ( $Value == .3 ) { return(.3); }; if ( $Value == .4 ) { return(.4); }; if ( $Value == .5 ) { return(.9); }; if ( $Value == .6 ) { return(.6); }; if ( $Value == .7 ) { return(.7); }; if ( $Value == .8 ) { return(.4); }; if ( $Value == .9 ) { return(.5); }; }

     if ( $Mode == 4) 
      { if ( $Value == 1 ) { return(-1); }; if ( $Value == .1 ) { return(.1); }; if ( $Value == .2 ) { return(.2); }; if ( $Value == .3 ) { return(.3); }; if ( $Value == .4 ) { return(.1); }; if ( $Value == .5 ) { return(-.1); }; if ( $Value == .6 ) { return(.8); }; if ( $Value == .7 ) { return(.1); }; if ( $Value == .8 ) { return(.1); }; if ( $Value == .9 ) { return(.1); }; }
    }

   /* Draw a rectangle with rounded corners */
   function drawRoundedRectangle($X1,$Y1,$X2,$Y2,$Radius,$Format="")
    {
     $R	    = isset($Format["R"]) ? $Format["R"] : 0;
     $G	    = isset($Format["G"]) ? $Format["G"] : 0;
     $B	    = isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha = isset($Format["Alpha"]) ? $Format["Alpha"] : 100;

     list($X1,$Y1,$X2,$Y2) = $this->fixBoxCoordinates($X1,$Y1,$X2,$Y2);

     if ( $X2 - $X1 < $Radius ) { $Radius = floor((($X2-$X1))/2); }
     if ( $Y2 - $Y1 < $Radius ) { $Radius = floor((($Y2-$Y1))/2); }

     $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"NoBorder"=>TRUE);

     if ( $Radius <= 0 ) { $this->drawRectangle($X1,$Y1,$X2,$Y2,$Color); return(0); }

     if ( $this->Antialias )
      {
       $this->drawLine($X1+$Radius,$Y1,$X2-$Radius,$Y1,$Color);
       $this->drawLine($X2,$Y1+$Radius,$X2,$Y2-$Radius,$Color);
       $this->drawLine($X2-$Radius,$Y2,$X1+$Radius,$Y2,$Color);
       $this->drawLine($X1,$Y1+$Radius,$X1,$Y2-$Radius,$Color);
      }
     else
      {
       $Color = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
       imageline($this->Picture,$X1+$Radius,$Y1,$X2-$Radius,$Y1,$Color);
       imageline($this->Picture,$X2,$Y1+$Radius,$X2,$Y2-$Radius,$Color);
       imageline($this->Picture,$X2-$Radius,$Y2,$X1+$Radius,$Y2,$Color);
       imageline($this->Picture,$X1,$Y1+$Radius,$X1,$Y2-$Radius,$Color);
      }

     $Step = 360 / (2 * PI * $Radius);
     for($i=0;$i<=90;$i=$i+$Step)
      {
       $X = cos(($i+180)*PI/180) * $Radius + $X1 + $Radius;
       $Y = sin(($i+180)*PI/180) * $Radius + $Y1 + $Radius;
       $this->drawAntialiasPixel($X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));

       $X = cos(($i+90)*PI/180) * $Radius + $X1 + $Radius;
       $Y = sin(($i+90)*PI/180) * $Radius + $Y2 - $Radius;
       $this->drawAntialiasPixel($X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));

       $X = cos($i*PI/180) * $Radius + $X2 - $Radius;
       $Y = sin($i*PI/180) * $Radius + $Y2 - $Radius;
       $this->drawAntialiasPixel($X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));

       $X = cos(($i+270)*PI/180) * $Radius + $X2 - $Radius;
       $Y = sin(($i+270)*PI/180) * $Radius + $Y1 + $Radius;
       $this->drawAntialiasPixel($X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
      }
    }

   /* Draw a rectangle with rounded corners */
   function drawRoundedFilledRectangle($X1,$Y1,$X2,$Y2,$Radius,$Format="")
    {
     $R			= isset($Format["R"]) ? $Format["R"] : 0;
     $G			= isset($Format["G"]) ? $Format["G"] : 0;
     $B			= isset($Format["B"]) ? $Format["B"] : 0;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : -1;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : -1;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : -1;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;

     /* Temporary fix for AA issue */
     $Y1 = floor($Y1); $Y2 = floor($Y2); $X1 = floor($X1); $X2 = floor($X2);

     if ( $Surrounding != NULL ) { $BorderR = $R+$Surrounding; $BorderG = $G+$Surrounding; $BorderB = $B+$Surrounding; }
     if ( $BorderR == -1 ) { $BorderR = $R; $BorderG = $G; $BorderB = $B; }

     list($X1,$Y1,$X2,$Y2) = $this->fixBoxCoordinates($X1,$Y1,$X2,$Y2);

     if ( $X2 - $X1 < $Radius*2 ) { $Radius = floor((($X2-$X1))/4); }
     if ( $Y2 - $Y1 < $Radius*2 ) { $Radius = floor((($Y2-$Y1))/4); }

     $RestoreShadow = $this->Shadow;
     if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
      {
       $this->Shadow = FALSE;
       $this->drawRoundedFilledRectangle($X1+$this->ShadowX,$Y1+$this->ShadowY,$X2+$this->ShadowX,$Y2+$this->ShadowY,$Radius,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa));
      }

     $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"NoBorder"=>TRUE);

     if ( $Radius <= 0 ) { $this->drawFilledRectangle($X1,$Y1,$X2,$Y2,$Color); return(0); }

     $YTop    = $Y1+$Radius;
     $YBottom = $Y2-$Radius;

     $Step = 360 / (2 * PI * $Radius);
     $Positions = ""; $Radius--; $MinY = ""; $MaxY = "";
     for($i=0;$i<=90;$i=$i+$Step)
      {
       $Xp1 = cos(($i+180)*PI/180) * $Radius + $X1 + $Radius;
       $Xp2 = cos(((90-$i)+270)*PI/180) * $Radius + $X2 - $Radius;
       $Yp  = floor(sin(($i+180)*PI/180) * $Radius + $YTop);
       if ( $MinY == "" || $Yp > $MinY ) { $MinY = $Yp; }

       if ( $Xp1 <= floor($X1) )  { $Xp1++; }
       if ( $Xp2 >= floor($X2) )  { $Xp2--; }
       $Xp1++;

       if ( !isset($Positions[$Yp]) )
        { $Positions[$Yp]["X1"] = $Xp1; $Positions[$Yp]["X2"] = $Xp2; }
       else
        { $Positions[$Yp]["X1"] = ($Positions[$Yp]["X1"]+$Xp1)/2; $Positions[$Yp]["X2"] = ($Positions[$Yp]["X2"]+$Xp2)/2; }

       $Xp1 = cos(($i+90)*PI/180) * $Radius + $X1 + $Radius;
       $Xp2 = cos((90-$i)*PI/180) * $Radius + $X2 - $Radius;
       $Yp  = floor(sin(($i+90)*PI/180) * $Radius + $YBottom);
       if ( $MaxY == "" || $Yp < $MaxY ) { $MaxY = $Yp; }

       if ( $Xp1 <= floor($X1) ) { $Xp1++; }
       if ( $Xp2 >= floor($X2) ) { $Xp2--; }
       $Xp1++;

       if ( !isset($Positions[$Yp]) )
        { $Positions[$Yp]["X1"] = $Xp1; $Positions[$Yp]["X2"] = $Xp2; }
       else
        { $Positions[$Yp]["X1"] = ($Positions[$Yp]["X1"]+$Xp1)/2; $Positions[$Yp]["X2"] = ($Positions[$Yp]["X2"]+$Xp2)/2; }
      }

     $ManualColor  = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
     foreach($Positions as $Yp => $Bounds)
      {
       $X1 = $Bounds["X1"]; $X1Dec = $this->getFirstDecimal($X1); if ( $X1Dec != 0 ) { $X1 = floor($X1)+1; }
       $X2 = $Bounds["X2"]; $X2Dec = $this->getFirstDecimal($X2); if ( $X2Dec != 0 ) { $X2 = floor($X2)-1; }
       imageline($this->Picture,$X1,$Yp,$X2,$Yp,$ManualColor);
      }
     $this->drawFilledRectangle($X1,$MinY+1,floor($X2),$MaxY-1,$Color);

     $Radius++;
     $this->drawRoundedRectangle($X1,$Y1,$X2+1,$Y2-1,$Radius,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));

     $this->Shadow = $RestoreShadow;
    }

   /* Draw a rectangle with rounded corners */
   function drawRoundedFilledRectangle_deprecated($X1,$Y1,$X2,$Y2,$Radius,$Format="")
    {
     $R			= isset($Format["R"]) ? $Format["R"] : 0;
     $G			= isset($Format["G"]) ? $Format["G"] : 0;
     $B			= isset($Format["B"]) ? $Format["B"] : 0;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : -1;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : -1;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : -1;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;

     if ( $Surrounding != NULL ) { $BorderR = $R+$Surrounding; $BorderG = $G+$Surrounding; $BorderB = $B+$Surrounding; }
     if ( $BorderR == -1 ) { $BorderR = $R; $BorderG = $G; $BorderB = $B; }

     list($X1,$Y1,$X2,$Y2) = $this->fixBoxCoordinates($X1,$Y1,$X2,$Y2);

     if ( $X2 - $X1 < $Radius ) { $Radius = floor((($X2-$X1)+2)/2); }
     if ( $Y2 - $Y1 < $Radius ) { $Radius = floor((($Y2-$Y1)+2)/2); }

     $RestoreShadow = $this->Shadow;
     if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
      {
       $this->Shadow = FALSE;
       $this->drawRoundedFilledRectangle($X1+$this->ShadowX,$Y1+$this->ShadowY,$X2+$this->ShadowX,$Y2+$this->ShadowY,$Radius,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa));
      }

     if ( $this->getFirstDecimal($X2) >= 5 )  { $XOffset2 = 1; } else { $XOffset2 = 0; }
     if ( $this->getFirstDecimal($X1) <= 5 )  { $XOffset1 = 1; } else { $XOffset1 = 0; }

     if ( !$this->Antialias ) { $XOffset1 = 1; $XOffset2 = 1; }

     $YTop    = floor($Y1+$Radius);
     $YBottom = floor($Y2-$Radius);

     $this->drawFilledRectangle($X1-$XOffset1,$YTop,$X2+$XOffset2,$YBottom,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"NoBorder"=>TRUE));

     $Step = 360 / (2 * PI * $Radius);
     $Color  = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
     $Color2 = $this->allocateColor($this->Picture,255,0,0,$Alpha);
     $Drawn = "";

     if ( $Alpha < 100 )  { $Drawn[$YTop] = FALSE; }
     if ( $Alpha < 100 )  { $Drawn[$YBottom] = TRUE; }
     
     for($i=0;$i<=90;$i=$i+$Step)
      {
       $Xp1 = cos(($i+180)*PI/180) * $Radius + $X1 + $Radius;
       $Xp2 = cos(((90-$i)+270)*PI/180) * $Radius + $X2 - $Radius;
       $Yp  = sin(($i+180)*PI/180) * $Radius + $YTop;

       if ( $this->getFirstDecimal($Xp1) > 5 )  { $XOffset1 = 1; } else { $XOffset1 = 0; }
       if ( $this->getFirstDecimal($Xp2) > 5 )  { $XOffset2 = 1; } else { $XOffset2 = 0; }
       if ( $this->getFirstDecimal($Yp) > 5 )  { $YOffset = 1; } else { $YOffset = 0; }

       if ( !isset($Drawn[$Yp+$YOffset]) || $Alpha == 100 )
        imageline($this->Picture,$Xp1+$XOffset1,$Yp+$YOffset,$Xp2+$XOffset2,$Yp+$YOffset,$Color);

       $Drawn[$Yp+$YOffset] = $Xp2;

       $Xp1 = cos(($i+90)*PI/180) * $Radius + $X1 + $Radius;
       $Xp2 = cos((90-$i)*PI/180) * $Radius + $X2 - $Radius;
       $Yp  = sin(($i+90)*PI/180) * $Radius + $YBottom;

       if ( $this->getFirstDecimal($Xp1) > 7 )  { $XOffset1 = 1; } else { $XOffset1 = 0; }
       if ( $this->getFirstDecimal($Xp2) > 7 )  { $XOffset2 = 1; } else { $XOffset2 = 0; }
       if ( $this->getFirstDecimal($Yp) > 5 )  { $YOffset = 1; } else { $YOffset = 0; }

       if ( !isset($Drawn[$Yp+$YOffset]) || $Alpha == 100 )
        imageline($this->Picture,$Xp1+$XOffset1,$Yp+$YOffset,$Xp2+$XOffset2,$Yp+$YOffset,$Color);

       $Drawn[$Yp+$YOffset] = $Xp2;
      }

     $this->drawRoundedRectangle($X1,$Y1,$X2,$Y2,$Radius,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));

     $this->Shadow = $RestoreShadow;
    }

   /* Draw a rectangle */
   function drawRectangle($X1,$Y1,$X2,$Y2,$Format="")
    {
     $R		= isset($Format["R"]) ? $Format["R"] : 0;
     $G		= isset($Format["G"]) ? $Format["G"] : 0;
     $B		= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Ticks	= isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
     $NoAngle	= isset($Format["NoAngle"]) ? $Format["NoAngle"] : FALSE;

     if ($X1 > $X2) { list($X1, $X2) = array($X2, $X1); }
     if ($Y1 > $Y2) { list($Y1, $Y2) = array($Y2, $Y1); }

     if ( $this->Antialias )
      {
       if ( $NoAngle )
        {
         $this->drawLine($X1+1,$Y1,$X2-1,$Y1,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
         $this->drawLine($X2,$Y1+1,$X2,$Y2-1,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
         $this->drawLine($X2-1,$Y2,$X1+1,$Y2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
         $this->drawLine($X1,$Y1+1,$X1,$Y2-1,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
        }
       else
        {
         $this->drawLine($X1+1,$Y1,$X2-1,$Y1,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
         $this->drawLine($X2,$Y1,$X2,$Y2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
         $this->drawLine($X2-1,$Y2,$X1+1,$Y2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
         $this->drawLine($X1,$Y1,$X1,$Y2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
        }
      }
     else
      {
       $Color = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
       imagerectangle($this->Picture,$X1,$Y1,$X2,$Y2,$Color);
      }
    }

   /* Draw a filled rectangle */
   function drawFilledRectangle($X1,$Y1,$X2,$Y2,$Format="")
    {
     $R			= isset($Format["R"]) ? $Format["R"] : 0;
     $G			= isset($Format["G"]) ? $Format["G"] : 0;
     $B			= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : -1;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : -1;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : -1;
     $BorderAlpha	= isset($Format["BorderAlpha"]) ? $Format["BorderAlpha"] : $Alpha;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $Ticks		= isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
     $NoAngle		= isset($Format["NoAngle"]) ? $Format["NoAngle"] : NULL;
     $Dash		= isset($Format["Dash"]) ? $Format["Dash"] : FALSE;
     $DashStep		= isset($Format["DashStep"]) ? $Format["DashStep"] : 4;
     $DashR		= isset($Format["DashR"]) ? $Format["DashR"] : 0;
     $DashG		= isset($Format["DashG"]) ? $Format["DashG"] : 0;
     $DashB		= isset($Format["DashB"]) ? $Format["DashB"] : 0;
     $NoBorder		= isset($Format["NoBorder"]) ? $Format["NoBorder"] : FALSE;

     if ( $Surrounding != NULL ) { $BorderR = $R+$Surrounding; $BorderG = $G+$Surrounding; $BorderB = $B+$Surrounding; }

     if ($X1 > $X2) { list($X1, $X2) = array($X2, $X1); }
     if ($Y1 > $Y2) { list($Y1, $Y2) = array($Y2, $Y1); }

     $RestoreShadow = $this->Shadow;
     if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
      {
       $this->Shadow = FALSE;
       $this->drawFilledRectangle($X1+$this->ShadowX,$Y1+$this->ShadowY,$X2+$this->ShadowX,$Y2+$this->ShadowY,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa,"Ticks"=>$Ticks,"NoAngle"=>$NoAngle));
      }

     $Color = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
     if ( $NoAngle )
      {
       imagefilledrectangle($this->Picture,ceil($X1)+1,ceil($Y1),floor($X2)-1,floor($Y2),$Color);
       imageline($this->Picture,ceil($X1),ceil($Y1)+1,ceil($X1),floor($Y2)-1,$Color);
       imageline($this->Picture,floor($X2),ceil($Y1)+1,floor($X2),floor($Y2)-1,$Color);
      }
     else
      imagefilledrectangle($this->Picture,ceil($X1),ceil($Y1),floor($X2),floor($Y2),$Color);

     if ( $Dash )
      {
       if ( $BorderR != -1 ) { $iX1=$X1+1; $iY1=$Y1+1; $iX2=$X2-1; $iY2=$Y2-1; } else { $iX1=$X1; $iY1=$Y1; $iX2=$X2; $iY2=$Y2; }

       $Color = $this->allocateColor($this->Picture,$DashR,$DashG,$DashB,$Alpha);
       $Y=$iY1-$DashStep;
       for($X=$iX1; $X<=$iX2+($iY2-$iY1); $X=$X+$DashStep)
        {
         $Y=$Y+$DashStep;
         if ( $X > $iX2 ) { $Xa = $X-($X-$iX2); $Ya = $iY1+($X-$iX2); } else { $Xa = $X; $Ya = $iY1; }
         if ( $Y > $iY2 ) { $Xb = $iX1+($Y-$iY2); $Yb = $Y-($Y-$iY2); } else { $Xb = $iX1; $Yb = $Y; }
         imageline($this->Picture,$Xa,$Ya,$Xb,$Yb,$Color);
        }
      }

     if ( $this->Antialias && !$NoBorder )
      {
       if ( $X1 < ceil($X1) )
        {
         $AlphaA = $Alpha * (ceil($X1) - $X1);
         $Color = $this->allocateColor($this->Picture,$R,$G,$B,$AlphaA);
         imageline($this->Picture,ceil($X1)-1,ceil($Y1),ceil($X1)-1,floor($Y2),$Color);
        }

       if ( $Y1 < ceil($Y1) )
        {
         $AlphaA = $Alpha * (ceil($Y1) - $Y1);
         $Color = $this->allocateColor($this->Picture,$R,$G,$B,$AlphaA);
         imageline($this->Picture,ceil($X1),ceil($Y1)-1,floor($X2),ceil($Y1)-1,$Color);
        }

       if ( $X2 > floor($X2) )
        {
         $AlphaA = $Alpha * (.5-($X2 - floor($X2)));
         $Color = $this->allocateColor($this->Picture,$R,$G,$B,$AlphaA);
         imageline($this->Picture,floor($X2)+1,ceil($Y1),floor($X2)+1,floor($Y2),$Color);
        } 

       if ( $Y2 > floor($Y2) )
        {
         $AlphaA = $Alpha * (.5-($Y2 - floor($Y2)));
         $Color = $this->allocateColor($this->Picture,$R,$G,$B,$AlphaA);
         imageline($this->Picture,ceil($X1),floor($Y2)+1,floor($X2),floor($Y2)+1,$Color);
        }
      }

     if ( $BorderR != -1 )
      $this->drawRectangle($X1,$Y1,$X2,$Y2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$Ticks,"NoAngle"=>$NoAngle));

     $this->Shadow = $RestoreShadow;
    }

   /* Draw a rectangular marker of the specified size */
   function drawRectangleMarker($X,$Y,$Format="")
    {
     $Size = isset($Format["Size"]) ? $Format["Size"] : 4;

     $HalfSize = floor($Size/2);
     $this->drawFilledRectangle($X-$HalfSize,$Y-$HalfSize,$X+$HalfSize,$Y+$HalfSize,$Format);
    }

   /* Drawn a spline based on the bezier function */
   function drawSpline($Coordinates,$Format="")
    {
     $R		= isset($Format["R"]) ? $Format["R"] : 0;
     $G		= isset($Format["G"]) ? $Format["G"] : 0;
     $B		= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Force	= isset($Format["Force"]) ? $Format["Force"] : 30;
     $Forces	= isset($Format["Forces"]) ? $Format["Forces"] : NULL;
     $ShowC	= isset($Format["ShowControl"]) ? $Format["ShowControl"] : FALSE;
     $Ticks	= isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
     $PathOnly	= isset($Format["PathOnly"]) ? $Format["PathOnly"] : FALSE;
     $Weight	= isset($Format["Weight"]) ? $Format["Weight"] : NULL;

     $Cpt = NULL; $Mode = NULL; $Result = "";
     for($i=1;$i<=count($Coordinates)-1;$i++)
      {
       $X1 = $Coordinates[$i-1][0]; $Y1 = $Coordinates[$i-1][1];
       $X2 = $Coordinates[$i][0];   $Y2 = $Coordinates[$i][1];

       if ( $Forces != NULL ) { $Force = $Forces[$i]; }

       /* First segment */
       if ( $i == 1 )
        { $Xv1 = $X1; $Yv1 = $Y1; }
       else
        {
         $Angle1 = $this->getAngle($XLast,$YLast,$X1,$Y1);
         $Angle2 = $this->getAngle($X1,$Y1,$X2,$Y2);
         $XOff = cos($Angle2 * PI / 180) * $Force + $X1;
         $YOff = sin($Angle2 * PI / 180) * $Force + $Y1;

         $Xv1 = cos($Angle1 * PI / 180) * $Force + $XOff;
         $Yv1 = sin($Angle1 * PI / 180) * $Force + $YOff;
        }

       /* Last segment */
       if ( $i == count($Coordinates)-1 )
        { $Xv2 = $X2; $Yv2 = $Y2; }
       else
        {
         $Angle1 = $this->getAngle($X2,$Y2,$Coordinates[$i+1][0],$Coordinates[$i+1][1]);
         $Angle2 = $this->getAngle($X1,$Y1,$X2,$Y2);
         $XOff = cos(($Angle2+180) * PI / 180) * $Force + $X2;
         $YOff = sin(($Angle2+180) * PI / 180) * $Force + $Y2;

         $Xv2 = cos(($Angle1+180) * PI / 180) * $Force + $XOff;
         $Yv2 = sin(($Angle1+180) * PI / 180) * $Force + $YOff;
        }

       $Path = $this->drawBezier($X1,$Y1,$X2,$Y2,$Xv1,$Yv1,$Xv2,$Yv2,$Format);
       if ($PathOnly) { $Result[] = $Path; }

       $XLast = $X1; $YLast = $Y1;
      }

     return($Result);
    }

   /* Draw a bezier curve with two controls points */
   function drawBezier($X1,$Y1,$X2,$Y2,$Xv1,$Yv1,$Xv2,$Yv2,$Format="")
    {
     $R		= isset($Format["R"]) ? $Format["R"] : 0;
     $G		= isset($Format["G"]) ? $Format["G"] : 0;
     $B		= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $ShowC	= isset($Format["ShowControl"]) ? $Format["ShowControl"] : FALSE;
     $Segments	= isset($Format["Segments"]) ? $Format["Segments"] : NULL;
     $Ticks	= isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
     $NoDraw    = isset($Format["NoDraw"]) ? $Format["NoDraw"] : FALSE;
     $PathOnly  = isset($Format["PathOnly"]) ? $Format["PathOnly"] : FALSE;
     $Weight    = isset($Format["Weight"]) ? $Format["Weight"] : NULL;
     $DrawArrow		= isset($Format["DrawArrow"]) ? $Format["DrawArrow"] : FALSE;
     $ArrowSize		= isset($Format["ArrowSize"]) ? $Format["ArrowSize"] : 10;
     $ArrowRatio	= isset($Format["ArrowRatio"]) ? $Format["ArrowRatio"] : .5;
     $ArrowTwoHeads	= isset($Format["ArrowTwoHeads"]) ? $Format["ArrowTwoHeads"] : FALSE;

     if ( $Segments == NULL )
      {
       $Length    = $this->getLength($X1,$Y1,$X2,$Y2);
       $Precision = ($Length*125)/1000;
      }
     else
      $Precision = $Segments;

     $P[0]["X"] = $X1;  $P[0]["Y"] = $Y1;
     $P[1]["X"] = $Xv1; $P[1]["Y"] = $Yv1;
     $P[2]["X"] = $Xv2; $P[2]["Y"] = $Yv2;
     $P[3]["X"] = $X2;  $P[3]["Y"] = $Y2;

     /* Compute the bezier points */
     $Q = ""; $ID = 0; $Path = "";
     for($i=0;$i<=$Precision;$i=$i+1)
      {
       $u = $i / $Precision;

       $C    = "";
       $C[0] = (1 - $u) * (1 - $u) * (1 - $u);
       $C[1] = ($u * 3) * (1 - $u) * (1 - $u);
       $C[2] = 3 * $u * $u * (1 - $u);
       $C[3] = $u * $u * $u;

       for($j=0;$j<=3;$j++)
        {
         if ( !isset($Q[$ID]) ) { $Q[$ID] = ""; }
         if ( !isset($Q[$ID]["X"]) ) { $Q[$ID]["X"] = 0; }
         if ( !isset($Q[$ID]["Y"]) ) { $Q[$ID]["Y"] = 0; }

         $Q[$ID]["X"] = $Q[$ID]["X"] + $P[$j]["X"] * $C[$j];
         $Q[$ID]["Y"] = $Q[$ID]["Y"] + $P[$j]["Y"] * $C[$j];
        }
       $ID++;
      }        
     $Q[$ID]["X"] = $X2; $Q[$ID]["Y"] = $Y2;

     if ( !$NoDraw )
      {
       /* Display the control points */
       if ( $ShowC && !$PathOnly )
        {
         $Xv1 = floor($Xv1); $Yv1 = floor($Yv1); $Xv2 = floor($Xv2); $Yv2 = floor($Yv2);

         $this->drawLine($X1,$Y1,$X2,$Y2,array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>30));

         $MyMarkerSettings = array("R"=>255,"G"=>0,"B"=>0,"BorderR"=>255,"BorderB"=>255,"BorderG"=>255,"Size"=>4);
         $this->drawRectangleMarker($Xv1,$Yv1,$MyMarkerSettings);
         $this->drawText($Xv1+4,$Yv1,"v1");
         $MyMarkerSettings = array("R"=>0,"G"=>0,"B"=>255,"BorderR"=>255,"BorderB"=>255,"BorderG"=>255,"Size"=>4);
         $this->drawRectangleMarker($Xv2,$Yv2,$MyMarkerSettings);
         $this->drawText($Xv2+4,$Yv2,"v2");
        }

       /* Draw the bezier */
       $LastX = NULL; $LastY = NULL; $Cpt = NULL; $Mode = NULL; $ArrowS = NULL;
       foreach ($Q as $Key => $Point)
        {
         $X = $Point["X"]; $Y = $Point["Y"];

         /* Get the first segment */
         if ( $ArrowS == NULL && $LastX != NULL && $LastY != NULL )
          { $ArrowS["X2"] = $LastX; $ArrowS["Y2"] = $LastY; $ArrowS["X1"] = $X; $ArrowS["Y1"] = $Y; }

         if ( $LastX != NULL && $LastY != NULL && !$PathOnly)
          list($Cpt,$Mode) = $this->drawLine($LastX,$LastY,$X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Cpt"=>$Cpt,"Mode"=>$Mode,"Weight"=>$Weight));

         /* Get the last segment */
         $ArrowE["X1"] = $LastX; $ArrowE["Y1"] = $LastY; $ArrowE["X2"] = $X; $ArrowE["Y2"] = $Y;

         $LastX = $X; $LastY = $Y;
        }

       if ( $DrawArrow && !$PathOnly )
        {
         $ArrowSettings = array("FillR"=>$R,"FillG"=>$G,"FillB"=>$B,"Alpha"=>$Alpha,"Size"=>$ArrowSize,"Ratio"=>$ArrowRatio);
         if ( $ArrowTwoHeads )
          $this->drawArrow($ArrowS["X1"],$ArrowS["Y1"],$ArrowS["X2"],$ArrowS["Y2"],$ArrowSettings);

         $this->drawArrow($ArrowE["X1"],$ArrowE["Y1"],$ArrowE["X2"],$ArrowE["Y2"],$ArrowSettings);
        }
      }
     return($Q);
    }

   /* Draw a line between two points */
   function drawLine($X1,$Y1,$X2,$Y2,$Format="")
    {
     $R		= isset($Format["R"]) ? $Format["R"] : 0;
     $G		= isset($Format["G"]) ? $Format["G"] : 0;
     $B		= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Ticks	= isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
     $Cpt	= isset($Format["Cpt"]) ? $Format["Cpt"] : 1;
     $Mode	= isset($Format["Mode"]) ? $Format["Mode"] : 1;
     $Weight	= isset($Format["Weight"]) ? $Format["Weight"] : NULL;
     $Threshold	= isset($Format["Threshold"]) ? $Format["Threshold"] : NULL;

     if ( $this->Antialias == FALSE && $Ticks == NULL )
      {
       if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
        {
         $ShadowColor = $this->allocateColor($this->Picture,$this->ShadowR,$this->ShadowG,$this->ShadowB,$this->Shadowa);
         imageline($this->Picture,$X1+$this->ShadowX,$Y1+$this->ShadowY,$X2+$this->ShadowX,$Y2+$this->ShadowY,$ShadowColor);
        }

       $Color = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
       imageline($this->Picture,$X1,$Y1,$X2,$Y2,$Color);
       return(0);
      }

     $Distance = sqrt(($X2-$X1)*($X2-$X1)+($Y2-$Y1)*($Y2-$Y1));  
     if ( $Distance == 0 ) { return(-1); }

     /* Derivative algorithm for overweighted lines, re-route to polygons primitives */
     if ( $Weight != NULL )
      {
       $Angle        = $this->getAngle($X1,$Y1,$X2,$Y2);
       $PolySettings = array ("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"BorderAlpha"=>$Alpha);

       if ( $Ticks == NULL )
        {
         $Points   = ""; 
         $Points[] = cos(deg2rad($Angle-90)) * $Weight + $X1; $Points[] = sin(deg2rad($Angle-90)) * $Weight + $Y1;
         $Points[] = cos(deg2rad($Angle+90)) * $Weight + $X1; $Points[] = sin(deg2rad($Angle+90)) * $Weight + $Y1;
         $Points[] = cos(deg2rad($Angle+90)) * $Weight + $X2; $Points[] = sin(deg2rad($Angle+90)) * $Weight + $Y2;
         $Points[] = cos(deg2rad($Angle-90)) * $Weight + $X2; $Points[] = sin(deg2rad($Angle-90)) * $Weight + $Y2;

         $this->drawPolygon($Points,$PolySettings);
        }
       else
        {
         for($i=0;$i<=$Distance;$i=$i+$Ticks*2)
          {
           $Xa = (($X2-$X1)/$Distance) * $i + $X1; $Ya = (($Y2-$Y1)/$Distance) * $i + $Y1;
           $Xb = (($X2-$X1)/$Distance) * ($i+$Ticks) + $X1; $Yb = (($Y2-$Y1)/$Distance) * ($i+$Ticks) + $Y1;

           $Points   = ""; 
           $Points[] = cos(deg2rad($Angle-90)) * $Weight + $Xa; $Points[] = sin(deg2rad($Angle-90)) * $Weight + $Ya;
           $Points[] = cos(deg2rad($Angle+90)) * $Weight + $Xa; $Points[] = sin(deg2rad($Angle+90)) * $Weight + $Ya;
           $Points[] = cos(deg2rad($Angle+90)) * $Weight + $Xb; $Points[] = sin(deg2rad($Angle+90)) * $Weight + $Yb;
           $Points[] = cos(deg2rad($Angle-90)) * $Weight + $Xb; $Points[] = sin(deg2rad($Angle-90)) * $Weight 	+ $Yb;

           $this->drawPolygon($Points,$PolySettings);
          }
        }

       return(1);
      }

     $XStep = ($X2-$X1) / $Distance;
     $YStep = ($Y2-$Y1) / $Distance;

     for($i=0;$i<=$Distance;$i++)
      {
       $X = $i * $XStep + $X1;
       $Y = $i * $YStep + $Y1;

       $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha);

       if ( $Threshold != NULL )
        {
         foreach($Threshold as $Key => $Parameters)
          {
           if ( $Y <= $Parameters["MinX"] && $Y >= $Parameters["MaxX"])
            {
             if ( isset($Parameters["R"]) ) { $RT = $Parameters["R"]; } else { $RT = 0; }
             if ( isset($Parameters["G"]) ) { $GT = $Parameters["G"]; } else { $GT = 0; }
             if ( isset($Parameters["B"]) ) { $BT = $Parameters["B"]; } else { $BT = 0; }
             if ( isset($Parameters["Alpha"]) ) { $AlphaT = $Parameters["Alpha"]; } else { $AlphaT = 0; }
             $Color = array("R"=>$RT,"G"=>$GT,"B"=>$BT,"Alpha"=>$AlphaT);
            }
          }
        }

       if ( $Ticks != NULL )
        {
         if ( $Cpt % $Ticks == 0 )
          { $Cpt = 0; if ( $Mode == 1 ) { $Mode = 0; } else { $Mode = 1; } }

         if ( $Mode == 1 )
          $this->drawAntialiasPixel($X,$Y,$Color);

         $Cpt++;
        }
       else
        $this->drawAntialiasPixel($X,$Y,$Color);
      }

     return(array($Cpt,$Mode));
    }

   /* Draw a circle */
   function drawCircle($Xc,$Yc,$Height,$Width,$Format="")
    {
     $R	    = isset($Format["R"]) ? $Format["R"] : 0;
     $G	    = isset($Format["G"]) ? $Format["G"] : 0;
     $B	    = isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha = isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;

     $Height	= abs($Height);
     $Width	= abs($Width);

     if ( $Height == 0 ) { $Height = 1; }
     if ( $Width == 0 )  { $Width = 1; }
     $Xc = floor($Xc); $Yc = floor($Yc);

     $RestoreShadow = $this->Shadow;
     if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
      {
       $this->Shadow = FALSE;
       $this->drawCircle($Xc+$this->ShadowX,$Yc+$this->ShadowY,$Height,$Width,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa,"Ticks"=>$Ticks));
      }

     if ( $Width == 0 ) { $Width = $Height; }
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $Step = 360 / (2 * PI * max($Width,$Height));
     $Mode = 1; $Cpt = 1;
     for($i=0;$i<=360;$i=$i+$Step)
      {
       $X = cos($i*PI/180) * $Height + $Xc;
       $Y = sin($i*PI/180) * $Width + $Yc;

       if ( $Ticks != NULL )
        {
         if ( $Cpt % $Ticks == 0 )
          { $Cpt = 0; if ( $Mode == 1 ) { $Mode = 0; } else { $Mode = 1; } }

         if ( $Mode == 1 )
          $this->drawAntialiasPixel($X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));

         $Cpt++;
        }
       else
        $this->drawAntialiasPixel($X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));

      }
     $this->Shadow = $RestoreShadow;
    }

   /* Draw a filled circle */
   function drawFilledCircle($X,$Y,$Radius,$Format="")
    {
     $R			= isset($Format["R"]) ? $Format["R"] : 0;
     $G			= isset($Format["G"]) ? $Format["G"] : 0;
     $B			= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : -1;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : -1;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : -1;
     $BorderAlpha	= isset($Format["BorderAlpha"]) ? $Format["BorderAlpha"] : $Alpha;
     $Ticks     	= isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
     $Surrounding 	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;

     if ( $Radius == 0 ) { $Radius = 1; }
     if ( $Surrounding != NULL ) { $BorderR = $R+$Surrounding; $BorderG = $G+$Surrounding; $BorderB = $B+$Surrounding; }
     $X = floor($X); $Y = floor($Y);

     $Radius = abs($Radius);

     $RestoreShadow = $this->Shadow;
     if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
      {
       $this->Shadow = FALSE;
       $this->drawFilledCircle($X+$this->ShadowX,$Y+$this->ShadowY,$Radius,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa,"Ticks"=>$Ticks));
      }

     $this->Mask  = "";
     $Color = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
     for ($i=0; $i<=$Radius*2; $i++)
      {
       $Slice  = sqrt($Radius * $Radius - ($Radius - $i) * ($Radius - $i));
       $XPos   = floor($Slice);
       $YPos   = $Y + $i - $Radius;
       $AAlias = $Slice - floor($Slice);

       $this->Mask[$X-$XPos][$YPos] = TRUE;
       $this->Mask[$X+$XPos][$YPos] = TRUE;
       imageline($this->Picture,$X-$XPos,$YPos,$X+$XPos,$YPos,$Color);
      }
     if ( $this->Antialias )
      $this->drawCircle($X,$Y,$Radius,$Radius,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));

     $this->Mask = "";

     if ( $BorderR != -1 )
      $this->drawCircle($X,$Y,$Radius,$Radius,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$Ticks));

     $this->Shadow	= $RestoreShadow;
    }

   /* Write text */
   function drawText($X,$Y,$Text,$Format="")
    {
     $R			= isset($Format["R"]) ? $Format["R"] : $this->FontColorR;
     $G			= isset($Format["G"]) ? $Format["G"] : $this->FontColorG;
     $B			= isset($Format["B"]) ? $Format["B"] : $this->FontColorB;
     $Angle		= isset($Format["Angle"]) ? $Format["Angle"] : 0;
     $Align		= isset($Format["Align"]) ? $Format["Align"] : TEXT_ALIGN_BOTTOMLEFT;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : $this->FontColorA;
     $FontName		= isset($Format["FontName"]) ? $Format["FontName"] : $this->FontName;
     $FontSize		= isset($Format["FontSize"]) ? $Format["FontSize"] : $this->FontSize;
     $ShowOrigine	= isset($Format["ShowOrigine"]) ? $Format["ShowOrigine"] : FALSE;
     $TOffset		= isset($Format["TOffset"]) ? $Format["TOffset"] : 2;
     $DrawBox		= isset($Format["DrawBox"]) ? $Format["DrawBox"] : FALSE;
     $DrawBoxBorder	= isset($Format["DrawBoxBorder"]) ? $Format["DrawBoxBorder"] : TRUE;
     $BorderOffset	= isset($Format["BorderOffset"]) ? $Format["BorderOffset"] : 6;
     $BoxRounded	= isset($Format["BoxRounded"]) ? $Format["BoxRounded"] : FALSE;
     $RoundedRadius	= isset($Format["RoundedRadius"]) ? $Format["RoundedRadius"] : 6;
     $BoxR		= isset($Format["BoxR"]) ? $Format["BoxR"] : 255;
     $BoxG		= isset($Format["BoxG"]) ? $Format["BoxG"] : 255;
     $BoxB		= isset($Format["BoxB"]) ? $Format["BoxB"] : 255;
     $BoxAlpha		= isset($Format["BoxAlpha"]) ? $Format["BoxAlpha"] : 50;
     $BoxSurrounding	= isset($Format["BoxSurrounding"]) ? $Format["BoxSurrounding"] : "";
     $BoxBorderR	= isset($Format["BoxR"]) ? $Format["BoxR"] : 0;
     $BoxBorderG	= isset($Format["BoxG"]) ? $Format["BoxG"] : 0;
     $BoxBorderB	= isset($Format["BoxB"]) ? $Format["BoxB"] : 0;
     $BoxBorderAlpha	= isset($Format["BoxAlpha"]) ? $Format["BoxAlpha"] : 50;
     $NoShadow		= isset($Format["NoShadow"]) ? $Format["NoShadow"] : FALSE;

     $Shadow = $this->Shadow;
     if ( $NoShadow ) { $this->Shadow = FALSE; }

     if ( $BoxSurrounding != "" ) { $BoxBorderR = $BoxR - $BoxSurrounding; $BoxBorderG = $BoxG - $BoxSurrounding; $BoxBorderB = $BoxB - $BoxSurrounding; $BoxBorderAlpha = $BoxAlpha; }

     if ( $ShowOrigine )
      {
       $MyMarkerSettings = array("R"=>255,"G"=>0,"B"=>0,"BorderR"=>255,"BorderB"=>255,"BorderG"=>255,"Size"=>4);
       $this->drawRectangleMarker($X,$Y,$MyMarkerSettings);
      }

     $TxtPos = $this->getTextBox($X,$Y,$FontName,$FontSize,$Angle,$Text);

     if ( $DrawBox && ($Angle == 0 || $Angle == 90 || $Angle == 180 || $Angle == 270))
      {
       $T[0]["X"]=0;$T[0]["Y"]=0;$T[1]["X"]=0;$T[1]["Y"]=0;$T[2]["X"]=0;$T[2]["Y"]=0;$T[3]["X"]=0;$T[3]["Y"]=0;
       if ( $Angle == 0 ) { $T[0]["X"]=-$TOffset;$T[0]["Y"]=$TOffset;$T[1]["X"]=$TOffset;$T[1]["Y"]=$TOffset;$T[2]["X"]=$TOffset;$T[2]["Y"]=-$TOffset;$T[3]["X"]=-$TOffset;$T[3]["Y"]=-$TOffset; }

       $X1 = min($TxtPos[0]["X"],$TxtPos[1]["X"],$TxtPos[2]["X"],$TxtPos[3]["X"]) - $BorderOffset + 3;
       $Y1 = min($TxtPos[0]["Y"],$TxtPos[1]["Y"],$TxtPos[2]["Y"],$TxtPos[3]["Y"]) - $BorderOffset;
       $X2 = max($TxtPos[0]["X"],$TxtPos[1]["X"],$TxtPos[2]["X"],$TxtPos[3]["X"]) + $BorderOffset + 3;
       $Y2 = max($TxtPos[0]["Y"],$TxtPos[1]["Y"],$TxtPos[2]["Y"],$TxtPos[3]["Y"]) + $BorderOffset - 3;

       $X1 = $X1 - $TxtPos[$Align]["X"] + $X + $T[0]["X"];
       $Y1 = $Y1 - $TxtPos[$Align]["Y"] + $Y + $T[0]["Y"];
       $X2 = $X2 - $TxtPos[$Align]["X"] + $X + $T[0]["X"];
       $Y2 = $Y2 - $TxtPos[$Align]["Y"] + $Y + $T[0]["Y"];

       $Settings = array("R"=>$BoxR,"G"=>$BoxG,"B"=>$BoxB,"Alpha"=>$BoxAlpha,"BorderR"=>$BoxBorderR,"BorderG"=>$BoxBorderG,"BorderB"=>$BoxBorderB,"BorderAlpha"=>$BoxBorderAlpha);

       if ( $BoxRounded )
        { $this->drawRoundedFilledRectangle($X1,$Y1,$X2,$Y2,$RoundedRadius,$Settings); }
       else
        { $this->drawFilledRectangle($X1,$Y1,$X2,$Y2,$Settings); }
      }

     $X = $X - $TxtPos[$Align]["X"] + $X;
     $Y = $Y - $TxtPos[$Align]["Y"] + $Y;

     if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
      {
       $C_ShadowColor = $this->allocateColor($this->Picture,$this->ShadowR,$this->ShadowG,$this->ShadowB,$this->Shadowa);
       imagettftext($this->Picture,$FontSize,$Angle,$X+$this->ShadowX,$Y+$this->ShadowY,$C_ShadowColor,$FontName,$this->getEncodedText($Text));
      }

     $C_TextColor = $this->AllocateColor($this->Picture,$R,$G,$B,$Alpha);
     imagettftext($this->Picture,$FontSize,$Angle,$X,$Y,$C_TextColor,$FontName,$this->getEncodedText($Text));

     $this->Shadow = $Shadow;

     return($TxtPos);
    }

     function getEncodedText($text)
     {
         $gdinfo = gd_info();
         if (!empty($gdinfo['JIS-mapped Japanese Font Support'])) {
             return mb_convert_encoding($text, "SJIS", "UTF-8");
         }

         return $text;
     }

   /* Draw a gradient within a defined area */
   function drawGradientArea($X1,$Y1,$X2,$Y2,$Direction,$Format="")
    {
     $StartR	= isset($Format["StartR"]) ? $Format["StartR"] : 90;
     $StartG	= isset($Format["StartG"]) ? $Format["StartG"] : 90;
     $StartB	= isset($Format["StartB"]) ? $Format["StartB"] : 90;
     $EndR	= isset($Format["EndR"]) ? $Format["EndR"] : 0;
     $EndG	= isset($Format["EndG"]) ? $Format["EndG"] : 0;
     $EndB	= isset($Format["EndB"]) ? $Format["EndB"] : 0;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Levels	= isset($Format["Levels"]) ? $Format["Levels"] : NULL;

     $Shadow = $this->Shadow;
     $this->Shadow = FALSE;

     if ( $StartR == $EndR && $StartG == $EndG && $StartB == $EndB )
      {
       $this->drawFilledRectangle($X1,$Y1,$X2,$Y2,array("R"=>$StartR,"G"=>$StartG,"B"=>$StartB,"Alpha"=>$Alpha));
       return(0);
      }

     if ( $Levels != NULL )
      { $EndR=$StartR+$Levels; $EndG=$StartG+$Levels; $EndB=$StartB+$Levels; }

     if ($X1 > $X2) { list($X1, $X2) = array($X2, $X1); }
     if ($Y1 > $Y2) { list($Y1, $Y2) = array($Y2, $Y1); }

     if ( $Direction == DIRECTION_VERTICAL )   { $Width = abs($Y2-$Y1); }
     if ( $Direction == DIRECTION_HORIZONTAL ) { $Width = abs($X2-$X1); }

     $Step     = max(abs($EndR-$StartR),abs($EndG-$StartG),abs($EndB-$StartB));
     $StepSize = $Width/$Step;
     $RStep    = ($EndR-$StartR)/$Step;
     $GStep    = ($EndG-$StartG)/$Step;
     $BStep    = ($EndB-$StartB)/$Step;

     $R=$StartR;$G=$StartG;$B=$StartB;
     switch($Direction)
      {
       case DIRECTION_VERTICAL:
        $StartY = $Y1; $EndY = floor($Y2)+1; $LastY2 = $StartY;
        for($i=0;$i<=$Step;$i++)
         {
          $Y2 = floor($StartY + ($i * $StepSize));

          if ($Y2 > $EndY) { $Y2 = $EndY; }
          if (($Y1 != $Y2 && $Y1 < $Y2) || $Y2 == $EndY)
           {
            $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha);
            $this->drawFilledRectangle($X1,$Y1,$X2,$Y2,$Color);
            $LastY2 = max($LastY2,$Y2);
            $Y1 = $Y2+1;
           }
          $R = $R + $RStep; $G = $G + $GStep; $B = $B + $BStep;
         }
        if ( $LastY2 < $EndY && isset($Color)) { for ($i=$LastY2+1;$i<=$EndY;$i++) { $this->drawLine($X1,$i,$X2,$i,$Color); } }
        break;

       case DIRECTION_HORIZONTAL:
        $StartX = $X1; $EndX = $X2;
        for($i=0;$i<=$Step;$i++)
         {
          $X2 = floor($StartX + ($i * $StepSize));

          if ($X2 > $EndX) { $X2 = $EndX; }
          if (($X1 != $X2 && $X1 < $X2) || $X2 == $EndX)
           {
            $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha);
            $this->drawFilledRectangle($X1,$Y1,$X2,$Y2,$Color);
            $X1 = $X2+1;
           }
          $R = $R + $RStep; $G = $G + $GStep; $B = $B + $BStep; 
         }
        if ( $X2 < $EndX && isset($Color)) { $this->drawFilledRectangle($X2,$Y1,$EndX,$Y2,$Color); }
        break;
      }

     $this->Shadow = $Shadow;

    }

   /* Draw an aliased pixel */
   function drawAntialiasPixel($X,$Y,$Format="")
    {
     $R     = isset($Format["R"]) ? $Format["R"] : 0;
     $G     = isset($Format["G"]) ? $Format["G"] : 0;
     $B     = isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha = isset($Format["Alpha"]) ? $Format["Alpha"] : 100;

     if ( $X < 0 || $Y < 0 || $X >= $this->XSize || $Y >= $this->YSize )
      return(-1);

     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     if ( !$this->Antialias )
      {
       if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
        {
         $ShadowColor = $this->allocateColor($this->Picture,$this->ShadowR,$this->ShadowG,$this->ShadowB,$this->Shadowa);
         imagesetpixel($this->Picture,$X+$this->ShadowX,$Y+$this->ShadowY,$ShadowColor);
        }

       $PlotColor = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
       imagesetpixel($this->Picture,$X,$Y,$PlotColor);

       return(0);
      }

     $Plot = "";
     $Xi   = floor($X);
     $Yi   = floor($Y);

     if ( $Xi == $X && $Yi == $Y)
      {
       if ( $Alpha == 100 )
        $this->drawAlphaPixel($X,$Y,100,$R,$G,$B);
       else
        $this->drawAlphaPixel($X,$Y,$Alpha,$R,$G,$B);
      }
     else
      {
       $Alpha1 = (((1 - ($X - floor($X))) * (1 - ($Y - floor($Y))) * 100) / 100) * $Alpha;
       if ( $Alpha1 > $this->AntialiasQuality ) { $this->drawAlphaPixel($Xi,$Yi,$Alpha1,$R,$G,$B); }

       $Alpha2 = ((($X - floor($X)) * (1 - ($Y - floor($Y))) * 100) / 100) * $Alpha;
       if ( $Alpha2 > $this->AntialiasQuality ) { $this->drawAlphaPixel($Xi+1,$Yi,$Alpha2,$R,$G,$B); }

       $Alpha3 = (((1 - ($X - floor($X))) * ($Y - floor($Y)) * 100) / 100) * $Alpha;
       if ( $Alpha3 > $this->AntialiasQuality ) { $this->drawAlphaPixel($Xi,$Yi+1,$Alpha3,$R,$G,$B); }

       $Alpha4 = ((($X - floor($X)) * ($Y - floor($Y)) * 100) / 100) * $Alpha;
       if ( $Alpha4 > $this->AntialiasQuality ) { $this->drawAlphaPixel($Xi+1,$Yi+1,$Alpha4,$R,$G,$B); }
      }
    }

   /* Draw a semi-transparent pixel */
   function drawAlphaPixel($X,$Y,$Alpha,$R,$G,$B)
    {
     if ( isset($this->Mask[$X])) { if ( isset($this->Mask[$X][$Y]) ) { return(0); } }

     if ( $X < 0 || $Y < 0 || $X >= $this->XSize || $Y >= $this->YSize )
      return(-1);

     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
      {
       $AlphaFactor = floor(($Alpha / 100) * $this->Shadowa);
       $ShadowColor = $this->allocateColor($this->Picture,$this->ShadowR,$this->ShadowG,$this->ShadowB,$AlphaFactor);
       imagesetpixel($this->Picture,$X+$this->ShadowX,$Y+$this->ShadowY,$ShadowColor);
      }

     $C_Aliased = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
     imagesetpixel($this->Picture,$X,$Y,$C_Aliased);
    }

   /* Convert apha to base 10 */
   function convertAlpha($AlphaValue)
    { return((127/100)*(100-$AlphaValue)); }

   /* Allocate a color with transparency */
   function allocateColor($Picture,$R,$G,$B,$Alpha=100)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }
     if ( $Alpha < 0 )  { $Alpha = 0; }
     if ( $Alpha > 100) { $Alpha = 100; }

     $Alpha = $this->convertAlpha($Alpha);
     return(imagecolorallocatealpha($Picture,$R,$G,$B,$Alpha));
    }

   /* Load a PNG file and draw it over the chart */
   function drawFromPNG($X,$Y,$FileName)
    { $this->drawFromPicture(1,$FileName,$X,$Y); }

   /* Load a GIF file and draw it over the chart */
   function drawFromGIF($X,$Y,$FileName)
    { $this->drawFromPicture(2,$FileName,$X,$Y); }

   /* Load a JPEG file and draw it over the chart */
   function drawFromJPG($X,$Y,$FileName)
    { $this->drawFromPicture(3,$FileName,$X,$Y); }

   function getPicInfo($FileName)
    {
     $Infos  = getimagesize($FileName);
     $Width  = $Infos[0];
     $Height = $Infos[1];
     $Type   = $Infos["mime"];

     if ( $Type == "image/png") { $Type = 1; }
     if ( $Type == "image/gif") { $Type = 2; }
     if ( $Type == "image/jpeg ") { $Type = 3; }

     return(array($Width,$Height,$Type));
    }

   /* Generic loader function for external pictures */
   function drawFromPicture($PicType,$FileName,$X,$Y)
    {
     if ( file_exists($FileName))
      {
       list($Width,$Height) = $this->getPicInfo($FileName);

       if ( $PicType == 1 )
        { $Raster = imagecreatefrompng($FileName); }
       elseif ( $PicType == 2 )
        { $Raster = imagecreatefromgif($FileName); }
       elseif ( $PicType == 3 )
        { $Raster = imagecreatefromjpeg($FileName); }
       else
        { return(0); }


       $RestoreShadow = $this->Shadow;
       if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
        {
         $this->Shadow = FALSE;
         if ( $PicType == 3 )
          $this->drawFilledRectangle($X+$this->ShadowX,$Y+$this->ShadowY,$X+$Width+$this->ShadowX,$Y+$Height+$this->ShadowY,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa));
         else
          {
           $TranparentID = imagecolortransparent($Raster);
           for ($Xc=0;$Xc<=$Width-1;$Xc++)
            {
             for ($Yc=0;$Yc<=$Height-1;$Yc++)
              {
               $RGBa   = imagecolorat($Raster,$Xc,$Yc);
               $Values = imagecolorsforindex($Raster,$RGBa);
               if ( $Values["alpha"] < 120 )
                {
                 $AlphaFactor = floor(($this->Shadowa / 100) * ((100 / 127) * (127-$Values["alpha"])));
                 $this->drawAlphaPixel($X+$Xc+$this->ShadowX,$Y+$Yc+$this->ShadowY,$AlphaFactor,$this->ShadowR,$this->ShadowG,$this->ShadowB);
                }
              }
            }
          }
        }
       $this->Shadow = $RestoreShadow;

       imagecopy($this->Picture,$Raster,$X,$Y,0,0,$Width,$Height);
       imagedestroy($Raster);
      }
    }

   /* Draw an arrow */
   function drawArrow($X1,$Y1,$X2,$Y2,$Format="")
    {
     $FillR	= isset($Format["FillR"]) ? $Format["FillR"] : 0;
     $FillG	= isset($Format["FillG"]) ? $Format["FillG"] : 0;
     $FillB	= isset($Format["FillB"]) ? $Format["FillB"] : 0;
     $BorderR	= isset($Format["BorderR"]) ? $Format["BorderR"] : $FillR;
     $BorderG	= isset($Format["BorderG"]) ? $Format["BorderG"] : $FillG;
     $BorderB	= isset($Format["BorderB"]) ? $Format["BorderB"] : $FillB;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Size	= isset($Format["Size"]) ? $Format["Size"] : 10;
     $Ratio	= isset($Format["Ratio"]) ? $Format["Ratio"] : .5;
     $TwoHeads	= isset($Format["TwoHeads"]) ? $Format["TwoHeads"] : FALSE;
     $Ticks	= isset($Format["Ticks"]) ? $Format["Ticks"] : FALSE;

     /* Calculate the line angle */
     $Angle = $this->getAngle($X1,$Y1,$X2,$Y2);

     /* Override Shadow support, this will be managed internally */
     $RestoreShadow = $this->Shadow;
     if ( $this->Shadow && $this->ShadowX != 0 && $this->ShadowY != 0 )
      {
       $this->Shadow = FALSE;
       $this->drawArrow($X1+$this->ShadowX,$Y1+$this->ShadowY,$X2+$this->ShadowX,$Y2+$this->ShadowY,array("FillR"=>$this->ShadowR,"FillG"=>$this->ShadowG,"FillB"=>$this->ShadowB,"Alpha"=>$this->Shadowa,"Size"=>$Size,"Ratio"=>$Ratio,"TwoHeads"=>$TwoHeads,"Ticks"=>$Ticks));
      }

     /* Draw the 1st Head */
     $TailX = cos(($Angle-180)*PI/180)*$Size+$X2;
     $TailY = sin(($Angle-180)*PI/180)*$Size+$Y2;

     $Points	= "";
     $Points[]  = $X2; $Points[]  = $Y2;
     $Points[]	= cos(($Angle-90)*PI/180)*$Size*$Ratio+$TailX; $Points[] = sin(($Angle-90)*PI/180)*$Size*$Ratio+$TailY;
     $Points[]	= cos(($Angle-270)*PI/180)*$Size*$Ratio+$TailX; $Points[] = sin(($Angle-270)*PI/180)*$Size*$Ratio+$TailY;
     $Points[]  = $X2; $Points[]  = $Y2;

     /* Visual correction */
     if ($Angle == 180 || $Angle == 360 ) { $Points[4] = $Points[2]; }
     if ($Angle == 90 || $Angle == 270 ) { $Points[5] = $Points[3]; }

     $ArrowColor = $this->allocateColor($this->Picture,$FillR,$FillG,$FillB,$Alpha);
     ImageFilledPolygon($this->Picture,$Points,4,$ArrowColor);

     $this->drawLine($Points[0],$Points[1],$Points[2],$Points[3],array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));
     $this->drawLine($Points[2],$Points[3],$Points[4],$Points[5],array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));
     $this->drawLine($Points[0],$Points[1],$Points[4],$Points[5],array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));

     /* Draw the second head */
     if ( $TwoHeads )
      {
       $Angle = $this->getAngle($X2,$Y2,$X1,$Y1);

       $TailX2 = cos(($Angle-180)*PI/180)*$Size+$X1;
       $TailY2 = sin(($Angle-180)*PI/180)*$Size+$Y1;

       $Points   = "";
       $Points[] = $X1; $Points[]  = $Y1;
       $Points[] = cos(($Angle-90)*PI/180)*$Size*$Ratio+$TailX2; $Points[] = sin(($Angle-90)*PI/180)*$Size*$Ratio+$TailY2;
       $Points[] = cos(($Angle-270)*PI/180)*$Size*$Ratio+$TailX2; $Points[] = sin(($Angle-270)*PI/180)*$Size*$Ratio+$TailY2;
       $Points[] = $X1; $Points[]  = $Y1;

       /* Visual correction */
       if ($Angle == 180 || $Angle == 360 ) { $Points[4] = $Points[2]; }
       if ($Angle == 90 || $Angle == 270 ) { $Points[5] = $Points[3]; }

       $ArrowColor = $this->allocateColor($this->Picture,$FillR,$FillG,$FillB,$Alpha);
       ImageFilledPolygon($this->Picture,$Points,4,$ArrowColor);

       $this->drawLine($Points[0],$Points[1],$Points[2],$Points[3],array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));
       $this->drawLine($Points[2],$Points[3],$Points[4],$Points[5],array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));
       $this->drawLine($Points[0],$Points[1],$Points[4],$Points[5],array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));

       $this->drawLine($TailX,$TailY,$TailX2,$TailY2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
      }
     else
      $this->drawLine($X1,$Y1,$TailX,$TailY,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha,"Ticks"=>$Ticks));

     /* Re-enable shadows */
     $this->Shadow = $RestoreShadow;
    }

   /* Draw a label with associated arrow */
   function drawArrowLabel($X1,$Y1,$Text,$Format="")
    {
     $FillR    = isset($Format["FillR"]) ? $Format["FillR"] : 0;
     $FillG    = isset($Format["FillG"]) ? $Format["FillG"] : 0;
     $FillB    = isset($Format["FillB"]) ? $Format["FillB"] : 0;
     $BorderR  = isset($Format["BorderR"]) ? $Format["BorderR"] : $FillR;
     $BorderG  = isset($Format["BorderG"]) ? $Format["BorderG"] : $FillG;
     $BorderB  = isset($Format["BorderB"]) ? $Format["BorderB"] : $FillB;
     $FontName = isset($Format["FontName"]) ? $Format["FontName"] : $this->FontName;
     $FontSize = isset($Format["FontSize"]) ? $Format["FontSize"] : $this->FontSize;
     $Alpha    = isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Length   = isset($Format["Length"]) ? $Format["Length"] : 50;
     $Angle    = isset($Format["Angle"]) ? $Format["Angle"] : 315;
     $Size     = isset($Format["Size"]) ? $Format["Size"] : 10;
     $Position = isset($Format["Position"]) ? $Format["Position"] : POSITION_TOP;
     $RoundPos = isset($Format["RoundPos"]) ? $Format["RoundPos"] : FALSE;
     $Ticks    = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;

     $Angle = $Angle % 360;

     $X2 = sin(($Angle+180)*PI/180)*$Length+$X1;
     $Y2 = cos(($Angle+180)*PI/180)*$Length+$Y1;

     if ( $RoundPos && $Angle > 0 && $Angle < 180 ) { $Y2 = ceil($Y2); }
     if ( $RoundPos && $Angle > 180 ) { $Y2 = floor($Y2); }

     $this->drawArrow($X2,$Y2,$X1,$Y1,$Format);

     $Size	= imagettfbbox($FontSize,0,$FontName,$this->getEncodedText($Text));
     $TxtWidth	= max(abs($Size[2]-$Size[0]),abs($Size[0]-$Size[6]));
     $TxtHeight	= max(abs($Size[1]-$Size[7]),abs($Size[3]-$Size[1]));

     if ( $Angle > 0 && $Angle < 180 )
      {
       $this->drawLine($X2,$Y2,$X2-$TxtWidth,$Y2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
       if ( $Position == POSITION_TOP )
        $this->drawText($X2,$Y2-2,$Text,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha,"Align"=>TEXT_ALIGN_BOTTOMRIGHT));
       else
        $this->drawText($X2,$Y2+4,$Text,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha,"Align"=>TEXT_ALIGN_TOPRIGHT));
      }
     else
      {
       $this->drawLine($X2,$Y2,$X2+$TxtWidth,$Y2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
       if ( $Position == POSITION_TOP )
        $this->drawText($X2,$Y2-2,$Text,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha));
       else
        $this->drawText($X2,$Y2+4,$Text,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$Alpha,"Align"=>TEXT_ALIGN_TOPLEFT));
      }
    }

   /* Draw a progress bar filled with specified % */
   function drawProgress($X,$Y,$Percent,$Format="")
    {
     if ( $Percent > 100 ) { $Percent = 100; }
     if ( $Percent < 0 ) { $Percent = 0; }

     $Width	= isset($Format["Width"]) ? $Format["Width"] : 200;
     $Height	= isset($Format["Height"]) ? $Format["Height"] : 20;
     $Orientation = isset($Format["Orientation"]) ? $Format["Orientation"] : ORIENTATION_HORIZONTAL;
     $ShowLabel = isset($Format["ShowLabel"]) ? $Format["ShowLabel"] : FALSE;
     $LabelPos	= isset($Format["LabelPos"]) ? $Format["LabelPos"] : LABEL_POS_INSIDE;
     $Margin    = isset($Format["Margin"]) ? $Format["Margin"] : 10;
     $R		= isset($Format["R"]) ? $Format["R"] : 130;
     $G		= isset($Format["G"]) ? $Format["G"] : 130;
     $B		= isset($Format["B"]) ? $Format["B"] : 130;
     $RFade	= isset($Format["RFade"]) ? $Format["RFade"] : -1;
     $GFade	= isset($Format["GFade"]) ? $Format["GFade"] : -1;
     $BFade	= isset($Format["BFade"]) ? $Format["BFade"] : -1;
     $BorderR	= isset($Format["BorderR"]) ? $Format["BorderR"] : $R;
     $BorderG	= isset($Format["BorderG"]) ? $Format["BorderG"] : $G;
     $BorderB	= isset($Format["BorderB"]) ? $Format["BorderB"] : $B;
     $BoxBorderR = isset($Format["BoxBorderR"]) ? $Format["BoxBorderR"] : 0;
     $BoxBorderG = isset($Format["BoxBorderG"]) ? $Format["BoxBorderG"] : 0;
     $BoxBorderB = isset($Format["BoxBorderB"]) ? $Format["BoxBorderB"] : 0;
     $BoxBackR	= isset($Format["BoxBackR"]) ? $Format["BoxBackR"] : 255;
     $BoxBackG	= isset($Format["BoxBackG"]) ? $Format["BoxBackG"] : 255;
     $BoxBackB	= isset($Format["BoxBackB"]) ? $Format["BoxBackB"] : 255;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $Surrounding = isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $BoxSurrounding = isset($Format["BoxSurrounding"]) ? $Format["BoxSurrounding"] : NULL;
     $NoAngle	= isset($Format["NoAngle"]) ? $Format["NoAngle"] : FALSE;

     if ( $RFade != -1 && $GFade != -1 && $BFade != -1 )
      {
       $RFade = (($RFade-$R)/100)*$Percent+$R;
       $GFade = (($GFade-$G)/100)*$Percent+$G;
       $BFade = (($BFade-$B)/100)*$Percent+$B;
      }

     if ( $Surrounding != NULL ) { $BorderR = $R + $Surrounding; $BorderG = $G + $Surrounding; $BorderB = $B + $Surrounding; }
     if ( $BoxSurrounding != NULL ) { $BoxBorderR = $BoxBackR + $Surrounding; $BoxBorderG = $BoxBackG + $Surrounding; $BoxBorderB = $BoxBackB + $Surrounding; }

     if ( $Orientation == ORIENTATION_VERTICAL )
      {
       $InnerHeight = (($Height-2)/100)*$Percent;
       $this->drawFilledRectangle($X,$Y,$X+$Width,$Y-$Height,array("R"=>$BoxBackR,"G"=>$BoxBackG,"B"=>$BoxBackB,"BorderR"=>$BoxBorderR,"BorderG"=>$BoxBorderG,"BorderB"=>$BoxBorderB,"NoAngle"=>$NoAngle));

       $RestoreShadow = $this->Shadow; $this->Shadow  = FALSE;
       if ( $RFade != -1 && $GFade != -1 && $BFade != -1 )
        {
         $GradientOptions = array("StartR"=>$RFade,"StartG"=>$GFade,"StartB"=>$BFade,"EndR"=>$R,"EndG"=>$G,"EndB"=>$B);
         $this->drawGradientArea($X+1,$Y-1,$X+$Width-1,$Y-$InnerHeight,DIRECTION_VERTICAL,$GradientOptions);

         if ( $Surrounding )
          $this->drawRectangle($X+1,$Y-1,$X+$Width-1,$Y-$InnerHeight,array("R"=>255,"G"=>255,"B"=>255,"Alpha"=>$Surrounding));
        }
       else
        $this->drawFilledRectangle($X+1,$Y-1,$X+$Width-1,$Y-$InnerHeight,array("R"=>$R,"G"=>$G,"B"=>$B,"BorderR"=>$BorderR,"BorderG"=>$BorderG,"BorderB"=>$BorderB));

       $this->Shadow = $RestoreShadow;

       if ( $ShowLabel && $LabelPos == LABEL_POS_BOTTOM )	{ $this->drawText($X+($Width/2),$Y+$Margin,$Percent."%",array("Align"=>TEXT_ALIGN_TOPMIDDLE)); }
       if ( $ShowLabel && $LabelPos == LABEL_POS_TOP )		{ $this->drawText($X+($Width/2),$Y-$Height-$Margin,$Percent."%",array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); }
       if ( $ShowLabel && $LabelPos == LABEL_POS_INSIDE )	{ $this->drawText($X+($Width/2),$Y-$InnerHeight-$Margin,$Percent."%",array("Align"=>TEXT_ALIGN_MIDDLELEFT,"Angle"=>90)); }
       if ( $ShowLabel && $LabelPos == LABEL_POS_CENTER )	{ $this->drawText($X+($Width/2),$Y-($Height/2),$Percent."%",array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"Angle"=>90)); }
      }
     else
      {
       if ( $Percent == 100 )
        $InnerWidth = $Width-1;
       else
        $InnerWidth = (($Width-2)/100)*$Percent;

       $this->drawFilledRectangle($X,$Y,$X+$Width,$Y+$Height,array("R"=>$BoxBackR,"G"=>$BoxBackG,"B"=>$BoxBackB,"BorderR"=>$BoxBorderR,"BorderG"=>$BoxBorderG,"BorderB"=>$BoxBorderB,"NoAngle"=>$NoAngle));

       $RestoreShadow = $this->Shadow; $this->Shadow  = FALSE;
       if ( $RFade != -1 && $GFade != -1 && $BFade != -1 )
        {
         $GradientOptions = array("StartR"=>$R,"StartG"=>$G,"StartB"=>$B,"EndR"=>$RFade,"EndG"=>$GFade,"EndB"=>$BFade);
         $this->drawGradientArea($X+1,$Y+1,$X+$InnerWidth,$Y+$Height-1,DIRECTION_HORIZONTAL,$GradientOptions);

         if ( $Surrounding )
          $this->drawRectangle($X+1,$Y+1,$X+$InnerWidth,$Y+$Height-1,array("R"=>255,"G"=>255,"B"=>255,"Alpha"=>$Surrounding));
        }
       else
        $this->drawFilledRectangle($X+1,$Y+1,$X+$InnerWidth,$Y+$Height-1,array("R"=>$R,"G"=>$G,"B"=>$B,"BorderR"=>$BorderR,"BorderG"=>$BorderG,"BorderB"=>$BorderB));
 
       $this->Shadow = $RestoreShadow;

       if ( $ShowLabel && $LabelPos == LABEL_POS_LEFT )		{ $this->drawText($X-$Margin,$Y+($Height/2),$Percent."%",array("Align"=>TEXT_ALIGN_MIDDLERIGHT)); }
       if ( $ShowLabel && $LabelPos == LABEL_POS_RIGHT )	{ $this->drawText($X+$Width+$Margin,$Y+($Height/2),$Percent."%",array("Align"=>TEXT_ALIGN_MIDDLELEFT)); }
       if ( $ShowLabel && $LabelPos == LABEL_POS_CENTER )	{ $this->drawText($X+($Width/2),$Y+($Height/2),$Percent."%",array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE)); }
       if ( $ShowLabel && $LabelPos == LABEL_POS_INSIDE )	{ $this->drawText($X+$InnerWidth+$Margin,$Y+($Height/2),$Percent."%",array("Align"=>TEXT_ALIGN_MIDDLELEFT)); }
      }
    }

   /* Get the legend box size */
   function getLegendSize($Format="")
    {
     $FontName		= isset($Format["FontName"]) ? $Format["FontName"] : $this->FontName;
     $FontSize		= isset($Format["FontSize"]) ? $Format["FontSize"] : $this->FontSize;
     $BoxSize		= isset($Format["BoxSize"]) ? $Format["BoxSize"] : 5;
     $Margin		= isset($Format["Margin"]) ? $Format["Margin"] : 5;
     $Style		= isset($Format["Style"]) ? $Format["Style"] : LEGEND_ROUND;
     $Mode		= isset($Format["Mode"]) ? $Format["Mode"] : LEGEND_VERTICAL;
     $BoxWidth		= isset($Format["BoxWidth"]) ? $Format["BoxWidth"] : 5;
     $BoxHeight		= isset($Format["BoxHeight"]) ? $Format["BoxHeight"] : 5;
     $IconAreaWidth	= isset($Format["IconAreaWidth"]) ? $Format["IconAreaWidth"] : $BoxWidth;
     $IconAreaHeight	= isset($Format["IconAreaHeight"]) ? $Format["IconAreaHeight"] : $BoxHeight;
     $XSpacing		= isset($Format["XSpacing"]) ? $Format["XSpacing"] : 5;

     $Data = $this->DataSet->getData();

     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] && isset($Serie["Picture"]))
        {
         list($PicWidth,$PicHeight) = $this->getPicInfo($Serie["Picture"]);
         if ( $IconAreaWidth < $PicWidth ) { $IconAreaWidth = $PicWidth; }
         if ( $IconAreaHeight < $PicHeight ) { $IconAreaHeight = $PicHeight; }
        }
      }

     $YStep = max($this->FontSize,$IconAreaHeight) + 5;
     $XStep = $IconAreaWidth + 5;
     $XStep = $XSpacing;

     $X=100; $Y=100;

     $Boundaries = ""; $Boundaries["L"] = $X; $Boundaries["T"] = $Y; $Boundaries["R"] = 0; $Boundaries["B"] = 0; $vY = $Y; $vX = $X;
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         if ( $Mode == LEGEND_VERTICAL )
          {
           $BoxArray = $this->getTextBox($vX+$IconAreaWidth+4,$vY+$IconAreaHeight/2,$FontName,$FontSize,0,$Serie["Description"]);

           if ( $Boundaries["T"] > $BoxArray[2]["Y"]+$IconAreaHeight/2 ) { $Boundaries["T"] = $BoxArray[2]["Y"]+$IconAreaHeight/2; }
           if ( $Boundaries["R"] < $BoxArray[1]["X"]+2 ) { $Boundaries["R"] = $BoxArray[1]["X"]+2; }
           if ( $Boundaries["B"] < $BoxArray[1]["Y"]+2+$IconAreaHeight/2 ) { $Boundaries["B"] = $BoxArray[1]["Y"]+2+$IconAreaHeight/2; }

           $Lines = preg_split("/\n/",$Serie["Description"]);
           $vY = $vY + max($this->FontSize*count($Lines),$IconAreaHeight) + 5;
          }
         elseif ( $Mode == LEGEND_HORIZONTAL )
          {
           $Lines = preg_split("/\n/",$Serie["Description"]);
           $Width = "";
           foreach($Lines as $Key => $Value)
            {
             $BoxArray = $this->getTextBox($vX+$IconAreaWidth+6,$Y+$IconAreaHeight/2+(($this->FontSize+3)*$Key),$FontName,$FontSize,0,$Value);

             if ( $Boundaries["T"] > $BoxArray[2]["Y"]+$IconAreaHeight/2 ) { $Boundaries["T"] = $BoxArray[2]["Y"]+$IconAreaHeight/2; }
             if ( $Boundaries["R"] < $BoxArray[1]["X"]+2 ) { $Boundaries["R"] = $BoxArray[1]["X"]+2; }
             if ( $Boundaries["B"] < $BoxArray[1]["Y"]+2+$IconAreaHeight/2 ) { $Boundaries["B"] = $BoxArray[1]["Y"]+2+$IconAreaHeight/2; }

             $Width[] = $BoxArray[1]["X"];
            }

           $vX=max($Width)+$XStep;
          }
        }
      }
     $vY=$vY-$YStep; $vX=$vX-$XStep;

     $TopOffset  = $Y - $Boundaries["T"];
     if ( $Boundaries["B"]-($vY+$IconAreaHeight) < $TopOffset ) { $Boundaries["B"] = $vY+$IconAreaHeight+$TopOffset; }

     $Width  = ($Boundaries["R"]+$Margin) - ($Boundaries["L"]-$Margin);
     $Height = ($Boundaries["B"]+$Margin) - ($Boundaries["T"]-$Margin);

     return(array("Width"=>$Width,"Height"=>$Height));
    }

   /* Draw the legend of the active series */
   function drawLegend($X,$Y,$Format="")
    {
     $Family	= isset($Format["Family"]) ? $Format["Family"] : LEGEND_FAMILY_BOX;
     $FontName	= isset($Format["FontName"]) ? $Format["FontName"] : $this->FontName;
     $FontSize	= isset($Format["FontSize"]) ? $Format["FontSize"] : $this->FontSize;
     $FontR	= isset($Format["FontR"]) ? $Format["FontR"] : $this->FontColorR;
     $FontG	= isset($Format["FontG"]) ? $Format["FontG"] : $this->FontColorG;
     $FontB	= isset($Format["FontB"]) ? $Format["FontB"] : $this->FontColorB;
     $BoxWidth	= isset($Format["BoxWidth"]) ? $Format["BoxWidth"] : 5;
     $BoxHeight	= isset($Format["BoxHeight"]) ? $Format["BoxHeight"] : 5;
     $IconAreaWidth	= isset($Format["IconAreaWidth"]) ? $Format["IconAreaWidth"] : $BoxWidth;
     $IconAreaHeight	= isset($Format["IconAreaHeight"]) ? $Format["IconAreaHeight"] : $BoxHeight;
     $XSpacing	= isset($Format["XSpacing"]) ? $Format["XSpacing"] : 5;
     $Margin	= isset($Format["Margin"]) ? $Format["Margin"] : 5;
     $R		= isset($Format["R"]) ? $Format["R"] : 200;
     $G		= isset($Format["G"]) ? $Format["G"] : 200;
     $B		= isset($Format["B"]) ? $Format["B"] : 200;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $BorderR	= isset($Format["BorderR"]) ? $Format["BorderR"] : 255;
     $BorderG	= isset($Format["BorderG"]) ? $Format["BorderG"] : 255;
     $BorderB	= isset($Format["BorderB"]) ? $Format["BorderB"] : 255;
     $Surrounding = isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $Style	= isset($Format["Style"]) ? $Format["Style"] : LEGEND_ROUND;
     $Mode	= isset($Format["Mode"]) ? $Format["Mode"] : LEGEND_VERTICAL;

     if ( $Surrounding != NULL ) { $BorderR = $R + $Surrounding; $BorderG = $G + $Surrounding; $BorderB = $B + $Surrounding; }

     $Data = $this->DataSet->getData();

     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] && isset($Serie["Picture"]))
        {
         list($PicWidth,$PicHeight) = $this->getPicInfo($Serie["Picture"]);
         if ( $IconAreaWidth < $PicWidth ) { $IconAreaWidth = $PicWidth; }
         if ( $IconAreaHeight < $PicHeight ) { $IconAreaHeight = $PicHeight; }
        }
      }

     $YStep = max($this->FontSize,$IconAreaHeight) + 5;
     $XStep = $IconAreaWidth + 5;
     $XStep = $XSpacing;

     $Boundaries = ""; $Boundaries["L"] = $X; $Boundaries["T"] = $Y; $Boundaries["R"] = 0; $Boundaries["B"] = 0; $vY = $Y; $vX = $X;
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         if ( $Mode == LEGEND_VERTICAL )
          {
           $BoxArray = $this->getTextBox($vX+$IconAreaWidth+4,$vY+$IconAreaHeight/2,$FontName,$FontSize,0,$Serie["Description"]);

           if ( $Boundaries["T"] > $BoxArray[2]["Y"]+$IconAreaHeight/2 ) { $Boundaries["T"] = $BoxArray[2]["Y"]+$IconAreaHeight/2; }
           if ( $Boundaries["R"] < $BoxArray[1]["X"]+2 ) { $Boundaries["R"] = $BoxArray[1]["X"]+2; }
           if ( $Boundaries["B"] < $BoxArray[1]["Y"]+2+$IconAreaHeight/2 ) { $Boundaries["B"] = $BoxArray[1]["Y"]+2+$IconAreaHeight/2; }

           $Lines = preg_split("/\n/",$Serie["Description"]);
           $vY = $vY + max($this->FontSize*count($Lines),$IconAreaHeight) + 5;
          }
         elseif ( $Mode == LEGEND_HORIZONTAL )
          {
           $Lines = preg_split("/\n/",$Serie["Description"]);
           $Width = "";
           foreach($Lines as $Key => $Value)
            {
             $BoxArray = $this->getTextBox($vX+$IconAreaWidth+6,$Y+$IconAreaHeight/2+(($this->FontSize+3)*$Key),$FontName,$FontSize,0,$Value);

             if ( $Boundaries["T"] > $BoxArray[2]["Y"]+$IconAreaHeight/2 ) { $Boundaries["T"] = $BoxArray[2]["Y"]+$IconAreaHeight/2; }
             if ( $Boundaries["R"] < $BoxArray[1]["X"]+2 ) { $Boundaries["R"] = $BoxArray[1]["X"]+2; }
             if ( $Boundaries["B"] < $BoxArray[1]["Y"]+2+$IconAreaHeight/2 ) { $Boundaries["B"] = $BoxArray[1]["Y"]+2+$IconAreaHeight/2; }

             $Width[] = $BoxArray[1]["X"];
            }

           $vX=max($Width)+$XStep;
          }
        }
      }
     $vY=$vY-$YStep; $vX=$vX-$XStep;

     $TopOffset  = $Y - $Boundaries["T"];
     if ( $Boundaries["B"]-($vY+$IconAreaHeight) < $TopOffset ) { $Boundaries["B"] = $vY+$IconAreaHeight+$TopOffset; }

     if ( $Style == LEGEND_ROUND )
      $this->drawRoundedFilledRectangle($Boundaries["L"]-$Margin,$Boundaries["T"]-$Margin,$Boundaries["R"]+$Margin,$Boundaries["B"]+$Margin,$Margin,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"BorderR"=>$BorderR,"BorderG"=>$BorderG,"BorderB"=>$BorderB));
     elseif ( $Style == LEGEND_BOX )
      $this->drawFilledRectangle($Boundaries["L"]-$Margin,$Boundaries["T"]-$Margin,$Boundaries["R"]+$Margin,$Boundaries["B"]+$Margin,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"BorderR"=>$BorderR,"BorderG"=>$BorderG,"BorderB"=>$BorderB));

     $RestoreShadow = $this->Shadow; $this->Shadow = FALSE;
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"];
         $Ticks = $Serie["Ticks"]; $Weight = $Serie["Weight"];

         if ( isset($Serie["Picture"]) )
          {
           $Picture = $Serie["Picture"];
           list($PicWidth,$PicHeight) = $this->getPicInfo($Picture);
           $PicX = $X+$IconAreaWidth/2; $PicY = $Y+$IconAreaHeight/2; 

           $this->drawFromPNG($PicX-$PicWidth/2,$PicY-$PicHeight/2,$Picture);
          }
         else
          {
           if ( $Family == LEGEND_FAMILY_BOX )
            {
             if ( $BoxWidth != $IconAreaWidth ) { $XOffset = floor(($IconAreaWidth-$BoxWidth)/2); } else { $XOffset = 0; }
             if ( $BoxHeight != $IconAreaHeight ) { $YOffset = floor(($IconAreaHeight-$BoxHeight)/2); } else { $YOffset = 0; }

             $this->drawFilledRectangle($X+1+$XOffset,$Y+1+$YOffset,$X+$BoxWidth+$XOffset+1,$Y+$BoxHeight+1+$YOffset,array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
             $this->drawFilledRectangle($X+$XOffset,$Y+$YOffset,$X+$BoxWidth+$XOffset,$Y+$BoxHeight+$YOffset,array("R"=>$R,"G"=>$G,"B"=>$B,"Surrounding"=>20));
            }
           elseif ( $Family == LEGEND_FAMILY_CIRCLE )
            {
             $this->drawFilledCircle($X+1+$IconAreaWidth/2,$Y+1+$IconAreaHeight/2,min($IconAreaHeight/2,$IconAreaWidth/2),array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
             $this->drawFilledCircle($X+$IconAreaWidth/2,$Y+$IconAreaHeight/2,min($IconAreaHeight/2,$IconAreaWidth/2),array("R"=>$R,"G"=>$G,"B"=>$B,"Surrounding"=>20));
            }
           elseif ( $Family == LEGEND_FAMILY_LINE )
            {
             $this->drawLine($X+1,$Y+1+$IconAreaHeight/2,$X+1+$IconAreaWidth,$Y+1+$IconAreaHeight/2,array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>20,"Ticks"=>$Ticks,"Weight"=>$Weight));
             $this->drawLine($X,$Y+$IconAreaHeight/2,$X+$IconAreaWidth,$Y+$IconAreaHeight/2,array("R"=>$R,"G"=>$G,"B"=>$B,"Ticks"=>$Ticks,"Weight"=>$Weight));
            }
          }

         if ( $Mode == LEGEND_VERTICAL )
          {
           $Lines = preg_split("/\n/",$Serie["Description"]);
           foreach($Lines as $Key => $Value)
            $this->drawText($X+$IconAreaWidth+4,$Y+$IconAreaHeight/2+(($this->FontSize+3)*$Key),$Value,array("R"=>$FontR,"G"=>$FontG,"B"=>$FontB,"Align"=>TEXT_ALIGN_MIDDLELEFT,"FontSize"=>$FontSize,"FontName"=>$FontName));

           $Y=$Y+max($this->FontSize*count($Lines),$IconAreaHeight) + 5;
          }
         elseif ( $Mode == LEGEND_HORIZONTAL )
          {
           $Lines = preg_split("/\n/",$Serie["Description"]);
           $Width = "";
           foreach($Lines as $Key => $Value)
            {
             $BoxArray = $this->drawText($X+$IconAreaWidth+4,$Y+$IconAreaHeight/2+(($this->FontSize+3)*$Key),$Value,array("R"=>$FontR,"G"=>$FontG,"B"=>$FontB,"Align"=>TEXT_ALIGN_MIDDLELEFT,"FontSize"=>$FontSize,"FontName"=>$FontName));
             $Width[] = $BoxArray[1]["X"];
            }
           $X=max($Width)+2+$XStep;
          }
        }
      }


     $this->Shadow = $RestoreShadow;
    }

   function drawScale($Format="")
    {
     $Pos		= isset($Format["Pos"]) ? $Format["Pos"] : SCALE_POS_LEFTRIGHT;
     $Floating		= isset($Format["Floating"]) ? $Format["Floating"] : FALSE;
     $Mode		= isset($Format["Mode"]) ? $Format["Mode"] : SCALE_MODE_FLOATING;
     $RemoveXAxis	= isset($Format["RemoveXAxis"]) ? $Format["RemoveXAxis"] : FALSE;
     $MinDivHeight	= isset($Format["MinDivHeight"]) ? $Format["MinDivHeight"] : 20;
     $Factors		= isset($Format["Factors"]) ? $Format["Factors"] : array(1,2,5);
     $ManualScale	= isset($Format["ManualScale"]) ? $Format["ManualScale"] : array("0"=>array("Min"=>-100,"Max"=>100));
     $XMargin		= isset($Format["XMargin"]) ? $Format["XMargin"] : AUTO;
     $YMargin		= isset($Format["YMargin"]) ? $Format["YMargin"] : 0;
     $ScaleSpacing	= isset($Format["ScaleSpacing"]) ? $Format["ScaleSpacing"] : 15;
     $InnerTickWidth	= isset($Format["InnerTickWidth"]) ? $Format["InnerTickWidth"] : 2;
     $OuterTickWidth	= isset($Format["OuterTickWidth"]) ? $Format["OuterTickWidth"] : 2;
     $DrawXLines	= isset($Format["DrawXLines"]) ? $Format["DrawXLines"] : TRUE;
     $DrawYLines	= isset($Format["DrawYLines"]) ? $Format["DrawYLines"] : ALL;
     $GridTicks		= isset($Format["GridTicks"]) ? $Format["GridTicks"] : 4;
     $GridR		= isset($Format["GridR"]) ? $Format["GridR"] : 255;
     $GridG		= isset($Format["GridG"]) ? $Format["GridG"] : 255;
     $GridB		= isset($Format["GridB"]) ? $Format["GridB"] : 255;
     $GridAlpha		= isset($Format["GridAlpha"]) ? $Format["GridAlpha"] : 40;
     $AxisRo		= isset($Format["AxisR"]) ? $Format["AxisR"] : 0;
     $AxisGo		= isset($Format["AxisG"]) ? $Format["AxisG"] : 0;
     $AxisBo		= isset($Format["AxisB"]) ? $Format["AxisB"] : 0;
     $AxisAlpha		= isset($Format["AxisAlpha"]) ? $Format["AxisAlpha"] : 100;
     $TickRo		= isset($Format["TickR"]) ? $Format["TickR"] : 0;
     $TickGo		= isset($Format["TickG"]) ? $Format["TickG"] : 0;
     $TickBo		= isset($Format["TickB"]) ? $Format["TickB"] : 0;
     $TickAlpha		= isset($Format["TickAlpha"]) ? $Format["TickAlpha"] : 100;
     $DrawSubTicks	= isset($Format["DrawSubTicks"]) ? $Format["DrawSubTicks"] : FALSE;
     $InnerSubTickWidth	= isset($Format["InnerSubTickWidth"]) ? $Format["InnerSubTickWidth"] : 0;
     $OuterSubTickWidth	= isset($Format["OuterSubTickWidth"]) ? $Format["OuterSubTickWidth"] : 2;
     $SubTickR		= isset($Format["SubTickR"]) ? $Format["SubTickR"] : 255;
     $SubTickG		= isset($Format["SubTickG"]) ? $Format["SubTickG"] : 0;
     $SubTickB		= isset($Format["SubTickB"]) ? $Format["SubTickB"] : 0;
     $SubTickAlpha	= isset($Format["SubTickAlpha"]) ? $Format["SubTickAlpha"] : 100;
     $AutoAxisLabels	= isset($Format["AutoAxisLabels"]) ? $Format["AutoAxisLabels"] : TRUE;
     $XReleasePercent	= isset($Format["XReleasePercent"]) ? $Format["XReleasePercent"] : 1;
     $DrawArrows	= isset($Format["DrawArrows"]) ? $Format["DrawArrows"] : FALSE;
     $ArrowSize         = isset($Format["ArrowSize"]) ? $Format["ArrowSize"] : 8;
     $CycleBackground	= isset($Format["CycleBackground"]) ? $Format["CycleBackground"] : FALSE;
     $BackgroundR1	= isset($Format["BackgroundR1"]) ? $Format["BackgroundR1"] : 255;
     $BackgroundG1	= isset($Format["BackgroundG1"]) ? $Format["BackgroundG1"] : 255;
     $BackgroundB1	= isset($Format["BackgroundB1"]) ? $Format["BackgroundB1"] : 255;
     $BackgroundAlpha1	= isset($Format["BackgroundAlpha1"]) ? $Format["BackgroundAlpha1"] : 20;
     $BackgroundR2	= isset($Format["BackgroundR2"]) ? $Format["BackgroundR2"] : 230;
     $BackgroundG2	= isset($Format["BackgroundG2"]) ? $Format["BackgroundG2"] : 230;
     $BackgroundB2	= isset($Format["BackgroundB2"]) ? $Format["BackgroundB2"] : 230;
     $BackgroundAlpha2	= isset($Format["BackgroundAlpha2"]) ? $Format["BackgroundAlpha2"] : 20;
     $LabelingMethod	= isset($Format["LabelingMethod"]) ? $Format["LabelingMethod"] : LABELING_ALL;
     $LabelSkip		= isset($Format["LabelSkip"]) ? $Format["LabelSkip"] : 0;
     $LabelRotation	= isset($Format["LabelRotation"]) ? $Format["LabelRotation"] : 0;
     $RemoveSkippedAxis	= isset($Format["RemoveSkippedAxis"]) ? $Format["RemoveSkippedAxis"] : FALSE;
     $SkippedAxisTicks	= isset($Format["SkippedAxisTicks"]) ? $Format["SkippedAxisTicks"] : $GridTicks+2;
     $SkippedAxisR	= isset($Format["SkippedAxisR"]) ? $Format["SkippedAxisR"] : $GridR;
     $SkippedAxisG	= isset($Format["SkippedAxisG"]) ? $Format["SkippedAxisG"] : $GridG;
     $SkippedAxisB	= isset($Format["SkippedAxisB"]) ? $Format["SkippedAxisB"] : $GridB;
     $SkippedAxisAlpha	= isset($Format["SkippedAxisAlpha"]) ? $Format["SkippedAxisAlpha"] : $GridAlpha-30;
     $SkippedTickR	= isset($Format["SkippedTickR"]) ? $Format["SkippedTickR"] : $TickRo;
     $SkippedTickG	= isset($Format["SkippedTickG"]) ? $Format["SkippedTickG"] : $TickGo;
     $SkippedTickB	= isset($Format["SkippedTicksB"]) ? $Format["SkippedTickB"] : $TickBo;
     $SkippedTickAlpha	= isset($Format["SkippedTickAlpha"]) ? $Format["SkippedTickAlpha"] : $TickAlpha-80;
     $SkippedInnerTickWidth	= isset($Format["SkippedInnerTickWidth"]) ? $Format["SkippedInnerTickWidth"] : 0;
     $SkippedOuterTickWidth	= isset($Format["SkippedOuterTickWidth"]) ? $Format["SkippedOuterTickWidth"] : 2;

     /* Floating scale require X & Y margins to be set manually */
     if ( $Floating && ( $XMargin == AUTO || $YMargin == 0 ) ) { $Floating = FALSE; }

     /* Skip a NOTICE event in case of an empty array */
     if ( $DrawYLines == NONE || $DrawYLines == FALSE ) { $DrawYLines = array("zarma"=>"31"); }

     /* Define the color for the skipped elements */
     $SkippedAxisColor = array("R"=>$SkippedAxisR,"G"=>$SkippedAxisG,"B"=>$SkippedAxisB,"Alpha"=>$SkippedAxisAlpha,"Ticks"=>$SkippedAxisTicks);
     $SkippedTickColor = array("R"=>$SkippedTickR,"G"=>$SkippedTickG,"B"=>$SkippedTickB,"Alpha"=>$SkippedTickAlpha);

     $Data = $this->DataSet->getData();
     if ( isset($Data["Abscissa"]) ) { $Abscissa = $Data["Abscissa"]; } else { $Abscissa = NULL; }

     /* Unset the abscissa axis, needed if we display multiple charts on the same picture */
     if ( $Abscissa != NULL )
      {
       foreach($Data["Axis"] as $AxisID => $Parameters)
        { if ($Parameters["Identity"] == AXIS_X) { unset($Data["Axis"][$AxisID]); } }
      }

     /* Build the scale settings */
     $GotXAxis = FALSE;
     foreach($Data["Axis"] as $AxisID => $AxisParameter)
      {
       if ( $AxisParameter["Identity"] == AXIS_X ) { $GotXAxis = TRUE; }

       if ( $Pos == SCALE_POS_LEFTRIGHT && $AxisParameter["Identity"] == AXIS_Y)
        { $Height = $this->GraphAreaY2-$this->GraphAreaY1 - $YMargin*2; }
       elseif ( $Pos == SCALE_POS_LEFTRIGHT && $AxisParameter["Identity"] == AXIS_X)
        { $Height = $this->GraphAreaX2-$this->GraphAreaX1; }
       elseif ( $Pos == SCALE_POS_TOPBOTTOM && $AxisParameter["Identity"] == AXIS_Y)
        { $Height = $this->GraphAreaX2-$this->GraphAreaX1 - $YMargin*2;; }
       else
        { $Height = $this->GraphAreaY2-$this->GraphAreaY1; }

       $AxisMin = ABSOLUTE_MAX; $AxisMax = OUT_OF_SIGHT;
       if ( $Mode == SCALE_MODE_FLOATING || $Mode == SCALE_MODE_START0 )
        {
         foreach($Data["Series"] as $SerieID => $SerieParameter)
          {
           if ( $SerieParameter["Axis"] == $AxisID && $Data["Series"][$SerieID]["isDrawable"] && $Data["Abscissa"] != $SerieID)
            {
             $AxisMax = max($AxisMax,$Data["Series"][$SerieID]["Max"]);
             $AxisMin = min($AxisMin,$Data["Series"][$SerieID]["Min"]);
            }
          }
         $AutoMargin = (($AxisMax-$AxisMin)/100)*$XReleasePercent;

         $Data["Axis"][$AxisID]["Min"] = $AxisMin-$AutoMargin; $Data["Axis"][$AxisID]["Max"] = $AxisMax+$AutoMargin;
         if ( $Mode == SCALE_MODE_START0 ) { $Data["Axis"][$AxisID]["Min"] = 0; }
        }
       elseif ( $Mode == SCALE_MODE_MANUAL )
        {
         if ( isset($ManualScale[$AxisID]["Min"]) && isset($ManualScale[$AxisID]["Max"]) )
          {
           $Data["Axis"][$AxisID]["Min"] = $ManualScale[$AxisID]["Min"];
           $Data["Axis"][$AxisID]["Max"] = $ManualScale[$AxisID]["Max"];
          }
         else
          { echo "Manual scale boundaries not set."; exit(); }
        }
       elseif ( $Mode == SCALE_MODE_ADDALL || $Mode == SCALE_MODE_ADDALL_START0 )
        {
         $Series = "";
         foreach($Data["Series"] as $SerieID => $SerieParameter)
          { if ( $SerieParameter["Axis"] == $AxisID && $SerieParameter["isDrawable"] && $Data["Abscissa"] != $SerieID ) { $Series[$SerieID] = count($Data["Series"][$SerieID]["Data"]); } }

         for ($ID=0;$ID<=max($Series)-1;$ID++)
          {
           $PointMin = 0; $PointMax = 0;
           foreach($Series as $SerieID => $ValuesCount )
            {
             if (isset($Data["Series"][$SerieID]["Data"][$ID]) && $Data["Series"][$SerieID]["Data"][$ID] != NULL )
              {
               $Value = $Data["Series"][$SerieID]["Data"][$ID];
               if ( $Value > 0 ) { $PointMax = $PointMax + $Value; } else { $PointMin = $PointMin + $Value; }
              }
            }
           $AxisMax = max($AxisMax,$PointMax);
           $AxisMin = min($AxisMin,$PointMin);
          }
         $AutoMargin = (($AxisMax-$AxisMin)/100)*$XReleasePercent;
         $Data["Axis"][$AxisID]["Min"] = $AxisMin-$AutoMargin; $Data["Axis"][$AxisID]["Max"] = $AxisMax+$AutoMargin;
        }
       $MaxDivs = floor($Height/$MinDivHeight);

       if ( $Mode == SCALE_MODE_ADDALL_START0 ) { $Data["Axis"][$AxisID]["Min"] = 0; }

       $Scale   = $this->computeScale($Data["Axis"][$AxisID]["Min"],$Data["Axis"][$AxisID]["Max"],$MaxDivs,$Factors,$AxisID);

       $Data["Axis"][$AxisID]["Margin"]    = $AxisParameter["Identity"] == AXIS_X ? $XMargin : $YMargin;
       $Data["Axis"][$AxisID]["ScaleMin"]  = $Scale["XMin"];
       $Data["Axis"][$AxisID]["ScaleMax"]  = $Scale["XMax"];
       $Data["Axis"][$AxisID]["Rows"]      = $Scale["Rows"];
       $Data["Axis"][$AxisID]["RowHeight"] = $Scale["RowHeight"];

       if ( isset($Scale["Format"]) ) { $Data["Axis"][$AxisID]["Format"] = $Scale["Format"]; }

       if ( !isset($Data["Axis"][$AxisID]["Display"]) ) { $Data["Axis"][$AxisID]["Display"] = NULL; }
       if ( !isset($Data["Axis"][$AxisID]["Format"]) )  { $Data["Axis"][$AxisID]["Format"] = NULL; }
       if ( !isset($Data["Axis"][$AxisID]["Unit"]) )    { $Data["Axis"][$AxisID]["Unit"] = NULL; }
      }

     /* Still no X axis */
     if ( $GotXAxis == FALSE )
      {
       if ( $Abscissa != NULL )
        {
         $Points = count($Data["Series"][$Abscissa]["Data"]);
         if ( $AutoAxisLabels )
          $AxisName = isset($Data["Series"][$Abscissa]["Description"]) ? $Data["Series"][$Abscissa]["Description"] : NULL;
         else
          $AxisName = NULL;
        }
       else
        {
         $Points = 0;
         $AxisName = isset($Data["XAxisName"]) ? $Data["XAxisName"] : NULL;
         foreach($Data["Series"] as $SerieID => $SerieParameter)
          { if ( $SerieParameter["isDrawable"] ) { $Points = max($Points,count($SerieParameter["Data"])); } }
        }

       $AxisID = count($Data["Axis"]);
       $Data["Axis"][$AxisID]["Identity"] = AXIS_X;
       if ( $Pos == SCALE_POS_LEFTRIGHT ) { $Data["Axis"][$AxisID]["Position"] = AXIS_POSITION_BOTTOM; } else { $Data["Axis"][$AxisID]["Position"] = AXIS_POSITION_LEFT; }
       if ( isset($Data["AbscissaName"]) ) { $Data["Axis"][$AxisID]["Name"] = $Data["AbscissaName"]; }
       if ( $XMargin == AUTO )
        {
         if ( $Pos == SCALE_POS_LEFTRIGHT )
          { $Height = $this->GraphAreaX2-$this->GraphAreaX1; }
         else
          { $Height = $this->GraphAreaY2-$this->GraphAreaY1; }

         if ( $Points == 1 )
          $Data["Axis"][$AxisID]["Margin"] = $Height / 2;
         else
          $Data["Axis"][$AxisID]["Margin"] = ($Height/$Points) / 2;
        }
       else
        { $Data["Axis"][$AxisID]["Margin"] = $XMargin; }
       $Data["Axis"][$AxisID]["Rows"] = $Points-1;
       if ( !isset($Data["Axis"][$AxisID]["Display"]) ) { $Data["Axis"][$AxisID]["Display"] = NULL; }
       if ( !isset($Data["Axis"][$AxisID]["Format"]) )  { $Data["Axis"][$AxisID]["Format"] = NULL; }
       if ( !isset($Data["Axis"][$AxisID]["Unit"]) )    { $Data["Axis"][$AxisID]["Unit"] = NULL; }
      }

     /* Do we need to reverse the abscissa position? */
     if ( $Pos != SCALE_POS_LEFTRIGHT )
      {
       if ( $Data["AbsicssaPosition"] == AXIS_POSITION_BOTTOM )
        { $Data["AbsicssaPosition"] = AXIS_POSITION_LEFT; }
       else
        { $Data["AbsicssaPosition"] = AXIS_POSITION_RIGHT; }
      }
     $Data["Axis"][$AxisID]["Position"] = $Data["AbsicssaPosition"];

     $this->DataSet->saveOrientation($Pos);
     $this->DataSet->saveAxisConfig($Data["Axis"]);
     $this->DataSet->saveYMargin($YMargin);

     $FontColorRo = $this->FontColorR; $FontColorGo = $this->FontColorG; $FontColorBo = $this->FontColorB;

     $AxisPos["L"] = $this->GraphAreaX1; $AxisPos["R"] = $this->GraphAreaX2; $AxisPos["T"] = $this->GraphAreaY1; $AxisPos["B"] = $this->GraphAreaY2;
     foreach($Data["Axis"] as $AxisID => $Parameters)
      {
       if ( isset($Parameters["Color"]) )
        {
         $AxisR = $Parameters["Color"]["R"]; $AxisG = $Parameters["Color"]["G"]; $AxisB = $Parameters["Color"]["B"];
         $TickR = $Parameters["Color"]["R"]; $TickG = $Parameters["Color"]["G"]; $TickB = $Parameters["Color"]["B"];
         $this->setFontProperties(array("R"=>$Parameters["Color"]["R"],"G"=>$Parameters["Color"]["G"],"B"=>$Parameters["Color"]["B"]));
        }
       else
        {
         $AxisR = $AxisRo; $AxisG = $AxisGo; $AxisB = $AxisBo;
         $TickR = $TickRo; $TickG = $TickGo; $TickB = $TickBo;
         $this->setFontProperties(array("R"=>$FontColorRo,"G"=>$FontColorGo,"B"=>$FontColorBo));
        }

       $LastValue = "w00t"; $ID = 1;
       if ( $Parameters["Identity"] == AXIS_X )
        {
         if ( $Pos == SCALE_POS_LEFTRIGHT )
          {
           if ( $Parameters["Position"] == AXIS_POSITION_BOTTOM )
            {
             if ( $LabelRotation == 0 )					{ $LabelAlign = TEXT_ALIGN_TOPMIDDLE; $YLabelOffset = 2; }
             if ( $LabelRotation > 0 && $LabelRotation < 190 )		{ $LabelAlign = TEXT_ALIGN_MIDDLERIGHT; $YLabelOffset = 5; }
             if ( $LabelRotation == 180 )				{ $LabelAlign = TEXT_ALIGN_BOTTOMMIDDLE; $YLabelOffset = 5; }
             if ( $LabelRotation > 180 && $LabelRotation < 360 )	{ $LabelAlign = TEXT_ALIGN_MIDDLELEFT; $YLabelOffset = 2; }

             if ( !$RemoveXAxis )
              {
               if ( $Floating )
                { $FloatingOffset = $YMargin; $this->drawLine($this->GraphAreaX1+$Parameters["Margin"],$AxisPos["B"],$this->GraphAreaX2-$Parameters["Margin"],$AxisPos["B"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }
               else
                { $FloatingOffset = 0; $this->drawLine($this->GraphAreaX1,$AxisPos["B"],$this->GraphAreaX2,$AxisPos["B"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }

               if ( $DrawArrows ) { $this->drawArrow($this->GraphAreaX2-$Parameters["Margin"],$AxisPos["B"],$this->GraphAreaX2+($ArrowSize*2),$AxisPos["B"],array("FillR"=>$AxisR,"FillG"=>$AxisG,"FillB"=>$AxisB,"Size"=>$ArrowSize)); }
              }

             $Width = ($this->GraphAreaX2 - $this->GraphAreaX1) - $Parameters["Margin"]*2;

             if ($Parameters["Rows"] == 0 ) { $Step  = $Width; } else { $Step  = $Width / ($Parameters["Rows"]); }
             
             $MaxBottom = $AxisPos["B"];
             for($i=0;$i<=$Parameters["Rows"];$i++)
              {
               $XPos  = $this->GraphAreaX1 + $Parameters["Margin"] + $Step*$i;
               $YPos  = $AxisPos["B"];

               if ( $Abscissa != NULL )
                { if ( isset($Data["Series"][$Abscissa]["Data"][$i]) ) { $Value = $this->scaleFormat($Data["Series"][$Abscissa]["Data"][$i],$Data["XAxisDisplay"],$Data["XAxisFormat"],$Data["XAxisUnit"]); } else { $Value = ""; } }
               else
                {
                 if ( isset($Parameters["ScaleMin"]) && isset ($Parameters["RowHeight"]) )
                  $Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"]*$i,$Data["XAxisDisplay"],$Data["XAxisFormat"],$Data["XAxisUnit"]);
                 else
                  $Value = $i;
                }

               $ID++; $Skipped = TRUE;
               if ( $this->isValidLabel($Value,$LastValue,$LabelingMethod,$ID,$LabelSkip) && !$RemoveXAxis)
                {
                 $Bounds    = $this->drawText($XPos,$YPos+$OuterTickWidth+$YLabelOffset,$Value,array("Angle"=>$LabelRotation,"Align"=>$LabelAlign));
                 $TxtBottom = $YPos+$OuterTickWidth+2+($Bounds[0]["Y"]-$Bounds[2]["Y"]);
                 $MaxBottom = max($MaxBottom,$TxtBottom);
                 $LastValue = $Value;
                 $Skipped   = FALSE;
                }

               if ( $RemoveXAxis ) { $Skipped   = FALSE; }

               if ( $Skipped )
                {
                 if ( $DrawXLines && !$RemoveSkippedAxis ) { $this->drawLine($XPos,$this->GraphAreaY1+$FloatingOffset,$XPos,$this->GraphAreaY2-$FloatingOffset,$SkippedAxisColor); }
                 if ( ($SkippedInnerTickWidth !=0 || $SkippedOuterTickWidth != 0) && !$RemoveXAxis && !$RemoveSkippedAxis) { $this->drawLine($XPos,$YPos-$SkippedInnerTickWidth,$XPos,$YPos+$SkippedOuterTickWidth,$SkippedTickColor); }
                }
               else
                {
                 if ( $DrawXLines && ($XPos != $this->GraphAreaX1 && $XPos != $this->GraphAreaX2) ) { $this->drawLine($XPos,$this->GraphAreaY1+$FloatingOffset,$XPos,$this->GraphAreaY2-$FloatingOffset,array("R"=>$GridR,"G"=>$GridG,"B"=>$GridB,"Alpha"=>$GridAlpha,"Ticks"=>$GridTicks)); }
                 if ( ($InnerTickWidth !=0 || $OuterTickWidth != 0) && !$RemoveXAxis ) { $this->drawLine($XPos,$YPos-$InnerTickWidth,$XPos,$YPos+$OuterTickWidth,array("R"=>$TickR,"G"=>$TickG,"B"=>$TickB,"Alpha"=>$TickAlpha)); }
                }
              }

             if ( isset($Parameters["Name"]) && !$RemoveXAxis)
              {
               $YPos   = $MaxBottom+2;
               $XPos   = $this->GraphAreaX1+($this->GraphAreaX2-$this->GraphAreaX1)/2;
               $Bounds = $this->drawText($XPos,$YPos,$Parameters["Name"],array("Align"=>TEXT_ALIGN_TOPMIDDLE));
               $MaxBottom = $Bounds[0]["Y"];

               $this->DataSet->Data["GraphArea"]["Y2"] = $MaxBottom + $this->FontSize;
              }

             $AxisPos["B"] = $MaxBottom + $ScaleSpacing;
            }
           elseif ( $Parameters["Position"] == AXIS_POSITION_TOP )
            {
             if ( $LabelRotation == 0 )					{ $LabelAlign = TEXT_ALIGN_BOTTOMMIDDLE; $YLabelOffset = 2; }
             if ( $LabelRotation > 0 && $LabelRotation < 190 )		{ $LabelAlign = TEXT_ALIGN_MIDDLELEFT; $YLabelOffset = 2; }
             if ( $LabelRotation == 180 )				{ $LabelAlign = TEXT_ALIGN_TOPMIDDLE; $YLabelOffset = 5; }
             if ( $LabelRotation > 180 && $LabelRotation < 360 )	{ $LabelAlign = TEXT_ALIGN_MIDDLERIGHT; $YLabelOffset = 5; }

             if ( !$RemoveXAxis )
              {
               if ( $Floating )
                { $FloatingOffset = $YMargin; $this->drawLine($this->GraphAreaX1+$Parameters["Margin"],$AxisPos["T"],$this->GraphAreaX2-$Parameters["Margin"],$AxisPos["T"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }
               else
                { $FloatingOffset = 0; $this->drawLine($this->GraphAreaX1,$AxisPos["T"],$this->GraphAreaX2,$AxisPos["T"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }

               if ( $DrawArrows ) { $this->drawArrow($this->GraphAreaX2-$Parameters["Margin"],$AxisPos["T"],$this->GraphAreaX2+($ArrowSize*2),$AxisPos["T"],array("FillR"=>$AxisR,"FillG"=>$AxisG,"FillB"=>$AxisB,"Size"=>$ArrowSize)); }
              }

             $Width = ($this->GraphAreaX2 - $this->GraphAreaX1) - $Parameters["Margin"]*2;

             if ($Parameters["Rows"] == 0 ) { $Step  = $Width; } else { $Step  = $Width / $Parameters["Rows"]; }
             
             $MinTop = $AxisPos["T"];
             for($i=0;$i<=$Parameters["Rows"];$i++)
              {
               $XPos  = $this->GraphAreaX1 + $Parameters["Margin"] + $Step*$i;
               $YPos  = $AxisPos["T"];

               if ( $Abscissa != NULL )
                { if ( isset($Data["Series"][$Abscissa]["Data"][$i]) ) { $Value = $this->scaleFormat($Data["Series"][$Abscissa]["Data"][$i],$Data["XAxisDisplay"],$Data["XAxisFormat"],$Data["XAxisUnit"]); } else { $Value = ""; } }
               else
                {
                 if ( isset($Parameters["ScaleMin"]) && isset ($Parameters["RowHeight"]) )
                  $Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"]*$i,$Data["XAxisDisplay"],$Data["XAxisFormat"],$Data["XAxisUnit"]);
                 else
                  $Value = $i;
                }

               $ID++; $Skipped = TRUE;
               if ( $this->isValidLabel($Value,$LastValue,$LabelingMethod,$ID,$LabelSkip) && !$RemoveXAxis)
                {
                 $Bounds = $this->drawText($XPos,$YPos-$OuterTickWidth-$YLabelOffset,$Value,array("Angle"=>$LabelRotation,"Align"=>$LabelAlign));
                 $TxtBox = $YPos-$OuterTickWidth-2-($Bounds[0]["Y"]-$Bounds[2]["Y"]);
                 $MinTop = min($MinTop,$TxtBox);
                 $LastValue = $Value;
                 $Skipped   = FALSE;
                }

               if ( $RemoveXAxis ) { $Skipped   = FALSE; }

               if ( $Skipped )
                {
                 if ( $DrawXLines && !$RemoveSkippedAxis ) { $this->drawLine($XPos,$this->GraphAreaY1+$FloatingOffset,$XPos,$this->GraphAreaY2-$FloatingOffset,$SkippedAxisColor); }
                 if ( ($SkippedInnerTickWidth !=0 || $SkippedOuterTickWidth != 0) && !$RemoveXAxis && !$RemoveSkippedAxis ) { $this->drawLine($XPos,$YPos+$SkippedInnerTickWidth,$XPos,$YPos-$SkippedOuterTickWidth,$SkippedTickColor); }
                }
               else
                {
                 if ( $DrawXLines ) { $this->drawLine($XPos,$this->GraphAreaY1+$FloatingOffset,$XPos,$this->GraphAreaY2-$FloatingOffset,array("R"=>$GridR,"G"=>$GridG,"B"=>$GridB,"Alpha"=>$GridAlpha,"Ticks"=>$GridTicks)); }
                 if ( ($InnerTickWidth !=0 || $OuterTickWidth != 0) && !$RemoveXAxis ) { $this->drawLine($XPos,$YPos+$InnerTickWidth,$XPos,$YPos-$OuterTickWidth,array("R"=>$TickR,"G"=>$TickG,"B"=>$TickB,"Alpha"=>$TickAlpha)); }
                }

              }

             if ( isset($Parameters["Name"]) && !$RemoveXAxis )
              {
               $YPos   = $MinTop-2;
               $XPos   = $this->GraphAreaX1+($this->GraphAreaX2-$this->GraphAreaX1)/2;
               $Bounds = $this->drawText($XPos,$YPos,$Parameters["Name"],array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
               $MinTop = $Bounds[2]["Y"];

               $this->DataSet->Data["GraphArea"]["Y1"] = $MinTop;
              }

             $AxisPos["T"] = $MinTop - $ScaleSpacing;
            }
          }
         elseif ( $Pos == SCALE_POS_TOPBOTTOM )
          {
           if ( $Parameters["Position"] == AXIS_POSITION_LEFT )
            {
             if ( $LabelRotation == 0 )					{ $LabelAlign = TEXT_ALIGN_MIDDLERIGHT; $XLabelOffset = -2; }
             if ( $LabelRotation > 0 && $LabelRotation < 190 )		{ $LabelAlign = TEXT_ALIGN_MIDDLERIGHT; $XLabelOffset = -6; }
             if ( $LabelRotation == 180 )				{ $LabelAlign = TEXT_ALIGN_MIDDLELEFT; $XLabelOffset = -2; }
             if ( $LabelRotation > 180 && $LabelRotation < 360 )	{ $LabelAlign = TEXT_ALIGN_MIDDLELEFT; $XLabelOffset = -5; }

             if ( !$RemoveXAxis )
              {
               if ( $Floating )
                { $FloatingOffset = $YMargin; $this->drawLine($AxisPos["L"],$this->GraphAreaY1+$Parameters["Margin"],$AxisPos["L"],$this->GraphAreaY2-$Parameters["Margin"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }
               else
                { $FloatingOffset = 0; $this->drawLine($AxisPos["L"],$this->GraphAreaY1,$AxisPos["L"],$this->GraphAreaY2,array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }

               if ( $DrawArrows ) { $this->drawArrow($AxisPos["L"],$this->GraphAreaY2-$Parameters["Margin"],$AxisPos["L"],$this->GraphAreaY2+($ArrowSize*2),array("FillR"=>$AxisR,"FillG"=>$AxisG,"FillB"=>$AxisB,"Size"=>$ArrowSize)); }
              }

             $Height = ($this->GraphAreaY2 - $this->GraphAreaY1) - $Parameters["Margin"]*2;

             if ($Parameters["Rows"] == 0 ) { $Step  = $Height; } else { $Step   = $Height / $Parameters["Rows"]; }

             $MinLeft = $AxisPos["L"];
             for($i=0;$i<=$Parameters["Rows"];$i++)
              {
               $YPos  = $this->GraphAreaY1 + $Parameters["Margin"] + $Step*$i;
               $XPos  = $AxisPos["L"];

               if ( $Abscissa != NULL )
                { if ( isset($Data["Series"][$Abscissa]["Data"][$i]) ) { $Value = $this->scaleFormat($Data["Series"][$Abscissa]["Data"][$i],$Data["XAxisDisplay"],$Data["XAxisFormat"],$Data["XAxisUnit"]); } else { $Value = ""; } }
               else
                {
                 if ( isset($Parameters["ScaleMin"]) && isset ($Parameters["RowHeight"]) )
                  $Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"]*$i,$Data["XAxisDisplay"],$Data["XAxisFormat"],$Data["XAxisUnit"]);
                 else
                  $Value = $i;
                }

               $ID++; $Skipped = TRUE;
               if ( $this->isValidLabel($Value,$LastValue,$LabelingMethod,$ID,$LabelSkip) && !$RemoveXAxis)
                {
                 $Bounds  = $this->drawText($XPos-$OuterTickWidth+$XLabelOffset,$YPos,$Value,array("Angle"=>$LabelRotation,"Align"=>$LabelAlign));
                 $TxtBox  = $XPos-$OuterTickWidth-2-($Bounds[1]["X"]-$Bounds[0]["X"]);
                 $MinLeft = min($MinLeft,$TxtBox);
                 $LastValue = $Value;
                 $Skipped   = FALSE;
                }

               if ( $RemoveXAxis ) { $Skipped   = FALSE; }

               if ( $Skipped )
                {
                 if ( $DrawXLines && !$RemoveSkippedAxis ) { $this->drawLine($this->GraphAreaX1+$FloatingOffset,$YPos,$this->GraphAreaX2-$FloatingOffset,$YPos,$SkippedAxisColor); }
                 if ( ($SkippedInnerTickWidth !=0 || $SkippedOuterTickWidth != 0) && !$RemoveXAxis && !$RemoveSkippedAxis ) { $this->drawLine($XPos-$SkippedOuterTickWidth,$YPos,$XPos+$SkippedInnerTickWidth,$YPos,$SkippedTickColor); }
                }
               else
                {
                 if ( $DrawXLines && ($YPos != $this->GraphAreaY1 && $YPos != $this->GraphAreaY2) ) { $this->drawLine($this->GraphAreaX1+$FloatingOffset,$YPos,$this->GraphAreaX2-$FloatingOffset,$YPos,array("R"=>$GridR,"G"=>$GridG,"B"=>$GridB,"Alpha"=>$GridAlpha,"Ticks"=>$GridTicks)); }
                 if ( ($InnerTickWidth !=0 || $OuterTickWidth != 0) && !$RemoveXAxis ) { $this->drawLine($XPos-$OuterTickWidth,$YPos,$XPos+$InnerTickWidth,$YPos,array("R"=>$TickR,"G"=>$TickG,"B"=>$TickB,"Alpha"=>$TickAlpha)); }
                }

              }
             if ( isset($Parameters["Name"]) && !$RemoveXAxis )
              {
               $XPos   = $MinLeft-2;
               $YPos   = $this->GraphAreaY1+($this->GraphAreaY2-$this->GraphAreaY1)/2;
               $Bounds = $this->drawText($XPos,$YPos,$Parameters["Name"],array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"Angle"=>90));
               $MinLeft = $Bounds[0]["X"];

               $this->DataSet->Data["GraphArea"]["X1"] = $MinLeft;
              }

             $AxisPos["L"] = $MinLeft - $ScaleSpacing;
            }
           elseif ( $Parameters["Position"] == AXIS_POSITION_RIGHT )
            {
             if ( $LabelRotation == 0 )					{ $LabelAlign = TEXT_ALIGN_MIDDLELEFT; $XLabelOffset = 2; }
             if ( $LabelRotation > 0 && $LabelRotation < 190 )		{ $LabelAlign = TEXT_ALIGN_MIDDLELEFT; $XLabelOffset = 6; }
             if ( $LabelRotation == 180 )				{ $LabelAlign = TEXT_ALIGN_MIDDLERIGHT; $XLabelOffset = 5; }
             if ( $LabelRotation > 180 && $LabelRotation < 360 )	{ $LabelAlign = TEXT_ALIGN_MIDDLERIGHT; $XLabelOffset = 7; }

             if ( !$RemoveXAxis )
              {
               if ( $Floating )
                { $FloatingOffset = $YMargin; $this->drawLine($AxisPos["R"],$this->GraphAreaY1+$Parameters["Margin"],$AxisPos["R"],$this->GraphAreaY2-$Parameters["Margin"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }
               else
                { $FloatingOffset = 0; $this->drawLine($AxisPos["R"],$this->GraphAreaY1,$AxisPos["R"],$this->GraphAreaY2,array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }

               if ( $DrawArrows ) { $this->drawArrow($AxisPos["R"],$this->GraphAreaY2-$Parameters["Margin"],$AxisPos["R"],$this->GraphAreaY2+($ArrowSize*2),array("FillR"=>$AxisR,"FillG"=>$AxisG,"FillB"=>$AxisB,"Size"=>$ArrowSize)); }
              }

             $Height = ($this->GraphAreaY2 - $this->GraphAreaY1) - $Parameters["Margin"]*2;

             if ($Parameters["Rows"] == 0 ) { $Step  = $Height; } else { $Step   = $Height / $Parameters["Rows"]; }
             
             $MaxRight = $AxisPos["R"];
             for($i=0;$i<=$Parameters["Rows"];$i++)
              {
               $YPos  = $this->GraphAreaY1 + $Parameters["Margin"] + $Step*$i;
               $XPos  = $AxisPos["R"];

               if ( $Abscissa != NULL )
                { if ( isset($Data["Series"][$Abscissa]["Data"][$i]) ) { $Value = $this->scaleFormat($Data["Series"][$Abscissa]["Data"][$i],$Data["XAxisDisplay"],$Data["XAxisFormat"],$Data["XAxisUnit"]); } else { $Value = ""; } }
               else
                {
                 if ( isset($Parameters["ScaleMin"]) && isset ($Parameters["RowHeight"]) )
                  $Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"]*$i,$Data["XAxisDisplay"],$Data["XAxisFormat"],$Data["XAxisUnit"]);
                 else
                  $Value = $i;
                }

               $ID++; $Skipped = TRUE;
               if ( $this->isValidLabel($Value,$LastValue,$LabelingMethod,$ID,$LabelSkip) && !$RemoveXAxis)
                {
                 $Bounds   = $this->drawText($XPos+$OuterTickWidth+$XLabelOffset,$YPos,$Value,array("Angle"=>$LabelRotation,"Align"=>$LabelAlign));
                 $TxtBox   = $XPos+$OuterTickWidth+2+($Bounds[1]["X"]-$Bounds[0]["X"]);
                 $MaxRight = max($MaxRight,$TxtBox);
                 $LastValue = $Value;
                 $Skipped   = FALSE;
                }

               if ( $RemoveXAxis ) { $Skipped   = FALSE; }

               if ( $Skipped )
                {
                 if ( $DrawXLines && !$RemoveSkippedAxis ) { $this->drawLine($this->GraphAreaX1+$FloatingOffset,$YPos,$this->GraphAreaX2-$FloatingOffset,$YPos,$SkippedAxisColor); }
                 if ( ($SkippedInnerTickWidth != 0 || $SkippedOuterTickWidth != 0) && !$RemoveXAxis && !$RemoveSkippedAxis ) { $this->drawLine($XPos+$SkippedOuterTickWidth,$YPos,$XPos-$SkippedInnerTickWidth,$YPos,$SkippedTickColor); }
                }
               else
                {
                 if ( $DrawXLines ) { $this->drawLine($this->GraphAreaX1+$FloatingOffset,$YPos,$this->GraphAreaX2-$FloatingOffset,$YPos,array("R"=>$GridR,"G"=>$GridG,"B"=>$GridB,"Alpha"=>$GridAlpha,"Ticks"=>$GridTicks)); }
                 if ( ($InnerTickWidth != 0 || $OuterTickWidth != 0) && !$RemoveXAxis ) { $this->drawLine($XPos+$OuterTickWidth,$YPos,$XPos-$InnerTickWidth,$YPos,array("R"=>$TickR,"G"=>$TickG,"B"=>$TickB,"Alpha"=>$TickAlpha)); }
                }

              }

             if ( isset($Parameters["Name"]) && !$RemoveXAxis)
              {
               $XPos   = $MaxRight+4;
               $YPos   = $this->GraphAreaY1+($this->GraphAreaY2-$this->GraphAreaY1)/2;
               $Bounds = $this->drawText($XPos,$YPos,$Parameters["Name"],array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"Angle"=>270));
               $MaxRight = $Bounds[1]["X"];

               $this->DataSet->Data["GraphArea"]["X2"] = $MaxRight + $this->FontSize;
              }

             $AxisPos["R"] = $MaxRight + $ScaleSpacing;
            }
          }
        }



       if ( $Parameters["Identity"] == AXIS_Y )
        {
         if ( $Pos == SCALE_POS_LEFTRIGHT )
          {
           if ( $Parameters["Position"] == AXIS_POSITION_LEFT )
            {

             if ( $Floating )
              { $FloatingOffset = $XMargin; $this->drawLine($AxisPos["L"],$this->GraphAreaY1+$Parameters["Margin"],$AxisPos["L"],$this->GraphAreaY2-$Parameters["Margin"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }
             else
              { $FloatingOffset = 0; $this->drawLine($AxisPos["L"],$this->GraphAreaY1,$AxisPos["L"],$this->GraphAreaY2,array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }

             if ( $DrawArrows ) { $this->drawArrow($AxisPos["L"],$this->GraphAreaY1+$Parameters["Margin"],$AxisPos["L"],$this->GraphAreaY1-($ArrowSize*2),array("FillR"=>$AxisR,"FillG"=>$AxisG,"FillB"=>$AxisB,"Size"=>$ArrowSize)); }

             $Height = ($this->GraphAreaY2 - $this->GraphAreaY1) - $Parameters["Margin"]*2;
             $Step   = $Height / $Parameters["Rows"]; $SubTicksSize = $Step /2; $MinLeft = $AxisPos["L"];
             $LastY  = NULL;
             for($i=0;$i<=$Parameters["Rows"];$i++)
              {
               $YPos  = $this->GraphAreaY2 - $Parameters["Margin"] - $Step*$i;
               $XPos  = $AxisPos["L"];
               $Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"]*$i,$Parameters["Display"],$Parameters["Format"],$Parameters["Unit"]);

               if ( $i%2 == 1 ) { $BGColor = array("R"=>$BackgroundR1,"G"=>$BackgroundG1,"B"=>$BackgroundB1,"Alpha"=>$BackgroundAlpha1); } else { $BGColor = array("R"=>$BackgroundR2,"G"=>$BackgroundG2,"B"=>$BackgroundB2,"Alpha"=>$BackgroundAlpha2); }
               if ( $LastY != NULL && $CycleBackground && ( $DrawYLines == ALL || in_array($AxisID,$DrawYLines) )) { $this->drawFilledRectangle($this->GraphAreaX1+$FloatingOffset,$LastY,$this->GraphAreaX2-$FloatingOffset,$YPos,$BGColor); }

               if ( $DrawYLines == ALL || in_array($AxisID,$DrawYLines) ) { $this->drawLine($this->GraphAreaX1+$FloatingOffset,$YPos,$this->GraphAreaX2-$FloatingOffset,$YPos,array("R"=>$GridR,"G"=>$GridG,"B"=>$GridB,"Alpha"=>$GridAlpha,"Ticks"=>$GridTicks)); }

               if ( $DrawSubTicks && $i != $Parameters["Rows"] )
                $this->drawLine($XPos-$OuterSubTickWidth,$YPos-$SubTicksSize,$XPos+$InnerSubTickWidth,$YPos-$SubTicksSize,array("R"=>$SubTickR,"G"=>$SubTickG,"B"=>$SubTickB,"Alpha"=>$SubTickAlpha));

               $this->drawLine($XPos-$OuterTickWidth,$YPos,$XPos+$InnerTickWidth,$YPos,array("R"=>$TickR,"G"=>$TickG,"B"=>$TickB,"Alpha"=>$TickAlpha));
               $Bounds  = $this->drawText($XPos-$OuterTickWidth-2,$YPos,$Value,array("Align"=>TEXT_ALIGN_MIDDLERIGHT));
               $TxtLeft = $XPos-$OuterTickWidth-2-($Bounds[1]["X"]-$Bounds[0]["X"]);
               $MinLeft = min($MinLeft,$TxtLeft);

               $LastY = $YPos;
              }

             if ( isset($Parameters["Name"]) )
              {
               $XPos    = $MinLeft-2;
               $YPos    = $this->GraphAreaY1+($this->GraphAreaY2-$this->GraphAreaY1)/2;
               $Bounds  = $this->drawText($XPos,$YPos,$Parameters["Name"],array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"Angle"=>90));
               $MinLeft = $Bounds[2]["X"];

               $this->DataSet->Data["GraphArea"]["X1"] = $MinLeft;
              }

             $AxisPos["L"] = $MinLeft - $ScaleSpacing;
            }
           elseif ( $Parameters["Position"] == AXIS_POSITION_RIGHT )
            {
             if ( $Floating )
              { $FloatingOffset = $XMargin; $this->drawLine($AxisPos["R"],$this->GraphAreaY1+$Parameters["Margin"],$AxisPos["R"],$this->GraphAreaY2-$Parameters["Margin"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }
             else
              { $FloatingOffset = 0; $this->drawLine($AxisPos["R"],$this->GraphAreaY1,$AxisPos["R"],$this->GraphAreaY2,array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }

             if ( $DrawArrows ) { $this->drawArrow($AxisPos["R"],$this->GraphAreaY1+$Parameters["Margin"],$AxisPos["R"],$this->GraphAreaY1-($ArrowSize*2),array("FillR"=>$AxisR,"FillG"=>$AxisG,"FillB"=>$AxisB,"Size"=>$ArrowSize)); }

             $Height = ($this->GraphAreaY2 - $this->GraphAreaY1) - $Parameters["Margin"]*2;
             $Step   = $Height / $Parameters["Rows"]; $SubTicksSize = $Step /2; $MaxLeft = $AxisPos["R"];
             $LastY  = NULL;
             for($i=0;$i<=$Parameters["Rows"];$i++)
              {
               $YPos  = $this->GraphAreaY2 - $Parameters["Margin"] - $Step*$i;
               $XPos  = $AxisPos["R"];
               $Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"]*$i,$Parameters["Display"],$Parameters["Format"],$Parameters["Unit"]);

               if ( $i%2 == 1 ) { $BGColor = array("R"=>$BackgroundR1,"G"=>$BackgroundG1,"B"=>$BackgroundB1,"Alpha"=>$BackgroundAlpha1); } else { $BGColor = array("R"=>$BackgroundR2,"G"=>$BackgroundG2,"B"=>$BackgroundB2,"Alpha"=>$BackgroundAlpha2); }
               if ( $LastY != NULL && $CycleBackground  && ( $DrawYLines == ALL || in_array($AxisID,$DrawYLines) )) { $this->drawFilledRectangle($this->GraphAreaX1+$FloatingOffset,$LastY,$this->GraphAreaX2-$FloatingOffset,$YPos,$BGColor); }

               if ( $DrawYLines == ALL || in_array($AxisID,$DrawYLines) ) { $this->drawLine($this->GraphAreaX1+$FloatingOffset,$YPos,$this->GraphAreaX2-$FloatingOffset,$YPos,array("R"=>$GridR,"G"=>$GridG,"B"=>$GridB,"Alpha"=>$GridAlpha,"Ticks"=>$GridTicks)); }

               if ( $DrawSubTicks && $i != $Parameters["Rows"] )
                $this->drawLine($XPos-$OuterSubTickWidth,$YPos-$SubTicksSize,$XPos+$InnerSubTickWidth,$YPos-$SubTicksSize,array("R"=>$SubTickR,"G"=>$SubTickG,"B"=>$SubTickB,"Alpha"=>$SubTickAlpha));

               $this->drawLine($XPos-$InnerTickWidth,$YPos,$XPos+$OuterTickWidth,$YPos,array("R"=>$TickR,"G"=>$TickG,"B"=>$TickB,"Alpha"=>$TickAlpha));
               $Bounds  = $this->drawText($XPos+$OuterTickWidth+2,$YPos,$Value,array("Align"=>TEXT_ALIGN_MIDDLELEFT));
               $TxtLeft = $XPos+$OuterTickWidth+2+($Bounds[1]["X"]-$Bounds[0]["X"]);
               $MaxLeft = max($MaxLeft,$TxtLeft);

               $LastY = $YPos;
              }

             if ( isset($Parameters["Name"]) )
              {
               $XPos    = $MaxLeft+6;
               $YPos    = $this->GraphAreaY1+($this->GraphAreaY2-$this->GraphAreaY1)/2;
               $Bounds  = $this->drawText($XPos,$YPos,$Parameters["Name"],array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE,"Angle"=>270));
               $MaxLeft = $Bounds[2]["X"];

               $this->DataSet->Data["GraphArea"]["X2"] = $MaxLeft + $this->FontSize;
              }
             $AxisPos["R"] = $MaxLeft + $ScaleSpacing;
            }
          }
         elseif ( $Pos == SCALE_POS_TOPBOTTOM )
          {
           if ( $Parameters["Position"] == AXIS_POSITION_TOP )
            {
             if ( $Floating )
              { $FloatingOffset = $XMargin; $this->drawLine($this->GraphAreaX1+$Parameters["Margin"],$AxisPos["T"],$this->GraphAreaX2-$Parameters["Margin"],$AxisPos["T"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }
             else
              { $FloatingOffset = 0; $this->drawLine($this->GraphAreaX1,$AxisPos["T"],$this->GraphAreaX2,$AxisPos["T"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }

             if ( $DrawArrows ) { $this->drawArrow($this->GraphAreaX2-$Parameters["Margin"],$AxisPos["T"],$this->GraphAreaX2+($ArrowSize*2),$AxisPos["T"],array("FillR"=>$AxisR,"FillG"=>$AxisG,"FillB"=>$AxisB,"Size"=>$ArrowSize)); }

             $Width = ($this->GraphAreaX2 - $this->GraphAreaX1) - $Parameters["Margin"]*2;
             $Step   = $Width / $Parameters["Rows"]; $SubTicksSize = $Step /2; $MinTop = $AxisPos["T"];
             $LastX  = NULL;
             for($i=0;$i<=$Parameters["Rows"];$i++)
              {
               $XPos  = $this->GraphAreaX1 + $Parameters["Margin"] + $Step*$i;
               $YPos  = $AxisPos["T"];
               $Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"]*$i,$Parameters["Display"],$Parameters["Format"],$Parameters["Unit"]);

               if ( $i%2 == 1 ) { $BGColor = array("R"=>$BackgroundR1,"G"=>$BackgroundG1,"B"=>$BackgroundB1,"Alpha"=>$BackgroundAlpha1); } else { $BGColor = array("R"=>$BackgroundR2,"G"=>$BackgroundG2,"B"=>$BackgroundB2,"Alpha"=>$BackgroundAlpha2); }
               if ( $LastX != NULL && $CycleBackground  && ( $DrawYLines == ALL || in_array($AxisID,$DrawYLines) )) { $this->drawFilledRectangle($LastX,$this->GraphAreaY1+$FloatingOffset,$XPos,$this->GraphAreaY2-$FloatingOffset,$BGColor); }

               if ( $DrawYLines == ALL || in_array($AxisID,$DrawYLines) ) { $this->drawLine($XPos,$this->GraphAreaY1+$FloatingOffset,$XPos,$this->GraphAreaY2-$FloatingOffset,array("R"=>$GridR,"G"=>$GridG,"B"=>$GridB,"Alpha"=>$GridAlpha,"Ticks"=>$GridTicks)); }

               if ( $DrawSubTicks && $i != $Parameters["Rows"] )
                $this->drawLine($XPos+$SubTicksSize,$YPos-$OuterSubTickWidth,$XPos+$SubTicksSize,$YPos+$InnerSubTickWidth,array("R"=>$SubTickR,"G"=>$SubTickG,"B"=>$SubTickB,"Alpha"=>$SubTickAlpha));

               $this->drawLine($XPos,$YPos-$OuterTickWidth,$XPos,$YPos+$InnerTickWidth,array("R"=>$TickR,"G"=>$TickG,"B"=>$TickB,"Alpha"=>$TickAlpha));
               $Bounds    = $this->drawText($XPos,$YPos-$OuterTickWidth-2,$Value,array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
               $TxtHeight = $YPos-$OuterTickWidth-2-($Bounds[1]["Y"]-$Bounds[2]["Y"]);
               $MinTop    = min($MinTop,$TxtHeight);

               $LastX = $XPos;
              }

             if ( isset($Parameters["Name"]) )
              {
               $YPos   = $MinTop-2;
               $XPos   = $this->GraphAreaX1+($this->GraphAreaX2-$this->GraphAreaX1)/2;
               $Bounds = $this->drawText($XPos,$YPos,$Parameters["Name"],array("Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
               $MinTop = $Bounds[2]["Y"];

               $this->DataSet->Data["GraphArea"]["Y1"] = $MinTop;
              }

             $AxisPos["T"] = $MinTop - $ScaleSpacing;
            }
           elseif ( $Parameters["Position"] == AXIS_POSITION_BOTTOM )
            {
             if ( $Floating )
              { $FloatingOffset = $XMargin; $this->drawLine($this->GraphAreaX1+$Parameters["Margin"],$AxisPos["B"],$this->GraphAreaX2-$Parameters["Margin"],$AxisPos["B"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }
             else
              { $FloatingOffset = 0; $this->drawLine($this->GraphAreaX1,$AxisPos["B"],$this->GraphAreaX2,$AxisPos["B"],array("R"=>$AxisR,"G"=>$AxisG,"B"=>$AxisB,"Alpha"=>$AxisAlpha)); }

             if ( $DrawArrows ) { $this->drawArrow($this->GraphAreaX2-$Parameters["Margin"],$AxisPos["B"],$this->GraphAreaX2+($ArrowSize*2),$AxisPos["B"],array("FillR"=>$AxisR,"FillG"=>$AxisG,"FillB"=>$AxisB,"Size"=>$ArrowSize)); }

             $Width = ($this->GraphAreaX2 - $this->GraphAreaX1) - $Parameters["Margin"]*2;
             $Step   = $Width / $Parameters["Rows"]; $SubTicksSize = $Step /2; $MaxBottom = $AxisPos["B"];
             $LastX  = NULL;
             for($i=0;$i<=$Parameters["Rows"];$i++)
              {
               $XPos  = $this->GraphAreaX1 + $Parameters["Margin"] + $Step*$i;
               $YPos  = $AxisPos["B"];
               $Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"]*$i,$Parameters["Display"],$Parameters["Format"],$Parameters["Unit"]);

               if ( $i%2 == 1 ) { $BGColor = array("R"=>$BackgroundR1,"G"=>$BackgroundG1,"B"=>$BackgroundB1,"Alpha"=>$BackgroundAlpha1); } else { $BGColor = array("R"=>$BackgroundR2,"G"=>$BackgroundG2,"B"=>$BackgroundB2,"Alpha"=>$BackgroundAlpha2); }
               if ( $LastX != NULL && $CycleBackground  && ( $DrawYLines == ALL || in_array($AxisID,$DrawYLines) )) { $this->drawFilledRectangle($LastX,$this->GraphAreaY1+$FloatingOffset,$XPos,$this->GraphAreaY2-$FloatingOffset,$BGColor); }

               if ( $DrawYLines == ALL || in_array($AxisID,$DrawYLines) ) { $this->drawLine($XPos,$this->GraphAreaY1+$FloatingOffset,$XPos,$this->GraphAreaY2-$FloatingOffset,array("R"=>$GridR,"G"=>$GridG,"B"=>$GridB,"Alpha"=>$GridAlpha,"Ticks"=>$GridTicks)); }

               if ( $DrawSubTicks && $i != $Parameters["Rows"] )
                $this->drawLine($XPos+$SubTicksSize,$YPos-$OuterSubTickWidth,$XPos+$SubTicksSize,$YPos+$InnerSubTickWidth,array("R"=>$SubTickR,"G"=>$SubTickG,"B"=>$SubTickB,"Alpha"=>$SubTickAlpha));

               $this->drawLine($XPos,$YPos-$OuterTickWidth,$XPos,$YPos+$InnerTickWidth,array("R"=>$TickR,"G"=>$TickG,"B"=>$TickB,"Alpha"=>$TickAlpha));
               $Bounds    = $this->drawText($XPos,$YPos+$OuterTickWidth+2,$Value,array("Align"=>TEXT_ALIGN_TOPMIDDLE));
               $TxtHeight = $YPos+$OuterTickWidth+2+($Bounds[1]["Y"]-$Bounds[2]["Y"]);
               $MaxBottom = max($MaxBottom,$TxtHeight);

               $LastX = $XPos;
              }

             if ( isset($Parameters["Name"]) )
              {
               $YPos   = $MaxBottom+2;
               $XPos   = $this->GraphAreaX1+($this->GraphAreaX2-$this->GraphAreaX1)/2;
               $Bounds = $this->drawText($XPos,$YPos,$Parameters["Name"],array("Align"=>TEXT_ALIGN_TOPMIDDLE));
               $MaxBottom = $Bounds[0]["Y"];

               $this->DataSet->Data["GraphArea"]["Y2"] = $MaxBottom + $this->FontSize;
              }

             $AxisPos["B"] = $MaxBottom + $ScaleSpacing;
            }
          }
        }
      }
    }

   function isValidLabel($Value,$LastValue,$LabelingMethod,$ID,$LabelSkip)
    {
     if ( $LabelingMethod == LABELING_DIFFERENT && $Value != $LastValue ) { return(TRUE); }
     if ( $LabelingMethod == LABELING_DIFFERENT && $Value == $LastValue ) { return(FALSE); }
     if ( $LabelingMethod == LABELING_ALL && $LabelSkip == 0 ) { return(TRUE); }
     if ( $LabelingMethod == LABELING_ALL && ($ID+$LabelSkip) % ($LabelSkip+1) != 1 ) { return(FALSE); }

     return(TRUE);
    }

   /* Compute the scale, check for the best visual factors */
   function computeScale($XMin,$XMax,$MaxDivs,$Factors,$AxisID=0)
    {
     /* Compute each factors */
     $Results = "";
     foreach ($Factors as $Key => $Factor)
      $Results[$Factor] = $this->processScale($XMin,$XMax,$MaxDivs,array($Factor),$AxisID);

     /* Remove scales that are creating to much decimals */
     $GoodScaleFactors = "";
     foreach ($Results as $Key => $Result)
      {
       $Decimals = preg_split("/\./",$Result["RowHeight"]);
       if ( (!isset($Decimals[1])) || (strlen($Decimals[1]) < 6) ) { $GoodScaleFactors[] = $Key; }
      }

     /* Found no correct scale, shame,... returns the 1st one as default */
     if ( $GoodScaleFactors == "" ) { return($Results[$Factors[0]]); }

     /* Find the factor that cause the maximum number of Rows */
     $MaxRows = 0; $BestFactor = 0;
     foreach($GoodScaleFactors as $Key => $Factor)
      { if ( $Results[$Factor]["Rows"] > $MaxRows ) { $MaxRows = $Results[$Factor]["Rows"]; $BestFactor = $Factor; } }

     /* Return the best visual scale */
     return($Results[$BestFactor]);
    }

   /* Compute the best matching scale based on size & factors */
   function processScale($XMin,$XMax,$MaxDivs,$Factors,$AxisID)
    {
     $ScaleHeight = abs(ceil($XMax)-floor($XMin));

     if ( isset($this->DataSet->Data["Axis"][$AxisID]["Format"]) )
      $Format = $this->DataSet->Data["Axis"][$AxisID]["Format"];
     else
      $Format = NULL;

     if ( isset($this->DataSet->Data["Axis"][$AxisID]["Display"]) )
      $Mode = $this->DataSet->Data["Axis"][$AxisID]["Display"];
     else
      $Mode = AXIS_FORMAT_DEFAULT;

     $Scale = "";
     if ( $XMin != $XMax )
      {
       $Found = FALSE; $Rescaled = FALSE; $Scaled10Factor = .0001; $Result = 0;
       while(!$Found)
        {
         foreach($Factors as $Key => $Factor)
          {
           if ( !$Found )
            {
             if ( !($this->modulo($XMin,$Factor*$Scaled10Factor) == 0) || ($XMin != floor($XMin))) { $XMinRescaled = floor($XMin/($Factor*$Scaled10Factor))*$Factor*$Scaled10Factor; } else { $XMinRescaled = $XMin; }
             if ( !($this->modulo($XMax,$Factor*$Scaled10Factor) == 0) || ($XMax != floor($XMax))) { $XMaxRescaled = floor($XMax/($Factor*$Scaled10Factor))*$Factor*$Scaled10Factor+($Factor*$Scaled10Factor); } else { $XMaxRescaled = $XMax; }
             $ScaleHeightRescaled = abs($XMaxRescaled-$XMinRescaled);

             if ( !$Found && floor($ScaleHeightRescaled/($Factor*$Scaled10Factor)) <= $MaxDivs ) { $Found = TRUE; $Rescaled = TRUE; $Result = $Factor * $Scaled10Factor; }
            }
          }
         $Scaled10Factor = $Scaled10Factor * 10;
        }

       /* ReCall Min / Max / Height */
       if ( $Rescaled ) { $XMin = $XMinRescaled; $XMax = $XMaxRescaled; $ScaleHeight = $ScaleHeightRescaled; }

       /* Compute rows size */
       $Rows      = floor($ScaleHeight / $Result); if ( $Rows == 0 ) { $Rows = 1; }
       $RowHeight = $ScaleHeight / $Rows;

       /* Return the results */
       $Scale["Rows"] = $Rows; $Scale["RowHeight"] = $RowHeight; $Scale["XMin"] = $XMin;  $Scale["XMax"] = $XMax;

       /* Compute the needed decimals for the metric view to avoid repetition of the same X Axis labels */
       if ( $Mode == AXIS_FORMAT_METRIC && $Format == NULL )
        {
         $Done = FALSE; $GoodDecimals = 0;
         for($Decimals=0;$Decimals<=10;$Decimals++)
          {
           if ( !$Done )
            {
             $LastLabel = "zob"; $ScaleOK = TRUE;
             for($i=0;$i<=$Rows;$i++)
              {
               $Value = $XMin + $i*$RowHeight;
               $Label = $this->scaleFormat($Value,AXIS_FORMAT_METRIC,$Decimals);

               if ( $LastLabel == $Label ) { $ScaleOK = FALSE; }
               $LastLabel = $Label;
              }
             if ( $ScaleOK ) { $Done = TRUE; $GoodDecimals = $Decimals; }
            }
          }

         $Scale["Format"] = $GoodDecimals;
        }
      }
     else
      {
       /* If all values are the same we keep a +1/-1 scale */
       $Rows = 2; $XMin = $XMax-1; $XMax = $XMax+1; $RowHeight = 1;

       /* Return the results */
       $Scale["Rows"] = $Rows; $Scale["RowHeight"] = $RowHeight; $Scale["XMin"] = $XMin;  $Scale["XMax"] = $XMax;
      }

     return($Scale);
    }

   function modulo($Value1,$Value2)
    {
     if (floor($Value2) == 0) { return(0); }
     if (floor($Value2) != 0) { return($Value1 % $Value2); }

     $MinValue = min($Value1,$Value2); $Factor = 10;
     while ( floor($MinValue*$Factor) == 0 )
      { $Factor = $Factor * 10; }

     return(($Value1*$Factor) % ($Value2*$Factor));
    }

   /* Draw an X threshold */
   function drawXThreshold($Value,$Format="")
    {
     $R			= isset($Format["R"]) ? $Format["R"] : 255;
     $G			= isset($Format["G"]) ? $Format["G"] : 0;
     $B			= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 50;
     $Weight		= isset($Format["Weight"]) ? $Format["Weight"] : NULL;
     $Ticks		= isset($Format["Ticks"]) ? $Format["Ticks"] : 6;
     $Wide		= isset($Format["Wide"]) ? $Format["Wide"] : FALSE;
     $WideFactor	= isset($Format["WideFactor"]) ? $Format["WideFactor"] : 5;
     $WriteCaption	= isset($Format["WriteCaption"]) ? $Format["WriteCaption"] : FALSE;
     $Caption		= isset($Format["Caption"]) ? $Format["Caption"] : NULL;
     $CaptionAlign	= isset($Format["CaptionAlign"]) ? $Format["CaptionAlign"] : CAPTION_LEFT_TOP;
     $CaptionOffset     = isset($Format["CaptionOffset"]) ? $Format["CaptionOffset"] : 5;
     $CaptionR		= isset($Format["CaptionR"]) ? $Format["CaptionR"] : 255;
     $CaptionG		= isset($Format["CaptionG"]) ? $Format["CaptionG"] : 255;
     $CaptionB		= isset($Format["CaptionB"]) ? $Format["CaptionB"] : 255;
     $CaptionAlpha	= isset($Format["CaptionAlpha"]) ? $Format["CaptionAlpha"] : 100;
     $DrawBox		= isset($Format["DrawBox"]) ? $Format["DrawBox"] : TRUE;
     $DrawBoxBorder	= isset($Format["DrawBoxBorder"]) ? $Format["DrawBoxBorder"] : FALSE;
     $BorderOffset	= isset($Format["BorderOffset"]) ? $Format["BorderOffset"] : 3;
     $BoxRounded	= isset($Format["BoxRounded"]) ? $Format["BoxRounded"] : TRUE;
     $RoundedRadius	= isset($Format["RoundedRadius"]) ? $Format["RoundedRadius"] : 3;
     $BoxR		= isset($Format["BoxR"]) ? $Format["BoxR"] : 0;
     $BoxG		= isset($Format["BoxG"]) ? $Format["BoxG"] : 0;
     $BoxB		= isset($Format["BoxB"]) ? $Format["BoxB"] : 0;
     $BoxAlpha		= isset($Format["BoxAlpha"]) ? $Format["BoxAlpha"] : 30;
     $BoxSurrounding	= isset($Format["BoxSurrounding"]) ? $Format["BoxSurrounding"] : "";
     $BoxBorderR	= isset($Format["BoxBorderR"]) ? $Format["BoxBorderR"] : 255;
     $BoxBorderG	= isset($Format["BoxBorderG"]) ? $Format["BoxBorderG"] : 255;
     $BoxBorderB	= isset($Format["BoxBorderB"]) ? $Format["BoxBorderB"] : 255;
     $BoxBorderAlpha	= isset($Format["BoxBorderAlpha"]) ? $Format["BoxBorderAlpha"] : 100;
     $ValueIsLabel	= isset($Format["ValueIsLabel"]) ? $Format["ValueIsLabel"] : FALSE;

     $Data           = $this->DataSet->getData();
     $AbscissaMargin = $this->getAbscissaMargin($Data);
     $XScale         = $this->scaleGetXSettings();

     if ( is_array($Value) ) { foreach ($Value as $Key => $ID) { $this->drawXThreshold($ID,$Format); } return(0); }

     if ( $ValueIsLabel )
      {
       $Format["ValueIsLabel"] = FALSE;
       foreach($Data["Series"][$Data["Abscissa"]]["Data"] as $Key => $SerieValue)
        { if ( $SerieValue == $Value ) { $this->drawXThreshold($Key,$Format); } }

       return(0);
      }

     $CaptionSettings = array("DrawBox"=>$DrawBox,"DrawBoxBorder"=>$DrawBoxBorder,"BorderOffset"=>$BorderOffset,"BoxRounded"=>$BoxRounded,"RoundedRadius"=>$RoundedRadius,
                              "BoxR"=>$BoxR,"BoxG"=>$BoxG,"BoxB"=>$BoxB,"BoxAlpha"=>$BoxAlpha,"BoxSurrounding"=>$BoxSurrounding,
                              "BoxBorderR"=>$BoxBorderR,"BoxBorderG"=>$BoxBorderG,"BoxBorderB"=>$BoxBorderB,"BoxBorderAlpha"=>$BoxBorderAlpha,
                              "R"=>$CaptionR,"G"=>$CaptionG,"B"=>$CaptionB,"Alpha"=>$CaptionAlpha);

     if ( $Caption == NULL )
      {
       if ( isset($Data["Abscissa"]) )
        {
         if ( isset($Data["Series"][$Data["Abscissa"]]["Data"][$Value]) )
          $Caption = $Data["Series"][$Data["Abscissa"]]["Data"][$Value];
         else
          $Caption = $Value;
        }
       else
        $Caption = $Value;
      }

     if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
      {
       $XStep = (($this->GraphAreaX2 - $this->GraphAreaX1) - $XScale[0] *2 ) / $XScale[1];
       $XPos  = $this->GraphAreaX1 + $XScale[0] + $XStep * $Value;
       $YPos1 = $this->GraphAreaY1 + $Data["YMargin"];
       $YPos2 = $this->GraphAreaY2 - $Data["YMargin"];

       if ( $XPos >= $this->GraphAreaX1 + $AbscissaMargin && $XPos <= $this->GraphAreaX2 - $AbscissaMargin )
        {
         $this->drawLine($XPos,$YPos1,$XPos,$YPos2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight));

         if ( $Wide )
          {
           $this->drawLine($XPos-1,$YPos1,$XPos-1,$YPos2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/$WideFactor,"Ticks"=>$Ticks));
           $this->drawLine($XPos+1,$YPos1,$XPos+1,$YPos2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/$WideFactor,"Ticks"=>$Ticks));
          }

         if ( $WriteCaption )
          {
           if ( $CaptionAlign == CAPTION_LEFT_TOP )
            { $Y = $YPos1 + $CaptionOffset; $CaptionSettings["Align"] = TEXT_ALIGN_TOPMIDDLE; }
           else 
            { $Y = $YPos2 - $CaptionOffset; $CaptionSettings["Align"] = TEXT_ALIGN_BOTTOMMIDDLE; }
           
           $this->drawText($XPos,$Y,$Caption,$CaptionSettings);
          }

         return(array("X"=>$XPos));
        }
      }
     elseif( $Data["Orientation"] == SCALE_POS_TOPBOTTOM )
      {
       $XStep = (($this->GraphAreaY2 - $this->GraphAreaY1) - $XScale[0] *2 ) / $XScale[1];
       $XPos  = $this->GraphAreaY1 + $XScale[0] + $XStep * $Value;
       $YPos1 = $this->GraphAreaX1 + $Data["YMargin"];
       $YPos2 = $this->GraphAreaX2 - $Data["YMargin"];

       if ( $XPos >= $this->GraphAreaY1 + $AbscissaMargin && $XPos <= $this->GraphAreaY2 - $AbscissaMargin )
        {
         $this->drawLine($YPos1,$XPos,$YPos2,$XPos,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight));

         if ( $Wide )
          {
           $this->drawLine($YPos1,$XPos-1,$YPos2,$XPos-1,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/$WideFactor,"Ticks"=>$Ticks));
           $this->drawLine($YPos1,$XPos+1,$YPos2,$XPos+1,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/$WideFactor,"Ticks"=>$Ticks));
          }

         if ( $WriteCaption )
          {
           if ( $CaptionAlign == CAPTION_LEFT_TOP )
            { $Y = $YPos1 + $CaptionOffset; $CaptionSettings["Align"] = TEXT_ALIGN_MIDDLELEFT; }
           else 
            { $Y = $YPos2 - $CaptionOffset; $CaptionSettings["Align"] = TEXT_ALIGN_MIDDLERIGHT; }
           
           $this->drawText($Y,$XPos,$Caption,$CaptionSettings);
          }

         return(array("X"=>$XPos));
        }
      }
    }

   /* Draw an X threshold area */
   function drawXThresholdArea($Value1,$Value2,$Format="")
    {
     $R		= isset($Format["R"]) ? $Format["R"] : 255;
     $G		= isset($Format["G"]) ? $Format["G"] : 0;
     $B		= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 20;
     $Border    = isset($Format["Border"]) ? $Format["Border"] : TRUE;
     $BorderR   = isset($Format["BorderR"]) ? $Format["BorderR"] : $R;
     $BorderG   = isset($Format["BorderG"]) ? $Format["BorderG"] : $G;
     $BorderB   = isset($Format["BorderB"]) ? $Format["BorderB"] : $B;
     $BorderAlpha = isset($Format["BorderAlpha"]) ? $Format["BorderAlpha"] : $Alpha + 20;
     $BorderTicks = isset($Format["BorderTicks"]) ? $Format["BorderTicks"] : 2;
     $AreaName 	= isset($Format["AreaName"]) ? $Format["AreaName"] : NULL;
     $NameAngle	= isset($Format["NameAngle"]) ? $Format["NameAngle"] : ZONE_NAME_ANGLE_AUTO;
     $NameR	= isset($Format["NameR"]) ? $Format["NameR"] : 255;
     $NameG	= isset($Format["NameG"]) ? $Format["NameG"] : 255;
     $NameB	= isset($Format["NameB"]) ? $Format["NameB"] : 255;
     $NameAlpha	= isset($Format["NameAlpha"]) ? $Format["NameAlpha"] : 100;
     $DisableShadowOnArea = isset($Format["DisableShadowOnArea"]) ? $Format["DisableShadowOnArea"] : TRUE;

     $RestoreShadow = $this->Shadow;
     if ( $DisableShadowOnArea && $this->Shadow ) { $this->Shadow = FALSE; }

     if ($BorderAlpha >100) { $BorderAlpha = 100;}

     $Data           = $this->DataSet->getData();
     $XScale         = $this->scaleGetXSettings();
     $AbscissaMargin = $this->getAbscissaMargin($Data);

     if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
      {
       $XStep = (($this->GraphAreaX2 - $this->GraphAreaX1) - $XScale[0] *2 ) / $XScale[1];
       $XPos1 = $this->GraphAreaX1 + $XScale[0] + $XStep * $Value1;
       $XPos2 = $this->GraphAreaX1 + $XScale[0] + $XStep * $Value2;
       $YPos1 = $this->GraphAreaY1 + $Data["YMargin"];
       $YPos2 = $this->GraphAreaY2 - $Data["YMargin"];

       if ( $XPos1 < $this->GraphAreaX1 + $XScale[0] ) { $XPos1 = $this->GraphAreaX1 + $XScale[0]; }
       if ( $XPos1 > $this->GraphAreaX2 - $XScale[0] ) { $XPos1 = $this->GraphAreaX2 - $XScale[0]; }
       if ( $XPos2 < $this->GraphAreaX1 + $XScale[0] ) { $XPos2 = $this->GraphAreaX1 + $XScale[0]; }
       if ( $XPos2 > $this->GraphAreaX2 - $XScale[0] ) { $XPos2 = $this->GraphAreaX2 - $XScale[0]; }

       $this->drawFilledRectangle($XPos1,$YPos1,$XPos2,$YPos2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));

       if ( $Border )
        {
         $this->drawLine($XPos1,$YPos1,$XPos1,$YPos2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$BorderTicks));
         $this->drawLine($XPos2,$YPos1,$XPos2,$YPos2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$BorderTicks));
        }

       if ( $AreaName != NULL )
        {
         $XPos = ($XPos2-$XPos1)/2 + $XPos1;
         $YPos = ($YPos2-$YPos1)/2 + $YPos1;

         if ( $NameAngle == ZONE_NAME_ANGLE_AUTO )
          {
           $TxtPos   = $this->getTextBox($XPos,$YPos,$this->FontName,$this->FontSize,0,$AreaName);
           $TxtWidth = $TxtPos[1]["X"] - $TxtPos[0]["X"];
           if ( abs($XPos2 - $XPos1) > $TxtWidth ) { $NameAngle = 0; } else { $NameAngle = 90; }
          }
         $this->Shadow = $RestoreShadow;
         $this->drawText($XPos,$YPos,$AreaName,array("R"=>$NameR,"G"=>$NameG,"B"=>$NameB,"Alpha"=>$NameAlpha,"Angle"=>$NameAngle,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));
         if ( $DisableShadowOnArea ) { $this->Shadow = FALSE; }
        }

       $this->Shadow = $RestoreShadow;
       return(array("X1"=>$XPos1,"X2"=>$XPos2));
      }
     elseif ( $Data["Orientation"] == SCALE_POS_TOPBOTTOM )
      {
       $XStep = (($this->GraphAreaY2 - $this->GraphAreaY1) - $XScale[0] *2 ) / $XScale[1];
       $XPos1 = $this->GraphAreaY1 + $XScale[0] + $XStep * $Value1;
       $XPos2 = $this->GraphAreaY1 + $XScale[0] + $XStep * $Value2;
       $YPos1 = $this->GraphAreaX1 + $Data["YMargin"];
       $YPos2 = $this->GraphAreaX2 - $Data["YMargin"];

       if ( $XPos1 < $this->GraphAreaY1 + $XScale[0] ) { $XPos1 = $this->GraphAreaY1 + $XScale[0]; }
       if ( $XPos1 > $this->GraphAreaY2 - $XScale[0] ) { $XPos1 = $this->GraphAreaY2 - $XScale[0]; }
       if ( $XPos2 < $this->GraphAreaY1 + $XScale[0] ) { $XPos2 = $this->GraphAreaY1 + $XScale[0]; }
       if ( $XPos2 > $this->GraphAreaY2 - $XScale[0] ) { $XPos2 = $this->GraphAreaY2 - $XScale[0]; }

       $this->drawFilledRectangle($YPos1,$XPos1,$YPos2,$XPos2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));

       if ( $Border )
        {
         $this->drawLine($YPos1,$XPos1,$YPos2,$XPos1,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$BorderTicks));
         $this->drawLine($YPos1,$XPos2,$YPos2,$XPos2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$BorderTicks));
        }

       if ( $AreaName != NULL )
        {
         $XPos = ($XPos2-$XPos1)/2 + $XPos1;
         $YPos = ($YPos2-$YPos1)/2 + $YPos1;

         $this->Shadow = $RestoreShadow;
         $this->drawText($YPos,$XPos,$AreaName,array("R"=>$NameR,"G"=>$NameG,"B"=>$NameB,"Alpha"=>$NameAlpha,"Angle"=>0,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));
         if ( $DisableShadowOnArea ) { $this->Shadow = FALSE; }
        }

       $this->Shadow = $RestoreShadow;
       return(array("X1"=>$XPos1,"X2"=>$XPos2));
      }
    }

   /* Draw an Y threshold with the computed scale */
   function drawThreshold($Value,$Format="")
    {
     $AxisID		= isset($Format["AxisID"]) ? $Format["AxisID"] : 0;
     $R			= isset($Format["R"]) ? $Format["R"] : 255;
     $G			= isset($Format["G"]) ? $Format["G"] : 0;
     $B			= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 50;
     $Weight		= isset($Format["Weight"]) ? $Format["Weight"] : NULL;
     $Ticks		= isset($Format["Ticks"]) ? $Format["Ticks"] : 6;
     $Wide		= isset($Format["Wide"]) ? $Format["Wide"] : FALSE;
     $WideFactor	= isset($Format["WideFactor"]) ? $Format["WideFactor"] : 5;
     $WriteCaption	= isset($Format["WriteCaption"]) ? $Format["WriteCaption"] : FALSE;
     $Caption		= isset($Format["Caption"]) ? $Format["Caption"] : NULL;
     $CaptionAlign	= isset($Format["CaptionAlign"]) ? $Format["CaptionAlign"] : CAPTION_LEFT_TOP;
     $CaptionOffset     = isset($Format["CaptionOffset"]) ? $Format["CaptionOffset"] : 10;
     $CaptionR		= isset($Format["CaptionR"]) ? $Format["CaptionR"] : 255;
     $CaptionG		= isset($Format["CaptionG"]) ? $Format["CaptionG"] : 255;
     $CaptionB		= isset($Format["CaptionB"]) ? $Format["CaptionB"] : 255;
     $CaptionAlpha	= isset($Format["CaptionAlpha"]) ? $Format["CaptionAlpha"] : 100;
     $DrawBox		= isset($Format["DrawBox"]) ? $Format["DrawBox"] : TRUE;
     $DrawBoxBorder	= isset($Format["DrawBoxBorder"]) ? $Format["DrawBoxBorder"] : FALSE;
     $BorderOffset	= isset($Format["BorderOffset"]) ? $Format["BorderOffset"] : 5;
     $BoxRounded	= isset($Format["BoxRounded"]) ? $Format["BoxRounded"] : TRUE;
     $RoundedRadius	= isset($Format["RoundedRadius"]) ? $Format["RoundedRadius"] : 3;
     $BoxR		= isset($Format["BoxR"]) ? $Format["BoxR"] : 0;
     $BoxG		= isset($Format["BoxG"]) ? $Format["BoxG"] : 0;
     $BoxB		= isset($Format["BoxB"]) ? $Format["BoxB"] : 0;
     $BoxAlpha		= isset($Format["BoxAlpha"]) ? $Format["BoxAlpha"] : 20;
     $BoxSurrounding	= isset($Format["BoxSurrounding"]) ? $Format["BoxSurrounding"] : "";
     $BoxBorderR	= isset($Format["BoxBorderR"]) ? $Format["BoxBorderR"] : 255;
     $BoxBorderG	= isset($Format["BoxBorderG"]) ? $Format["BoxBorderG"] : 255;
     $BoxBorderB	= isset($Format["BoxBorderB"]) ? $Format["BoxBorderB"] : 255;
     $BoxBorderAlpha	= isset($Format["BoxBorderAlpha"]) ? $Format["BoxBorderAlpha"] : 100;
     $NoMargin		= isset($Format["NoMargin"]) ? $Format["NoMargin"] : FALSE;

     if ( is_array($Value) ) { foreach ($Value as $Key => $ID) { $this->drawThreshold($ID,$Format); } return(0); }

     $CaptionSettings = array("DrawBox"=>$DrawBox,"DrawBoxBorder"=>$DrawBoxBorder,"BorderOffset"=>$BorderOffset,"BoxRounded"=>$BoxRounded,"RoundedRadius"=>$RoundedRadius,
                              "BoxR"=>$BoxR,"BoxG"=>$BoxG,"BoxB"=>$BoxB,"BoxAlpha"=>$BoxAlpha,"BoxSurrounding"=>$BoxSurrounding,
                              "BoxBorderR"=>$BoxBorderR,"BoxBorderG"=>$BoxBorderG,"BoxBorderB"=>$BoxBorderB,"BoxBorderAlpha"=>$BoxBorderAlpha,
                              "R"=>$CaptionR,"G"=>$CaptionG,"B"=>$CaptionB,"Alpha"=>$CaptionAlpha);

     $Data           = $this->DataSet->getData();
     $AbscissaMargin = $this->getAbscissaMargin($Data);

     if ( $NoMargin ) { $AbscissaMargin = 0; }
     if ( !isset($Data["Axis"][$AxisID]) ) { return(-1); }
     if ( $Caption == NULL ) { $Caption = $Value; }

     if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
      {
       $YPos = $this->scaleComputeY($Value,array("AxisID"=>$AxisID));
       if ( $YPos >= $this->GraphAreaY1+$Data["Axis"][$AxisID]["Margin"] && $YPos <= $this->GraphAreaY2-$Data["Axis"][$AxisID]["Margin"] )
        {
         $X1 = $this->GraphAreaX1 + $AbscissaMargin;
         $X2 = $this->GraphAreaX2 - $AbscissaMargin;

         $this->drawLine($X1,$YPos,$X2,$YPos,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight));

         if ( $Wide )
          {
           $this->drawLine($X1,$YPos-1,$X2,$YPos-1,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/$WideFactor,"Ticks"=>$Ticks));
           $this->drawLine($X1,$YPos+1,$X2,$YPos+1,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/$WideFactor,"Ticks"=>$Ticks));
          }

         if ( $WriteCaption )
          {
           if ( $CaptionAlign == CAPTION_LEFT_TOP )
            { $X = $X1 + $CaptionOffset; $CaptionSettings["Align"] = TEXT_ALIGN_MIDDLELEFT; }
           else 
            { $X = $X2 - $CaptionOffset; $CaptionSettings["Align"] = TEXT_ALIGN_MIDDLERIGHT; }
           
           $this->drawText($X,$YPos,$Caption,$CaptionSettings);
          }
        }

       return(array("Y"=>$YPos));
      }

     if ( $Data["Orientation"] == SCALE_POS_TOPBOTTOM )
      {
       $XPos = $this->scaleComputeY($Value,array("AxisID"=>$AxisID));
       if ( $XPos >= $this->GraphAreaX1+$Data["Axis"][$AxisID]["Margin"] && $XPos <= $this->GraphAreaX2-$Data["Axis"][$AxisID]["Margin"] )
        {
         $Y1 = $this->GraphAreaY1 + $AbscissaMargin;
         $Y2 = $this->GraphAreaY2 - $AbscissaMargin;

         $this->drawLine($XPos,$Y1,$XPos,$Y2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight));

         if ( $Wide )
          {
           $this->drawLine($XPos-1,$Y1,$XPos-1,$Y2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/$WideFactor,"Ticks"=>$Ticks));
           $this->drawLine($XPos+1,$Y1,$XPos+1,$Y2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/$WideFactor,"Ticks"=>$Ticks));
          }

         if ( $WriteCaption )
          {
           if ( $CaptionAlign == CAPTION_LEFT_TOP )
            { $Y = $Y1 + $CaptionOffset; $CaptionSettings["Align"] = TEXT_ALIGN_TOPMIDDLE; }
           else 
            { $Y = $Y2 - $CaptionOffset; $CaptionSettings["Align"] = TEXT_ALIGN_BOTTOMMIDDLE; }

           $CaptionSettings["Align"] = TEXT_ALIGN_TOPMIDDLE;
           $this->drawText($XPos,$Y,$Caption,$CaptionSettings);
          }
        }

       return(array("Y"=>$XPos));
      }
    }

   /* Draw a threshold with the computed scale */
   function drawThresholdArea($Value1,$Value2,$Format="")
    {
     $AxisID	= isset($Format["AxisID"]) ? $Format["AxisID"] : 0;
     $R		= isset($Format["R"]) ? $Format["R"] : 255;
     $G		= isset($Format["G"]) ? $Format["G"] : 0;
     $B		= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 20;
     $Border    = isset($Format["Border"]) ? $Format["Border"] : TRUE;
     $BorderR   = isset($Format["BorderR"]) ? $Format["BorderR"] : $R;
     $BorderG   = isset($Format["BorderG"]) ? $Format["BorderG"] : $G;
     $BorderB   = isset($Format["BorderB"]) ? $Format["BorderB"] : $B;
     $BorderAlpha = isset($Format["BorderAlpha"]) ? $Format["BorderAlpha"] : $Alpha + 20;
     $BorderTicks = isset($Format["BorderTicks"]) ? $Format["BorderTicks"] : 2;
     $AreaName 	= isset($Format["AreaName"]) ? $Format["AreaName"] : NULL;
     $NameAngle	= isset($Format["NameAngle"]) ? $Format["NameAngle"] : ZONE_NAME_ANGLE_AUTO;
     $NameR	= isset($Format["NameR"]) ? $Format["NameR"] : 255;
     $NameG	= isset($Format["NameG"]) ? $Format["NameG"] : 255;
     $NameB	= isset($Format["NameB"]) ? $Format["NameB"] : 255;
     $NameAlpha	= isset($Format["NameAlpha"]) ? $Format["NameAlpha"] : 100;
     $DisableShadowOnArea = isset($Format["DisableShadowOnArea"]) ? $Format["DisableShadowOnArea"] : TRUE;
     $NoMargin	= isset($Format["NoMargin"]) ? $Format["NoMargin"] : FALSE;

     if ($Value1 > $Value2) { list($Value1, $Value2) = array($Value2, $Value1); }

     $RestoreShadow = $this->Shadow;
     if ( $DisableShadowOnArea && $this->Shadow ) { $this->Shadow = FALSE; }

     if ($BorderAlpha >100) { $BorderAlpha = 100;}

     $Data           = $this->DataSet->getData();
     $AbscissaMargin = $this->getAbscissaMargin($Data);

     if ( $NoMargin ) { $AbscissaMargin = 0; }
     if ( !isset($Data["Axis"][$AxisID]) ) { return(-1); }

     if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
      {
       $XPos1 = $this->GraphAreaX1 + $AbscissaMargin;
       $XPos2 = $this->GraphAreaX2 - $AbscissaMargin;
       $YPos1 = $this->scaleComputeY($Value1,array("AxisID"=>$AxisID));
       $YPos2 = $this->scaleComputeY($Value2,array("AxisID"=>$AxisID));

       if ( $YPos1 < $this->GraphAreaY1+$Data["Axis"][$AxisID]["Margin"] ) { $YPos1 = $this->GraphAreaY1+$Data["Axis"][$AxisID]["Margin"]; }
       if ( $YPos1 > $this->GraphAreaY2-$Data["Axis"][$AxisID]["Margin"] ) { $YPos1 = $this->GraphAreaY2-$Data["Axis"][$AxisID]["Margin"]; }
       if ( $YPos2 < $this->GraphAreaY1+$Data["Axis"][$AxisID]["Margin"] ) { $YPos2 = $this->GraphAreaY1+$Data["Axis"][$AxisID]["Margin"]; }
       if ( $YPos2 > $this->GraphAreaY2-$Data["Axis"][$AxisID]["Margin"] ) { $YPos2 = $this->GraphAreaY2-$Data["Axis"][$AxisID]["Margin"]; }

       $this->drawFilledRectangle($XPos1,$YPos1,$XPos2,$YPos2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
       if ( $Border )
        {
         $this->drawLine($XPos1,$YPos1,$XPos2,$YPos1,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$BorderTicks));
         $this->drawLine($XPos1,$YPos2,$XPos2,$YPos2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$BorderTicks));
        }

       if ( $AreaName != NULL )
        {
         $XPos = ($XPos2-$XPos1)/2 + $XPos1;
         $YPos = ($YPos2-$YPos1)/2 + $YPos1;
         $this->Shadow = $RestoreShadow;
         $this->drawText($XPos,$YPos,$AreaName,array("R"=>$NameR,"G"=>$NameG,"B"=>$NameB,"Alpha"=>$NameAlpha,"Angle"=>0,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));
         if ( $DisableShadowOnArea ) { $this->Shadow = FALSE; }
        }

       $this->Shadow = $RestoreShadow;
       return(array("Y1"=>$YPos1,"Y2"=>$YPos2));
      }
     elseif ( $Data["Orientation"] == SCALE_POS_TOPBOTTOM )
      {
       $YPos1 = $this->GraphAreaY1 + $AbscissaMargin;
       $YPos2 = $this->GraphAreaY2 - $AbscissaMargin;
       $XPos1 = $this->scaleComputeY($Value1,array("AxisID"=>$AxisID));
       $XPos2 = $this->scaleComputeY($Value2,array("AxisID"=>$AxisID));

       if ( $XPos1 < $this->GraphAreaX1+$Data["Axis"][$AxisID]["Margin"] ) { $XPos1 = $this->GraphAreaX1+$Data["Axis"][$AxisID]["Margin"]; }
       if ( $XPos1 > $this->GraphAreaX2-$Data["Axis"][$AxisID]["Margin"] ) { $XPos1 = $this->GraphAreaX2-$Data["Axis"][$AxisID]["Margin"]; }
       if ( $XPos2 < $this->GraphAreaX1+$Data["Axis"][$AxisID]["Margin"] ) { $XPos2 = $this->GraphAreaX1+$Data["Axis"][$AxisID]["Margin"]; }
       if ( $XPos2 > $this->GraphAreaX2-$Data["Axis"][$AxisID]["Margin"] ) { $XPos2 = $this->GraphAreaX2-$Data["Axis"][$AxisID]["Margin"]; }

       $this->drawFilledRectangle($XPos1,$YPos1,$XPos2,$YPos2,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
       if ( $Border )
        {
         $this->drawLine($XPos1,$YPos1,$XPos1,$YPos2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$BorderTicks));
         $this->drawLine($XPos2,$YPos1,$XPos2,$YPos2,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Ticks"=>$BorderTicks));
        }

       if ( $AreaName != NULL )
        {
         $XPos = ($YPos2-$YPos1)/2 + $YPos1;
         $YPos = ($XPos2-$XPos1)/2 + $XPos1;

         if ( $NameAngle == ZONE_NAME_ANGLE_AUTO )
          {
           $TxtPos   = $this->getTextBox($XPos,$YPos,$this->FontName,$this->FontSize,0,$AreaName);
           $TxtWidth = $TxtPos[1]["X"] - $TxtPos[0]["X"];
           if ( abs($XPos2 - $XPos1) > $TxtWidth ) { $NameAngle = 0; } else { $NameAngle = 90; }
          }
         $this->Shadow = $RestoreShadow;
         $this->drawText($YPos,$XPos,$AreaName,array("R"=>$NameR,"G"=>$NameG,"B"=>$NameB,"Alpha"=>$NameAlpha,"Angle"=>$NameAngle,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));
         if ( $DisableShadowOnArea ) { $this->Shadow = FALSE; }
        }

       $this->Shadow = $RestoreShadow;
       return(array("Y1"=>$XPos1,"Y2"=>$XPos2));
      }
    }

   function scaleGetXSettings()
    {
     $Data = $this->DataSet->getData();
     foreach($Data["Axis"] as $AxisID => $Settings)
      {
       if ( $Settings["Identity"] == AXIS_X )
        {
         $Rows = $Settings["Rows"];

         return(array($Settings["Margin"],$Rows));
        }
      }
    }

   function scaleComputeY($Values,$Option="",$ReturnOnly0Height=FALSE)
    {
     $AxisID	= isset($Option["AxisID"]) ? $Option["AxisID"] : 0;
     $SerieName	= isset($Option["SerieName"]) ? $Option["SerieName"] : NULL;

     $Data = $this->DataSet->getData();
     if ( !isset($Data["Axis"][$AxisID]) ) { return(-1); }

     if ( $SerieName != NULL ) { $AxisID = $Data["Series"][$SerieName]["Axis"]; }
     if ( !is_array($Values) ) { $tmp = $Values; $Values = ""; $Values[0] = $tmp; }

     $Result = "";
     if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
      {
       $Height      = ($this->GraphAreaY2 - $this->GraphAreaY1) - $Data["Axis"][$AxisID]["Margin"]*2;
       $ScaleHeight = $Data["Axis"][$AxisID]["ScaleMax"] - $Data["Axis"][$AxisID]["ScaleMin"];
       $Step        = $Height / $ScaleHeight;

       if ( $ReturnOnly0Height )
        { foreach($Values as $Key => $Value) { if ( $Value == VOID ) { $Result[] = VOID; } else { $Result[] = $Step * $Value; } } }
       else
        { foreach($Values as $Key => $Value) { if ( $Value == VOID ) { $Result[] = VOID; } else { $Result[] = $this->GraphAreaY2 - $Data["Axis"][$AxisID]["Margin"] - ($Step * ($Value-$Data["Axis"][$AxisID]["ScaleMin"])); } } }
      }
     else
      {
       $Width      = ($this->GraphAreaX2 - $this->GraphAreaX1) - $Data["Axis"][$AxisID]["Margin"]*2;
       $ScaleWidth = $Data["Axis"][$AxisID]["ScaleMax"] - $Data["Axis"][$AxisID]["ScaleMin"];
       $Step       = $Width / $ScaleWidth;

       if ( $ReturnOnly0Height )
        { foreach($Values as $Key => $Value) { if ( $Value == VOID ) { $Result[] = VOID; } else { $Result[] = $Step * $Value; } } }
       else
        { foreach($Values as $Key => $Value) { if ( $Value == VOID ) { $Result[] = VOID; } else { $Result[] = $this->GraphAreaX1 + $Data["Axis"][$AxisID]["Margin"] + ($Step * ($Value-$Data["Axis"][$AxisID]["ScaleMin"])); } } }
      }

     if ( count($Result) == 1 )
      return($Result[0]);
     else
      return($Result);
    }

   /* Format the axis values */
   function scaleFormat($Value,$Mode=NULL,$Format=NULL,$Unit=NULL)
    {
     if ( $Value == VOID ) { return(""); }

     if ( $Mode == AXIS_FORMAT_TRAFFIC )
      {
       if ( $Value == 0 ) { return("0B"); }
       $Units = array("B","KB","MB","GB","TB","PB");
       $Sign = ""; if ( $Value < 0 ) { $Value = abs($Value); $Sign = "-"; }

       $Value = number_format($Value/pow(1024,($Scale=floor(log($Value,1024)))),2,",",".");
       return($Sign.$Value." ".$Units[$Scale]);
      }

     if ( $Mode == AXIS_FORMAT_CUSTOM )
      { if ( function_exists($Format) ) { return(call_user_func($Format,$Value)); } }

     if ( $Mode == AXIS_FORMAT_DATE )
      { if ( $Format == NULL ) { $Pattern = "d/m/Y"; } else { $Pattern = $Format; } return(gmdate($Pattern,$Value)); }

     if ( $Mode == AXIS_FORMAT_TIME )
      { if ( $Format == NULL ) { $Pattern = "H:i:s"; } else { $Pattern = $Format; } return(gmdate($Pattern,$Value)); }

     if ( $Mode == AXIS_FORMAT_CURRENCY )
      { return($Format.number_format($Value,2)); }

     if ( $Mode == AXIS_FORMAT_METRIC )
      {
       if (abs($Value) > 1000000000)
        return(round($Value/1000000000,$Format)."g".$Unit);
       if (abs($Value) > 1000000)
        return(round($Value/1000000,$Format)."m".$Unit);
       elseif (abs($Value) >= 1000)
        return(round($Value/1000,$Format)."k".$Unit);
       
      }
     return($Value.$Unit);
    }

   /* Write Max value on a chart */
   function writeBounds($Type=BOUND_BOTH,$Format=NULL)
    {
     $MaxLabelTxt	= isset($Format["MaxLabelTxt"]) ? $Format["MaxLabelTxt"] : "max=";
     $MinLabelTxt	= isset($Format["MinLabelTxt"]) ? $Format["MinLabelTxt"] : "min=";
     $Decimals		= isset($Format["Decimals"]) ? $Format["Decimals"] : 1;
     $ExcludedSeries	= isset($Format["ExcludedSeries"]) ? $Format["ExcludedSeries"] : "";
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 4;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $MaxDisplayR	= isset($Format["MaxDisplayR"]) ? $Format["MaxDisplayR"] : 0;
     $MaxDisplayG	= isset($Format["MaxDisplayG"]) ? $Format["MaxDisplayG"] : 0;
     $MaxDisplayB	= isset($Format["MaxDisplayB"]) ? $Format["MaxDisplayB"] : 0;
     $MinDisplayR	= isset($Format["MinDisplayR"]) ? $Format["MinDisplayR"] : 255;
     $MinDisplayG	= isset($Format["MinDisplayG"]) ? $Format["MinDisplayG"] : 255;
     $MinDisplayB	= isset($Format["MinDisplayB"]) ? $Format["MinDisplayB"] : 255;
     $MinLabelPos	= isset($Format["MinLabelPos"]) ? $Format["MinLabelPos"] : BOUND_LABEL_POS_AUTO;
     $MaxLabelPos	= isset($Format["MaxLabelPos"]) ? $Format["MaxLabelPos"] : BOUND_LABEL_POS_AUTO;
     $DrawBox		= isset($Format["DrawBox"]) ? $Format["DrawBox"] : TRUE;
     $DrawBoxBorder	= isset($Format["DrawBoxBorder"]) ? $Format["DrawBoxBorder"] : FALSE;
     $BorderOffset	= isset($Format["BorderOffset"]) ? $Format["BorderOffset"] : 5;
     $BoxRounded	= isset($Format["BoxRounded"]) ? $Format["BoxRounded"] : TRUE;
     $RoundedRadius	= isset($Format["RoundedRadius"]) ? $Format["RoundedRadius"] : 3;
     $BoxR		= isset($Format["BoxR"]) ? $Format["BoxR"] : 0;
     $BoxG		= isset($Format["BoxG"]) ? $Format["BoxG"] : 0;
     $BoxB		= isset($Format["BoxB"]) ? $Format["BoxB"] : 0;
     $BoxAlpha		= isset($Format["BoxAlpha"]) ? $Format["BoxAlpha"] : 20;
     $BoxSurrounding	= isset($Format["BoxSurrounding"]) ? $Format["BoxSurrounding"] : "";
     $BoxBorderR	= isset($Format["BoxBorderR"]) ? $Format["BoxBorderR"] : 255;
     $BoxBorderG	= isset($Format["BoxBorderG"]) ? $Format["BoxBorderG"] : 255;
     $BoxBorderB	= isset($Format["BoxBorderB"]) ? $Format["BoxBorderB"] : 255;
     $BoxBorderAlpha	= isset($Format["BoxBorderAlpha"]) ? $Format["BoxBorderAlpha"] : 100;

     $CaptionSettings = array("DrawBox"=>$DrawBox,"DrawBoxBorder"=>$DrawBoxBorder,"BorderOffset"=>$BorderOffset,"BoxRounded"=>$BoxRounded,"RoundedRadius"=>$RoundedRadius,
                              "BoxR"=>$BoxR,"BoxG"=>$BoxG,"BoxB"=>$BoxB,"BoxAlpha"=>$BoxAlpha,"BoxSurrounding"=>$BoxSurrounding,
                              "BoxBorderR"=>$BoxBorderR,"BoxBorderG"=>$BoxBorderG,"BoxBorderB"=>$BoxBorderB,"BoxBorderAlpha"=>$BoxBorderAlpha);

     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     $Data = $this->DataSet->getData();
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] && !isset($ExcludedSeries[$SerieName]))
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"];
         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }

         $MinValue = $this->DataSet->getMin($SerieName);
         $MaxValue = $this->DataSet->getMax($SerieName);

         $MinPos = VOID; $MaxPos = VOID;
         foreach($Serie["Data"] as $Key => $Value)
          { 
           if ( $Value == $MinValue && $MinPos == VOID ) { $MinPos = $Key; }
           if ( $Value == $MaxValue ) { $MaxPos = $Key; }
          }

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           $XStep       = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs;
           $X           = $this->GraphAreaX1 + $XMargin;
           $SerieOffset = isset($Serie["XOffset"]) ? $Serie["XOffset"] : 0;

           if ( $Type == BOUND_MAX || $Type == BOUND_BOTH )
            {
             if ( $MaxLabelPos == BOUND_LABEL_POS_TOP    || ( $MaxLabelPos ==  BOUND_LABEL_POS_AUTO && $MaxValue >= 0) ) { $YPos  = $PosArray[$MaxPos] - $DisplayOffset + 2; $Align = TEXT_ALIGN_BOTTOMMIDDLE; }
             if ( $MaxLabelPos == BOUND_LABEL_POS_BOTTOM || ( $MaxLabelPos ==  BOUND_LABEL_POS_AUTO && $MaxValue < 0) ) { $YPos  = $PosArray[$MaxPos] + $DisplayOffset + 2; $Align = TEXT_ALIGN_TOPMIDDLE; }

             $XPos  = $X + $MaxPos*$XStep + $SerieOffset;
             $Label = $MaxLabelTxt.$this->scaleFormat(round($MaxValue,$Decimals),$Mode,$Format,$Unit);

             $TxtPos  = $this->getTextBox($XPos,$YPos,$this->FontName,$this->FontSize,0,$Label);
             $XOffset = 0; $YOffset = 0;
             if ( $TxtPos[0]["X"] < $this->GraphAreaX1 ) { $XOffset = (($this->GraphAreaX1 - $TxtPos[0]["X"])/2); }
             if ( $TxtPos[1]["X"] > $this->GraphAreaX2 ) { $XOffset = -(($TxtPos[1]["X"] - $this->GraphAreaX2)/2); }
             if ( $TxtPos[2]["Y"] < $this->GraphAreaY1 ) { $YOffset = $this->GraphAreaY1 - $TxtPos[2]["Y"]; }
             if ( $TxtPos[0]["Y"] > $this->GraphAreaY2 ) { $YOffset = -($TxtPos[0]["Y"] - $this->GraphAreaY2); }

             $CaptionSettings["R"] = $MaxDisplayR; $CaptionSettings["G"] = $MaxDisplayG;
             $CaptionSettings["B"] = $MaxDisplayB; $CaptionSettings["Align"] = $Align;

             $this->drawText($XPos+$XOffset,$YPos+$YOffset,$Label,$CaptionSettings);
            }

           if ( $Type == BOUND_MIN || $Type == BOUND_BOTH )
            {
             if ( $MinLabelPos == BOUND_LABEL_POS_TOP    || ( $MinLabelPos ==  BOUND_LABEL_POS_AUTO && $MinValue >= 0) ) { $YPos  = $PosArray[$MinPos] - $DisplayOffset + 2; $Align = TEXT_ALIGN_BOTTOMMIDDLE; }
             if ( $MinLabelPos == BOUND_LABEL_POS_BOTTOM || ( $MinLabelPos ==  BOUND_LABEL_POS_AUTO && $MinValue < 0) ) { $YPos  = $PosArray[$MinPos] + $DisplayOffset + 2; $Align = TEXT_ALIGN_TOPMIDDLE; }

             $XPos  = $X + $MinPos*$XStep + $SerieOffset;
             $Label = $MinLabelTxt.$this->scaleFormat(round($MinValue,$Decimals),$Mode,$Format,$Unit);

             $TxtPos  = $this->getTextBox($XPos,$YPos,$this->FontName,$this->FontSize,0,$Label);
             $XOffset = 0; $YOffset = 0;
             if ( $TxtPos[0]["X"] < $this->GraphAreaX1 ) { $XOffset = (($this->GraphAreaX1 - $TxtPos[0]["X"])/2); }
             if ( $TxtPos[1]["X"] > $this->GraphAreaX2 ) { $XOffset = -(($TxtPos[1]["X"] - $this->GraphAreaX2)/2); }
             if ( $TxtPos[2]["Y"] < $this->GraphAreaY1 ) { $YOffset = $this->GraphAreaY1 - $TxtPos[2]["Y"]; }
             if ( $TxtPos[0]["Y"] > $this->GraphAreaY2 ) { $YOffset = -($TxtPos[0]["Y"] - $this->GraphAreaY2); }

             $CaptionSettings["R"] = $MinDisplayR; $CaptionSettings["G"] = $MinDisplayG;
             $CaptionSettings["B"] = $MinDisplayB; $CaptionSettings["Align"] = $Align;

             $this->drawText($XPos+$XOffset,$YPos-$DisplayOffset+$YOffset,$Label,$CaptionSettings);
            }
          }
         else
          {
           $XStep       = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs;
           $X           = $this->GraphAreaY1 + $XMargin;
           $SerieOffset = isset($Serie["XOffset"]) ? $Serie["XOffset"] : 0;

           if ( $Type == BOUND_MAX || $Type == BOUND_BOTH )
            {
             if ( $MaxLabelPos == BOUND_LABEL_POS_TOP    || ( $MaxLabelPos ==  BOUND_LABEL_POS_AUTO && $MaxValue >= 0) ) { $YPos  = $PosArray[$MaxPos] + $DisplayOffset + 2; $Align = TEXT_ALIGN_MIDDLELEFT; }
             if ( $MaxLabelPos == BOUND_LABEL_POS_BOTTOM || ( $MaxLabelPos ==  BOUND_LABEL_POS_AUTO && $MaxValue < 0) ) { $YPos  = $PosArray[$MaxPos] - $DisplayOffset + 2; $Align = TEXT_ALIGN_MIDDLERIGHT; }

             $XPos  = $X + $MaxPos*$XStep + $SerieOffset;
             $Label = $MaxLabelTxt.$this->scaleFormat($MaxValue,$Mode,$Format,$Unit);

             $TxtPos  = $this->getTextBox($YPos,$XPos,$this->FontName,$this->FontSize,0,$Label);
             $XOffset = 0; $YOffset = 0;
             if ( $TxtPos[0]["X"] < $this->GraphAreaX1 ) { $XOffset = $this->GraphAreaX1 - $TxtPos[0]["X"]; }
             if ( $TxtPos[1]["X"] > $this->GraphAreaX2 ) { $XOffset = -($TxtPos[1]["X"] - $this->GraphAreaX2); }
             if ( $TxtPos[2]["Y"] < $this->GraphAreaY1 ) { $YOffset = ($this->GraphAreaY1 - $TxtPos[2]["Y"])/2; }
             if ( $TxtPos[0]["Y"] > $this->GraphAreaY2 ) { $YOffset = -(($TxtPos[0]["Y"] - $this->GraphAreaY2)/2);}

             $CaptionSettings["R"] = $MaxDisplayR; $CaptionSettings["G"] = $MaxDisplayG;
             $CaptionSettings["B"] = $MaxDisplayB; $CaptionSettings["Align"] = $Align;

             $this->drawText($YPos+$XOffset,$XPos+$YOffset,$Label,$CaptionSettings);
            }

           if ( $Type == BOUND_MIN || $Type == BOUND_BOTH )
            {
             if ( $MinLabelPos == BOUND_LABEL_POS_TOP    || ( $MinLabelPos ==  BOUND_LABEL_POS_AUTO && $MinValue >= 0) ) { $YPos  = $PosArray[$MinPos] + $DisplayOffset + 2; $Align = TEXT_ALIGN_MIDDLELEFT; }
             if ( $MinLabelPos == BOUND_LABEL_POS_BOTTOM || ( $MinLabelPos ==  BOUND_LABEL_POS_AUTO && $MinValue < 0) ) { $YPos  = $PosArray[$MinPos] - $DisplayOffset + 2; $Align = TEXT_ALIGN_MIDDLERIGHT; }

             $XPos  = $X + $MinPos*$XStep + $SerieOffset;
             $Label = $MinLabelTxt.$this->scaleFormat($MinValue,$Mode,$Format,$Unit);

             $TxtPos  = $this->getTextBox($YPos,$XPos,$this->FontName,$this->FontSize,0,$Label);
             $XOffset = 0; $YOffset = 0;
             if ( $TxtPos[0]["X"] < $this->GraphAreaX1 ) { $XOffset = $this->GraphAreaX1 - $TxtPos[0]["X"]; }
             if ( $TxtPos[1]["X"] > $this->GraphAreaX2 ) { $XOffset = -($TxtPos[1]["X"] - $this->GraphAreaX2); }
             if ( $TxtPos[2]["Y"] < $this->GraphAreaY1 ) { $YOffset = ($this->GraphAreaY1 - $TxtPos[2]["Y"])/2; }
             if ( $TxtPos[0]["Y"] > $this->GraphAreaY2 ) { $YOffset = -(($TxtPos[0]["Y"] - $this->GraphAreaY2)/2);}

             $CaptionSettings["R"] = $MinDisplayR; $CaptionSettings["G"] = $MinDisplayG;
             $CaptionSettings["B"] = $MinDisplayB; $CaptionSettings["Align"] = $Align;

             $this->drawText($YPos+$XOffset,$XPos+$YOffset,$Label,$CaptionSettings);
            }
          }
        }
      }
    }

   /* Draw a plot chart */
   function drawPlotChart($Format=NULL)
    {
     $PlotSize		= isset($Format["PlotSize"]) ? $Format["PlotSize"] : NULL;
     $PlotBorder	= isset($Format["PlotBorder"]) ? $Format["PlotBorder"] : FALSE;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : 50;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : 50;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : 50;
     $BorderAlpha	= isset($Format["BorderAlpha"]) ? $Format["BorderAlpha"] : 30;
     $BorderSize	= isset($Format["BorderSize"]) ? $Format["BorderSize"] : 2;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] : FALSE;
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 4;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         if ( isset($Serie["Weight"]) ) { $SerieWeight = $Serie["Weight"] + 2; } else { $SerieWeight = 2; }
         if ( $PlotSize != NULL ) { $SerieWeight = $PlotSize; }

         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"];
         if ( $Surrounding != NULL ) { $BorderR = $R + $Surrounding; $BorderG = $G + $Surrounding; $BorderB = $B + $Surrounding; }
         if ( isset($Serie["Picture"]) )
          { $Picture = $Serie["Picture"]; list($PicWidth,$PicHeight,$PicType) = $this->getPicInfo($Picture); }
         else { $Picture = NULL; $PicOffset = 0; }

         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }

         $AxisID	= $Serie["Axis"];
         $Shape		= $Serie["Shape"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         if (isset($Serie["Description"])) { $SerieDescription = $Serie["Description"]; } else { $SerieDescription = $SerieName; }

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           if ( $Picture != NULL ) { $PicOffset = $PicHeight / 2; $SerieWeight = 0; }
           $X = $this->GraphAreaX1 + $XMargin;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           foreach($PosArray as $Key => $Y)
            {
             if ( $DisplayValues ) 
              $this->drawText($X,$Y-$DisplayOffset-$SerieWeight-$BorderSize-$PicOffset,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

             if ( $Y != VOID )
              {
               if ( $RecordImageMap ) { $this->addToImageMap("CIRCLE",floor($X).",".floor($Y).",".$SerieWeight,$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

               if ( $Picture != NULL )
                { $this->drawFromPicture($PicType,$Picture,$X-$PicWidth/2,$Y-$PicHeight/2); }
               else
                { $this->drawShape($X,$Y,$Shape,$SerieWeight,$PlotBorder,$BorderSize,$R,$G,$B,$Alpha,$BorderR,$BorderG,$BorderB,$BorderAlpha); }
              }
             $X = $X + $XStep;
            }
          }
         else
          {
           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           if ( $Picture != NULL ) { $PicOffset = $PicWidth / 2; $SerieWeight = 0; }
           $Y = $this->GraphAreaY1 + $XMargin;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           foreach($PosArray as $Key => $X)
            {
             if ( $DisplayValues ) 
              $this->drawText($X+$DisplayOffset+$SerieWeight+$BorderSize+$PicOffset,$Y,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("Angle"=>270,"R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

             if ( $X != VOID )
              {
               if ( $RecordImageMap ) { $this->addToImageMap("CIRCLE",floor($X).",".floor($Y).",".$SerieWeight,$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

               if ( $Picture != NULL )
                { $this->drawFromPicture($PicType,$Picture,$X-$PicWidth/2,$Y-$PicHeight/2); }
               else
                { $this->drawShape($X,$Y,$Shape,$SerieWeight,$PlotBorder,$BorderSize,$R,$G,$B,$Alpha,$BorderR,$BorderG,$BorderB,$BorderAlpha); }
              }
             $Y = $Y + $YStep;
            }
          }
        }
      }
    }

   /* Draw a spline chart */
   function drawSplineChart($Format=NULL)
    {
     $BreakVoid		= isset($Format["BreakVoid"]) ? $Format["BreakVoid"] : TRUE;
     $VoidTicks		= isset($Format["VoidTicks"]) ? $Format["VoidTicks"] : 4;
     $BreakR		= isset($Format["BreakR"]) ? $Format["BreakR"] : NULL; // 234
     $BreakG		= isset($Format["BreakG"]) ? $Format["BreakG"] : NULL; // 55
     $BreakB		= isset($Format["BreakB"]) ? $Format["BreakB"] : NULL; // 26
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] : FALSE;
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 2;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;
     $ImageMapPlotSize  = isset($Format["ImageMapPlotSize"]) ? $Format["ImageMapPlotSize"] : 5;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"]; $Weight = $Serie["Weight"];

         if ( $BreakR == NULL )
          $BreakSettings = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$VoidTicks);
         else
          $BreakSettings = array("R"=>$BreakR,"G"=>$BreakG,"B"=>$BreakB,"Alpha"=>$Alpha,"Ticks"=>$VoidTicks,"Weight"=>$Weight);

         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         if (isset($Serie["Description"])) { $SerieDescription = $Serie["Description"]; } else { $SerieDescription = $SerieName; }

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X     = $this->GraphAreaX1 + $XMargin; $WayPoints = "";
           $Force = $XStep / 5;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $LastGoodY = NULL; $LastGoodX = NULL; $LastX = 1; $LastY = 1;
           foreach($PosArray as $Key => $Y)
            {
             if ( $DisplayValues ) 
              $this->drawText($X,$Y-$DisplayOffset,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
	
             if ( $RecordImageMap && $Y != VOID ) { $this->addToImageMap("CIRCLE",floor($X).",".floor($Y).",".$ImageMapPlotSize,$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

             if ( $Y == VOID && $LastY != NULL )
              { $this->drawSpline($WayPoints,array("Force"=>$Force,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight)); $WayPoints = ""; }

             if ( $Y != VOID && $LastY == NULL && $LastGoodY != NULL && !$BreakVoid )
              { $this->drawLine($LastGoodX,$LastGoodY,$X,$Y,$BreakSettings); }

             if ( $Y != VOID )
              $WayPoints[] = array($X,$Y);

             if ( $Y != VOID ) { $LastGoodY = $Y; $LastGoodX = $X; }
             if ( $Y == VOID ) { $Y = NULL; }

             $LastX = $X; $LastY = $Y;
             $X = $X + $XStep;
            }
           $this->drawSpline($WayPoints,array("Force"=>$Force,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight));
          }
         else
          {
           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y     = $this->GraphAreaY1 + $XMargin; $WayPoints = "";
           $Force = $YStep / 5;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $LastGoodY = NULL; $LastGoodX = NULL; $LastX = 1; $LastY = 1;
           foreach($PosArray as $Key => $X)
            {
             if ( $DisplayValues ) 
              $this->drawText($X+$DisplayOffset,$Y,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("Angle"=>270,"R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

             if ( $RecordImageMap && $X != VOID ) { $this->addToImageMap("CIRCLE",floor($X).",".floor($Y).",".$ImageMapPlotSize,$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

             if ( $X == VOID && $LastX != NULL )
              { $this->drawSpline($WayPoints,array("Force"=>$Force,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight)); $WayPoints = ""; }

             if ( $X != VOID && $LastX == NULL && $LastGoodX != NULL && !$BreakVoid )
              { $this->drawLine($LastGoodX,$LastGoodY,$X,$Y,$BreakSettings); }

             if ( $X != VOID )
              $WayPoints[] = array($X,$Y);

             if ( $X != VOID ) { $LastGoodX = $X; $LastGoodY = $Y; }
             if ( $X == VOID ) { $X = NULL; }

             $LastX = $X; $LastY = $Y;
             $Y = $Y + $YStep;
            }
           $this->drawSpline($WayPoints,array("Force"=>$Force,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight));
          }
        }
      }
    }

   /* Draw a filled spline chart */
   function drawFilledSplineChart($Format=NULL)
    {
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] : FALSE;
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 2;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $AroundZero	= isset($Format["AroundZero"]) ? $Format["AroundZero"] : TRUE;
     $Threshold		= isset($Format["Threshold"]) ? $Format["Threshold"] : NULL;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"];
         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));
         if ( $AroundZero ) { $YZero = $this->scaleComputeY(0,array("AxisID"=>$Serie["Axis"])); }

         if ( $Threshold != NULL )
          {
           foreach($Threshold as $Key => $Params)
            {
             $Threshold[$Key]["MinX"] = $this->scaleComputeY($Params["Min"],array("AxisID"=>$Serie["Axis"]));
             $Threshold[$Key]["MaxX"] = $this->scaleComputeY($Params["Max"],array("AxisID"=>$Serie["Axis"]));
            }
          }

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X     = $this->GraphAreaX1 + $XMargin; $WayPoints = "";
           $Force = $XStep / 5;

           if ( !$AroundZero ) { $YZero = $this->GraphAreaY2-1; }
           if ( $YZero > $this->GraphAreaY2-1 ) { $YZero = $this->GraphAreaY2-1; }
           if ( $YZero < $this->GraphAreaY1+1 ) { $YZero = $this->GraphAreaY1+1; }

           $LastX = ""; $LastY = "";
           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           foreach($PosArray as $Key => $Y)
            {
             if ( $DisplayValues ) 
              $this->drawText($X,$Y-$DisplayOffset,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

             if ( $Y == VOID )
              {
               $Area = $this->drawSpline($WayPoints,array("Force"=>$Force,"PathOnly"=>TRUE));

               if ( $Area != "" )
                {
                 foreach ($Area as $key => $Points)
                  {
                   $Corners = ""; $Corners[] = $Area[$key][0]["X"]; $Corners[] = $YZero;
                   foreach($Points as $subKey => $Point)
                    {
                     if ( $subKey == count($Points)-1) { $Corners[] = $Point["X"]-1; } else { $Corners[] = $Point["X"]; }
                     $Corners[] = $Point["Y"]+1;
                    }
                   $Corners[] = $Points[$subKey]["X"]-1; $Corners[] = $YZero;

                   $this->drawPolygonChart($Corners,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/2,"NoBorder"=>TRUE,"Threshold"=>$Threshold));
                  }
                 $this->drawSpline($WayPoints,array("Force"=>$Force,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
                }

               $WayPoints = "";
              }
             else
              $WayPoints[] = array($X,$Y-.5); /* -.5 for AA visual fix */

             $X = $X + $XStep;
            }
           $Area = $this->drawSpline($WayPoints,array("Force"=>$Force,"PathOnly"=>TRUE));

           if ( $Area != "" )
            {
             foreach ($Area as $key => $Points)
              {
               $Corners = ""; $Corners[] = $Area[$key][0]["X"]; $Corners[] = $YZero;
               foreach($Points as $subKey => $Point)
                {
                 if ( $subKey == count($Points)-1) { $Corners[] = $Point["X"]-1; } else { $Corners[] = $Point["X"]; }
                 $Corners[] = $Point["Y"]+1;
                }
               $Corners[] = $Points[$subKey]["X"]-1; $Corners[] = $YZero;

               $this->drawPolygonChart($Corners,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/2,"NoBorder"=>TRUE,"Threshold"=>$Threshold));
              }
             $this->drawSpline($WayPoints,array("Force"=>$Force,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
            }
          }
         else
          {
           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y     = $this->GraphAreaY1 + $XMargin; $WayPoints = "";
           $Force = $YStep / 5;

           if ( !$AroundZero ) { $YZero = $this->GraphAreaX1+1; }
           if ( $YZero > $this->GraphAreaX2-1 ) { $YZero = $this->GraphAreaX2-1; }
           if ( $YZero < $this->GraphAreaX1+1 ) { $YZero = $this->GraphAreaX1+1; }

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           foreach($PosArray as $Key => $X)
            {
             if ( $DisplayValues ) 
              $this->drawText($X+$DisplayOffset,$Y,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("Angle"=>270,"R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

             if ( $X == VOID )
              {
               $Area = $this->drawSpline($WayPoints,array("Force"=>$Force,"PathOnly"=>TRUE));

               if ( $Area != "" )
                {
                 foreach ($Area as $key => $Points)
                  {
                   $Corners = ""; $Corners[] = $YZero; $Corners[] = $Area[$key][0]["Y"];
                   foreach($Points as $subKey => $Point)
                    {
                     if ( $subKey == count($Points)-1) { $Corners[] = $Point["X"]-1; } else { $Corners[] = $Point["X"]; }
                     $Corners[] = $Point["Y"];
                    }
                   $Corners[] = $YZero; $Corners[] = $Points[$subKey]["Y"]-1;

                   $this->drawPolygonChart($Corners,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/2,"NoBorder"=>TRUE,"Threshold"=>$Threshold));
                  }
                 $this->drawSpline($WayPoints,array("Force"=>$Force,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
                }

               $WayPoints = "";
              }
             else
              $WayPoints[] = array($X,$Y);

             $Y = $Y + $YStep;
            }
           $Area = $this->drawSpline($WayPoints,array("Force"=>$Force,"PathOnly"=>TRUE));

           if ( $Area != "" )
            {
             foreach ($Area as $key => $Points)
              {
               $Corners = ""; $Corners[] = $YZero; $Corners[] = $Area[$key][0]["Y"];
               foreach($Points as $subKey => $Point)
                {
                 if ( $subKey == count($Points)-1) { $Corners[] = $Point["X"]-1; } else { $Corners[] = $Point["X"]; }
                 $Corners[] = $Point["Y"];
                }
               $Corners[] = $YZero; $Corners[] = $Points[$subKey]["Y"]-1;

               $this->drawPolygonChart($Corners,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha/2,"NoBorder"=>TRUE,"Threshold"=>$Threshold));
              }
             $this->drawSpline($WayPoints,array("Force"=>$Force,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks));
            }

          }
        }
      }
    }

   /* Draw a line chart */
   function drawLineChart($Format=NULL)
    {
     $BreakVoid		= isset($Format["BreakVoid"]) ? $Format["BreakVoid"] : TRUE;
     $VoidTicks		= isset($Format["VoidTicks"]) ? $Format["VoidTicks"] : 4;
     $BreakR		= isset($Format["BreakR"]) ? $Format["BreakR"] : NULL;
     $BreakG		= isset($Format["BreakG"]) ? $Format["BreakG"] : NULL;
     $BreakB		= isset($Format["BreakB"]) ? $Format["BreakB"] : NULL;
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] : FALSE;
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 2;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;
     $ImageMapPlotSize  = isset($Format["ImageMapPlotSize"]) ? $Format["ImageMapPlotSize"] : 5;
     $ForceColor	= isset($Format["ForceColor"]) ? $Format["ForceColor"] : FALSE;
     $ForceR		= isset($Format["ForceR"]) ? $Format["ForceR"] : 0;
     $ForceG		= isset($Format["ForceG"]) ? $Format["ForceG"] : 0;
     $ForceB		= isset($Format["ForceB"]) ? $Format["ForceB"] : 0;
     $ForceAlpha	= isset($Format["ForceAlpha"]) ? $Format["ForceAlpha"] : 100;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"]; $Weight = $Serie["Weight"];

         if ( $ForceColor )
          { $R = $ForceR; $G = $ForceG; $B = $ForceB; $Alpha = $ForceAlpha; }

         if ( $BreakR == NULL )
          $BreakSettings = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$VoidTicks,"Weight"=>$Weight);
         else
          $BreakSettings = array("R"=>$BreakR,"G"=>$BreakG,"B"=>$BreakB,"Alpha"=>$Alpha,"Ticks"=>$VoidTicks,"Weight"=>$Weight);

         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         if (isset($Serie["Description"])) { $SerieDescription = $Serie["Description"]; } else { $SerieDescription = $SerieName; }

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X = $this->GraphAreaX1 + $XMargin; $LastX = NULL; $LastY = NULL;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $LastGoodY = NULL; $LastGoodX = NULL;
           foreach($PosArray as $Key => $Y)
            {
             if ( $DisplayValues && $Serie["Data"][$Key] != VOID ) 
              {
               if ( $Serie["Data"][$Key] > 0 ) { $Align = TEXT_ALIGN_BOTTOMMIDDLE; $Offset = $DisplayOffset; } else { $Align = TEXT_ALIGN_TOPMIDDLE; $Offset = -$DisplayOffset; }
               $this->drawText($X,$Y-$Offset-$Weight,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>$Align));
              }

             if ( $RecordImageMap && $Y != VOID ) { $this->addToImageMap("CIRCLE",floor($X).",".floor($Y).",".$ImageMapPlotSize,$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

             if ( $Y != VOID && $LastX != NULL && $LastY != NULL )
              $this->drawLine($LastX,$LastY,$X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight));

             if ( $Y != VOID && $LastY == NULL && $LastGoodY != NULL && !$BreakVoid )
              { $this->drawLine($LastGoodX,$LastGoodY,$X,$Y,$BreakSettings); $LastGoodY = NULL; }

             if ( $Y != VOID ) { $LastGoodY = $Y; $LastGoodX = $X; }
             if ( $Y == VOID ) { $Y = NULL; }

             $LastX = $X; $LastY = $Y;
             $X = $X + $XStep;
            }
          }
         else
          {
           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y = $this->GraphAreaY1 + $XMargin; $LastX = NULL; $LastY = NULL;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $LastGoodY = NULL; $LastGoodX = NULL;
           foreach($PosArray as $Key => $X)
            {
             if ( $DisplayValues && $Serie["Data"][$Key] != VOID ) 
              { $this->drawText($X+$DisplayOffset+$Weight,$Y,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("Angle"=>270,"R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); }

             if ( $RecordImageMap && $X != VOID ) { $this->addToImageMap("CIRCLE",floor($X).",".floor($Y).",".$ImageMapPlotSize,$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

             if ( $X != VOID && $LastX != NULL && $LastY != NULL )
              $this->drawLine($LastX,$LastY,$X,$Y,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight));

             if ( $X != VOID && $LastX == NULL && $LastGoodY != NULL && !$BreakVoid )
              { $this->drawLine($LastGoodX,$LastGoodY,$X,$Y,$BreakSettings); $LastGoodY = NULL; }

             if ( $X != VOID ) { $LastGoodY = $Y; $LastGoodX = $X; }
             if ( $X == VOID ) { $X = NULL; }

             $LastX = $X; $LastY = $Y;
             $Y = $Y + $YStep;
            }
          }
        }
      }
    }

   /* Draw a line chart */
   function drawZoneChart($SerieA,$SerieB,$Format=NULL)
    {
     $AxisID	= isset($Format["AxisID"]) ? $Format["AxisID"] : 0;
     $LineR	= isset($Format["LineR"]) ? $Format["LineR"] : 150;
     $LineG	= isset($Format["LineG"]) ? $Format["LineG"] : 150;
     $LineB	= isset($Format["LineB"]) ? $Format["LineB"] : 150;
     $LineAlpha	= isset($Format["LineAlpha"]) ? $Format["LineAlpha"] : 50;
     $LineTicks	= isset($Format["LineTicks"]) ? $Format["LineTicks"] : 1;
     $AreaR	= isset($Format["AreaR"]) ? $Format["AreaR"] : 150;
     $AreaG	= isset($Format["AreaG"]) ? $Format["AreaG"] : 150;
     $AreaB	= isset($Format["AreaB"]) ? $Format["AreaB"] : 150;
     $AreaAlpha	= isset($Format["AreaAlpha"]) ? $Format["AreaAlpha"] : 5;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data	 = $this->DataSet->getData();
     if ( !isset($Data["Series"][$SerieA]["Data"]) || !isset($Data["Series"][$SerieB]["Data"]) ) { return(0); }
     $SerieAData = $Data["Series"][$SerieA]["Data"];
     $SerieBData = $Data["Series"][$SerieB]["Data"];

     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     $Mode	= $Data["Axis"][$AxisID]["Display"];
     $Format	= $Data["Axis"][$AxisID]["Format"];
     $Unit	= $Data["Axis"][$AxisID]["Unit"];

     $PosArrayA = $this->scaleComputeY($SerieAData,array("AxisID"=>$AxisID));
     $PosArrayB = $this->scaleComputeY($SerieBData,array("AxisID"=>$AxisID));
     if ( count($PosArrayA) != count($PosArrayB) ) { return(0); }

     if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
      {
       if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
       $X = $this->GraphAreaX1 + $XMargin; $LastX = NULL; $LastY = NULL;

       $LastX = NULL; $LastY1 = NULL; $LastY2 = NULL;
       $BoundsA = ""; $BoundsB = "";
       foreach($PosArrayA as $Key => $Y1)
        {
         $Y2 = $PosArrayB[$Key];

         $BoundsA[] = $X; $BoundsA[] = $Y1;
         $BoundsB[] = $X; $BoundsB[] = $Y2;

         $LastX = $X;
         $LastY1 = $Y1; $LastY2 = $Y2;

         $X = $X + $XStep;
        }
       $Bounds = array_merge($BoundsA,$this->reversePlots($BoundsB));
       $this->drawPolygonChart($Bounds,array("R"=>$AreaR,"G"=>$AreaG,"B"=>$AreaB,"Alpha"=>$AreaAlpha));

       for($i=0;$i<=count($BoundsA)-4;$i=$i+2)
        {
         $this->drawLine($BoundsA[$i],$BoundsA[$i+1],$BoundsA[$i+2],$BoundsA[$i+3],array("R"=>$LineR,"G"=>$LineG,"B"=>$LineB,"Alpha"=>$LineAlpha,"Ticks"=>$LineTicks));
         $this->drawLine($BoundsB[$i],$BoundsB[$i+1],$BoundsB[$i+2],$BoundsB[$i+3],array("R"=>$LineR,"G"=>$LineG,"B"=>$LineB,"Alpha"=>$LineAlpha,"Ticks"=>$LineTicks));
        }
      }
     else
      {
       if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
       $Y = $this->GraphAreaY1 + $XMargin; $LastX = NULL; $LastY = NULL;

       $LastY = NULL; $LastX1 = NULL; $LastX2 = NULL;
       $BoundsA = ""; $BoundsB = "";
       foreach($PosArrayA as $Key => $X1)
        {
         $X2 = $PosArrayB[$Key];

         $BoundsA[] = $X1; $BoundsA[] = $Y;
         $BoundsB[] = $X2; $BoundsB[] = $Y;

         $LastY = $Y;
         $LastX1 = $X1; $LastX2 = $X2;

         $Y = $Y + $YStep;
        }
       $Bounds = array_merge($BoundsA,$this->reversePlots($BoundsB));
       $this->drawPolygonChart($Bounds,array("R"=>$AreaR,"G"=>$AreaG,"B"=>$AreaB,"Alpha"=>$AreaAlpha));

       for($i=0;$i<=count($BoundsA)-4;$i=$i+2)
        {
         $this->drawLine($BoundsA[$i],$BoundsA[$i+1],$BoundsA[$i+2],$BoundsA[$i+3],array("R"=>$LineR,"G"=>$LineG,"B"=>$LineB,"Alpha"=>$LineAlpha,"Ticks"=>$LineTicks));
         $this->drawLine($BoundsB[$i],$BoundsB[$i+1],$BoundsB[$i+2],$BoundsB[$i+3],array("R"=>$LineR,"G"=>$LineG,"B"=>$LineB,"Alpha"=>$LineAlpha,"Ticks"=>$LineTicks));
        }
      }
    }

   /* Draw a step chart */
   function drawStepChart($Format=NULL)
    {
     $BreakVoid		= isset($Format["BreakVoid"]) ? $Format["BreakVoid"] : FALSE;
     $ReCenter		= isset($Format["ReCenter"]) ? $Format["ReCenter"] : TRUE;
     $VoidTicks		= isset($Format["VoidTicks"]) ? $Format["VoidTicks"] : 4;
     $BreakR		= isset($Format["BreakR"]) ? $Format["BreakR"] : NULL;
     $BreakG		= isset($Format["BreakG"]) ? $Format["BreakG"] : NULL;
     $BreakB		= isset($Format["BreakB"]) ? $Format["BreakB"] : NULL;
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] :FALSE;
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 2;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;
     $ImageMapPlotSize  = isset($Format["ImageMapPlotSize"]) ? $Format["ImageMapPlotSize"] : 5;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"]; $Weight = $Serie["Weight"];

         if (isset($Serie["Description"])) { $SerieDescription = $Serie["Description"]; } else { $SerieDescription = $SerieName; }

         if ( $BreakR == NULL )
          $BreakSettings = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$VoidTicks,"Weight"=>$Weight);
         else
          $BreakSettings = array("R"=>$BreakR,"G"=>$BreakG,"B"=>$BreakB,"Alpha"=>$Alpha,"Ticks"=>$VoidTicks,"Weight"=>$Weight);

         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];
         $Color		= array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight);

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X = $this->GraphAreaX1 + $XMargin; $LastX = NULL; $LastY = NULL;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $LastGoodY = NULL; $LastGoodX = NULL; $Init = FALSE;
           foreach($PosArray as $Key => $Y)
            {
             if ( $DisplayValues && $Serie["Data"][$Key] != VOID ) 
              {
               if ( $Y <= $LastY ) { $Align = TEXT_ALIGN_BOTTOMMIDDLE; $Offset = $DisplayOffset; } else { $Align = TEXT_ALIGN_TOPMIDDLE; $Offset = -$DisplayOffset; }
               $this->drawText($X,$Y-$Offset-$Weight,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>$Align));
              }

             if ( $Y != VOID && $LastX != NULL && $LastY != NULL )
              {
               $this->drawLine($LastX,$LastY,$X,$LastY,$Color);
               $this->drawLine($X,$LastY,$X,$Y,$Color);
               if ( $ReCenter && $X+$XStep < $this->GraphAreaX2 - $XMargin )
                {
                 $this->drawLine($X,$Y,$X+$XStep,$Y,$Color);
                 if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($X-$ImageMapPlotSize).",".floor($Y-$ImageMapPlotSize).",".floor($X+$XStep+$ImageMapPlotSize).",".floor($Y+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
                }
               else
                { if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($LastX-$ImageMapPlotSize).",".floor($LastY-$ImageMapPlotSize).",".floor($X+$ImageMapPlotSize).",".floor($LastY+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); } }
              }

             if ( $Y != VOID && $LastY == NULL && $LastGoodY != NULL && !$BreakVoid )
              { 
               if ( $ReCenter )
                {
                 $this->drawLine($LastGoodX+$XStep,$LastGoodY,$X,$LastGoodY,$BreakSettings);
                 if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($LastGoodX+$XStep-$ImageMapPlotSize).",".floor($LastGoodY-$ImageMapPlotSize).",".floor($X+$ImageMapPlotSize).",".floor($LastGoodY+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
                }
               else
                {
                 $this->drawLine($LastGoodX,$LastGoodY,$X,$LastGoodY,$BreakSettings);
                 if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($LastGoodX-$ImageMapPlotSize).",".floor($LastGoodY-$ImageMapPlotSize).",".floor($X+$ImageMapPlotSize).",".floor($LastGoodY+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
                }

               $this->drawLine($X,$LastGoodY,$X,$Y,$BreakSettings);
               $LastGoodY = NULL;
              }
             elseif( !$BreakVoid && $LastGoodY == NULL && $Y != VOID )
              {
              $this->drawLine($this->GraphAreaX1 + $XMargin,$Y,$X,$Y,$BreakSettings);
               if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($this->GraphAreaX1+$XMargin-$ImageMapPlotSize).",".floor($Y-$ImageMapPlotSize).",".floor($X+$ImageMapPlotSize).",".floor($Y+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
              }

             if ( $Y != VOID ) { $LastGoodY = $Y; $LastGoodX = $X; }
             if ( $Y == VOID ) { $Y = NULL; }

             if ( !$Init && $ReCenter ) { $X = $X - $XStep/2; $Init = TRUE; }
             $LastX = $X; $LastY = $Y;
             if ( $LastX < $this->GraphAreaX1 + $XMargin ) { $LastX = $this->GraphAreaX1 + $XMargin; }
             $X = $X + $XStep;
            }
           if ( $ReCenter )
            {
             $this->drawLine($LastX,$LastY,$this->GraphAreaX2 - $XMargin,$LastY,$Color);
             if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($LastX-$ImageMapPlotSize).",".floor($LastY-$ImageMapPlotSize).",".floor($this->GraphAreaX2-$XMargin+$ImageMapPlotSize).",".floor($LastY+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
            }
          }
         else
          {
           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y = $this->GraphAreaY1 + $XMargin; $LastX = NULL; $LastY = NULL;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $LastGoodY = NULL; $LastGoodX = NULL; $Init = FALSE;
           foreach($PosArray as $Key => $X)
            {
             if ( $DisplayValues && $Serie["Data"][$Key] != VOID ) 
              {
               if ( $X >= $LastX ) { $Align = TEXT_ALIGN_MIDDLELEFT; $Offset = $DisplayOffset; } else { $Align = TEXT_ALIGN_MIDDLERIGHT; $Offset = -$DisplayOffset; }
               $this->drawText($X+$Offset+$Weight,$Y,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>$Align));
              }

             if ( $X != VOID && $LastX != NULL && $LastY != NULL )
              {
               $this->drawLine($LastX,$LastY,$LastX,$Y,$Color);
               $this->drawLine($LastX,$Y,$X,$Y,$Color);

               if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($LastX-$ImageMapPlotSize).",".floor($LastY-$ImageMapPlotSize).",".floor($LastX+$XStep+$ImageMapPlotSize).",".floor($Y+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
              }

             if ( $X != VOID && $LastX == NULL && $LastGoodY != NULL && !$BreakVoid )
              {
               $this->drawLine($LastGoodX,$LastGoodY,$LastGoodX,$LastGoodY+$YStep,$Color);
               if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($LastGoodX-$ImageMapPlotSize).",".floor($LastGoodY-$ImageMapPlotSize).",".floor($LastGoodX+$ImageMapPlotSize).",".floor($LastGoodY+$YStep+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

               $this->drawLine($LastGoodX,$LastGoodY+$YStep,$LastGoodX,$Y,$BreakSettings);
               if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($LastGoodX-$ImageMapPlotSize).",".floor($LastGoodY+$YStep-$ImageMapPlotSize).",".floor($LastGoodX+$ImageMapPlotSize).",".floor($YStep+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

               $this->drawLine($LastGoodX,$Y,$X,$Y,$BreakSettings);
               $LastGoodY = NULL;
              }
             elseif ( $X != VOID && $LastGoodY == NULL && !$BreakVoid )
              {
               $this->drawLine($X,$this->GraphAreaY1 + $XMargin,$X,$Y,$BreakSettings);
               if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($X-$ImageMapPlotSize).",".floor($this->GraphAreaY1+$XMargin-$ImageMapPlotSize).",".floor($X+$ImageMapPlotSize).",".floor($Y+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
              }

             if ( $X != VOID ) { $LastGoodY = $Y; $LastGoodX = $X; }
             if ( $X == VOID ) { $X = NULL; }

             if ( !$Init && $ReCenter ) { $Y = $Y - $YStep/2; $Init = TRUE; }
             $LastX = $X; $LastY = $Y;
             if ( $LastY < $this->GraphAreaY1 + $XMargin ) { $LastY = $this->GraphAreaY1 + $XMargin; }
             $Y = $Y + $YStep;
            }
           if ( $ReCenter )
            {
             $this->drawLine($LastX,$LastY,$LastX,$this->GraphAreaY2 - $XMargin,$Color);
             if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($LastX-$ImageMapPlotSize).",".floor($LastY-$ImageMapPlotSize).",".floor($LastX+$ImageMapPlotSize).",".floor($this->GraphAreaY2-$XMargin+$ImageMapPlotSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
            }
          }
        }
      }
    }

   /* Draw a step chart */
   function drawFilledStepChart($Format=NULL)
    {
     $ReCenter		= isset($Format["ReCenter"]) ? $Format["ReCenter"] : TRUE;
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] :FALSE;
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 2;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $ForceTransparency	= isset($Format["ForceTransparency"]) ? $Format["ForceTransparency"] : NULL;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $AroundZero	= isset($Format["AroundZero"]) ? $Format["AroundZero"] : TRUE;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"]; $Weight = $Serie["Weight"];

         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         $Color		= array("R"=>$R,"G"=>$G,"B"=>$B);
         if ( $ForceTransparency != NULL ) { $Color["Alpha"] = $ForceTransparency; } else { $Color["Alpha"] = $Alpha; }

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));
         $YZero    = $this->scaleComputeY(0,array("AxisID"=>$Serie["Axis"]));

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $YZero > $this->GraphAreaY2-1 ) { $YZero = $this->GraphAreaY2-1; }
           if ( $YZero < $this->GraphAreaY1+1 ) { $YZero = $this->GraphAreaY1+1; }

           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X = $this->GraphAreaX1 + $XMargin; $LastX = NULL; $LastY = NULL;

           if ( !$AroundZero ) { $YZero = $this->GraphAreaY2-1; }

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $LastGoodY = NULL; $LastGoodX = NULL; $Points = ""; $Init = FALSE;
           foreach($PosArray as $Key => $Y)
            {
             if ( $Y == VOID && $LastX != NULL && $LastY != NULL && $Points !="" )
              {
               $Points[] = $LastX; $Points[] = $LastY;
               $Points[] = $X; $Points[] = $LastY;
               $Points[] = $X; $Points[] = $YZero;
               $this->drawPolygon($Points,$Color);
               $Points = "";
              }

             if ( $Y != VOID && $LastX != NULL && $LastY != NULL )
              {
               if ( $Points == "") { $Points[] = $LastX; $Points[] = $YZero; }
               $Points[] = $LastX; $Points[] = $LastY;
               $Points[] = $X; $Points[] = $LastY;
               $Points[] = $X; $Points[] = $Y;
              }

             if ( $Y != VOID ) { $LastGoodY = $Y; $LastGoodX = $X; }
             if ( $Y == VOID ) { $Y = NULL; }

             if ( !$Init && $ReCenter ) { $X = $X - $XStep/2; $Init = TRUE; }
             $LastX = $X; $LastY = $Y;
             if ( $LastX < $this->GraphAreaX1 + $XMargin ) { $LastX = $this->GraphAreaX1 + $XMargin; }
             $X = $X + $XStep;
            }

           if ( $ReCenter )
            {
             $Points[] = $LastX+$XStep/2; $Points[] = $LastY;
             $Points[] = $LastX+$XStep/2; $Points[] = $YZero;
            }
           else
            { $Points[] = $LastX; $Points[] = $YZero; }

           $this->drawPolygon($Points,$Color);
          }
         else
          {
           if ( $YZero < $this->GraphAreaX1+1 ) { $YZero = $this->GraphAreaX1+1; }
           if ( $YZero > $this->GraphAreaX2-1 ) { $YZero = $this->GraphAreaX2-1; }

           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y = $this->GraphAreaY1 + $XMargin; $LastX = NULL; $LastY = NULL;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $LastGoodY = NULL; $LastGoodX = NULL; $Points = "";
           foreach($PosArray as $Key => $X)
            {
             if ( $X == VOID && $LastX != NULL && $LastY != NULL && $Points !="" )
              {
               $Points[] = $LastX; $Points[] = $LastY;
               $Points[] = $LastX; $Points[] = $Y;
               $Points[] = $YZero; $Points[] = $Y;
               $this->drawPolygon($Points,$Color);
               $Points = "";
              }

             if ( $X != VOID && $LastX != NULL && $LastY != NULL )
              {
               if ( $Points == "") { $Points[] = $YZero; $Points[] = $LastY; }
               $Points[] = $LastX; $Points[] = $LastY;
               $Points[] = $LastX; $Points[] = $Y;
               $Points[] = $X; $Points[] = $Y;
              }

             if ( $X != VOID ) { $LastGoodY = $Y; $LastGoodX = $X; }
             if ( $X == VOID ) { $X = NULL; }

             if ( $LastX == NULL && $ReCenter ) { $Y = $Y - $YStep/2; }
             $LastX = $X; $LastY = $Y;
             if ( $LastY < $this->GraphAreaY1 + $XMargin ) { $LastY = $this->GraphAreaY1 + $XMargin; }
             $Y = $Y + $YStep;
            }

           if ( $ReCenter )
            {
             $Points[] = $LastX; $Points[] = $LastY+$YStep/2;
             $Points[] = $YZero; $Points[] = $LastY+$YStep/2;
            }
           else
            { $Points[] = $YZero; $Points[] = $LastY; }

           $this->drawPolygon($Points,$Color);
          }
        }
      }
    }

   /* Draw an area chart */
   function drawAreaChart($Format=NULL)
    {
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] : FALSE;
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 2;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $ForceTransparency	= isset($Format["ForceTransparency"]) ? $Format["ForceTransparency"] : 25;
     $AroundZero	= isset($Format["AroundZero"]) ? $Format["AroundZero"] : TRUE;
     $Threshold		= isset($Format["Threshold"]) ? $Format["Threshold"] : NULL;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data  = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"];
         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));
         $YZero    = $this->scaleComputeY(0,array("AxisID"=>$Serie["Axis"]));

         if ( $Threshold != NULL )
          {
           foreach($Threshold as $Key => $Params)
            {
             $Threshold[$Key]["MinX"] = $this->scaleComputeY($Params["Min"],array("AxisID"=>$Serie["Axis"]));
             $Threshold[$Key]["MaxX"] = $this->scaleComputeY($Params["Max"],array("AxisID"=>$Serie["Axis"]));
            }
          }

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $YZero > $this->GraphAreaY2-1 ) { $YZero = $this->GraphAreaY2-1; }

           $Areas = ""; $AreaID = 0; 
           $Areas[$AreaID][] = $this->GraphAreaX1 + $XMargin;
           if ( $AroundZero ) { $Areas[$AreaID][] = $YZero; } else { $Areas[$AreaID][] = $this->GraphAreaY2-1; }

           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X = $this->GraphAreaX1 + $XMargin; $LastX = NULL; $LastY = NULL;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           foreach($PosArray as $Key => $Y)
            {
             if ( $DisplayValues && $Serie["Data"][$Key] != VOID )
              {
               if ( $Serie["Data"][$Key] > 0 ) { $Align = TEXT_ALIGN_BOTTOMMIDDLE; $Offset = $DisplayOffset; } else { $Align = TEXT_ALIGN_TOPMIDDLE; $Offset = -$DisplayOffset; }
               $this->drawText($X,$Y-$Offset,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>$Align));
              }

             if ( $Y == VOID && isset($Areas[$AreaID]) )
              {
               if($LastX == NULL)
                { $Areas[$AreaID][] = $X; }
               else
                { $Areas[$AreaID][] = $LastX; }

               if ( $AroundZero ) { $Areas[$AreaID][] = $YZero; } else { $Areas[$AreaID][] = $this->GraphAreaY2-1; }
               $AreaID++;
              }
             elseif ($Y != VOID)
              {
               if ( !isset($Areas[$AreaID]) )
                {
                 $Areas[$AreaID][] = $X;
                 if ( $AroundZero ) { $Areas[$AreaID][] = $YZero; } else { $Areas[$AreaID][] = $this->GraphAreaY2-1; }
                }

               $Areas[$AreaID][] = $X;
               $Areas[$AreaID][] = $Y;
              }

             $LastX = $X;
             $X = $X + $XStep;
            }
           $Areas[$AreaID][] = $LastX;
           if ( $AroundZero ) { $Areas[$AreaID][] = $YZero; } else { $Areas[$AreaID][] = $this->GraphAreaY2-1; }

           /* Handle shadows in the areas */
           if ( $this->Shadow )
            {
             $ShadowArea = "";
             foreach($Areas as $Key => $Points)
              {
               $ShadowArea[$Key] = "";
               foreach($Points as $Key2 => $Value)
                {
                 if ( $Key2 % 2 == 0 )
                  { $ShadowArea[$Key][] = $Value + $this->ShadowX; }
                 else
                  { $ShadowArea[$Key][] = $Value + $this->ShadowY; }
                }
              }              

             foreach($ShadowArea as $Key => $Points)
              $this->drawPolygonChart($Points,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa));
            }

           $Alpha = $ForceTransparency != NULL ? $ForceTransparency : $Alpha;
           $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Threshold"=>$Threshold);

           foreach($Areas as $Key => $Points)
            $this->drawPolygonChart($Points,$Color);
          }
         else
          {
           if ( $YZero < $this->GraphAreaX1+1 ) { $YZero = $this->GraphAreaX1+1; }
           if ( $YZero > $this->GraphAreaX2-1 ) { $YZero = $this->GraphAreaX2-1; }

           $Areas = ""; $AreaID = 0; 
           if ( $AroundZero ) { $Areas[$AreaID][] = $YZero; } else { $Areas[$AreaID][] = $this->GraphAreaX1+1; }
           $Areas[$AreaID][] = $this->GraphAreaY1 + $XMargin;

           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y     = $this->GraphAreaY1 + $XMargin; $LastX = NULL; $LastY = NULL;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           foreach($PosArray as $Key => $X)
            {
             if ( $DisplayValues && $Serie["Data"][$Key] != VOID )
              {
               if ( $Serie["Data"][$Key] > 0 ) { $Align = TEXT_ALIGN_BOTTOMMIDDLE; $Offset = $DisplayOffset; } else { $Align = TEXT_ALIGN_TOPMIDDLE; $Offset = -$DisplayOffset; }
               $this->drawText($X+$Offset,$Y,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("Angle"=>270,"R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>$Align));
              }

             if ( $X == VOID && isset($Areas[$AreaID]) )
              {
               if ( $AroundZero ) { $Areas[$AreaID][] = $YZero; } else { $Areas[$AreaID][] = $this->GraphAreaX1+1; }

               if($LastY == NULL)
                { $Areas[$AreaID][] = $Y; }
               else
                { $Areas[$AreaID][] = $LastY; }

               $AreaID++;
              }
             elseif ($X != VOID)
              {
               if ( !isset($Areas[$AreaID]) )
                {
                 if ( $AroundZero ) { $Areas[$AreaID][] = $YZero; } else { $Areas[$AreaID][] = $this->GraphAreaX1+1; }
                 $Areas[$AreaID][] = $Y;
                }

               $Areas[$AreaID][] = $X;
               $Areas[$AreaID][] = $Y;
              }

             $LastX = $X; $LastY = $Y;
             $Y = $Y + $YStep;
            }
           if ( $AroundZero ) { $Areas[$AreaID][] = $YZero; } else { $Areas[$AreaID][] = $this->GraphAreaX1+1; }
           $Areas[$AreaID][] = $LastY;

           /* Handle shadows in the areas */
           if ( $this->Shadow )
            {
             $ShadowArea = "";
             foreach($Areas as $Key => $Points)
              {
               $ShadowArea[$Key] = "";
               foreach($Points as $Key2 => $Value)
                {
                 if ( $Key2 % 2 == 0 )
                  { $ShadowArea[$Key][] = $Value + $this->ShadowX; }
                 else
                  { $ShadowArea[$Key][] = $Value + $this->ShadowY; }
                }
              }              

             foreach($ShadowArea as $Key => $Points)
              $this->drawPolygonChart($Points,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa));
            }

           $Alpha = $ForceTransparency != NULL ? $ForceTransparency : $Alpha;
           $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Threshold"=>$Threshold);

           foreach($Areas as $Key => $Points)
            $this->drawPolygonChart($Points,$Color);
          }
        }
      }
    }


   /* Draw a bar chart */
   function drawBarChart($Format=NULL)
    {
     $Floating0Serie	= isset($Format["Floating0Serie"]) ? $Format["Floating0Serie"] : NULL;
     $Floating0Value	= isset($Format["Floating0Value"]) ? $Format["Floating0Value"] : NULL;
     $Draw0Line		= isset($Format["Draw0Line"]) ? $Format["Draw0Line"] : FALSE;
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] : FALSE;
     $DisplayOrientation = isset($Format["DisplayOrientation"]) ? $Format["DisplayOrientation"] : ORIENTATION_HORIZONTAL;
     $DisplayOffset	= isset($Format["DisplayOffset"]) ? $Format["DisplayOffset"] : 2;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $DisplayFont	= isset($Format["DisplaySize"]) ? $Format["DisplaySize"] : $this->FontName;
     $DisplaySize	= isset($Format["DisplaySize"]) ? $Format["DisplaySize"] : $this->FontSize;
     $DisplayPos	= isset($Format["DisplayPos"]) ? $Format["DisplayPos"] : LABEL_POS_OUTSIDE;
     $DisplayShadow	= isset($Format["DisplayShadow"]) ? $Format["DisplayShadow"] : TRUE;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $AroundZero	= isset($Format["AroundZero"]) ? $Format["AroundZero"] : TRUE;
     $Interleave	= isset($Format["Interleave"]) ? $Format["Interleave"] : .5;
     $Rounded		= isset($Format["Rounded"]) ? $Format["Rounded"] : FALSE;
     $RoundRadius	= isset($Format["RoundRadius"]) ? $Format["RoundRadius"] : 4;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : -1;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : -1;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : -1;
     $Gradient		= isset($Format["Gradient"]) ? $Format["Gradient"] : FALSE;
     $GradientMode	= isset($Format["GradientMode"]) ? $Format["GradientMode"] : GRADIENT_SIMPLE;
     $GradientAlpha	= isset($Format["GradientAlpha"]) ? $Format["GradientAlpha"] : 20;
     $GradientStartR	= isset($Format["GradientStartR"]) ? $Format["GradientStartR"] : 255;
     $GradientStartG	= isset($Format["GradientStartG"]) ? $Format["GradientStartG"] : 255;
     $GradientStartB	= isset($Format["GradientStartB"]) ? $Format["GradientStartB"] : 255;
     $GradientEndR	= isset($Format["GradientEndR"]) ? $Format["GradientEndR"] : 0;
     $GradientEndG	= isset($Format["GradientEndG"]) ? $Format["GradientEndG"] : 0;
     $GradientEndB	= isset($Format["GradientEndB"]) ? $Format["GradientEndB"] : 0;
     $TxtMargin		= isset($Format["TxtMargin"]) ? $Format["TxtMargin"] : 6;
     $OverrideColors	= isset($Format["OverrideColors"]) ? $Format["OverrideColors"] : NULL;
     $OverrideSurrounding = isset($Format["OverrideSurrounding"]) ? $Format["OverrideSurrounding"] : 30;
     $InnerSurrounding	= isset($Format["InnerSurrounding"]) ? $Format["InnerSurrounding"] : NULL;
     $InnerBorderR	= isset($Format["InnerBorderR"]) ? $Format["InnerBorderR"] : -1;
     $InnerBorderG	= isset($Format["InnerBorderG"]) ? $Format["InnerBorderG"] : -1;
     $InnerBorderB	= isset($Format["InnerBorderB"]) ? $Format["InnerBorderB"] : -1;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;

     $this->LastChartLayout = CHART_LAST_LAYOUT_REGULAR;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     if ( $OverrideColors != NULL )
      {
       $OverrideColors = $this->validatePalette($OverrideColors,$OverrideSurrounding);
       $this->DataSet->saveExtendedData("Palette",$OverrideColors);
      }

     $RestoreShadow = $this->Shadow;

     $SeriesCount  = $this->countDrawableSeries();
     $CurrentSerie = 0;
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"];
         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = $R; $DisplayG = $G; $DisplayB = $B; }
         if ( $Surrounding != NULL ) { $BorderR = $R+$Surrounding; $BorderG = $G+$Surrounding; $BorderB = $B+$Surrounding; }
         if ( $InnerSurrounding != NULL ) { $InnerBorderR = $R+$InnerSurrounding; $InnerBorderG = $G+$InnerSurrounding; $InnerBorderB = $B+$InnerSurrounding; }
         if ( $InnerBorderR == -1 ) { $InnerColor = NULL; } else { $InnerColor = array("R"=>$InnerBorderR,"G"=>$InnerBorderG,"B"=>$InnerBorderB); }
         $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"BorderR"=>$BorderR,"BorderG"=>$BorderG,"BorderB"=>$BorderB);

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         if (isset($Serie["Description"])) { $SerieDescription = $Serie["Description"]; } else { $SerieDescription = $SerieName; }

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));

         if ( $Floating0Value != NULL )
          { $YZero = $this->scaleComputeY($Floating0Value,array("AxisID"=>$Serie["Axis"])); }
         else
          { $YZero = $this->scaleComputeY(0,array("AxisID"=>$Serie["Axis"])); }

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $YZero > $this->GraphAreaY2-1 ) { $YZero = $this->GraphAreaY2-1; }
           if ( $YZero < $this->GraphAreaY1+1 ) { $YZero = $this->GraphAreaY1+1; }

           if ( $XDivs == 0 ) { $XStep = 0; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X = $this->GraphAreaX1 + $XMargin;

           if ( $AroundZero ) { $Y1 = $YZero; } else { $Y1 = $this->GraphAreaY2-1; }
           if ( $XDivs == 0 ) { $XSize = ($this->GraphAreaX2-$this->GraphAreaX1)/($SeriesCount+$Interleave); } else { $XSize   = ($XStep / ($SeriesCount+$Interleave) ); }

           $XOffset = -($XSize*$SeriesCount)/2 + $CurrentSerie * $XSize;
           if ( $X + $XOffset <= $this->GraphAreaX1 ) { $XOffset = $this->GraphAreaX1 - $X + 1 ; }

           $this->DataSet->Data["Series"][$SerieName]["XOffset"] = $XOffset + $XSize / 2;

           if ( $Rounded || $BorderR != -1) { $XSpace = 1; } else { $XSpace = 0; }

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }

           $ID = 0;
           foreach($PosArray as $Key => $Y2)
            {
             if ( $Floating0Serie != NULL )
              {
               if ( isset($Data["Series"][$Floating0Serie]["Data"][$Key]) )
                { $Value = $Data["Series"][$Floating0Serie]["Data"][$Key]; }
               else
                { $Value = 0; }

               $YZero = $this->scaleComputeY($Value,array("AxisID"=>$Serie["Axis"]));
               if ( $YZero > $this->GraphAreaY2-1 ) { $YZero = $this->GraphAreaY2-1; }
               if ( $YZero < $this->GraphAreaY1+1 ) { $YZero = $this->GraphAreaY1+1; }

               if ( $AroundZero ) { $Y1 = $YZero; } else { $Y1 = $this->GraphAreaY2-1; }
              }

             if ( $OverrideColors != NULL )
              { if ( isset($OverrideColors[$ID]) ) { $Color = array("R"=>$OverrideColors[$ID]["R"],"G"=>$OverrideColors[$ID]["G"],"B"=>$OverrideColors[$ID]["B"],"Alpha"=>$OverrideColors[$ID]["Alpha"],"BorderR"=>$OverrideColors[$ID]["BorderR"],"BorderG"=>$OverrideColors[$ID]["BorderG"],"BorderB"=>$OverrideColors[$ID]["BorderB"]); } else { $Color = $this->getRandomColor(); } }

             if ( $Y2 != VOID )
              {
               $BarHeight = $Y1 - $Y2;

               if ( $Serie["Data"][$Key] == 0 )
                {
                 $this->drawLine($X+$XOffset+$XSpace,$Y1,$X+$XOffset+$XSize-$XSpace,$Y1,$Color);
                 if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($X+$XOffset+$XSpace).",".floor($Y1-1).",".floor($X+$XOffset+$XSize-$XSpace).",".floor($Y1+1),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
                }
               else
                {
                 if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($X+$XOffset+$XSpace).",".floor($Y1).",".floor($X+$XOffset+$XSize-$XSpace).",".floor($Y2),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

                 if ( $Rounded )
                  $this->drawRoundedFilledRectangle($X+$XOffset+$XSpace,$Y1,$X+$XOffset+$XSize-$XSpace,$Y2,$RoundRadius,$Color);
                 else
                  {
                   $this->drawFilledRectangle($X+$XOffset+$XSpace,$Y1,$X+$XOffset+$XSize-$XSpace,$Y2,$Color);

                   if ( $InnerColor != NULL ) { $this->drawRectangle($X+$XOffset+$XSpace+1,min($Y1,$Y2)+1,$X+$XOffset+$XSize-$XSpace-1,max($Y1,$Y2)-1,$InnerColor); }

                   if ( $Gradient )
                    {
                     $this->Shadow  = FALSE;

                     if ( $GradientMode == GRADIENT_SIMPLE )
                      {
                       if ( $Serie["Data"][$Key] >= 0 )
                        $GradienColor = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$GradientAlpha);
                       else
                        $GradienColor = array("StartR"=>$GradientEndR,"StartG"=>$GradientEndG,"StartB"=>$GradientEndB,"EndR"=>$GradientStartR,"EndG"=>$GradientStartG,"EndB"=>$GradientStartB,"Alpha"=>$GradientAlpha);

                       $this->drawGradientArea($X+$XOffset+$XSpace,$Y1,$X+$XOffset+$XSize-$XSpace,$Y2,DIRECTION_VERTICAL,$GradienColor);
                      }
                     elseif ( $GradientMode == GRADIENT_EFFECT_CAN )
                      {
                       $GradienColor1 = array("StartR"=>$GradientEndR,"StartG"=>$GradientEndG,"StartB"=>$GradientEndB,"EndR"=>$GradientStartR,"EndG"=>$GradientStartG,"EndB"=>$GradientStartB,"Alpha"=>$GradientAlpha);
                       $GradienColor2 = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$GradientAlpha);
                       $XSpan = floor($XSize / 3);

                       $this->drawGradientArea($X+$XOffset+$XSpace,$Y1,$X+$XOffset+$XSpan-$XSpace,$Y2,DIRECTION_HORIZONTAL,$GradienColor1);
                       $this->drawGradientArea($X+$XOffset+$XSpan+$XSpace,$Y1,$X+$XOffset+$XSize-$XSpace,$Y2,DIRECTION_HORIZONTAL,$GradienColor2);
                      }
                     $this->Shadow = $RestoreShadow;
                    }
                  }

                 if ( $Draw0Line )
                  {
                   $Line0Color = array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>20);

                   if ( abs($Y1 - $Y2) > 3 ) { $Line0Width = 3; } else { $Line0Width = 1; }
                   if ( $Y1 - $Y2 < 0 ) { $Line0Width = -$Line0Width; }

                   $this->drawFilledRectangle($X+$XOffset+$XSpace,floor($Y1),$X+$XOffset+$XSize-$XSpace,floor($Y1)-$Line0Width,$Line0Color);
                   $this->drawLine($X+$XOffset+$XSpace,floor($Y1),$X+$XOffset+$XSize-$XSpace,floor($Y1),$Line0Color);
                  }
                }

               if ( $DisplayValues && $Serie["Data"][$Key] != VOID )
                {
                 if ( $DisplayShadow ) { $this->Shadow = TRUE; }

                 $Caption    = $this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit);
                 $TxtPos     = $this->getTextBox(0,0,$DisplayFont,$DisplaySize,90,$Caption);
                 $TxtHeight  = $TxtPos[0]["Y"] - $TxtPos[1]["Y"] + $TxtMargin;

                 if ( $DisplayPos == LABEL_POS_INSIDE && abs($TxtHeight) < abs($BarHeight) )
                  {
                   $CenterX = (($X+$XOffset+$XSize-$XSpace)-($X+$XOffset+$XSpace))/2 + $X+$XOffset+$XSpace;
                   $CenterY = ($Y2-$Y1)/2 + $Y1;

                   $this->drawText($CenterX,$CenterY,$Caption,array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"FontSize"=>$DisplaySize,"Angle"=>90));
                  }
                 else
                  {
                   if ( $Serie["Data"][$Key] >= 0 ) { $Align = TEXT_ALIGN_BOTTOMMIDDLE; $Offset = $DisplayOffset; } else { $Align = TEXT_ALIGN_TOPMIDDLE; $Offset = -$DisplayOffset; }
                   $this->drawText($X+$XOffset+$XSize/2,$Y2-$Offset,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>$Align,"FontSize"=>$DisplaySize));
                  }

                 $this->Shadow = $RestoreShadow;
                }
              }

             $X = $X + $XStep;
             $ID++;
            }
          }
         else
          {
           if ( $YZero < $this->GraphAreaX1+1 ) { $YZero = $this->GraphAreaX1+1; }
           if ( $YZero > $this->GraphAreaX2-1 ) { $YZero = $this->GraphAreaX2-1; }

           if ( $XDivs == 0 ) { $YStep = 0; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           
           $Y = $this->GraphAreaY1 + $XMargin;

           if ( $AroundZero ) { $X1 = $YZero; } else { $X1 = $this->GraphAreaX1+1; }
           if ( $XDivs == 0 ) { $YSize = ($this->GraphAreaY2-$this->GraphAreaY1)/($SeriesCount+$Interleave); } else { $YSize   = ($YStep / ($SeriesCount+$Interleave) ); }
           
           $YOffset = -($YSize*$SeriesCount)/2 + $CurrentSerie * $YSize;
           if ( $Y + $YOffset <= $this->GraphAreaY1 ) { $YOffset = $this->GraphAreaY1 - $Y + 1 ; }

           $this->DataSet->Data["Series"][$SerieName]["XOffset"] = $YOffset + $YSize / 2;

           if ( $Rounded || $BorderR != -1 ) { $YSpace = 1; } else { $YSpace = 0; }

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }

           $ID = 0 ;
           foreach($PosArray as $Key => $X2)
            {
             if ( $Floating0Serie != NULL )
              {
               if ( isset($Data["Series"][$Floating0Serie]["Data"][$Key]) )
                $Value = $Data["Series"][$Floating0Serie]["Data"][$Key];
               else { $Value = 0; }

               $YZero = $this->scaleComputeY($Value,array("AxisID"=>$Serie["Axis"]));
               if ( $YZero < $this->GraphAreaX1+1 ) { $YZero = $this->GraphAreaX1+1; }
               if ( $YZero > $this->GraphAreaX2-1 ) { $YZero = $this->GraphAreaX2-1; }
               if ( $AroundZero ) { $X1 = $YZero; } else { $X1 = $this->GraphAreaX1+1; }
              }

             if ( $OverrideColors != NULL )
              { if ( isset($OverrideColors[$ID]) ) { $Color = array("R"=>$OverrideColors[$ID]["R"],"G"=>$OverrideColors[$ID]["G"],"B"=>$OverrideColors[$ID]["B"],"Alpha"=>$OverrideColors[$ID]["Alpha"],"BorderR"=>$OverrideColors[$ID]["BorderR"],"BorderG"=>$OverrideColors[$ID]["BorderG"],"BorderB"=>$OverrideColors[$ID]["BorderB"]); } else { $Color = $this->getRandomColor(); } }

             if ( $X2 != VOID )
              {
               $BarWidth = $X2 - $X1;

               if ( $Serie["Data"][$Key] == 0 )
                {
                 $this->drawLine($X1,$Y+$YOffset+$YSpace,$X1,$Y+$YOffset+$YSize-$YSpace,$Color);
                 if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($X1-1).",".floor($Y+$YOffset+$YSpace).",".floor($X1+1).",".floor($Y+$YOffset+$YSize-$YSpace),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }
                }
               else
                {
                 if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($X1).",".floor($Y+$YOffset+$YSpace).",".floor($X2).",".floor($Y+$YOffset+$YSize-$YSpace),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

                 if ( $Rounded )
                  $this->drawRoundedFilledRectangle($X1+1,$Y+$YOffset+$YSpace,$X2,$Y+$YOffset+$YSize-$YSpace,$RoundRadius,$Color);
                 else
                  {
                   $this->drawFilledRectangle($X1,$Y+$YOffset+$YSpace,$X2,$Y+$YOffset+$YSize-$YSpace,$Color);

                   if ( $InnerColor != NULL ) { $this->drawRectangle(min($X1,$X2)+1,$Y+$YOffset+$YSpace+1,max($X1,$X2)-1,$Y+$YOffset+$YSize-$YSpace-1,$InnerColor); }

                   if ( $Gradient )
                    {
                     $this->Shadow  = FALSE;

                     if ( $GradientMode == GRADIENT_SIMPLE )
                      {
                       if ( $Serie["Data"][$Key] >= 0 )
                        $GradienColor = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$GradientAlpha);
                       else
                        $GradienColor = array("StartR"=>$GradientEndR,"StartG"=>$GradientEndG,"StartB"=>$GradientEndB,"EndR"=>$GradientStartR,"EndG"=>$GradientStartG,"EndB"=>$GradientStartB,"Alpha"=>$GradientAlpha);

                       $this->drawGradientArea($X1,$Y+$YOffset+$YSpace,$X2,$Y+$YOffset+$YSize-$YSpace,DIRECTION_HORIZONTAL,$GradienColor);
                      }
                     elseif ( $GradientMode == GRADIENT_EFFECT_CAN )
                      {
                       $GradienColor1 = array("StartR"=>$GradientEndR,"StartG"=>$GradientEndG,"StartB"=>$GradientEndB,"EndR"=>$GradientStartR,"EndG"=>$GradientStartG,"EndB"=>$GradientStartB,"Alpha"=>$GradientAlpha);
                       $GradienColor2 = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$GradientAlpha);
                       $YSpan = floor($YSize / 3);

                       $this->drawGradientArea($X1,$Y+$YOffset+$YSpace,$X2,$Y+$YOffset+$YSpan-$YSpace,DIRECTION_VERTICAL,$GradienColor1);
                       $this->drawGradientArea($X1,$Y+$YOffset+$YSpan,$X2,$Y+$YOffset+$YSize-$YSpace,DIRECTION_VERTICAL,$GradienColor2);
                      }
                     $this->Shadow = $RestoreShadow;
                    }
                  }

                 if ( $Draw0Line )
                  {
                   $Line0Color = array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>20);

                   if ( abs($X1 - $X2) > 3 ) { $Line0Width = 3; } else { $Line0Width = 1; }
                   if ( $X2 - $X1 < 0 ) { $Line0Width = -$Line0Width; }

                   $this->drawFilledRectangle(floor($X1),$Y+$YOffset+$YSpace,floor($X1)+$Line0Width,$Y+$YOffset+$YSize-$YSpace,$Line0Color);
                   $this->drawLine(floor($X1),$Y+$YOffset+$YSpace,floor($X1),$Y+$YOffset+$YSize-$YSpace,$Line0Color);
                  }
                }

               if ( $DisplayValues && $Serie["Data"][$Key] != VOID ) 
                {
                 if ( $DisplayShadow ) { $this->Shadow = TRUE; }

                 $Caption   = $this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit);
                 $TxtPos    = $this->getTextBox(0,0,$DisplayFont,$DisplaySize,0,$Caption);
                 $TxtWidth  = $TxtPos[1]["X"] - $TxtPos[0]["X"] + $TxtMargin;

                 if ( $DisplayPos == LABEL_POS_INSIDE && abs($TxtWidth) < abs($BarWidth) )
                  {
                   $CenterX = ($X2-$X1)/2 + $X1;
                   $CenterY = (($Y+$YOffset+$YSize-$YSpace)-($Y+$YOffset+$YSpace))/2 + ($Y+$YOffset+$YSpace);

                   $this->drawText($CenterX,$CenterY,$Caption,array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"FontSize"=>$DisplaySize));
                  }
                 else
                  {
                   if ( $Serie["Data"][$Key] >= 0 ) { $Align = TEXT_ALIGN_MIDDLELEFT; $Offset = $DisplayOffset; } else { $Align = TEXT_ALIGN_MIDDLERIGHT; $Offset = -$DisplayOffset; }
                   $this->drawText($X2+$Offset,$Y+$YOffset+$YSize/2,$Caption,array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>$Align,"FontSize"=>$DisplaySize));
                  }

                 $this->Shadow = $RestoreShadow;
                }
              }
             $Y = $Y + $YStep;
             $ID++;
            }
          }
         $CurrentSerie++;
        }
      }
    }

   /* Draw a bar chart */
   function drawStackedBarChart($Format=NULL)
    {
     $DisplayValues	= isset($Format["DisplayValues"]) ? $Format["DisplayValues"] : FALSE;
     $DisplayOrientation = isset($Format["DisplayOrientation"]) ? $Format["DisplayOrientation"] : ORIENTATION_AUTO;
     $DisplayRound      = isset($Format["DisplayRound"]) ? $Format["DisplayRound"] : 0;
     $DisplayColor	= isset($Format["DisplayColor"]) ? $Format["DisplayColor"] : DISPLAY_MANUAL;
     $DisplayFont	= isset($Format["DisplayFont"]) ? $Format["DisplayFont"] : $this->FontName;
     $DisplaySize	= isset($Format["DisplaySize"]) ? $Format["DisplaySize"] : $this->FontSize;
     $DisplayR		= isset($Format["DisplayR"]) ? $Format["DisplayR"] : 0;
     $DisplayG		= isset($Format["DisplayG"]) ? $Format["DisplayG"] : 0;
     $DisplayB		= isset($Format["DisplayB"]) ? $Format["DisplayB"] : 0;
     $Interleave	= isset($Format["Interleave"]) ? $Format["Interleave"] : .5;
     $Rounded		= isset($Format["Rounded"]) ? $Format["Rounded"] : FALSE;
     $RoundRadius	= isset($Format["RoundRadius"]) ? $Format["RoundRadius"] : 4;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : -1;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : -1;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : -1;
     $Gradient		= isset($Format["Gradient"]) ? $Format["Gradient"] : FALSE;
     $GradientMode	= isset($Format["GradientMode"]) ? $Format["GradientMode"] : GRADIENT_SIMPLE;
     $GradientAlpha	= isset($Format["GradientAlpha"]) ? $Format["GradientAlpha"] : 20;
     $GradientStartR	= isset($Format["GradientStartR"]) ? $Format["GradientStartR"] : 255;
     $GradientStartG	= isset($Format["GradientStartG"]) ? $Format["GradientStartG"] : 255;
     $GradientStartB	= isset($Format["GradientStartB"]) ? $Format["GradientStartB"] : 255;
     $GradientEndR	= isset($Format["GradientEndR"]) ? $Format["GradientEndR"] : 0;
     $GradientEndG	= isset($Format["GradientEndG"]) ? $Format["GradientEndG"] : 0;
     $GradientEndB	= isset($Format["GradientEndB"]) ? $Format["GradientEndB"] : 0;
     $InnerSurrounding	= isset($Format["InnerSurrounding"]) ? $Format["InnerSurrounding"] : NULL;
     $InnerBorderR	= isset($Format["InnerBorderR"]) ? $Format["InnerBorderR"] : -1;
     $InnerBorderG	= isset($Format["InnerBorderG"]) ? $Format["InnerBorderG"] : -1;
     $InnerBorderB	= isset($Format["InnerBorderB"]) ? $Format["InnerBorderB"] : -1;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;
     $FontFactor	= isset($Format["FontFactor"]) ? $Format["FontFactor"] : 8;

     $this->LastChartLayout = CHART_LAST_LAYOUT_STACKED;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     $RestoreShadow = $this->Shadow;

     $LastX = ""; $LastY = "";
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"];
         if ( $DisplayColor == DISPLAY_AUTO ) { $DisplayR = 255; $DisplayG = 255; $DisplayB = 255; }
         if ( $Surrounding != NULL ) { $BorderR = $R+$Surrounding; $BorderG = $G+$Surrounding; $BorderB = $B+$Surrounding; }
         if ( $InnerSurrounding != NULL ) { $InnerBorderR = $R+$InnerSurrounding; $InnerBorderG = $G+$InnerSurrounding; $InnerBorderB = $B+$InnerSurrounding; }
         if ( $InnerBorderR == -1 ) { $InnerColor = NULL; } else { $InnerColor = array("R"=>$InnerBorderR,"G"=>$InnerBorderG,"B"=>$InnerBorderB); }

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         if (isset($Serie["Description"])) { $SerieDescription = $Serie["Description"]; } else { $SerieDescription = $SerieName; }

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]),TRUE);
         $YZero    = $this->scaleComputeY(0,array("AxisID"=>$Serie["Axis"]));

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         $Color = array("TransCorner"=>TRUE,"R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"BorderR"=>$BorderR,"BorderG"=>$BorderG,"BorderB"=>$BorderB);

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $YZero > $this->GraphAreaY2-1 ) { $YZero = $this->GraphAreaY2-1; }
           if ( $YZero > $this->GraphAreaY2-1 ) { $YZero = $this->GraphAreaY2-1; }

           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X     = $this->GraphAreaX1 + $XMargin;

           $XSize   = ($XStep / (1+$Interleave) );
           $XOffset = -($XSize/2);

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           foreach($PosArray as $Key => $Height)
            {
             if ( $Height != VOID && $Serie["Data"][$Key] != 0 )
              {
               if ( $Serie["Data"][$Key] > 0 ) { $Pos = "+"; } else { $Pos = "-"; }

               if ( !isset($LastY[$Key] ) ) { $LastY[$Key] = ""; }
               if ( !isset($LastY[$Key][$Pos] ) ) { $LastY[$Key][$Pos] = $YZero; }

               $Y1 = $LastY[$Key][$Pos];
               $Y2 = $Y1 - $Height;

               if ( ($Rounded || $BorderR != -1) && ($Pos == "+" && $Y1 != $YZero) ) { $YSpaceUp = 1; } else { $YSpaceUp = 0; }
               if ( ($Rounded || $BorderR != -1) && ($Pos == "-" && $Y1 != $YZero) ) { $YSpaceDown = 1; } else { $YSpaceDown = 0; }

               if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($X+$XOffset).",".floor($Y1-$YSpaceUp+$YSpaceDown).",".floor($X+$XOffset+$XSize).",".floor($Y2),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

               if ( $Rounded )
                $this->drawRoundedFilledRectangle($X+$XOffset,$Y1-$YSpaceUp+$YSpaceDown,$X+$XOffset+$XSize,$Y2,$RoundRadius,$Color);
               else
                {
                 $this->drawFilledRectangle($X+$XOffset,$Y1-$YSpaceUp+$YSpaceDown,$X+$XOffset+$XSize,$Y2,$Color);

                 if ( $InnerColor != NULL ) { $RestoreShadow = $this->Shadow; $this->Shadow = FALSE; $this->drawRectangle(min($X+$XOffset+1,$X+$XOffset+$XSize),min($Y1-$YSpaceUp+$YSpaceDown,$Y2)+1,max($X+$XOffset+1,$X+$XOffset+$XSize)-1,max($Y1-$YSpaceUp+$YSpaceDown,$Y2)-1,$InnerColor); $this->Shadow = $RestoreShadow;}

                 if ( $Gradient )
                  {
                   $this->Shadow  = FALSE;

                   if ( $GradientMode == GRADIENT_SIMPLE )
                    {
                     $GradientColor = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$GradientAlpha);
                     $this->drawGradientArea($X+$XOffset,$Y1-1-$YSpaceUp+$YSpaceDown,$X+$XOffset+$XSize,$Y2+1,DIRECTION_VERTICAL,$GradientColor);
                    }
                   elseif ( $GradientMode == GRADIENT_EFFECT_CAN )
                    {
                     $GradientColor1 = array("StartR"=>$GradientEndR,"StartG"=>$GradientEndG,"StartB"=>$GradientEndB,"EndR"=>$GradientStartR,"EndG"=>$GradientStartG,"EndB"=>$GradientStartB,"Alpha"=>$GradientAlpha);
                     $GradientColor2 = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$GradientAlpha);
                     $XSpan = floor($XSize / 3);

                     $this->drawGradientArea($X+$XOffset-.5,$Y1-.5-$YSpaceUp+$YSpaceDown,$X+$XOffset+$XSpan,$Y2+.5,DIRECTION_HORIZONTAL,$GradientColor1);
                     $this->drawGradientArea($X+$XSpan+$XOffset-.5,$Y1-.5-$YSpaceUp+$YSpaceDown,$X+$XOffset+$XSize,$Y2+.5,DIRECTION_HORIZONTAL,$GradientColor2);
                    }
                   $this->Shadow = $RestoreShadow;
                  }
                }

               if ( $DisplayValues )
                {
                 $BarHeight = abs($Y2-$Y1)-2;
                 $BarWidth  = $XSize+($XOffset/2)-$FontFactor;

                 $Caption   = $this->scaleFormat(round($Serie["Data"][$Key],$DisplayRound),$Mode,$Format,$Unit);
                 $TxtPos    = $this->getTextBox(0,0,$DisplayFont,$DisplaySize,0,$Caption);
                 $TxtHeight = abs($TxtPos[2]["Y"] - $TxtPos[0]["Y"]);
                 $TxtWidth  = abs($TxtPos[1]["X"] - $TxtPos[0]["X"]);

                 $XCenter = ( ($X+$XOffset+$XSize) - ($X+$XOffset) ) / 2 + $X+$XOffset;
                 $YCenter = ( ($Y2) - ($Y1-$YSpaceUp+$YSpaceDown) ) / 2 + $Y1-$YSpaceUp+$YSpaceDown;

                 $Done = FALSE;
                 if ( $DisplayOrientation == ORIENTATION_HORIZONTAL || $DisplayOrientation == ORIENTATION_AUTO )
                  {
                   if ( $TxtHeight < $BarHeight && $TxtWidth < $BarWidth  )
                    {
                     $this->drawText($XCenter,$YCenter,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"FontSize"=>$DisplaySize,"FontName"=>$DisplayFont));
                     $Done = TRUE;
                    }
                  }

                 if ( $DisplayOrientation == ORIENTATION_VERTICAL || ( $DisplayOrientation == ORIENTATION_AUTO && !$Done) )
                  {
                   if ( $TxtHeight < $BarWidth && $TxtWidth < $BarHeight  )
                    $this->drawText($XCenter,$YCenter,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Angle"=>90,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"FontSize"=>$DisplaySize,"FontName"=>$DisplayFont));
                  }
                }

               $LastY[$Key][$Pos] = $Y2;
              }

             $X = $X + $XStep;
            }
          }
         else
          {
           if ( $YZero < $this->GraphAreaX1+1 ) { $YZero = $this->GraphAreaX1+1; }
           if ( $YZero > $this->GraphAreaX2-1 ) { $YZero = $this->GraphAreaX2-1; }

           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y     = $this->GraphAreaY1 + $XMargin;

           $YSize   = $YStep / (1+$Interleave);
           $YOffset = -($YSize/2);

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           foreach($PosArray as $Key => $Width)
            {
             if ( $Width != VOID && $Serie["Data"][$Key] != 0 )
              {
               if ( $Serie["Data"][$Key] > 0 ) { $Pos = "+"; } else { $Pos = "-"; }

               if ( !isset($LastX[$Key] ) ) { $LastX[$Key] = ""; }
               if ( !isset($LastX[$Key][$Pos] ) ) { $LastX[$Key][$Pos] = $YZero; }

               $X1 = $LastX[$Key][$Pos];
               $X2 = $X1 + $Width;

               if ( ($Rounded || $BorderR != -1) && ($Pos == "+" && $X1 != $YZero) ) { $XSpaceLeft = 2; } else { $XSpaceLeft = 0; }
               if ( ($Rounded || $BorderR != -1) && ($Pos == "-" && $X1 != $YZero) ) { $XSpaceRight = 2; } else { $XSpaceRight = 0; }

               if ( $RecordImageMap ) { $this->addToImageMap("RECT",floor($X1+$XSpaceLeft).",".floor($Y+$YOffset).",".floor($X2-$XSpaceRight).",".floor($Y+$YOffset+$YSize),$this->toHTMLColor($R,$G,$B),$SerieDescription,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit)); }

               if ( $Rounded )
                $this->drawRoundedFilledRectangle($X1+$XSpaceLeft,$Y+$YOffset,$X2-$XSpaceRight,$Y+$YOffset+$YSize,$RoundRadius,$Color);
               else
                {
                 $this->drawFilledRectangle($X1+$XSpaceLeft,$Y+$YOffset,$X2-$XSpaceRight,$Y+$YOffset+$YSize,$Color);

                 if ( $InnerColor != NULL ) { $RestoreShadow = $this->Shadow; $this->Shadow = FALSE; $this->drawRectangle(min($X1+$XSpaceLeft,$X2-$XSpaceRight)+1,min($Y+$YOffset,$Y+$YOffset+$YSize)+1,max($X1+$XSpaceLeft,$X2-$XSpaceRight)-1,max($Y+$YOffset,$Y+$YOffset+$YSize)-1,$InnerColor); $this->Shadow = $RestoreShadow;}

                 if ( $Gradient )
                  {
                   $this->Shadow  = FALSE;

                   if ( $GradientMode == GRADIENT_SIMPLE )
                    {
                     $GradientColor = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$GradientAlpha);
                     $this->drawGradientArea($X1+$XSpaceLeft,$Y+$YOffset,$X2-$XSpaceRight,$Y+$YOffset+$YSize,DIRECTION_HORIZONTAL,$GradientColor);
                    }
                   elseif ( $GradientMode == GRADIENT_EFFECT_CAN )
                    {
                     $GradientColor1 = array("StartR"=>$GradientEndR,"StartG"=>$GradientEndG,"StartB"=>$GradientEndB,"EndR"=>$GradientStartR,"EndG"=>$GradientStartG,"EndB"=>$GradientStartB,"Alpha"=>$GradientAlpha);
                     $GradientColor2 = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$GradientAlpha);
                     $YSpan = floor($YSize / 3);

                     $this->drawGradientArea($X1+$XSpaceLeft,$Y+$YOffset,$X2-$XSpaceRight,$Y+$YOffset+$YSpan,DIRECTION_VERTICAL,$GradientColor1);
                     $this->drawGradientArea($X1+$XSpaceLeft,$Y+$YOffset+$YSpan,$X2-$XSpaceRight,$Y+$YOffset+$YSize,DIRECTION_VERTICAL,$GradientColor2);
                    }
                   $this->Shadow = $RestoreShadow;
                  }
                }

               if ( $DisplayValues )
                {
                 $BarWidth = abs($X2-$X1)-$FontFactor;
                 $BarHeight = $YSize+($YOffset/2)-$FontFactor/2;
                 $Caption   = $this->scaleFormat(round($Serie["Data"][$Key],$DisplayRound),$Mode,$Format,$Unit);
                 $TxtPos    = $this->getTextBox(0,0,$DisplayFont,$DisplaySize,0,$Caption);
                 $TxtHeight = abs($TxtPos[2]["Y"] - $TxtPos[0]["Y"]);
                 $TxtWidth  = abs($TxtPos[1]["X"] - $TxtPos[0]["X"]);

                 $XCenter  = ( $X2 - $X1 ) / 2 + $X1;
                 $YCenter  = ( ($Y+$YOffset+$YSize) - ($Y+$YOffset) ) / 2 + $Y+$YOffset;

                 $Done = FALSE;
                 if ( $DisplayOrientation == ORIENTATION_HORIZONTAL || $DisplayOrientation == ORIENTATION_AUTO )
                  {
                   if ( $TxtHeight < $BarHeight && $TxtWidth < $BarWidth  )
                    {
                     $this->drawText($XCenter,$YCenter,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"FontSize"=>$DisplaySize,"FontName"=>$DisplayFont));
                     $Done = TRUE;
                    }
                  }

                 if ( $DisplayOrientation == ORIENTATION_VERTICAL || ( $DisplayOrientation == ORIENTATION_AUTO && !$Done) )
                  {
                   if ( $TxtHeight < $BarWidth && $TxtWidth < $BarHeight  )
                    $this->drawText($XCenter,$YCenter,$this->scaleFormat($Serie["Data"][$Key],$Mode,$Format,$Unit),array("R"=>$DisplayR,"G"=>$DisplayG,"B"=>$DisplayB,"Angle"=>90,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"FontSize"=>$DisplaySize,"FontName"=>$DisplayFont));
                  }
                }

               $LastX[$Key][$Pos] = $X2;
              }

             $Y = $Y + $YStep;
            }
          }
        }
      }
    }

   /* Draw a stacked area chart */
   function drawStackedAreaChart($Format=NULL)
    {
     $DrawLine		= isset($Format["DrawLine"]) ? $Format["DrawLine"] : FALSE;
     $LineSurrounding	= isset($Format["LineSurrounding"]) ? $Format["LineSurrounding"] : NULL;
     $LineR		= isset($Format["LineR"]) ? $Format["LineR"] : VOID;
     $LineG		= isset($Format["LineG"]) ? $Format["LineG"] : VOID;
     $LineB		= isset($Format["LineB"]) ? $Format["LineB"] : VOID;
     $LineAlpha		= isset($Format["LineAlpha"]) ? $Format["LineAlpha"] : 100;
     $DrawPlot		= isset($Format["DrawPlot"]) ? $Format["DrawPlot"] : FALSE;
     $PlotRadius	= isset($Format["PlotRadius"]) ? $Format["PlotRadius"] : 2;
     $PlotBorder	= isset($Format["PlotBorder"]) ? $Format["PlotBorder"] : 1;
     $PlotBorderSurrounding = isset($Format["PlotBorderSurrounding"]) ? $Format["PlotBorderSurrounding"] : NULL;
     $PlotBorderR	= isset($Format["PlotBorderR"]) ? $Format["PlotBorderR"] : 0;
     $PlotBorderG	= isset($Format["PlotBorderG"]) ? $Format["PlotBorderG"] : 0;
     $PlotBorderB	= isset($Format["PlotBorderB"]) ? $Format["PlotBorderB"] : 0;
     $PlotBorderAlpha	= isset($Format["PlotBorderAlpha"]) ? $Format["PlotBorderAlpha"] : 50;
     $ForceTransparency	= isset($Format["ForceTransparency"]) ? $Format["ForceTransparency"] : NULL;

     $this->LastChartLayout = CHART_LAST_LAYOUT_STACKED;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     $RestoreShadow = $this->Shadow;
     $this->Shadow  = FALSE;

     /* Build the offset data series */
     $OffsetData    = "";
     $OverallOffset = "";
     $SerieOrder    = "";
     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $SerieOrder[] = $SerieName;

         foreach($Serie["Data"] as $Key => $Value)
          {
           if ( $Value == VOID ) { $Value = 0; }
           if ($Value >= 0) { $Sign = "+"; } else { $Sign = "-"; }
           if ( !isset($OverallOffset[$Key]) || !isset($OverallOffset[$Key][$Sign]) ) { $OverallOffset[$Key][$Sign] = 0; }

           if ( $Sign == "+" )
            { $Data["Series"][$SerieName]["Data"][$Key] = $Value + $OverallOffset[$Key][$Sign]; }
           else
            { $Data["Series"][$SerieName]["Data"][$Key] = $Value - $OverallOffset[$Key][$Sign]; }

           $OverallOffset[$Key][$Sign] = $OverallOffset[$Key][$Sign] + abs($Value);
          }
        } 
      }
     $SerieOrder = array_reverse($SerieOrder);

     $LastX = ""; $LastY = "";
     foreach($SerieOrder as $Key => $SerieName)
      {
       $Serie = $Data["Series"][$SerieName];
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"];
         if ( $ForceTransparency != NULL ) { $Alpha = $ForceTransparency; }

         $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha);

         if ( $LineSurrounding != NULL )
          $LineColor = array("R"=>$R+$LineSurrounding,"G"=>$G+$LineSurrounding,"B"=>$B+$LineSurrounding,"Alpha"=>$Alpha);
         elseif ( $LineR != VOID )
          $LineColor = array("R"=>$LineR,"G"=>$LineG,"B"=>$LineB,"Alpha"=>$LineAlpha);
         else
          $LineColor = $Color;

         if ( $PlotBorderSurrounding != NULL )
          $PlotBorderColor = array("R"=>$R+$PlotBorderSurrounding,"G"=>$G+$PlotBorderSurrounding,"B"=>$B+$PlotBorderSurrounding,"Alpha"=>$PlotBorderAlpha);
         else
          $PlotBorderColor = array("R"=>$PlotBorderR,"G"=>$PlotBorderG,"B"=>$PlotBorderB,"Alpha"=>$PlotBorderAlpha);

         $AxisID	= $Serie["Axis"];
         $Mode		= $Data["Axis"][$AxisID]["Display"];
         $Format	= $Data["Axis"][$AxisID]["Format"];
         $Unit		= $Data["Axis"][$AxisID]["Unit"];

         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]),TRUE);
         $YZero    = $this->scaleComputeY(0,array("AxisID"=>$Serie["Axis"]));

         $this->DataSet->Data["Series"][$SerieName]["XOffset"] = 0;

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $YZero < $this->GraphAreaY1+1 ) { $YZero = $this->GraphAreaY1+1; }
           if ( $YZero > $this->GraphAreaY2-1 ) { $YZero = $this->GraphAreaY2-1; }

           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X = $this->GraphAreaX1 + $XMargin;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }

           $Plots = ""; $Plots[] = $X; $Plots[] = $YZero;
           foreach($PosArray as $Key => $Height)
            {
             if ( $Height != VOID ) { $Plots[] = $X; $Plots[] = $YZero-$Height; }
             $X = $X + $XStep;
            }
           $Plots[] = $X-$XStep; $Plots[] = $YZero;

           $this->drawPolygon($Plots,$Color);

           $this->Shadow = $RestoreShadow;
           if ( $DrawLine ) { for($i=2; $i<=count($Plots)-6; $i=$i+2) { $this->drawLine($Plots[$i],$Plots[$i+1],$Plots[$i+2],$Plots[$i+3],$LineColor); } }
           if ( $DrawPlot )
            {
             for($i=2; $i<=count($Plots)-4; $i=$i+2)
              {
               if ( $PlotBorder != 0 )
                { $this->drawFilledCircle($Plots[$i],$Plots[$i+1],$PlotRadius+$PlotBorder,$PlotBorderColor); }

               $this->drawFilledCircle($Plots[$i],$Plots[$i+1],$PlotRadius,$Color);
              }
            }
           $this->Shadow = FALSE;
          }
         elseif ( $Data["Orientation"] == SCALE_POS_TOPBOTTOM )
          {
           if ( $YZero < $this->GraphAreaX1+1 ) { $YZero = $this->GraphAreaX1+1; }
           if ( $YZero > $this->GraphAreaX2-1 ) { $YZero = $this->GraphAreaX2-1; }

           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y = $this->GraphAreaY1 + $XMargin;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }

           $Plots = ""; $Plots[] = $YZero; $Plots[] = $Y;
           foreach($PosArray as $Key => $Height)
            {
             if ( $Height != VOID ) { $Plots[] = $YZero+$Height; $Plots[] = $Y; }
             $Y = $Y + $YStep;
            }
           $Plots[] = $YZero; $Plots[] = $Y-$YStep;

           $this->drawPolygon($Plots,$Color);

           $this->Shadow = $RestoreShadow;
           if ( $DrawLine ) { for($i=2; $i<=count($Plots)-6; $i=$i+2) { $this->drawLine($Plots[$i],$Plots[$i+1],$Plots[$i+2],$Plots[$i+3],$LineColor); } }
           if ( $DrawPlot )
            {
             for($i=2; $i<=count($Plots)-4; $i=$i+2)
              {
               if ( $PlotBorder != 0 )
                { $this->drawFilledCircle($Plots[$i],$Plots[$i+1],$PlotRadius+$PlotBorder,$PlotBorderColor); }

               $this->drawFilledCircle($Plots[$i],$Plots[$i+1],$PlotRadius,$Color);
              }
            }
           $this->Shadow = FALSE;
          }
        }
      }
     $this->Shadow = $RestoreShadow;
    }

   /* Returns a random color */
   function getRandomColor($Alpha=100)
    { return(array("R"=>rand(0,255),"G"=>rand(0,255),"B"=>rand(0,255),"Alpha"=>$Alpha)); }
 
   /* Validate a palette */
   function validatePalette($Colors,$Surrounding=NULL)
    {
     $Result = "";

     if ( !is_array($Colors) ) { return($this->getRandomColor()); }

     foreach($Colors as $Key => $Values)
      {
       if ( isset($Values["R"]) ) { $Result[$Key]["R"] = $Values["R"]; } else { $Result[$Key]["R"] = rand(0,255); }
       if ( isset($Values["G"]) ) { $Result[$Key]["G"] = $Values["G"]; } else { $Result[$Key]["G"] = rand(0,255); }
       if ( isset($Values["B"]) ) { $Result[$Key]["B"] = $Values["B"]; } else { $Result[$Key]["B"] = rand(0,255); }
       if ( isset($Values["Alpha"]) ) { $Result[$Key]["Alpha"] = $Values["Alpha"]; } else { $Result[$Key]["Alpha"] = 100; }

       if ( $Surrounding != NULL )
        {
         $Result[$Key]["BorderR"] = $Result[$Key]["R"] + $Surrounding;
         $Result[$Key]["BorderG"] = $Result[$Key]["G"] + $Surrounding;
         $Result[$Key]["BorderB"] = $Result[$Key]["B"] + $Surrounding;
        }
       else
        {
         if ( isset($Values["BorderR"]) )     { $Result[$Key]["BorderR"] = $Values["BorderR"]; } else { $Result[$Key]["BorderR"] = $Result[$Key]["R"]; }
         if ( isset($Values["BorderG"]) )     { $Result[$Key]["BorderG"] = $Values["BorderG"]; } else { $Result[$Key]["BorderG"] = $Result[$Key]["G"]; }
         if ( isset($Values["BorderB"]) )     { $Result[$Key]["BorderB"] = $Values["BorderB"]; } else { $Result[$Key]["BorderB"] = $Result[$Key]["B"]; }
         if ( isset($Values["BorderAlpha"]) ) { $Result[$Key]["BorderAlpha"] = $Values["BorderAlpha"]; } else { $Result[$Key]["BorderAlpha"] = $Result[$Key]["Alpha"]; }
        }
      }

     return($Result);
    }

   /* Draw the derivative chart associated to the data series */
   function drawDerivative($Format=NULL)
    {
     $Offset		= isset($Format["Offset"]) ? $Format["Offset"] : 10;
     $SerieSpacing	= isset($Format["SerieSpacing"]) ? $Format["SerieSpacing"] : 3;
     $DerivativeHeight	= isset($Format["DerivativeHeight"]) ? $Format["DerivativeHeight"] : 4;
     $ShadedSlopeBox	= isset($Format["ShadedSlopeBox"]) ? $Format["ShadedSlopeBox"] : FALSE;
     $DrawBackground	= isset($Format["DrawBackground"]) ? $Format["DrawBackground"] : TRUE;
     $BackgroundR	= isset($Format["BackgroundR"]) ? $Format["BackgroundR"] : 255;
     $BackgroundG	= isset($Format["BackgroundG"]) ? $Format["BackgroundG"] : 255;
     $BackgroundB	= isset($Format["BackgroundB"]) ? $Format["BackgroundB"] : 255;
     $BackgroundAlpha	= isset($Format["BackgroundAlpha"]) ? $Format["BackgroundAlpha"] : 20;
     $DrawBorder	= isset($Format["DrawBorder"]) ? $Format["DrawBorder"] : TRUE;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : 0;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : 0;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : 0;
     $BorderAlpha	= isset($Format["BorderAlpha"]) ? $Format["BorderAlpha"] : 100;
     $Caption		= isset($Format["Caption"]) ? $Format["Caption"] : TRUE;
     $CaptionHeight	= isset($Format["CaptionHeight"]) ? $Format["CaptionHeight"] : 10;
     $CaptionWidth	= isset($Format["CaptionWidth"]) ? $Format["CaptionWidth"] : 20;
     $CaptionMargin	= isset($Format["CaptionMargin"]) ? $Format["CaptionMargin"] : 4;
     $CaptionLine	= isset($Format["CaptionLine"]) ? $Format["CaptionLine"] : FALSE;
     $CaptionBox	= isset($Format["CaptionBox"]) ? $Format["CaptionBox"] : FALSE;
     $CaptionBorderR	= isset($Format["CaptionBorderR"]) ? $Format["CaptionBorderR"] : 0;
     $CaptionBorderG	= isset($Format["CaptionBorderG"]) ? $Format["CaptionBorderG"] : 0;
     $CaptionBorderB	= isset($Format["CaptionBorderB"]) ? $Format["CaptionBorderB"] : 0;
     $CaptionFillR	= isset($Format["CaptionFillR"]) ? $Format["CaptionFillR"] : 255;
     $CaptionFillG	= isset($Format["CaptionFillG"]) ? $Format["CaptionFillG"] : 255;
     $CaptionFillB	= isset($Format["CaptionFillB"]) ? $Format["CaptionFillB"] : 255;
     $CaptionFillAlpha	= isset($Format["CaptionFillAlpha"]) ? $Format["CaptionFillAlpha"] : 80;
     $PositiveSlopeStartR	= isset($Format["PositiveSlopeStartR"]) ? $Format["PositiveSlopeStartR"] : 184;
     $PositiveSlopeStartG	= isset($Format["PositiveSlopeStartG"]) ? $Format["PositiveSlopeStartG"] : 234;
     $PositiveSlopeStartB	= isset($Format["PositiveSlopeStartB"]) ? $Format["PositiveSlopeStartB"] : 88;
     $PositiveSlopeEndR		= isset($Format["PositiveSlopeStartR"]) ? $Format["PositiveSlopeStartR"] : 239;
     $PositiveSlopeEndG		= isset($Format["PositiveSlopeStartG"]) ? $Format["PositiveSlopeStartG"] : 31;
     $PositiveSlopeEndB		= isset($Format["PositiveSlopeStartB"]) ? $Format["PositiveSlopeStartB"] : 36;
     $NegativeSlopeStartR	= isset($Format["NegativeSlopeStartR"]) ? $Format["NegativeSlopeStartR"] : 184;
     $NegativeSlopeStartG	= isset($Format["NegativeSlopeStartG"]) ? $Format["NegativeSlopeStartG"] : 234;
     $NegativeSlopeStartB	= isset($Format["NegativeSlopeStartB"]) ? $Format["NegativeSlopeStartB"] : 88;
     $NegativeSlopeEndR		= isset($Format["NegativeSlopeStartR"]) ? $Format["NegativeSlopeStartR"] : 67;
     $NegativeSlopeEndG		= isset($Format["NegativeSlopeStartG"]) ? $Format["NegativeSlopeStartG"] : 124;
     $NegativeSlopeEndB		= isset($Format["NegativeSlopeStartB"]) ? $Format["NegativeSlopeStartB"] : 227;

     $Data = $this->DataSet->getData();

     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
      $YPos = $this->DataSet->Data["GraphArea"]["Y2"] + $Offset;
     else
      $XPos = $this->DataSet->Data["GraphArea"]["X2"] + $Offset;

     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; $Alpha = $Serie["Color"]["Alpha"]; $Ticks = $Serie["Ticks"]; $Weight = $Serie["Weight"];

         $AxisID   = $Serie["Axis"];
         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $Caption )
            {
             if ( $CaptionLine ) 
              {
               $StartX = floor($this->GraphAreaX1-$CaptionWidth+$XMargin-$CaptionMargin);
               $EndX   = floor($this->GraphAreaX1-$CaptionMargin+$XMargin);

               $CaptionSettings = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight);
               if ( $CaptionBox ) { $this->drawFilledRectangle($StartX,$YPos,$EndX,$YPos+$CaptionHeight,array("R"=>$CaptionFillR,"G"=>$CaptionFillG,"B"=>$CaptionFillB,"BorderR"=>$CaptionBorderR,"BorderG"=>$CaptionBorderG,"BorderB"=>$CaptionBorderB,"Alpha"=>$CaptionFillAlpha)); }
               $this->drawLine($StartX+2,$YPos+($CaptionHeight/2),$EndX-2,$YPos+($CaptionHeight/2),$CaptionSettings);
              }
             else
              $this->drawFilledRectangle($this->GraphAreaX1-$CaptionWidth+$XMargin-$CaptionMargin,$YPos,$this->GraphAreaX1-$CaptionMargin+$XMargin,$YPos+$CaptionHeight,array("R"=>$R,"G"=>$G,"B"=>$B,"BorderR"=>$CaptionBorderR,"BorderG"=>$CaptionBorderG,"BorderB"=>$CaptionBorderB));
            }

           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X = $this->GraphAreaX1 + $XMargin;

           $TopY    = $YPos + ($CaptionHeight/2) - ($DerivativeHeight/2);
           $BottomY = $YPos + ($CaptionHeight/2) + ($DerivativeHeight/2);

           $StartX  = floor($this->GraphAreaX1+$XMargin);
           $EndX    = floor($this->GraphAreaX2-$XMargin);

           if ( $DrawBackground ) { $this->drawFilledRectangle($StartX-1,$TopY-1,$EndX+1,$BottomY+1,array("R"=>$BackgroundR,"G"=>$BackgroundG,"B"=>$BackgroundB,"Alpha"=>$BackgroundAlpha)); }
           if ( $DrawBorder ) { $this->drawRectangle($StartX-1,$TopY-1,$EndX+1,$BottomY+1,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha)); }

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }

           $RestoreShadow = $this->Shadow;
           $this->Shadow  = FALSE;

           /* Determine the Max slope index */
           $LastX = NULL; $LastY = NULL; $MinSlope = 0; $MaxSlope = 1;
           foreach($PosArray as $Key => $Y)
            {
             if ( $Y != VOID && $LastX != NULL )
              { $Slope = ($LastY - $Y); if ( $Slope > $MaxSlope ) { $MaxSlope = $Slope; } if ( $Slope < $MinSlope ) { $MinSlope = $Slope; } }

             if ( $Y == VOID )
              { $LastX = NULL; $LastY = NULL; }
             else
              { $LastX = $X; $LastY = $Y; }
            }

           $LastX = NULL; $LastY = NULL; $LastColor = NULL;
           foreach($PosArray as $Key => $Y)
            {
             if ( $Y != VOID && $LastY != NULL )
              {
               $Slope = ($LastY - $Y);

               if ( $Slope >= 0 )
                {
                 $SlopeIndex = (100 / $MaxSlope) * $Slope;
                 $R = (($PositiveSlopeEndR - $PositiveSlopeStartR)/100)*$SlopeIndex+$PositiveSlopeStartR;
                 $G = (($PositiveSlopeEndG - $PositiveSlopeStartG)/100)*$SlopeIndex+$PositiveSlopeStartG;
                 $B = (($PositiveSlopeEndB - $PositiveSlopeStartB)/100)*$SlopeIndex+$PositiveSlopeStartB;
                }
               elseif ( $Slope < 0 )
                {
                 $SlopeIndex = (100 / abs($MinSlope)) * abs($Slope);
                 $R = (($NegativeSlopeEndR - $NegativeSlopeStartR)/100)*$SlopeIndex+$NegativeSlopeStartR;
                 $G = (($NegativeSlopeEndG - $NegativeSlopeStartG)/100)*$SlopeIndex+$NegativeSlopeStartG;
                 $B = (($NegativeSlopeEndB - $NegativeSlopeStartB)/100)*$SlopeIndex+$NegativeSlopeStartB;
                }

               $Color = array("R"=>$R,"G"=>$G,"B"=>$B);

               if ( $ShadedSlopeBox && $LastColor != NULL ) // && $Slope != 0
                {
                 $GradientSettings = array("StartR"=>$LastColor["R"],"StartG"=>$LastColor["G"],"StartB"=>$LastColor["B"],"EndR"=>$R,"EndG"=>$G,"EndB"=>$B);
                 $this->drawGradientArea($LastX,$TopY,$X,$BottomY,DIRECTION_HORIZONTAL,$GradientSettings);
                }
               elseif ( !$ShadedSlopeBox || $LastColor == NULL ) // || $Slope == 0
                $this->drawFilledRectangle(floor($LastX),$TopY,floor($X),$BottomY,$Color);

               $LastColor = $Color;
              }

             if ( $Y == VOID )
              { $LastY = NULL; }
             else
              { $LastX = $X; $LastY = $Y; }

             $X = $X + $XStep;
            }

           $YPos = $YPos + $CaptionHeight + $SerieSpacing;
          }
         else
          {
           if ( $Caption )
            {
             $StartY = floor($this->GraphAreaY1-$CaptionWidth+$XMargin-$CaptionMargin);
             $EndY   = floor($this->GraphAreaY1-$CaptionMargin+$XMargin);
             if ( $CaptionLine ) 
              {
               $CaptionSettings = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks,"Weight"=>$Weight);
               if ( $CaptionBox ) { $this->drawFilledRectangle($XPos,$StartY,$XPos+$CaptionHeight,$EndY,array("R"=>$CaptionFillR,"G"=>$CaptionFillG,"B"=>$CaptionFillB,"BorderR"=>$CaptionBorderR,"BorderG"=>$CaptionBorderG,"BorderB"=>$CaptionBorderB,"Alpha"=>$CaptionFillAlpha)); }
               $this->drawLine($XPos+($CaptionHeight/2),$StartY+2,$XPos+($CaptionHeight/2),$EndY-2,$CaptionSettings);
              }
             else
              $this->drawFilledRectangle($XPos,$StartY,$XPos+$CaptionHeight,$EndY,array("R"=>$R,"G"=>$G,"B"=>$B,"BorderR"=>$CaptionBorderR,"BorderG"=>$CaptionBorderG,"BorderB"=>$CaptionBorderB));
            }


           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $XStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y = $this->GraphAreaY1 + $XMargin;

           $TopX    = $XPos + ($CaptionHeight/2) - ($DerivativeHeight/2);
           $BottomX = $XPos + ($CaptionHeight/2) + ($DerivativeHeight/2);

           $StartY  = floor($this->GraphAreaY1+$XMargin);
           $EndY    = floor($this->GraphAreaY2-$XMargin);

           if ( $DrawBackground ) { $this->drawFilledRectangle($TopX-1,$StartY-1,$BottomX+1,$EndY+1,array("R"=>$BackgroundR,"G"=>$BackgroundG,"B"=>$BackgroundB,"Alpha"=>$BackgroundAlpha)); }
           if ( $DrawBorder ) { $this->drawRectangle($TopX-1,$StartY-1,$BottomX+1,$EndY+1,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha)); }

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }

           $RestoreShadow = $this->Shadow;
           $this->Shadow  = FALSE;

           /* Determine the Max slope index */
           $LastX = NULL; $LastY = NULL; $MinSlope = 0; $MaxSlope = 1;
           foreach($PosArray as $Key => $X)
            {
             if ( $X != VOID && $LastX != NULL )
              { $Slope = ($X - $LastX); if ( $Slope > $MaxSlope ) { $MaxSlope = $Slope; } if ( $Slope < $MinSlope ) { $MinSlope = $Slope; } }

             if ( $X == VOID )
              { $LastX = NULL; }
             else
              { $LastX = $X; }
            }

           $LastX = NULL; $LastY = NULL; $LastColor = NULL;
           foreach($PosArray as $Key => $X)
            {
             if ( $X != VOID && $LastX != NULL )
              {
               $Slope = ($X - $LastX);

               if ( $Slope >= 0 )
                {
                 $SlopeIndex = (100 / $MaxSlope) * $Slope;
                 $R = (($PositiveSlopeEndR - $PositiveSlopeStartR)/100)*$SlopeIndex+$PositiveSlopeStartR;
                 $G = (($PositiveSlopeEndG - $PositiveSlopeStartG)/100)*$SlopeIndex+$PositiveSlopeStartG;
                 $B = (($PositiveSlopeEndB - $PositiveSlopeStartB)/100)*$SlopeIndex+$PositiveSlopeStartB;
                }
               elseif ( $Slope < 0 )
                {
                 $SlopeIndex = (100 / abs($MinSlope)) * abs($Slope);
                 $R = (($NegativeSlopeEndR - $NegativeSlopeStartR)/100)*$SlopeIndex+$NegativeSlopeStartR;
                 $G = (($NegativeSlopeEndG - $NegativeSlopeStartG)/100)*$SlopeIndex+$NegativeSlopeStartG;
                 $B = (($NegativeSlopeEndB - $NegativeSlopeStartB)/100)*$SlopeIndex+$NegativeSlopeStartB;
                }

               $Color = array("R"=>$R,"G"=>$G,"B"=>$B);

               if ( $ShadedSlopeBox && $LastColor != NULL )
                {
                 $GradientSettings = array("StartR"=>$LastColor["R"],"StartG"=>$LastColor["G"],"StartB"=>$LastColor["B"],"EndR"=>$R,"EndG"=>$G,"EndB"=>$B);

                 $this->drawGradientArea($TopX,$LastY,$BottomX,$Y,DIRECTION_VERTICAL,$GradientSettings);
                }
               elseif ( !$ShadedSlopeBox || $LastColor == NULL ) 
                $this->drawFilledRectangle($TopX,floor($LastY),$BottomX,floor($Y),$Color);

               $LastColor = $Color;
              }

             if ( $X == VOID )
              { $LastX = NULL; }
             else
              { $LastX = $X; $LastY = $Y; }

             $Y = $Y + $XStep;
            }

           $XPos = $XPos + $CaptionHeight + $SerieSpacing;
          }

         $this->Shadow = $RestoreShadow;
        }
      }
    }

   /* Draw the line of best fit */
   function drawBestFit($Format="")
    {
     $OverrideTicks	= isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
     $OverrideR		= isset($Format["R"]) ? $Format["R"] : VOID;
     $OverrideG		= isset($Format["G"]) ? $Format["G"] : VOID;
     $OverrideB		= isset($Format["B"]) ? $Format["B"] : VOID;
     $OverrideAlpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : VOID;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     foreach($Data["Series"] as $SerieName => $Serie)
      {
       if ( $Serie["isDrawable"] == TRUE && $SerieName != $Data["Abscissa"] )
        {
         if ( $OverrideR != VOID && $OverrideG != VOID && $OverrideB != VOID ) { $R = $OverrideR; $G = $OverrideG; $B = $OverrideB; } else { $R = $Serie["Color"]["R"]; $G = $Serie["Color"]["G"]; $B = $Serie["Color"]["B"]; }
         if ( $OverrideTicks == NULL ) { $Ticks = $Serie["Ticks"]; } else { $Ticks = $OverrideTicks; }
         if ( $OverrideAlpha == VOID ) { $Alpha = $Serie["Color"]["Alpha"]; } else { $Alpha = $OverrideAlpha; }

         $Color = array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"Ticks"=>$Ticks);

         $AxisID   = $Serie["Axis"];
         $PosArray = $this->scaleComputeY($Serie["Data"],array("AxisID"=>$Serie["Axis"]));

         if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
          {
           if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
           $X = $this->GraphAreaX1 + $XMargin;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $Sxy = 0; $Sx = 0; $Sy = 0; $Sxx = 0;
           foreach($PosArray as $Key => $Y)
            {
             if ( $Y != VOID )
              {
               $Sxy = $Sxy + $X*$Y;
               $Sx  = $Sx + $X;
               $Sy  = $Sy + $Y;
               $Sxx = $Sxx + $X*$X;
              }

             $X = $X + $XStep;
            }
           $n = count($this->DataSet->stripVOID($PosArray)); //$n = count($PosArray);
           $M = (($n*$Sxy)-($Sx*$Sy)) / (($n*$Sxx)-($Sx*$Sx));
           $B = (($Sy)-($M*$Sx))/($n);

           $X1 = $this->GraphAreaX1 + $XMargin;
           $Y1 = $M * $X1 + $B;
           $X2 = $this->GraphAreaX2 - $XMargin;
           $Y2 = $M * $X2 + $B;

           if ( $Y1 < $this->GraphAreaY1 ) { $X1 = $X1 + ($this->GraphAreaY1-$Y1); $Y1 = $this->GraphAreaY1; }
           if ( $Y1 > $this->GraphAreaY2 ) { $X1 = $X1 + ($Y1-$this->GraphAreaY2); $Y1 = $this->GraphAreaY2; }
           if ( $Y2 < $this->GraphAreaY1 ) { $X2 = $X2 - ($this->GraphAreaY1-$Y2); $Y2 = $this->GraphAreaY1; }
           if ( $Y2 > $this->GraphAreaY2 ) { $X2 = $X2 - ($Y2-$this->GraphAreaY2); $Y2 = $this->GraphAreaY2; }

           $this->drawLine($X1,$Y1,$X2,$Y2,$Color);
          }
         else
          {
           if ( $XDivs == 0 ) { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $YStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
           $Y = $this->GraphAreaY1 + $XMargin;

           if ( !is_array($PosArray) ) { $Value = $PosArray; $PosArray = ""; $PosArray[0] = $Value; }
           $Sxy = 0; $Sx = 0; $Sy = 0; $Sxx = 0;
           foreach($PosArray as $Key => $X)
            {
             if ( $X != VOID )
              {
               $Sxy = $Sxy + $X*$Y;
               $Sx  = $Sx + $Y;
               $Sy  = $Sy + $X;
               $Sxx = $Sxx + $Y*$Y;
              }

             $Y = $Y + $YStep;
            }
           $n = count($this->DataSet->stripVOID($PosArray)); //$n = count($PosArray);
           $M = (($n*$Sxy)-($Sx*$Sy)) / (($n*$Sxx)-($Sx*$Sx));
           $B = (($Sy)-($M*$Sx))/($n);

           $Y1 = $this->GraphAreaY1 + $XMargin;
           $X1 = $M * $Y1 + $B;
           $Y2 = $this->GraphAreaY2 - $XMargin;
           $X2 = $M * $Y2 + $B;

           if ( $X1 < $this->GraphAreaX1 ) { $Y1 = $Y1 + ($this->GraphAreaX1-$X1); $X1 = $this->GraphAreaX1; }
           if ( $X1 > $this->GraphAreaX2 ) { $Y1 = $Y1 + ($X1-$this->GraphAreaX2); $X1 = $this->GraphAreaX2; }
           if ( $X2 < $this->GraphAreaX1 ) { $Y2 = $Y2 - ($this->GraphAreaY1-$X2); $X2 = $this->GraphAreaX1; }
           if ( $X2 > $this->GraphAreaX2 ) { $Y2 = $Y2 - ($X2-$this->GraphAreaX2); $X2 = $this->GraphAreaX2; }

           $this->drawLine($X1,$Y1,$X2,$Y2,$Color);
          }
        }
      }
    }

   /* Write labels */
   function writeLabel($SeriesName,$Indexes,$Format="")
    {
     $OverrideTitle	= isset($Format["OverrideTitle"]) ? $Format["OverrideTitle"] : NULL;
     $ForceLabels	= isset($Format["ForceLabels"]) ? $Format["ForceLabels"] : NULL;
     $DrawPoint		= isset($Format["DrawPoint"]) ? $Format["DrawPoint"] : LABEL_POINT_BOX;
     $DrawVerticalLine	= isset($Format["DrawVerticalLine"]) ? $Format["DrawVerticalLine"] : FALSE;
     $VerticalLineR	= isset($Format["VerticalLineR"]) ? $Format["VerticalLineR"] : 0;
     $VerticalLineG	= isset($Format["VerticalLineG"]) ? $Format["VerticalLineG"] : 0;
     $VerticalLineB	= isset($Format["VerticalLineB"]) ? $Format["VerticalLineB"] : 0;
     $VerticalLineAlpha	= isset($Format["VerticalLineAlpha"]) ? $Format["VerticalLineAlpha"] : 40;
     $VerticalLineTicks	= isset($Format["VerticalLineTicks"]) ? $Format["VerticalLineTicks"] : 2;

     $Data = $this->DataSet->getData();
     list($XMargin,$XDivs) = $this->scaleGetXSettings();

     if ( !is_array($Indexes) )    { $Index = $Indexes; $Indexes = ""; $Indexes[] = $Index; }
     if ( !is_array($SeriesName) ) { $SerieName = $SeriesName; $SeriesName = ""; $SeriesName[] = $SerieName; }
     if ( $ForceLabels != NULL && !is_array($ForceLabels) ) { $ForceLabel = $ForceLabels; $ForceLabels = ""; $ForceLabels[] = $ForceLabel; }

     foreach ($Indexes as $Key => $Index)
      {
       $Series = "";

       if ( $Data["Orientation"] == SCALE_POS_LEFTRIGHT )
        {
         if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1)/4; } else { $XStep = ($this->GraphAreaX2-$this->GraphAreaX1-$XMargin*2)/$XDivs; }
         $X = $this->GraphAreaX1 + $XMargin + $Index * $XStep;

         if ( $DrawVerticalLine ) { $this->drawLine($X,$this->GraphAreaY1+$Data["YMargin"],$X,$this->GraphAreaY2-$Data["YMargin"],array("R"=>$VerticalLineR,"G"=>$VerticalLineG,"B"=>$VerticalLineB,"Alpha"=>$VerticalLineAlpha,"Ticks"=>$VerticalLineTicks)); }

         $MinY = $this->GraphAreaY2;
         foreach ($SeriesName as $iKey => $SerieName)
          {
           if ( isset($Data["Series"][$SerieName]["Data"][$Index]) )
            {
             $AxisID       = $Data["Series"][$SerieName]["Axis"];
             $XAxisMode    = $Data["XAxisDisplay"];
             $XAxisFormat  = $Data["XAxisFormat"];
             $XAxisUnit    = $Data["XAxisUnit"];
             $AxisMode     = $Data["Axis"][$AxisID]["Display"];
             $AxisFormat   = $Data["Axis"][$AxisID]["Format"];
             $AxisUnit     = $Data["Axis"][$AxisID]["Unit"];

             if ( isset($Data["Abscissa"]) && isset($Data["Series"][$Data["Abscissa"]]["Data"][$Index]) )
              $XLabel = $this->scaleFormat($Data["Series"][$Data["Abscissa"]]["Data"][$Index],$XAxisMode,$XAxisFormat,$XAxisUnit);
             else
              $XLabel = "";

             if ( $OverrideTitle != NULL)
              $Description = $OverrideTitle;
             elseif ( count($SeriesName) == 1 )
              {
               $Description = $Data["Series"][$SerieName]["Description"]." - ".$XLabel;
              }
             elseif ( isset($Data["Abscissa"]) && isset($Data["Series"][$Data["Abscissa"]]["Data"][$Index]) )
              $Description = $XLabel;

             $Serie = "";
             $Serie["R"] = $Data["Series"][$SerieName]["Color"]["R"];
             $Serie["G"] = $Data["Series"][$SerieName]["Color"]["G"];
             $Serie["B"] = $Data["Series"][$SerieName]["Color"]["B"];
             $Serie["Alpha"] = $Data["Series"][$SerieName]["Color"]["Alpha"];

             if ( count($SeriesName) == 1 && isset($Data["Series"][$SerieName]["XOffset"]) )
              $SerieOffset = $Data["Series"][$SerieName]["XOffset"];
             else
              $SerieOffset = 0;

             $Value = $Data["Series"][$SerieName]["Data"][$Index];
             if ( $Value == VOID ) { $Value = "NaN"; }

             if ( $ForceLabels != NULL )
              $Caption = isset($ForceLabels[$Key]) ? $ForceLabels[$Key] : "Not set";
             else
              $Caption = $this->scaleFormat($Value,$AxisMode,$AxisFormat,$AxisUnit);

             if ( $this->LastChartLayout == CHART_LAST_LAYOUT_STACKED )
              {
               if ( $Value >=0 ) { $LookFor = "+"; } else { $LookFor = "-"; }

               $Value = 0; $Done = FALSE;
               foreach($Data["Series"] as $Name => $SerieLookup)
                {
                 if ( $SerieLookup["isDrawable"] == TRUE && $Name != $Data["Abscissa"] && !$Done )
                  {
                   if ( isset($Data["Series"][$Name]["Data"][$Index]) && $Data["Series"][$Name]["Data"][$Index] != VOID )
                    {
                     if ($Data["Series"][$Name]["Data"][$Index] >= 0 && $LookFor == "+" ) { $Value = $Value + $Data["Series"][$Name]["Data"][$Index]; }
                     if ($Data["Series"][$Name]["Data"][$Index] < 0 && $LookFor == "-" )  { $Value = $Value - $Data["Series"][$Name]["Data"][$Index]; }
                     if ($Name == $SerieName ) { $Done = TRUE; }
                    }
                  }
                }
              }

             $X = floor($this->GraphAreaX1 + $XMargin + $Index * $XStep + $SerieOffset);
             $Y = floor($this->scaleComputeY($Value,array("AxisID"=>$AxisID)));

             if ($Y < $MinY) { $MinY = $Y; }

             if ( $DrawPoint == LABEL_POINT_CIRCLE )
              $this->drawFilledCircle($X,$Y,3,array("R"=>255,"G"=>255,"B"=>255,"BorderR"=>0,"BorderG"=>0,"BorderB"=>0));
             elseif ( $DrawPoint == LABEL_POINT_BOX )
              $this->drawFilledRectangle($X-2,$Y-2,$X+2,$Y+2,array("R"=>255,"G"=>255,"B"=>255,"BorderR"=>0,"BorderG"=>0,"BorderB"=>0));

             $Series[] = array("Format"=>$Serie,"Caption"=>$Caption);
            }
          }
         $this->drawLabelBox($X,$MinY-3,$Description,$Series,$Format);

        }
       else
        {
         if ( $XDivs == 0 ) { $XStep = ($this->GraphAreaY2-$this->GraphAreaY1)/4; } else { $XStep = ($this->GraphAreaY2-$this->GraphAreaY1-$XMargin*2)/$XDivs; }
         $Y = $this->GraphAreaY1 + $XMargin + $Index * $XStep;

         if ( $DrawVerticalLine ) { $this->drawLine($this->GraphAreaX1+$Data["YMargin"],$Y,$this->GraphAreaX2-$Data["YMargin"],$Y,array("R"=>$VerticalLineR,"G"=>$VerticalLineG,"B"=>$VerticalLineB,"Alpha"=>$VerticalLineAlpha,"Ticks"=>$VerticalLineTicks)); }

         $MinX = $this->GraphAreaX2;
         foreach ($SeriesName as $Key => $SerieName)
          {
           if ( isset($Data["Series"][$SerieName]["Data"][$Index]) )
            {
             $AxisID      = $Data["Series"][$SerieName]["Axis"];
             $XAxisMode   = $Data["XAxisDisplay"];
             $XAxisFormat = $Data["XAxisFormat"];
             $XAxisUnit   = $Data["XAxisUnit"];
             $AxisMode    = $Data["Axis"][$AxisID]["Display"];
             $AxisFormat  = $Data["Axis"][$AxisID]["Format"];
             $AxisUnit    = $Data["Axis"][$AxisID]["Unit"];

             if ( isset($Data["Abscissa"]) && isset($Data["Series"][$Data["Abscissa"]]["Data"][$Index]) )
              $XLabel = $this->scaleFormat($Data["Series"][$Data["Abscissa"]]["Data"][$Index],$XAxisMode,$XAxisFormat,$XAxisUnit);
             else
              $XLabel = "";

             if ( $OverrideTitle != NULL)
              $Description = $OverrideTitle;
             elseif ( count($SeriesName) == 1 )
              {
               if ( isset($Data["Abscissa"]) && isset($Data["Series"][$Data["Abscissa"]]["Data"][$Index]) )
                $Description = $Data["Series"][$SerieName]["Description"]." - ".$XLabel;
              }
             elseif ( isset($Data["Abscissa"]) && isset($Data["Series"][$Data["Abscissa"]]["Data"][$Index]) )
              $Description = $XLabel;

             $Serie = "";
             if ( isset($Data["Extended"]["Palette"][$Index] ) )
              {
               $Serie["R"] = $Data["Extended"]["Palette"][$Index]["R"];
               $Serie["G"] = $Data["Extended"]["Palette"][$Index]["G"];
               $Serie["B"] = $Data["Extended"]["Palette"][$Index]["B"];
               $Serie["Alpha"] = $Data["Extended"]["Palette"][$Index]["Alpha"];
              }
             else
              {
               $Serie["R"] = $Data["Series"][$SerieName]["Color"]["R"];
               $Serie["G"] = $Data["Series"][$SerieName]["Color"]["G"];
               $Serie["B"] = $Data["Series"][$SerieName]["Color"]["B"];
               $Serie["Alpha"] = $Data["Series"][$SerieName]["Color"]["Alpha"];
              }

             if ( count($SeriesName) == 1 && isset($Data["Series"][$SerieName]["XOffset"]) )
              $SerieOffset = $Data["Series"][$SerieName]["XOffset"];
             else
              $SerieOffset = 0;

             $Value = $Data["Series"][$SerieName]["Data"][$Index];
             if ( $ForceLabels != NULL )
              $Caption = isset($ForceLabels[$Key]) ? $ForceLabels[$Key] : "Not set";
             else
              $Caption = $this->scaleFormat($Value,$AxisMode,$AxisFormat,$AxisUnit);
             if ( $Value == VOID ) { $Value = "NaN"; }

             if ( $this->LastChartLayout == CHART_LAST_LAYOUT_STACKED )
              {
               if ( $Value >=0 ) { $LookFor = "+"; } else { $LookFor = "-"; }

               $Value = 0; $Done = FALSE;
               foreach($Data["Series"] as $Name => $SerieLookup)
                {
                 if ( $SerieLookup["isDrawable"] == TRUE && $Name != $Data["Abscissa"] && !$Done )
                  {
                   if ( isset($Data["Series"][$Name]["Data"][$Index]) && $Data["Series"][$Name]["Data"][$Index] != VOID )
                    {
                     if ($Data["Series"][$Name]["Data"][$Index] >= 0 && $LookFor == "+" ) { $Value = $Value + $Data["Series"][$Name]["Data"][$Index]; }
                     if ($Data["Series"][$Name]["Data"][$Index] < 0 && $LookFor == "-" )  { $Value = $Value - $Data["Series"][$Name]["Data"][$Index]; }
                     if ($Name == $SerieName ) { $Done = TRUE; }
                    }
                  }
                }
              }

             $X = floor($this->scaleComputeY($Value,array("AxisID"=>$AxisID)));
             $Y = floor($this->GraphAreaY1 + $XMargin + $Index * $XStep + $SerieOffset);

             if ($X < $MinX) { $MinX = $X; }

             if ( $DrawPoint == LABEL_POINT_CIRCLE )
              $this->drawFilledCircle($X,$Y,3,array("R"=>255,"G"=>255,"B"=>255,"BorderR"=>0,"BorderG"=>0,"BorderB"=>0));
             elseif ( $DrawPoint == LABEL_POINT_BOX )
              $this->drawFilledRectangle($X-2,$Y-2,$X+2,$Y+2,array("R"=>255,"G"=>255,"B"=>255,"BorderR"=>0,"BorderG"=>0,"BorderB"=>0));

             $Series[] = array("Format"=>$Serie,"Caption"=>$Caption);
            }
          }
         $this->drawLabelBox($MinX,$Y-3,$Description,$Series,$Format);

        }
      }
    }

   /* Draw a label box */
   function drawLabelBox($X,$Y,$Title,$Captions,$Format="")
    {
     $NoTitle			= isset($Format["NoTitle"]) ? $Format["NoTitle"] : NULL;
     $BoxWidth			= isset($Format["BoxWidth"]) ? $Format["BoxWidth"] : 50;
     $DrawSerieColor		= isset($Format["DrawSerieColor"]) ? $Format["DrawSerieColor"] : TRUE;
     $SerieR			= isset($Format["SerieR"]) ? $Format["SerieR"] : NULL;
     $SerieG			= isset($Format["SerieG"]) ? $Format["SerieG"] : NULL;
     $SerieB			= isset($Format["SerieB"]) ? $Format["SerieB"] : NULL;
     $SerieAlpha		= isset($Format["SerieAlpha"]) ? $Format["SerieAlpha"] : NULL;
     $SerieBoxSize		= isset($Format["SerieBoxSize"]) ? $Format["SerieBoxSize"] : 6;
     $SerieBoxSpacing		= isset($Format["SerieBoxSpacing"]) ? $Format["SerieBoxSpacing"] : 4;
     $VerticalMargin		= isset($Format["VerticalMargin"]) ? $Format["VerticalMargin"] : 10;
     $HorizontalMargin		= isset($Format["HorizontalMargin"]) ? $Format["HorizontalMargin"] : 8;
     $R				= isset($Format["R"]) ? $Format["R"] : $this->FontColorR;
     $G				= isset($Format["G"]) ? $Format["G"] : $this->FontColorG;
     $B				= isset($Format["B"]) ? $Format["B"] : $this->FontColorB;
     $Alpha			= isset($Format["Alpha"]) ? $Format["Alpha"] : $this->FontColorA;
     $FontName			= isset($Format["FontName"]) ? $Format["FontName"] : $this->FontName;
     $FontSize			= isset($Format["FontSize"]) ? $Format["FontSize"] : $this->FontSize;
     $TitleMode			= isset($Format["TitleMode"]) ? $Format["TitleMode"] : LABEL_TITLE_NOBACKGROUND;
     $TitleR			= isset($Format["TitleR"]) ? $Format["TitleR"] : $R;
     $TitleG			= isset($Format["TitleG"]) ? $Format["TitleG"] : $G;
     $TitleB			= isset($Format["TitleB"]) ? $Format["TitleB"] : $B;
     $TitleAlpha		= isset($Format["TitleAlpha"]) ? $Format["TitleAlpha"] : 100;
     $TitleBackgroundR		= isset($Format["TitleBackgroundR"]) ? $Format["TitleBackgroundR"] : 0;
     $TitleBackgroundG		= isset($Format["TitleBackgroundG"]) ? $Format["TitleBackgroundG"] : 0;
     $TitleBackgroundB		= isset($Format["TitleBackgroundB"]) ? $Format["TitleBackgroundB"] : 0;
     $TitleBackgroundAlpha	= isset($Format["TitleBackgroundAlpha"]) ? $Format["TitleBackgroundAlpha"] : 100;
     $GradientStartR		= isset($Format["GradientStartR"]) ? $Format["GradientStartR"] : 255;
     $GradientStartG		= isset($Format["GradientStartG"]) ? $Format["GradientStartG"] : 255;
     $GradientStartB		= isset($Format["GradientStartB"]) ? $Format["GradientStartB"] : 255;
     $GradientEndR		= isset($Format["GradientEndR"]) ? $Format["GradientEndR"] : 220;
     $GradientEndG		= isset($Format["GradientEndG"]) ? $Format["GradientEndG"] : 220;
     $GradientEndB		= isset($Format["GradientEndB"]) ? $Format["GradientEndB"] : 220;
     $BoxAlpha			= isset($Format["BoxAlpha"]) ? $Format["BoxAlpha"] : 100;

     if ( !$DrawSerieColor ) { $SerieBoxSize = 0; $SerieBoxSpacing = 0; }

     $TxtPos      = $this->getTextBox($X,$Y,$FontName,$FontSize,0,$Title);
     $TitleWidth  = ($TxtPos[1]["X"] - $TxtPos[0]["X"])+$VerticalMargin*2;
     $TitleHeight = ($TxtPos[0]["Y"] - $TxtPos[2]["Y"]);

     if ( $NoTitle ) { $TitleWidth = 0; $TitleHeight = 0; }

     $CaptionWidth = 0; $CaptionHeight = -$HorizontalMargin;
     foreach($Captions as $Key =>$Caption)
      {
       $TxtPos        = $this->getTextBox($X,$Y,$FontName,$FontSize,0,$Caption["Caption"]);
       $CaptionWidth  = max($CaptionWidth,($TxtPos[1]["X"] - $TxtPos[0]["X"])+$VerticalMargin*2);
       $CaptionHeight = $CaptionHeight + max(($TxtPos[0]["Y"] - $TxtPos[2]["Y"]),($SerieBoxSize+2)) + $HorizontalMargin;
      }

     if ( $CaptionHeight <= 5 ) { $CaptionHeight = $CaptionHeight + $HorizontalMargin/2; }

     if ( $DrawSerieColor ) { $CaptionWidth = $CaptionWidth + $SerieBoxSize + $SerieBoxSpacing; }

     $BoxWidth = max($BoxWidth,$TitleWidth,$CaptionWidth);

     $XMin = $X - 5 - floor(($BoxWidth-10) / 2);
     $XMax = $X + 5 + floor(($BoxWidth-10) / 2);

     $RestoreShadow = $this->Shadow;
     if ( $this->Shadow == TRUE )
      {
       $this->Shadow = FALSE;

       $Poly = "";
       $Poly[] = $X+$this->ShadowX; $Poly[] = $Y+$this->ShadowX;
       $Poly[] = $X+5+$this->ShadowX; $Poly[] = $Y-5+$this->ShadowX;
       $Poly[] = $XMax+$this->ShadowX; $Poly[] = $Y-5+$this->ShadowX;

       if ( $NoTitle )
        {
         $Poly[] = $XMax+$this->ShadowX; $Poly[] = $Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*2+$this->ShadowX;
         $Poly[] = $XMin+$this->ShadowX; $Poly[] = $Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*2+$this->ShadowX;
        }
       else
        {
         $Poly[] = $XMax+$this->ShadowX; $Poly[] = $Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*3+$this->ShadowX;
         $Poly[] = $XMin+$this->ShadowX; $Poly[] = $Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*3+$this->ShadowX;
        }

       $Poly[] = $XMin+$this->ShadowX; $Poly[] = $Y-5+$this->ShadowX;
       $Poly[] = $X-5+$this->ShadowX; $Poly[] = $Y-5+$this->ShadowX;
       $this->drawPolygon($Poly,array("R"=>$this->ShadowR,"G"=>$this->ShadowG,"B"=>$this->ShadowB,"Alpha"=>$this->Shadowa));
      }

     /* Draw the background */
     $GradientSettings = array("StartR"=>$GradientStartR,"StartG"=>$GradientStartG,"StartB"=>$GradientStartB,"EndR"=>$GradientEndR,"EndG"=>$GradientEndG,"EndB"=>$GradientEndB,"Alpha"=>$BoxAlpha);
     if ( $NoTitle )
      $this->drawGradientArea($XMin,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$XMax,$Y-6,DIRECTION_VERTICAL,$GradientSettings);
     else
      $this->drawGradientArea($XMin,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$XMax,$Y-6,DIRECTION_VERTICAL,$GradientSettings);
     $Poly = ""; $Poly[] = $X; $Poly[] = $Y; $Poly[] = $X-5; $Poly[] = $Y-5; $Poly[] = $X+5; $Poly[] = $Y-5;
     $this->drawPolygon($Poly,array("R"=>$GradientEndR,"G"=>$GradientEndG,"B"=>$GradientEndB,"Alpha"=>$BoxAlpha,"NoBorder"=>TRUE));

     /* Outer border */
     $OuterBorderColor = $this->allocateColor($this->Picture,100,100,100,$BoxAlpha);
     imageline($this->Picture,$XMin,$Y-5,$X-5,$Y-5,$OuterBorderColor);
     imageline($this->Picture,$X,$Y,$X-5,$Y-5,$OuterBorderColor);
     imageline($this->Picture,$X,$Y,$X+5,$Y-5,$OuterBorderColor);
     imageline($this->Picture,$X+5,$Y-5,$XMax,$Y-5,$OuterBorderColor);
     if ( $NoTitle )
      {
       imageline($this->Picture,$XMin,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$XMin,$Y-5,$OuterBorderColor);
       imageline($this->Picture,$XMax,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$XMax,$Y-5,$OuterBorderColor);
       imageline($this->Picture,$XMin,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$XMax,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$OuterBorderColor);
      }
     else
      {
       imageline($this->Picture,$XMin,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$XMin,$Y-5,$OuterBorderColor);
       imageline($this->Picture,$XMax,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$XMax,$Y-5,$OuterBorderColor);
       imageline($this->Picture,$XMin,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$XMax,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$OuterBorderColor);
      }

     /* Inner border */
     $InnerBorderColor = $this->allocateColor($this->Picture,255,255,255,$BoxAlpha);
     imageline($this->Picture,$XMin+1,$Y-6,$X-5,$Y-6,$InnerBorderColor);
     imageline($this->Picture,$X,$Y-1,$X-5,$Y-6,$InnerBorderColor);
     imageline($this->Picture,$X,$Y-1,$X+5,$Y-6,$InnerBorderColor);
     imageline($this->Picture,$X+5,$Y-6,$XMax-1,$Y-6,$InnerBorderColor);
     if ( $NoTitle )
      {
       imageline($this->Picture,$XMin+1,$Y-4-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$XMin+1,$Y-6,$InnerBorderColor);
       imageline($this->Picture,$XMax-1,$Y-4-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$XMax-1,$Y-6,$InnerBorderColor);
       imageline($this->Picture,$XMin+1,$Y-4-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$XMax-1,$Y-4-$TitleHeight-$CaptionHeight-$HorizontalMargin*2,$InnerBorderColor);
      }
     else
      {
       imageline($this->Picture,$XMin+1,$Y-4-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$XMin+1,$Y-6,$InnerBorderColor);
       imageline($this->Picture,$XMax-1,$Y-4-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$XMax-1,$Y-6,$InnerBorderColor);
       imageline($this->Picture,$XMin+1,$Y-4-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$XMax-1,$Y-4-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$InnerBorderColor);
      }

     /* Draw the separator line */
     if ( $TitleMode == LABEL_TITLE_NOBACKGROUND && !$NoTitle )
      {
       $YPos    = $Y-7-$CaptionHeight-$HorizontalMargin-$HorizontalMargin/2;
       $XMargin = $VerticalMargin / 2;
       $this->drawLine($XMin+$XMargin,$YPos+1,$XMax-$XMargin,$YPos+1,array("R"=>$GradientEndR,"G"=>$GradientEndG,"B"=>$GradientEndB,"Alpha"=>$BoxAlpha));
       $this->drawLine($XMin+$XMargin,$YPos,$XMax-$XMargin,$YPos,array("R"=>$GradientStartR,"G"=>$GradientStartG,"B"=>$GradientStartB,"Alpha"=>$BoxAlpha));
      }
     elseif ( $TitleMode == LABEL_TITLE_BACKGROUND )
      {
       $this->drawFilledRectangle($XMin,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin*3,$XMax,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin+$HorizontalMargin/2,array("R"=>$TitleBackgroundR,"G"=>$TitleBackgroundG,"B"=>$TitleBackgroundB,"Alpha"=>$BoxAlpha));
       imageline($this->Picture,$XMin+1,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin+$HorizontalMargin/2+1,$XMax-1,$Y-5-$TitleHeight-$CaptionHeight-$HorizontalMargin+$HorizontalMargin/2+1,$InnerBorderColor);
      }

     /* Write the description */
     if ( !$NoTitle )
      $this->drawText($XMin+$VerticalMargin,$Y-7-$CaptionHeight-$HorizontalMargin*2,$Title,array("Align"=>TEXT_ALIGN_BOTTOMLEFT,"R"=>$TitleR,"G"=>$TitleG,"B"=>$TitleB));

     /* Write the value */
     $YPos = $Y-5-$HorizontalMargin; $XPos = $XMin+$VerticalMargin+$SerieBoxSize+$SerieBoxSpacing;
     foreach($Captions as $Key => $Caption)
      {
       $CaptionTxt    = $Caption["Caption"];
       $TxtPos        = $this->getTextBox($XPos,$YPos,$FontName,$FontSize,0,$CaptionTxt);
       $CaptionHeight = ($TxtPos[0]["Y"] - $TxtPos[2]["Y"]);

       /* Write the serie color if needed */
       if ( $DrawSerieColor )
        {
         $BoxSettings = array("R"=>$Caption["Format"]["R"],"G"=>$Caption["Format"]["G"],"B"=>$Caption["Format"]["B"],"Alpha"=>$Caption["Format"]["Alpha"],"BorderR"=>0,"BorderG"=>0,"BorderB"=>0);
         $this->drawFilledRectangle($XMin+$VerticalMargin,$YPos-$SerieBoxSize,$XMin+$VerticalMargin+$SerieBoxSize,$YPos,$BoxSettings);
        }

       $this->drawText($XPos,$YPos,$CaptionTxt,array("Align"=>TEXT_ALIGN_BOTTOMLEFT));

       $YPos = $YPos - $CaptionHeight - $HorizontalMargin;
      }

     $this->Shadow = $RestoreShadow;
    }

   /* Draw a basic shape */
   function drawShape($X,$Y,$Shape,$PlotSize,$PlotBorder,$BorderSize,$R,$G,$B,$Alpha,$BorderR,$BorderG,$BorderB,$BorderAlpha)
    {
     if ( $Shape == SERIE_SHAPE_FILLEDCIRCLE )
      {
       if ( $PlotBorder ) { $this->drawFilledCircle($X,$Y,$PlotSize+$BorderSize,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha)); }
       $this->drawFilledCircle($X,$Y,$PlotSize,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
      }
     elseif ( $Shape == SERIE_SHAPE_FILLEDSQUARE )
      {
       if ( $PlotBorder ) { $this->drawFilledRectangle($X-$PlotSize-$BorderSize,$Y-$PlotSize-$BorderSize,$X+$PlotSize+$BorderSize,$Y+$PlotSize+$BorderSize,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha)); }
       $this->drawFilledRectangle($X-$PlotSize,$Y-$PlotSize,$X+$PlotSize,$Y+$PlotSize,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
      }
     elseif ( $Shape == SERIE_SHAPE_FILLEDTRIANGLE )
      {
       if ( $PlotBorder )
        {
         $Pos = ""; $Pos[]=$X; $Pos[]=$Y-$PlotSize-$BorderSize; $Pos[]=$X-$PlotSize-$BorderSize; $Pos[]=$Y+$PlotSize+$BorderSize; $Pos[]=$X+$PlotSize+$BorderSize; $Pos[]=$Y+$PlotSize+$BorderSize;
         $this->drawPolygon($Pos,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha));
        }

       $Pos = ""; $Pos[]=$X; $Pos[]=$Y-$PlotSize; $Pos[]=$X-$PlotSize; $Pos[]=$Y+$PlotSize; $Pos[]=$X+$PlotSize; $Pos[]=$Y+$PlotSize;
       $this->drawPolygon($Pos,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
      }
     elseif ( $Shape == SERIE_SHAPE_TRIANGLE )
      {
       $this->drawLine($X,$Y-$PlotSize,$X-$PlotSize,$Y+$PlotSize,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
       $this->drawLine($X-$PlotSize,$Y+$PlotSize,$X+$PlotSize,$Y+$PlotSize,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
       $this->drawLine($X+$PlotSize,$Y+$PlotSize,$X,$Y-$PlotSize,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
      }
     elseif ( $Shape == SERIE_SHAPE_SQUARE )
      $this->drawRectangle($X-$PlotSize,$Y-$PlotSize,$X+$PlotSize,$Y+$PlotSize,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
     elseif ( $Shape == SERIE_SHAPE_CIRCLE )
      $this->drawCircle($X,$Y,$PlotSize,$PlotSize,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
     elseif ( $Shape == SERIE_SHAPE_DIAMOND )
      {
       $Pos = ""; $Pos[]=$X-$PlotSize; $Pos[]=$Y; $Pos[]=$X; $Pos[]=$Y-$PlotSize; $Pos[]=$X+$PlotSize; $Pos[]=$Y; $Pos[]=$X; $Pos[]=$Y+$PlotSize;
       $this->drawPolygon($Pos,array("NoFill"=>TRUE,"BorderR"=>$R,"BorderG"=>$G,"BorderB"=>$B,"BorderAlpha"=>$Alpha));
      }      
     elseif ( $Shape == SERIE_SHAPE_FILLEDDIAMOND )
      {
       if ( $PlotBorder )
        {
         $Pos = ""; $Pos[]=$X-$PlotSize-$BorderSize; $Pos[]=$Y; $Pos[]=$X; $Pos[]=$Y-$PlotSize-$BorderSize; $Pos[]=$X+$PlotSize+$BorderSize; $Pos[]=$Y; $Pos[]=$X; $Pos[]=$Y+$PlotSize+$BorderSize;
         $this->drawPolygon($Pos,array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha));
        }

       $Pos = ""; $Pos[]=$X-$PlotSize; $Pos[]=$Y; $Pos[]=$X; $Pos[]=$Y-$PlotSize; $Pos[]=$X+$PlotSize; $Pos[]=$Y; $Pos[]=$X; $Pos[]=$Y+$PlotSize;
       $this->drawPolygon($Pos,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha));
      }      
    }

   function drawPolygonChart($Points,$Format="")
    {
     $R			= isset($Format["R"]) ? $Format["R"] : 0;
     $G			= isset($Format["G"]) ? $Format["G"] : 0;
     $B			= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $NoFill		= isset($Format["NoFill"]) ? $Format["NoFill"] : FALSE;
     $NoBorder		= isset($Format["NoBorder"]) ? $Format["NoBorder"] : FALSE;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : $R;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : $G;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : $B;
     $BorderAlpha 	= isset($Format["BorderAlpha"]) ? $Format["BorderAlpha"] : $Alpha / 2;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $Threshold         = isset($Format["Threshold"]) ? $Format["Threshold"] : NULL;

     if ( $Surrounding != NULL ) { $BorderR = $R+$Surrounding; $BorderG = $G+$Surrounding; $BorderB = $B+$Surrounding; }

     $RestoreShadow = $this->Shadow;
     $this->Shadow = FALSE;

     $AllIntegers = TRUE;
     for($i=0;$i<=count($Points)-2;$i=$i+2)
      { if ( $this->getFirstDecimal($Points[$i+1]) != 0 ) { $AllIntegers = FALSE; } }

     /* Convert polygon to segments */
     $Segments = "";
     for($i=2;$i<=count($Points)-2;$i=$i+2)
      { $Segments[] = array("X1"=>$Points[$i-2],"Y1"=>$Points[$i-1],"X2"=>$Points[$i],"Y2"=>$Points[$i+1]); }
     $Segments[] = array("X1"=>$Points[$i-2],"Y1"=>$Points[$i-1],"X2"=>$Points[0],"Y2"=>$Points[1]);

     /* Simplify straight lines */
     $Result = ""; $inHorizon = FALSE; $LastX = VOID;
     foreach($Segments as $Key => $Pos)
      {
       if ( $Pos["Y1"] != $Pos["Y2"] )
        {
         if ( $inHorizon ) { $inHorizon = FALSE; $Result[] = array("X1"=>$LastX,"Y1"=>$Pos["Y1"],"X2"=>$Pos["X1"],"Y2"=>$Pos["Y1"]); }

         $Result[] = array("X1"=>$Pos["X1"],"Y1"=>$Pos["Y1"],"X2"=>$Pos["X2"],"Y2"=>$Pos["Y2"]);
        }
       else { if ( !$inHorizon ) { $inHorizon = TRUE; $LastX = $Pos["X1"];} }
      }
     $Segments = $Result;

     /* Do we have something to draw */
     if ( $Segments == "" ) { return(0); }

     /* For segments debugging purpose */
     //foreach($Segments as $Key => $Pos)
     // echo $Pos["X1"].",".$Pos["Y1"].",".$Pos["X2"].",".$Pos["Y2"]."\r\n";

     /* Find out the min & max Y boundaries */
     $MinY = OUT_OF_SIGHT; $MaxY = OUT_OF_SIGHT;
     foreach($Segments as $Key => $Coords)
      {
       if ( $MinY == OUT_OF_SIGHT || $MinY > min($Coords["Y1"],$Coords["Y2"]) ) { $MinY = min($Coords["Y1"],$Coords["Y2"]); }
       if ( $MaxY == OUT_OF_SIGHT || $MaxY < max($Coords["Y1"],$Coords["Y2"]) ) { $MaxY = max($Coords["Y1"],$Coords["Y2"]); }
      }

     if ( $AllIntegers ) { $YStep = 1; } else { $YStep = .5; }

     $MinY = floor($MinY); $MaxY = floor($MaxY);

     /* Scan each Y lines */
     $DefaultColor = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
     $DebugLine = 0; $DebugColor = $this->allocateColor($this->Picture,255,0,0,100);

     $MinY = floor($MinY); $MaxY = floor($MaxY); $YStep = 1; 

     if ( !$NoFill )
      {
       //if ( $DebugLine ) { $MinY = $DebugLine; $MaxY = $DebugLine; }
       for($Y=$MinY;$Y<=$MaxY;$Y=$Y+$YStep)
        {
         $Intersections = ""; $LastSlope = NULL; $RestoreLast = "-";
         foreach($Segments as $Key => $Coords)
          {
           $X1 = $Coords["X1"]; $X2 = $Coords["X2"]; $Y1 = $Coords["Y1"]; $Y2 = $Coords["Y2"];

           if ( min($Y1,$Y2) <= $Y && max($Y1,$Y2) >= $Y )
            {
             if ( $Y1 == $Y2 )
              { $X = $X1; }
             else
              { $X = $X1 + ( ($Y-$Y1)*$X2 - ($Y-$Y1)*$X1 ) / ($Y2-$Y1); }

             $X = floor($X);

             if ( $X2 == $X1 )
              { $Slope = "!"; }
             else
              {
               $SlopeC = ($Y2 - $Y1) / ($X2 - $X1);
               if( $SlopeC == 0 )
                { $Slope = "="; }
               elseif( $SlopeC > 0 )
                { $Slope = "+"; }
               elseif ( $SlopeC < 0 )
                { $Slope = "-"; }
              }

             if ( !is_array($Intersections) )
              { $Intersections[] = $X; }
             elseif( !in_array($X,$Intersections) )
              { $Intersections[] = $X; }
             elseif( in_array($X,$Intersections) )
              {
               if ($Y == $DebugLine) { echo $Slope."/".$LastSlope."(".$X.") "; }

               if ( $Slope == "=" && $LastSlope == "-"  )                             { $Intersections[] = $X; }
               if ( $Slope != $LastSlope && $LastSlope != "!" && $LastSlope != "=" )  { $Intersections[] = $X; }
               if ( $Slope != $LastSlope && $LastSlope == "!" && $Slope == "+" )      { $Intersections[] = $X; }
              }

             if ( is_array($Intersections) && in_array($X,$Intersections) && $LastSlope == "=" && ($Slope == "-" )) { $Intersections[] = $X; }

             $LastSlope = $Slope;
            }
          }
         if ( $RestoreLast != "-" ) { $Intersections[] = $RestoreLast; echo "@".$Y."\r\n"; }

         if ( is_array($Intersections) )
          {
           sort($Intersections);

           if ($Y == $DebugLine) { print_r($Intersections); }

           /* Remove NULL plots */
           $Result = "";
           for($i=0;$i<=count($Intersections)-1;$i=$i+2)
            {
             if ( isset($Intersections[$i+1]) )
              { if ( $Intersections[$i] != $Intersections[$i+1] ) { $Result[] = $Intersections[$i]; $Result[] = $Intersections[$i+1]; } }
            }

           if ( is_array($Result) )
            {
             $Intersections = $Result;

             $LastX = OUT_OF_SIGHT;
             foreach($Intersections as $Key => $X)
              {
               if ( $LastX == OUT_OF_SIGHT )
                $LastX = $X;
               elseif ( $LastX != OUT_OF_SIGHT )
                {
                 if ( $this->getFirstDecimal($LastX) > 1 ) { $LastX++; }

                 $Color = $DefaultColor;
                 if ( $Threshold != NULL )
                  {
                   foreach($Threshold as $Key => $Parameters)
                    {
                     if ( $Y <= $Parameters["MinX"] && $Y >= $Parameters["MaxX"])
                      {
                       if ( isset($Parameters["R"]) ) { $R = $Parameters["R"]; } else { $R = 0; }
                       if ( isset($Parameters["G"]) ) { $G = $Parameters["G"]; } else { $G = 0; }
                       if ( isset($Parameters["B"]) ) { $B = $Parameters["B"]; } else { $B = 0; }
                       if ( isset($Parameters["Alpha"]) ) { $Alpha = $Parameters["Alpha"]; } else { $Alpha = 100; }
                       $Color = $this->allocateColor($this->Picture,$R,$G,$B,$Alpha);
                      }
                    }
                  }

                 imageline($this->Picture,$LastX,$Y,$X,$Y,$Color);

                 if ( $Y == $DebugLine) { imageline($this->Picture,$LastX,$Y,$X,$Y,$DebugColor); }

                 $LastX = OUT_OF_SIGHT;
                }
              }
            }
          }
        }
      }

     /* Draw the polygon border, if required */
     if ( !$NoBorder)
      {
       foreach($Segments as $Key => $Coords)
        $this->drawLine($Coords["X1"],$Coords["Y1"],$Coords["X2"],$Coords["Y2"],array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha,"Threshold"=>$Threshold));
      }

     $this->Shadow = $RestoreShadow;
    }

   /* Return the abscissa margin */
   function getAbscissaMargin($Data)
    {
     foreach($Data["Axis"] as $AxisID => $Values) { if ( $Values["Identity"] == AXIS_X ) { return($Values["Margin"]); } }
     return(0);
    }

  }
?>