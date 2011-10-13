<?php
 /*
     pChart - a PHP class to build charts!
     Copyright (C) 2008 Jean-Damien POGOLOTTI
     Version  1.27d last updated on 09/30/08

     http://pchart.sourceforge.net

     This program is free software: you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published by
     the Free Software Foundation, either version 1,2,3 of the License, or
     (at your option) any later version.

     This program is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with this program.  If not, see <http://www.gnu.org/licenses/>.

     Class initialisation :
      pChart($XSize,$YSize)
     Draw methods :
      drawBackground($R,$G,$B)
      drawRectangle($X1,$Y1,$X2,$Y2,$R,$G,$B)
      drawFilledRectangle($X1,$Y1,$X2,$Y2,$R,$G,$B,$DrawBorder=TRUE,$Alpha=100)
      drawRoundedRectangle($X1,$Y1,$X2,$Y2,$Radius,$R,$G,$B)
      drawFilledRoundedRectangle($X1,$Y1,$X2,$Y2,$Radius,$R,$G,$B)
      drawCircle($Xc,$Yc,$Height,$R,$G,$B,$Width=0)
      drawFilledCircle($Xc,$Yc,$Height,$R,$G,$B,$Width=0)
      drawEllipse($Xc,$Yc,$Height,$Width,$R,$G,$B)
      drawFilledEllipse($Xc,$Yc,$Height,$Width,$R,$G,$B)
      drawLine($X1,$Y1,$X2,$Y2,$R,$G,$B,$GraphFunction=FALSE)
      drawDottedLine($X1,$Y1,$X2,$Y2,$DotSize,$R,$G,$B)
      drawAlphaPixel($X,$Y,$Alpha,$R,$G,$B)
      drawFromPNG($FileName,$X,$Y,$Alpha=100)
      drawFromGIF($FileName,$X,$Y,$Alpha=100)
      drawFromJPG($FileName,$X,$Y,$Alpha=100)
     Graph setup methods :
      addBorder($Width=3,$R=0,$G=0,$B=0)
      clearScale()
      clearShadow()
      createColorGradientPalette($R1,$G1,$B1,$R2,$G2,$B2,$Shades)
      drawGraphArea($R,$G,$B,$Stripe=FALSE)
      drawScale($Data,$DataDescription,$ScaleMode,$R,$G,$B,$DrawTicks=TRUE,$Angle=0,$Decimals=1,$WithMargin=FALSE,$SkipLabels=1,$RightScale=FALSE)
      drawRightScale($Data,$DataDescription,$ScaleMode,$R,$G,$B,$DrawTicks=TRUE,$Angle=0,$Decimals=1,$WithMargin=FALSE,$SkipLabels=1)
      drawXYScale($Data,$DataDescription,$YSerieName,$XSerieName,$R,$G,$B,$WithMargin=0,$Angle=0,$Decimals=1)
      drawGrid($LineWidth,$Mosaic=TRUE,$R=220,$G=220,$B=220,$Alpha=100)
      drawLegend($XPos,$YPos,$DataDescription,$R,$G,$B,$Rs=-1,$Gs=-1,$Bs=-1,$Rt=0,$Gt=0,$Bt=0,$Border=FALSE)
      drawPieLegend($XPos,$YPos,$Data,$DataDescription,$R,$G,$B)
      drawTitle($XPos,$YPos,$Value,$R,$G,$B,$XPos2=-1,$YPos2=-1,$Shadow=FALSE)
      drawTreshold($Value,$R,$G,$B,$ShowLabel=FALSE,$ShowOnRight=FALSE,$TickWidth=4,$FreeText=NULL)
      drawArea($Data,$Serie1,$Serie2,$R,$G,$B,$Alpha = 50)
      drawRadarAxis($Data,$DataDescription,$Mosaic=TRUE,$BorderOffset=10,$A_R=60,$A_G=60,$A_B=60,$S_R=200,$S_G=200,$S_B=200,$MaxValue=-1)
      drawGraphAreaGradient($R,$G,$B,$Decay,$Target=TARGET_GRAPHAREA)
      drawTextBox($X1,$Y1,$X2,$Y2,$Text,$Angle=0,$R=255,$G=255,$B=255,$Align=ALIGN_LEFT,$Shadow=TRUE,$BgR=-1,$BgG=-1,$BgB=-1,$Alpha=100)
      getLegendBoxSize($DataDescription)
      loadColorPalette($FileName,$Delimiter=",")
      reportWarnings($Interface="CLI")
      setGraphArea($X1,$Y1,$X2,$Y2)
      setLabel($Data,$DataDescription,$SerieName,$ValueName,$Caption,$R=210,$G=210,$B=210)
      setColorPalette($ID,$R,$G,$B)
      setCurrency($Currency)
      setDateFormat($Format)
      setFontProperties($FontName,$FontSize)
      setLineStyle($Width=1,$DotSize=0)
      setFixedScale($VMin,$VMax,$Divisions=5,$VXMin=0,$VXMin=0,$XDivisions=5)
      setShadowProperties($XDistance=1,$YDistance=1,$R=60,$G=60,$B=60,$Alpha)
      writeValues($Data,$DataDescription,$Series)
    Graphs methods :
      drawPlotGraph($Data,$DataDescription,$BigRadius=5,$SmallRadius=2,$R2=-1,$G2=-1,$B2=-1,$Shadow=FALSE)
      drawXYPlotGraph($Data,$DataDescription,$YSerieName,$XSerieName,$PaletteID=0,$BigRadius=5,$SmallRadius=2,$R2=-1,$G2=-1,$B2=-1)
      drawLineGraph($Data,$DataDescription,$SerieName="")
      drawXYGraph($Data,$DataDescription,$YSerieName,$XSerieName,$PaletteID=0)
      drawFilledLineGraph($Data,$DataDescription,$Alpha=100,$AroundZero=FALSE)
      drawCubicCurve($Data,$DataDescription,$Accuracy=.1,$SerieName="")
      drawFilledCubicCurve($Data,$DataDescription,$Accuracy=.1,$Alpha=100,$AroundZero=FALSE)
      drawOverlayBarGraph($Data,$DataDescription,$Alpha=50)
      drawBarGraph($Data,$DataDescription,$Shadow=FALSE)
      drawStackedBarGraph($Data,$DataDescription,$Alpha=50,$Contiguous=FALSE)
      drawLimitsGraph($Data,$DataDescription,$R=0,$G=0,$B=0)
      drawRadar($Data,$DataDescription,$BorderOffset=10,$MaxValue=-1)
      drawFilledRadar($Data,$DataDescription,$Alpha=50,$BorderOffset=10,$MaxValue=-1)
      drawBasicPieGraph($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$R=255,$G=255,$B=255,$Decimals=0)
      drawFlatPieGraph($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$SpliceDistance=0,$Decimals = 0)
      drawFlatPieGraphWithShadow($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$SpliceDistance=0,$Decimals = 0)
      drawPieGraph($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$EnhanceColors=TRUE,$Skew=60,$SpliceHeight=20,$SpliceDistance=0,$Decimals=0)
     Other methods :
      setImageMap($Mode=TRUE,$GraphID="MyGraph")
      getImageMap($MapName,$Flush=TRUE)
      Render($FileName)
      Stroke()
 */
 
 /* Declare some script wide constants */
 define("SCALE_NORMAL",1);
 define("SCALE_ADDALL",2);
 define("SCALE_START0",3);
 define("SCALE_ADDALLSTART0",4);
 define("PIE_PERCENTAGE", 1);
 define("PIE_LABELS",2);
 define("PIE_NOLABEL",3);
 define("PIE_PERCENTAGE_LABEL", 4);
 define("TARGET_GRAPHAREA",1);
 define("TARGET_BACKGROUND",2);
 define("ALIGN_TOP_LEFT",1);
 define("ALIGN_TOP_CENTER",2);
 define("ALIGN_TOP_RIGHT",3);
 define("ALIGN_LEFT",4);
 define("ALIGN_CENTER",5);
 define("ALIGN_RIGHT",6);
 define("ALIGN_BOTTOM_LEFT",7);
 define("ALIGN_BOTTOM_CENTER",8);
 define("ALIGN_BOTTOM_RIGHT",9);

 /* pChart class definition */
 class pChart
  {
   /* Palettes definition */
   var $Palette = array("0"=>array("R"=>188,"G"=>224,"B"=>46),
                        "1"=>array("R"=>224,"G"=>100,"B"=>46),
                        "2"=>array("R"=>224,"G"=>214,"B"=>46),
                        "3"=>array("R"=>46,"G"=>151,"B"=>224),
                        "4"=>array("R"=>176,"G"=>46,"B"=>224),
                        "5"=>array("R"=>224,"G"=>46,"B"=>117),
                        "6"=>array("R"=>92,"G"=>224,"B"=>46),
                        "7"=>array("R"=>224,"G"=>176,"B"=>46));

   /* Some static vars used in the class */
   var $XSize          = NULL;
   var $YSize          = NULL;
   var $Picture        = NULL;
   var $ImageMap       = NULL;

   /* Error management */
   var $ErrorReporting = FALSE;
   var $ErrorInterface = "CLI";
   var $Errors         = NULL;
   var $ErrorFontName  = "Fonts/pf_arma_five.ttf";
   var $ErrorFontSize  = 6;

   /* vars related to the graphing area */
   var $GArea_X1        = NULL;
   var $GArea_Y1        = NULL;
   var $GArea_X2        = NULL;
   var $GArea_Y2        = NULL;
   var $GAreaXOffset    = NULL;
   var $VMax            = NULL;
   var $VMin            = NULL;
   var $VXMax           = NULL;
   var $VXMin           = NULL;
   var $Divisions       = NULL;
   var $XDivisions      = NULL;
   var $DivisionHeight  = NULL;
   var $XDivisionHeight = NULL;
   var $DivisionCount   = NULL;
   var $XDivisionCount  = NULL;
   var $DivisionRatio   = NULL;
   var $XDivisionRatio  = NULL;
   var $DivisionWidth   = NULL;
   var $DataCount       = NULL;
   var $Currency        = "\$";

   /* Text format related vars */
   var $FontName       = NULL;
   var $FontSize       = NULL;
   var $DateFormat     = "d/m/Y";

   /* Lines format related vars */
   var $LineWidth      = 1;
   var $LineDotSize    = 0;

   /* Layer related vars */
   var $Layers         = NULL;

   /* Set antialias quality : 0 is maximum, 100 minimum*/
   var $AntialiasQuality = 0;

   /* Shadow settings */
   var $ShadowActive    = FALSE;
   var $ShadowXDistance = 1;
   var $ShadowYDistance = 1;
   var $ShadowRColor    = 60;
   var $ShadowGColor    = 60;
   var $ShadowBColor    = 60;
   var $ShadowAlpha     = 50;
   var $ShadowBlur      = 0;

   /* Image Map settings */
   var $BuildMap         = FALSE;
   var $MapFunction      = NULL;
   var $tmpFolder        = "tmp/";
   var $MapID            = NULL;

   /* This function create the background picture */
   function pChart($XSize,$YSize)
    {
     $this->XSize   = $XSize;
     $this->YSize   = $YSize;
     $this->Picture = imagecreatetruecolor($XSize,$YSize);

     $C_White =$this->AllocateColor($this->Picture,255,255,255);
     imagefilledrectangle($this->Picture,0,0,$XSize,$YSize,$C_White);
     imagecolortransparent($this->Picture,$C_White);

     $this->setFontProperties("tahoma.ttf",8);
    }

  /* Set if warnings should be reported */
  function reportWarnings($Interface="CLI")
   {
    $this->ErrorReporting = TRUE;
    $this->ErrorInterface = $Interface;
    }

   /* Set the font properties */
   function setFontProperties($FontName,$FontSize)
    {
     $this->FontName = $FontName;
     $this->FontSize = $FontSize;
    }

   /* Set the shadow properties */
   function setShadowProperties($XDistance=1,$YDistance=1,$R=60,$G=60,$B=60,$Alpha=50,$Blur=0)
    {
     $this->ShadowActive    = TRUE;
     $this->ShadowXDistance = $XDistance;
     $this->ShadowYDistance = $YDistance;
     $this->ShadowRColor    = $R;
     $this->ShadowGColor    = $G;
     $this->ShadowBColor    = $B;
     $this->ShadowAlpha     = $Alpha;
     $this->ShadowBlur      = $Blur;
    }

   /* Remove shadow option */
   function clearShadow()
    {
     $this->ShadowActive = FALSE;
    }
 
   /* Set Palette color */
   function setColorPalette($ID,$R,$G,$B)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $this->Palette[$ID]["R"] = $R;
     $this->Palette[$ID]["G"] = $G;
     $this->Palette[$ID]["B"] = $B;
    }

   /* Create a color palette shading from one color to another */
   function createColorGradientPalette($R1,$G1,$B1,$R2,$G2,$B2,$Shades)
    {
     $RFactor = ($R2-$R1)/$Shades;
     $GFactor = ($G2-$G1)/$Shades;
     $BFactor = ($B2-$B1)/$Shades;

     for($i=0;$i<=$Shades-1;$i++)
      {
       $this->Palette[$i]["R"] = $R1+$RFactor*$i;
       $this->Palette[$i]["G"] = $G1+$GFactor*$i;
       $this->Palette[$i]["B"] = $B1+$BFactor*$i;
      }
    }

   /* Load Color Palette from file */
   function loadColorPalette($FileName,$Delimiter=",")
    {
     $handle  = @fopen($FileName,"r");
     $ColorID = 0;
     if ($handle)
      {
       while (!feof($handle))
        {
         $buffer = fgets($handle, 4096);
         $buffer = str_replace(chr(10),"",$buffer);
         $buffer = str_replace(chr(13),"",$buffer);
         $Values = split($Delimiter,$buffer);
         if ( count($Values) == 3 )
          {
           $this->Palette[$ColorID]["R"] = $Values[0];
           $this->Palette[$ColorID]["G"] = $Values[1];
           $this->Palette[$ColorID]["B"] = $Values[2];
           $ColorID++;
          }
        }
      }
    }

   /* Set line style */
  function setLineStyle($Width=1,$DotSize=0)
   {
    $this->LineWidth   = $Width;
    $this->LineDotSize = $DotSize;
   }

   /* Set currency symbol */
   function setCurrency($Currency)
    {
     $this->Currency = $Currency;
    }

   /* Set the graph area location */
   function setGraphArea($X1,$Y1,$X2,$Y2)
    {
     $this->GArea_X1 = $X1;
     $this->GArea_Y1 = $Y1;
     $this->GArea_X2 = $X2;
     $this->GArea_Y2 = $Y2;
    }

   /* Prepare the graph area */
   function drawGraphArea($R,$G,$B,$Stripe=FALSE)
    {
     $this->drawFilledRectangle($this->GArea_X1,$this->GArea_Y1,$this->GArea_X2,$this->GArea_Y2,$R,$G,$B,FALSE);
     $this->drawRectangle($this->GArea_X1,$this->GArea_Y1,$this->GArea_X2,$this->GArea_Y2,$R-40,$G-40,$B-40);

     if ( $Stripe )
      {
       $R2 = $R-15; if ( $R2 < 0 ) { $R2 = 0; }
       $G2 = $R-15; if ( $G2 < 0 ) { $G2 = 0; }
       $B2 = $R-15; if ( $B2 < 0 ) { $B2 = 0; }

       $LineColor =$this->AllocateColor($this->Picture,$R2,$G2,$B2);
       $SkewWidth = $this->GArea_Y2-$this->GArea_Y1-1;

       for($i=$this->GArea_X1-$SkewWidth;$i<=$this->GArea_X2;$i=$i+4)
        {
         $X1 = $i;            $Y1 = $this->GArea_Y2;
         $X2 = $i+$SkewWidth; $Y2 = $this->GArea_Y1;


         if ( $X1 < $this->GArea_X1 )
          { $X1 = $this->GArea_X1; $Y1 = $this->GArea_Y1 + $X2 - $this->GArea_X1 + 1; }

         if ( $X2 >= $this->GArea_X2 )
          { $Y2 = $this->GArea_Y1 + $X2 - $this->GArea_X2 +1; $X2 = $this->GArea_X2 - 1; }
// * Fixed in 1.27 *         { $X2 = $this->GArea_X2 - 1; $Y2 = $this->GArea_Y2 - ($this->GArea_X2 - $X1); }

         imageline($this->Picture,$X1,$Y1,$X2,$Y2+1,$LineColor);
        }
      }
    }

   /* Allow you to clear the scale : used if drawing multiple charts */
   function clearScale()
    {
     $this->VMin       = NULL;
     $this->VMax       = NULL;
     $this->VXMin      = NULL;
     $this->VXMax      = NULL;
     $this->Divisions  = NULL;
     $this->XDivisions = NULL;    }

   /* Allow you to fix the scale, use this to bypass the automatic scaling */
   function setFixedScale($VMin,$VMax,$Divisions=5,$VXMin=0,$VXMax=0,$XDivisions=5)
    {
     $this->VMin      = $VMin;
     $this->VMax      = $VMax;
     $this->Divisions = $Divisions;

     if ( !$VXMin == 0 )
      {
       $this->VXMin      = $VXMin;
       $this->VXMax      = $VXMax;
       $this->XDivisions = $XDivisions;
      }
    }

   /* Wrapper to the drawScale() function allowing a second scale to be drawn */
   function drawRightScale($Data,$DataDescription,$ScaleMode,$R,$G,$B,$DrawTicks=TRUE,$Angle=0,$Decimals=1,$WithMargin=FALSE,$SkipLabels=1)
    {
     $this->drawScale($Data,$DataDescription,$ScaleMode,$R,$G,$B,$DrawTicks,$Angle,$Decimals,$WithMargin,$SkipLabels,TRUE);
    }

   /* Compute and draw the scale */
   function drawScale($Data,$DataDescription,$ScaleMode,$R,$G,$B,$DrawTicks=TRUE,$Angle=0,$Decimals=1,$WithMargin=FALSE,$SkipLabels=1,$RightScale=FALSE)
    {
     /* Validate the Data and DataDescription array */
     $this->validateData("drawScale",$Data);

     $C_TextColor         =$this->AllocateColor($this->Picture,$R,$G,$B);

     $this->drawLine($this->GArea_X1,$this->GArea_Y1,$this->GArea_X1,$this->GArea_Y2,$R,$G,$B);
     $this->drawLine($this->GArea_X1,$this->GArea_Y2,$this->GArea_X2,$this->GArea_Y2,$R,$G,$B);

     if ( $this->VMin == NULL && $this->VMax == NULL)
      {
       if (isset($DataDescription["Values"][0]))
        {
         $this->VMin = $Data[0][$DataDescription["Values"][0]];
         $this->VMax = $Data[0][$DataDescription["Values"][0]];
        }
       else { $this->VMin = 2147483647; $this->VMax = -2147483647; }

       /* Compute Min and Max values */
       if ( $ScaleMode == SCALE_NORMAL || $ScaleMode == SCALE_START0 )
        {
         if ( $ScaleMode == SCALE_START0 ) { $this->VMin = 0; }

         foreach ( $Data as $Key => $Values )
          {
           foreach ( $DataDescription["Values"] as $Key2 => $ColName )
            {
             if (isset($Data[$Key][$ColName]))
              {
               $Value = $Data[$Key][$ColName];

               if ( is_numeric($Value) )
                {
                 if ( $this->VMax < $Value) { $this->VMax = $Value; }
                 if ( $this->VMin > $Value) { $this->VMin = $Value; }
                }
              }
            }
          }
        }
       elseif ( $ScaleMode == SCALE_ADDALL || $ScaleMode == SCALE_ADDALLSTART0 ) /* Experimental */
        {
         if ( $ScaleMode == SCALE_ADDALLSTART0 ) { $this->VMin = 0; }

         foreach ( $Data as $Key => $Values )
          {
           $Sum = 0;
           foreach ( $DataDescription["Values"] as $Key2 => $ColName )
            {
             if (isset($Data[$Key][$ColName]))
              {
               $Value = $Data[$Key][$ColName];
               if ( is_numeric($Value) )
                $Sum  += $Value;
              }
            }
           if ( $this->VMax < $Sum) { $this->VMax = $Sum; }
           if ( $this->VMin > $Sum) { $this->VMin = $Sum; }
          }
        }

       if ( $this->VMax > preg_replace('/\.[0-9]+/','',$this->VMax) )
        $this->VMax = preg_replace('/\.[0-9]+/','',$this->VMax)+1;

       /* If all values are the same */
       if ( $this->VMax == $this->VMin )
        {
         if ( $this->VMax >= 0 ) { $this->VMax++; }
         else { $this->VMin--; }
        }

       $DataRange = $this->VMax - $this->VMin;
       if ( $DataRange == 0 ) { $DataRange = .1; }

       /* Compute automatic scaling */
       $ScaleOk = FALSE; $Factor = 1;
       $MinDivHeight = 25; $MaxDivs = ($this->GArea_Y2 - $this->GArea_Y1) / $MinDivHeight;

       if ( $this->VMin == 0 && $this->VMax == 0 )
        { $this->VMin = 0; $this->VMax = 2; $Scale = 1; $Divisions = 2;}
       elseif ($MaxDivs > 1)
        {
         while(!$ScaleOk)
          {
           $Scale1 = ( $this->VMax - $this->VMin ) / $Factor;
           $Scale2 = ( $this->VMax - $this->VMin ) / $Factor / 2;
           $Scale4 = ( $this->VMax - $this->VMin ) / $Factor / 4;

           if ( $Scale1 > 1 && $Scale1 <= $MaxDivs && !$ScaleOk) { $ScaleOk = TRUE; $Divisions = floor($Scale1); $Scale = 1;}
           if ( $Scale2 > 1 && $Scale2 <= $MaxDivs && !$ScaleOk) { $ScaleOk = TRUE; $Divisions = floor($Scale2); $Scale = 2;}
           if (!$ScaleOk)
            {
             if ( $Scale2 > 1 ) { $Factor = $Factor * 10; }
             if ( $Scale2 < 1 ) { $Factor = $Factor / 10; }
            }
          }

         if ( floor($this->VMax / $Scale / $Factor) != $this->VMax / $Scale / $Factor)
          {
           $GridID     = floor ( $this->VMax / $Scale / $Factor) + 1;
           $this->VMax = $GridID * $Scale * $Factor;
           $Divisions++;
          }

         if ( floor($this->VMin / $Scale / $Factor) != $this->VMin / $Scale / $Factor)
          {
           $GridID     = floor( $this->VMin / $Scale / $Factor);
           $this->VMin = $GridID * $Scale * $Factor;
           $Divisions++;
          }
        }
       else /* Can occurs for small graphs */
        $Scale = 1;

       if ( !isset($Divisions) )
        $Divisions = 2;

       if ($Scale == 1 && $Divisions%2 == 1)
        $Divisions--;
      }
     else
      $Divisions = $this->Divisions;

     $this->DivisionCount = $Divisions;

     $DataRange = $this->VMax - $this->VMin;
     if ( $DataRange == 0 ) { $DataRange = .1; }

     $this->DivisionHeight = ( $this->GArea_Y2 - $this->GArea_Y1 ) / $Divisions;
     $this->DivisionRatio  = ( $this->GArea_Y2 - $this->GArea_Y1 ) / $DataRange;

     $this->GAreaXOffset  = 0;
     if ( count($Data) > 1 )
      {
       if ( $WithMargin == FALSE )
        $this->DivisionWidth = ( $this->GArea_X2 - $this->GArea_X1 ) / (count($Data)-1);
       else
        {
         $this->DivisionWidth = ( $this->GArea_X2 - $this->GArea_X1 ) / (count($Data));
         $this->GAreaXOffset  = $this->DivisionWidth / 2;
        }
      }
     else
      {
       $this->DivisionWidth = $this->GArea_X2 - $this->GArea_X1;
       $this->GAreaXOffset  = $this->DivisionWidth / 2;
      }

     $this->DataCount = count($Data);

     if ( $DrawTicks == FALSE )
      return(0);

     $YPos = $this->GArea_Y2; $XMin = NULL;
     for($i=1;$i<=$Divisions+1;$i++)
      {
       if ( $RightScale )
        $this->drawLine($this->GArea_X2,$YPos,$this->GArea_X2+5,$YPos,$R,$G,$B);
       else
        $this->drawLine($this->GArea_X1,$YPos,$this->GArea_X1-5,$YPos,$R,$G,$B);

       $Value     = $this->VMin + ($i-1) * (( $this->VMax - $this->VMin ) / $Divisions);
       $Value     = round($Value * pow(10,$Decimals)) / pow(10,$Decimals);
       if ( $DataDescription["Format"]["Y"] == "number" )
        $Value = $Value.$DataDescription["Unit"]["Y"];
       if ( $DataDescription["Format"]["Y"] == "time" )
        $Value = $this->ToTime($Value);        
       if ( $DataDescription["Format"]["Y"] == "date" )
        $Value = $this->ToDate($Value);        
       if ( $DataDescription["Format"]["Y"] == "metric" )
        $Value = $this->ToMetric($Value);        
       if ( $DataDescription["Format"]["Y"] == "currency" )
        $Value = $this->ToCurrency($Value);        

       $Position  = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextWidth = $Position[2]-$Position[0];

       if ( $RightScale )
        {
         imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X2+10,$YPos+($this->FontSize/2),$C_TextColor,$this->FontName,$Value);
         if ( $XMin < $this->GArea_X2+15+$TextWidth || $XMin == NULL ) { $XMin = $this->GArea_X2+15+$TextWidth; }
        }
       else
        {
         imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X1-10-$TextWidth,$YPos+($this->FontSize/2),$C_TextColor,$this->FontName,$Value);
         if ( $XMin > $this->GArea_X1-10-$TextWidth || $XMin == NULL ) { $XMin = $this->GArea_X1-10-$TextWidth; }
        }

       $YPos = $YPos - $this->DivisionHeight;
      }

     /* Write the Y Axis caption if set */ 
     if ( isset($DataDescription["Axis"]["Y"]) )
      {
       $Position   = imageftbbox($this->FontSize,90,$this->FontName,$DataDescription["Axis"]["Y"]);
       $TextHeight = abs($Position[1])+abs($Position[3]);
       $TextTop    = (($this->GArea_Y2 - $this->GArea_Y1) / 2) + $this->GArea_Y1 + ($TextHeight/2);

       if ( $RightScale )
        imagettftext($this->Picture,$this->FontSize,90,$XMin+$this->FontSize,$TextTop,$C_TextColor,$this->FontName,$DataDescription["Axis"]["Y"]);
       else
        imagettftext($this->Picture,$this->FontSize,90,$XMin-$this->FontSize,$TextTop,$C_TextColor,$this->FontName,$DataDescription["Axis"]["Y"]);
      }

     /* Horizontal Axis */
     $XPos = $this->GArea_X1 + $this->GAreaXOffset;
     $ID = 1; $YMax = NULL;
     foreach ( $Data as $Key => $Values )
      {
       if ( $ID % $SkipLabels == 0 )
        {
         $this->drawLine(floor($XPos),$this->GArea_Y2,floor($XPos),$this->GArea_Y2+5,$R,$G,$B);
         $Value      = $Data[$Key][$DataDescription["Position"]];
         if ( $DataDescription["Format"]["X"] == "number" )
          $Value = $Value.$DataDescription["Unit"]["X"];
         if ( $DataDescription["Format"]["X"] == "time" )
          $Value = $this->ToTime($Value);        
         if ( $DataDescription["Format"]["X"] == "date" )
          $Value = $this->ToDate($Value);        
         if ( $DataDescription["Format"]["X"] == "metric" )
          $Value = $this->ToMetric($Value);        
         if ( $DataDescription["Format"]["X"] == "currency" )
          $Value = $this->ToCurrency($Value);        

         $Position   = imageftbbox($this->FontSize,$Angle,$this->FontName,$Value);
         $TextWidth  = abs($Position[2])+abs($Position[0]);
         $TextHeight = abs($Position[1])+abs($Position[3]);

         if ( $Angle == 0 )
          {
           $YPos = $this->GArea_Y2+18;
           imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)-floor($TextWidth/2),$YPos,$C_TextColor,$this->FontName,$Value);
          }
         else
          {
           $YPos = $this->GArea_Y2+10+$TextHeight;
           if ( $Angle <= 90 )
            imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)-$TextWidth+5,$YPos,$C_TextColor,$this->FontName,$Value);
           else
            imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)+$TextWidth+5,$YPos,$C_TextColor,$this->FontName,$Value);
          }
         if ( $YMax < $YPos || $YMax == NULL ) { $YMax = $YPos; }
        }

       $XPos = $XPos + $this->DivisionWidth;
       $ID++;
      }

    /* Write the X Axis caption if set */ 
    if ( isset($DataDescription["Axis"]["X"]) )
      {
       $Position   = imageftbbox($this->FontSize,90,$this->FontName,$DataDescription["Axis"]["X"]);
       $TextWidth  = abs($Position[2])+abs($Position[0]);
       $TextLeft   = (($this->GArea_X2 - $this->GArea_X1) / 2) + $this->GArea_X1 + ($TextWidth/2);
       imagettftext($this->Picture,$this->FontSize,0,$TextLeft,$YMax+$this->FontSize+5,$C_TextColor,$this->FontName,$DataDescription["Axis"]["X"]);
      }
    }

   /* Compute and draw the scale for X/Y charts */
   function drawXYScale($Data,$DataDescription,$YSerieName,$XSerieName,$R,$G,$B,$WithMargin=0,$Angle=0,$Decimals=1)
    {
     /* Validate the Data and DataDescription array */
     $this->validateData("drawScale",$Data);

     $C_TextColor =$this->AllocateColor($this->Picture,$R,$G,$B);

     $this->drawLine($this->GArea_X1,$this->GArea_Y1,$this->GArea_X1,$this->GArea_Y2,$R,$G,$B);
     $this->drawLine($this->GArea_X1,$this->GArea_Y2,$this->GArea_X2,$this->GArea_Y2,$R,$G,$B);

     /* Process Y scale */
     if ( $this->VMin == NULL && $this->VMax == NULL)
      {
       $this->VMin = $Data[0][$YSerieName];
       $this->VMax = $Data[0][$YSerieName];

       foreach ( $Data as $Key => $Values )
        {
         if (isset($Data[$Key][$YSerieName]))
          {
           $Value = $Data[$Key][$YSerieName];
           if ( $this->VMax < $Value) { $this->VMax = $Value; }
           if ( $this->VMin > $Value) { $this->VMin = $Value; }
          }
        }

       if ( $this->VMax > preg_replace('/\.[0-9]+/','',$this->VMax) )
        $this->VMax = preg_replace('/\.[0-9]+/','',$this->VMax)+1;

       $DataRange = $this->VMax - $this->VMin;
       if ( $DataRange == 0 ) { $DataRange = .1; }

       /* Compute automatic scaling */
       $ScaleOk = FALSE; $Factor = 1;
       $MinDivHeight = 25; $MaxDivs = ($this->GArea_Y2 - $this->GArea_Y1) / $MinDivHeight;

       if ( $this->VMin == 0 && $this->VMax == 0 )
        { $this->VMin = 0; $this->VMax = 2; $Scale = 1; $Divisions = 2;}
       elseif ($MaxDivs > 1)
        {
         while(!$ScaleOk)
          {
           $Scale1 = ( $this->VMax - $this->VMin ) / $Factor;
           $Scale2 = ( $this->VMax - $this->VMin ) / $Factor / 2;
           $Scale4 = ( $this->VMax - $this->VMin ) / $Factor / 4;

           if ( $Scale1 > 1 && $Scale1 <= $MaxDivs && !$ScaleOk) { $ScaleOk = TRUE; $Divisions = floor($Scale1); $Scale = 1;}
           if ( $Scale2 > 1 && $Scale2 <= $MaxDivs && !$ScaleOk) { $ScaleOk = TRUE; $Divisions = floor($Scale2); $Scale = 2;}
           if (!$ScaleOk)
            {
             if ( $Scale2 > 1 ) { $Factor = $Factor * 10; }
             if ( $Scale2 < 1 ) { $Factor = $Factor / 10; }
            }
          }

         if ( floor($this->VMax / $Scale / $Factor) != $this->VMax / $Scale / $Factor)
          {
           $GridID     = floor ( $this->VMax / $Scale / $Factor) + 1;
           $this->VMax = $GridID * $Scale * $Factor;
           $Divisions++;
          }

         if ( floor($this->VMin / $Scale / $Factor) != $this->VMin / $Scale / $Factor)
          {
           $GridID     = floor( $this->VMin / $Scale / $Factor);
           $this->VMin = $GridID * $Scale * $Factor;
           $Divisions++;
          }
        }
       else /* Can occurs for small graphs */
        $Scale = 1;

       if ( !isset($Divisions) )
        $Divisions = 2;

       if ( $this->isRealInt(($this->VMax-$this->VMin)/($Divisions-1)))
        $Divisions--;
       elseif ( $this->isRealInt(($this->VMax-$this->VMin)/($Divisions+1)))
        $Divisions++;
      }
     else
      $Divisions = $this->Divisions;

     $this->DivisionCount = $Divisions;

     $DataRange = $this->VMax - $this->VMin;
     if ( $DataRange == 0 ) { $DataRange = .1; }

     $this->DivisionHeight = ( $this->GArea_Y2 - $this->GArea_Y1 ) / $Divisions;
     $this->DivisionRatio  = ( $this->GArea_Y2 - $this->GArea_Y1 ) / $DataRange;

     $YPos = $this->GArea_Y2; $XMin = NULL;
     for($i=1;$i<=$Divisions+1;$i++)
      {
       $this->drawLine($this->GArea_X1,$YPos,$this->GArea_X1-5,$YPos,$R,$G,$B);
       $Value     = $this->VMin + ($i-1) * (( $this->VMax - $this->VMin ) / $Divisions);
       $Value     = round($Value * pow(10,$Decimals)) / pow(10,$Decimals);
       if ( $DataDescription["Format"]["Y"] == "number" )
        $Value = $Value.$DataDescription["Unit"]["Y"];
       if ( $DataDescription["Format"]["Y"] == "time" )
        $Value = $this->ToTime($Value);        
       if ( $DataDescription["Format"]["Y"] == "date" )
        $Value = $this->ToDate($Value);        
       if ( $DataDescription["Format"]["Y"] == "metric" )
        $Value = $this->ToMetric($Value);        
       if ( $DataDescription["Format"]["Y"] == "currency" )
        $Value = $this->ToCurrency($Value);        

       $Position  = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextWidth = $Position[2]-$Position[0];
       imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X1-10-$TextWidth,$YPos+($this->FontSize/2),$C_TextColor,$this->FontName,$Value);

       if ( $XMin > $this->GArea_X1-10-$TextWidth || $XMin == NULL ) { $XMin = $this->GArea_X1-10-$TextWidth; }

       $YPos = $YPos - $this->DivisionHeight;
      }

     /* Process X scale */
     if ( $this->VXMin == NULL && $this->VXMax == NULL)
      {
       $this->VXMin = $Data[0][$XSerieName];
       $this->VXMax = $Data[0][$XSerieName];

       foreach ( $Data as $Key => $Values )
        {
         if (isset($Data[$Key][$XSerieName]))
          {
           $Value = $Data[$Key][$XSerieName];
           if ( $this->VXMax < $Value) { $this->VXMax = $Value; }
           if ( $this->VXMin > $Value) { $this->VXMin = $Value; }
          }
        }

       if ( $this->VXMax > preg_replace('/\.[0-9]+/','',$this->VXMax) )
        $this->VXMax = preg_replace('/\.[0-9]+/','',$this->VXMax)+1;

       $DataRange = $this->VMax - $this->VMin;
       if ( $DataRange == 0 ) { $DataRange = .1; }

       /* Compute automatic scaling */
       $ScaleOk = FALSE; $Factor = 1;
       $MinDivWidth = 25; $MaxDivs = ($this->GArea_X2 - $this->GArea_X1) / $MinDivWidth;

       if ( $this->VXMin == 0 && $this->VXMax == 0 )
        { $this->VXMin = 0; $this->VXMax = 2; $Scale = 1; $XDivisions = 2;}
       elseif ($MaxDivs > 1)
        {
         while(!$ScaleOk)
          {
           $Scale1 = ( $this->VXMax - $this->VXMin ) / $Factor;
           $Scale2 = ( $this->VXMax - $this->VXMin ) / $Factor / 2;
           $Scale4 = ( $this->VXMax - $this->VXMin ) / $Factor / 4;

           if ( $Scale1 > 1 && $Scale1 <= $MaxDivs && !$ScaleOk) { $ScaleOk = TRUE; $XDivisions = floor($Scale1); $Scale = 1;}
           if ( $Scale2 > 1 && $Scale2 <= $MaxDivs && !$ScaleOk) { $ScaleOk = TRUE; $XDivisions = floor($Scale2); $Scale = 2;}
           if (!$ScaleOk)
            {
             if ( $Scale2 > 1 ) { $Factor = $Factor * 10; }
             if ( $Scale2 < 1 ) { $Factor = $Factor / 10; }
            }
          }

         if ( floor($this->VXMax / $Scale / $Factor) != $this->VXMax / $Scale / $Factor)
          {
           $GridID     = floor ( $this->VXMax / $Scale / $Factor) + 1;
           $this->VXMax = $GridID * $Scale * $Factor;
           $XDivisions++;
          }

         if ( floor($this->VXMin / $Scale / $Factor) != $this->VXMin / $Scale / $Factor)
          {
           $GridID     = floor( $this->VXMin / $Scale / $Factor);
           $this->VXMin = $GridID * $Scale * $Factor;
           $XDivisions++;
          }
        }
       else /* Can occurs for small graphs */
        $Scale = 1;

       if ( !isset($XDivisions) )
        $XDivisions = 2;

       if ( $this->isRealInt(($this->VXMax-$this->VXMin)/($XDivisions-1)))
        $XDivisions--;
       elseif ( $this->isRealInt(($this->VXMax-$this->VXMin)/($XDivisions+1)))
        $XDivisions++;
      }
     else
      $XDivisions = $this->XDivisions;

     $this->XDivisionCount = $Divisions;
     $this->DataCount      = $Divisions + 2;

     $XDataRange = $this->VXMax - $this->VXMin;
     if ( $XDataRange == 0 ) { $XDataRange = .1; }

     $this->DivisionWidth   = ( $this->GArea_X2 - $this->GArea_X1 ) / $XDivisions;
     $this->XDivisionRatio  = ( $this->GArea_X2 - $this->GArea_X1 ) / $XDataRange;

     $XPos = $this->GArea_X1; $YMax = NULL;
     for($i=1;$i<=$XDivisions+1;$i++)
      {
       $this->drawLine($XPos,$this->GArea_Y2,$XPos,$this->GArea_Y2+5,$R,$G,$B);

       $Value     = $this->VXMin + ($i-1) * (( $this->VXMax - $this->VXMin ) / $XDivisions);
       $Value     = round($Value * pow(10,$Decimals)) / pow(10,$Decimals);
       if ( $DataDescription["Format"]["Y"] == "number" )
        $Value = $Value.$DataDescription["Unit"]["Y"];
       if ( $DataDescription["Format"]["Y"] == "time" )
        $Value = $this->ToTime($Value);        
       if ( $DataDescription["Format"]["Y"] == "date" )
        $Value = $this->ToDate($Value);        
       if ( $DataDescription["Format"]["Y"] == "metric" )
        $Value = $this->ToMetric($Value);        
       if ( $DataDescription["Format"]["Y"] == "currency" )
        $Value = $this->ToCurrency($Value);        

       $Position   = imageftbbox($this->FontSize,$Angle,$this->FontName,$Value);
       $TextWidth  = abs($Position[2])+abs($Position[0]);
       $TextHeight = abs($Position[1])+abs($Position[3]);

       if ( $Angle == 0 )
        {
         $YPos = $this->GArea_Y2+18;
         imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)-floor($TextWidth/2),$YPos,$C_TextColor,$this->FontName,$Value);
        }
       else
        {
         $YPos = $this->GArea_Y2+10+$TextHeight;
         if ( $Angle <= 90 )
          imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)-$TextWidth+5,$YPos,$C_TextColor,$this->FontName,$Value);
         else
          imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)+$TextWidth+5,$YPos,$C_TextColor,$this->FontName,$Value);
         }

       if ( $YMax < $YPos || $YMax == NULL ) { $YMax = $YPos; }

       $XPos = $XPos + $this->DivisionWidth;
      }

     /* Write the Y Axis caption if set */ 
     if ( isset($DataDescription["Axis"]["Y"]) )
      {
       $Position   = imageftbbox($this->FontSize,90,$this->FontName,$DataDescription["Axis"]["Y"]);
       $TextHeight = abs($Position[1])+abs($Position[3]);
       $TextTop    = (($this->GArea_Y2 - $this->GArea_Y1) / 2) + $this->GArea_Y1 + ($TextHeight/2);
       imagettftext($this->Picture,$this->FontSize,90,$XMin-$this->FontSize,$TextTop,$C_TextColor,$this->FontName,$DataDescription["Axis"]["Y"]);
      }

     /* Write the X Axis caption if set */ 
     if ( isset($DataDescription["Axis"]["X"]) )
      {
       $Position   = imageftbbox($this->FontSize,90,$this->FontName,$DataDescription["Axis"]["X"]);
       $TextWidth  = abs($Position[2])+abs($Position[0]);
       $TextLeft   = (($this->GArea_X2 - $this->GArea_X1) / 2) + $this->GArea_X1 + ($TextWidth/2);
       imagettftext($this->Picture,$this->FontSize,0,$TextLeft,$YMax+$this->FontSize+5,$C_TextColor,$this->FontName,$DataDescription["Axis"]["X"]);
      }
    }

   /* Compute and draw the scale */
   function drawGrid($LineWidth,$Mosaic=TRUE,$R=220,$G=220,$B=220,$Alpha=100)
    {
     /* Draw mosaic */
     if ( $Mosaic )
      {
       $LayerWidth  = $this->GArea_X2-$this->GArea_X1;
       $LayerHeight = $this->GArea_Y2-$this->GArea_Y1;

       $this->Layers[0] = imagecreatetruecolor($LayerWidth,$LayerHeight);
       $C_White         =$this->AllocateColor($this->Layers[0],255,255,255);
       imagefilledrectangle($this->Layers[0],0,0,$LayerWidth,$LayerHeight,$C_White);
       imagecolortransparent($this->Layers[0],$C_White);

       $C_Rectangle =$this->AllocateColor($this->Layers[0],250,250,250);

       $YPos  = $LayerHeight; //$this->GArea_Y2-1;
       $LastY = $YPos;
       for($i=0;$i<=$this->DivisionCount;$i++)
        {
         $LastY = $YPos;
         $YPos  = $YPos - $this->DivisionHeight;

         if ( $YPos <= 0 ) { $YPos = 1; }

         if ( $i % 2 == 0 )
          {
           imagefilledrectangle($this->Layers[0],1,$YPos,$LayerWidth-1,$LastY,$C_Rectangle);
          }
        }
       imagecopymerge($this->Picture,$this->Layers[0],$this->GArea_X1,$this->GArea_Y1,0,0,$LayerWidth,$LayerHeight,$Alpha);
       imagedestroy($this->Layers[0]);
      }

     /* Horizontal lines */
     $YPos = $this->GArea_Y2 - $this->DivisionHeight;
     for($i=1;$i<=$this->DivisionCount;$i++)
      {
       if ( $YPos > $this->GArea_Y1 && $YPos < $this->GArea_Y2 )
        $this->drawDottedLine($this->GArea_X1,$YPos,$this->GArea_X2,$YPos,$LineWidth,$R,$G,$B);
        
       $YPos = $YPos - $this->DivisionHeight;
      }

     /* Vertical lines */
     if ( $this->GAreaXOffset == 0 )
      { $XPos = $this->GArea_X1 + $this->DivisionWidth + $this->GAreaXOffset; $ColCount = $this->DataCount-2; }
     else
      { $XPos = $this->GArea_X1 + $this->GAreaXOffset; $ColCount = floor( ($this->GArea_X2 - $this->GArea_X1) / $this->DivisionWidth ); }

     for($i=1;$i<=$ColCount;$i++)
      {
       if ( $XPos > $this->GArea_X1 && $XPos < $this->GArea_X2 )
        $this->drawDottedLine(floor($XPos),$this->GArea_Y1,floor($XPos),$this->GArea_Y2,$LineWidth,$R,$G,$B);
       $XPos = $XPos + $this->DivisionWidth;
      }
    }

   /* retrieve the legends size */
   function getLegendBoxSize($DataDescription)
    {
     if ( !isset($DataDescription["Description"]) )
      return(-1);

     /* <-10->[8]<-4->Text<-10-> */
     $MaxWidth = 0; $MaxHeight = 8;
     foreach($DataDescription["Description"] as $Key => $Value)
      {
       $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextWidth  = $Position[2]-$Position[0];
       $TextHeight = $Position[1]-$Position[7];
       if ( $TextWidth > $MaxWidth) { $MaxWidth = $TextWidth; }
       $MaxHeight = $MaxHeight + $TextHeight + 4;
      }
     $MaxHeight = $MaxHeight - 3;
     $MaxWidth  = $MaxWidth + 32;

     return(array($MaxWidth,$MaxHeight));
    }

   /* Draw the data legends */
   function drawLegend($XPos,$YPos,$DataDescription,$R,$G,$B,$Rs=-1,$Gs=-1,$Bs=-1,$Rt=0,$Gt=0,$Bt=0,$Border=TRUE)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawLegend",$DataDescription);

     if ( !isset($DataDescription["Description"]) )
      return(-1);

     $C_TextColor =$this->AllocateColor($this->Picture,$Rt,$Gt,$Bt);

     /* <-10->[8]<-4->Text<-10-> */
     $MaxWidth = 0; $MaxHeight = 8;
     foreach($DataDescription["Description"] as $Key => $Value)
      {
       $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextWidth  = $Position[2]-$Position[0];
       $TextHeight = $Position[1]-$Position[7];
       if ( $TextWidth > $MaxWidth) { $MaxWidth = $TextWidth; }
       $MaxHeight = $MaxHeight + $TextHeight + 4;
      }
     $MaxHeight = $MaxHeight - 5;
     $MaxWidth  = $MaxWidth + 32;

     if ( $Rs == -1 || $Gs == -1 || $Bs == -1 )
      { $Rs = $R-30; $Gs = $G-30; $Bs = $B-30; }

     if ( $Border )
      {
       $this->drawFilledRoundedRectangle($XPos+1,$YPos+1,$XPos+$MaxWidth+1,$YPos+$MaxHeight+1,5,$Rs,$Gs,$Bs);
       $this->drawFilledRoundedRectangle($XPos,$YPos,$XPos+$MaxWidth,$YPos+$MaxHeight,5,$R,$G,$B);
      }

     $YOffset = 4 + $this->FontSize; $ID = 0;
     foreach($DataDescription["Description"] as $Key => $Value)
      {
       $this->drawFilledRoundedRectangle($XPos+10,$YPos+$YOffset-4,$XPos+14,$YPos+$YOffset-4,2,$this->Palette[$ID]["R"],$this->Palette[$ID]["G"],$this->Palette[$ID]["B"]);
       imagettftext($this->Picture,$this->FontSize,0,$XPos+22,$YPos+$YOffset,$C_TextColor,$this->FontName,$Value);

       $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextHeight = $Position[1]-$Position[7];

       $YOffset = $YOffset + $TextHeight + 4;
       $ID++;
      }
    }

   /* Draw the data legends */
   function drawPieLegend($XPos,$YPos,$Data,$DataDescription,$R,$G,$B)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawPieLegend",$DataDescription,FALSE);
     $this->validateData("drawPieLegend",$Data);

     if ( !isset($DataDescription["Position"]) )
      return(-1);

     $C_TextColor =$this->AllocateColor($this->Picture,0,0,0);

     /* <-10->[8]<-4->Text<-10-> */
     $MaxWidth = 0; $MaxHeight = 8;
     foreach($Data as $Key => $Value)
      {
       $Value2 = $Value[$DataDescription["Position"]];
       $Position  = imageftbbox($this->FontSize,0,$this->FontName,$Value2);
       $TextWidth = $Position[2]-$Position[0];
       $TextHeight = $Position[1]-$Position[7];
       if ( $TextWidth > $MaxWidth) { $MaxWidth = $TextWidth; }

       $MaxHeight = $MaxHeight + $TextHeight + 4;
      }
     $MaxHeight = $MaxHeight - 3;
     $MaxWidth  = $MaxWidth + 32;

     $this->drawFilledRoundedRectangle($XPos+1,$YPos+1,$XPos+$MaxWidth+1,$YPos+$MaxHeight+1,5,$R-30,$G-30,$B-30);
     $this->drawFilledRoundedRectangle($XPos,$YPos,$XPos+$MaxWidth,$YPos+$MaxHeight,5,$R,$G,$B);

     $YOffset = 4 + $this->FontSize; $ID = 0;
     foreach($Data as $Key => $Value)
      {
       $Value2     = $Value[$DataDescription["Position"]];
       $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Value2);
       $TextHeight = $Position[1]-$Position[7];
       $this->drawFilledRectangle($XPos+10,$YPos+$YOffset-6,$XPos+14,$YPos+$YOffset-2,$this->Palette[$ID]["R"],$this->Palette[$ID]["G"],$this->Palette[$ID]["B"]);

       imagettftext($this->Picture,$this->FontSize,0,$XPos+22,$YPos+$YOffset,$C_TextColor,$this->FontName,$Value2);
       $YOffset = $YOffset + $TextHeight + 4;
       $ID++;
      }
    }

   /* Draw the graph title */
   function drawTitle($XPos,$YPos,$Value,$R,$G,$B,$XPos2=-1,$YPos2=-1,$Shadow=FALSE)
    {
     $C_TextColor = $this->AllocateColor($this->Picture,$R,$G,$B);

     if ( $XPos2 != -1 )
      {
       $Position  = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextWidth = $Position[2]-$Position[0];
       $XPos      = floor(( $XPos2 - $XPos - $TextWidth ) / 2 ) + $XPos;
      }

     if ( $YPos2 != -1 )
      {
       $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextHeight = $Position[5]-$Position[3];
       $YPos       = floor(( $YPos2 - $YPos - $TextHeight ) / 2 ) + $YPos;
      }

     if ( $Shadow )
      {
       $C_ShadowColor = $this->AllocateColor($this->Picture,$this->ShadowRColor,$this->ShadowGColor,$this->ShadowBColor);
       imagettftext($this->Picture,$this->FontSize,0,$XPos+$this->ShadowXDistance,$YPos+$this->ShadowYDistance,$C_ShadowColor,$this->FontName,$Value);     
      }

     imagettftext($this->Picture,$this->FontSize,0,$XPos,$YPos,$C_TextColor,$this->FontName,$Value);     
    }

   /* Draw a text box with text align & alpha properties */
   function drawTextBox($X1,$Y1,$X2,$Y2,$Text,$Angle=0,$R=255,$G=255,$B=255,$Align=ALIGN_LEFT,$Shadow=TRUE,$BgR=-1,$BgG=-1,$BgB=-1,$Alpha=100)
    {
     $Position   = imageftbbox($this->FontSize,$Angle,$this->FontName,$Text);
     $TextWidth  = $Position[2]-$Position[0];
     $TextHeight = $Position[5]-$Position[3];
     $AreaWidth  = $X2 - $X1;
     $AreaHeight = $Y2 - $Y1;

     if ( $BgR != -1 && $BgG != -1 && $BgB != -1 )
      $this->drawFilledRectangle($X1,$Y1,$X2,$Y2,$BgR,$BgG,$BgB,FALSE,$Alpha);

     if ( $Align == ALIGN_TOP_LEFT )      { $X = $X1+1; $Y = $Y1+$this->FontSize+1; }
     if ( $Align == ALIGN_TOP_CENTER )    { $X = $X1+($AreaWidth/2)-($TextWidth/2); $Y = $Y1+$this->FontSize+1; }
     if ( $Align == ALIGN_TOP_RIGHT )     { $X = $X2-$TextWidth-1; $Y = $Y1+$this->FontSize+1; }
     if ( $Align == ALIGN_LEFT )          { $X = $X1+1; $Y = $Y1+($AreaHeight/2)-($TextHeight/2); }
     if ( $Align == ALIGN_CENTER )        { $X = $X1+($AreaWidth/2)-($TextWidth/2); $Y = $Y1+($AreaHeight/2)-($TextHeight/2); }
     if ( $Align == ALIGN_RIGHT )         { $X = $X2-$TextWidth-1; $Y = $Y1+($AreaHeight/2)-($TextHeight/2); }
     if ( $Align == ALIGN_BOTTOM_LEFT )   { $X = $X1+1; $Y = $Y2-1; }
     if ( $Align == ALIGN_BOTTOM_CENTER ) { $X = $X1+($AreaWidth/2)-($TextWidth/2); $Y = $Y2-1; }
     if ( $Align == ALIGN_BOTTOM_RIGHT )  { $X = $X2-$TextWidth-1; $Y = $Y2-1; }

     $C_TextColor   =$this->AllocateColor($this->Picture,$R,$G,$B);
     $C_ShadowColor =$this->AllocateColor($this->Picture,0,0,0);
     if ( $Shadow )
      imagettftext($this->Picture,$this->FontSize,$Angle,$X+1,$Y+1,$C_ShadowColor,$this->FontName,$Text);     

     imagettftext($this->Picture,$this->FontSize,$Angle,$X,$Y,$C_TextColor,$this->FontName,$Text);     
    }

   /* Compute and draw the scale */
   function drawTreshold($Value,$R,$G,$B,$ShowLabel=FALSE,$ShowOnRight=FALSE,$TickWidth=4,$FreeText=NULL)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_TextColor =$this->AllocateColor($this->Picture,$R,$G,$B);
     $Y = $this->GArea_Y2 - ($Value - $this->VMin) * $this->DivisionRatio;

     if ( $Y <= $this->GArea_Y1 || $Y >= $this->GArea_Y2 )
      return(-1);

     if ( $TickWidth == 0 )
      $this->drawLine($this->GArea_X1,$Y,$this->GArea_X2,$Y,$R,$G,$B);
     else
      $this->drawDottedLine($this->GArea_X1,$Y,$this->GArea_X2,$Y,$TickWidth,$R,$G,$B);

     if ( $ShowLabel )
      {
       if ( $FreeText == NULL )
        { $Label = $Value; } else { $Label = $FreeText; }

       if ( $ShowOnRight )
        imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X2+2,$Y+($this->FontSize/2),$C_TextColor,$this->FontName,$Label);
       else
        imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X1+2,$Y-($this->FontSize/2),$C_TextColor,$this->FontName,$Label);
      }
    }

   /* This function put a label on a specific point */
   function setLabel($Data,$DataDescription,$SerieName,$ValueName,$Caption,$R=210,$G=210,$B=210)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("setLabel",$DataDescription);
     $this->validateData("setLabel",$Data);
     $ShadowFactor = 100;
     $C_Label      =$this->AllocateColor($this->Picture,$R,$G,$B);
     $C_Shadow     =$this->AllocateColor($this->Picture,$R-$ShadowFactor,$G-$ShadowFactor,$B-$ShadowFactor);
     $C_TextColor  =$this->AllocateColor($this->Picture,0,0,0);

     $Cp = 0; $Found = FALSE;
     foreach ( $Data as $Key => $Value )
      {
       if ( $Data[$Key][$DataDescription["Position"]] == $ValueName )
        { $NumericalValue = $Data[$Key][$SerieName]; $Found = TRUE; }
       if ( !$Found )
        $Cp++;
      }

     $XPos = $this->GArea_X1 + $this->GAreaXOffset + ( $this->DivisionWidth * $Cp ) + 2;
     $YPos = $this->GArea_Y2 - ($NumericalValue - $this->VMin) * $this->DivisionRatio;

     $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Caption);
     $TextHeight = $Position[3] - $Position[5];
     $TextWidth  = $Position[2]-$Position[0] + 2;
     $TextOffset = floor($TextHeight/2);

     // Shadow
     $Poly = array($XPos+1,$YPos+1,$XPos + 9,$YPos - $TextOffset,$XPos + 8,$YPos + $TextOffset + 2);
     imagefilledpolygon($this->Picture,$Poly,3,$C_Shadow);
     $this->drawLine($XPos,$YPos+1,$XPos + 9,$YPos - $TextOffset - .2,$R-$ShadowFactor,$G-$ShadowFactor,$B-$ShadowFactor);
     $this->drawLine($XPos,$YPos+1,$XPos + 9,$YPos + $TextOffset + 2.2,$R-$ShadowFactor,$G-$ShadowFactor,$B-$ShadowFactor);
     $this->drawFilledRectangle($XPos + 9,$YPos - $TextOffset-.2,$XPos + 13 + $TextWidth,$YPos + $TextOffset + 2.2,$R-$ShadowFactor,$G-$ShadowFactor,$B-$ShadowFactor);

     // Label background
     $Poly = array($XPos,$YPos,$XPos + 8,$YPos - $TextOffset - 1,$XPos + 8,$YPos + $TextOffset + 1);
     imagefilledpolygon($this->Picture,$Poly,3,$C_Label);
     $this->drawLine($XPos-1,$YPos,$XPos + 8,$YPos - $TextOffset - 1.2,$R,$G,$B);
     $this->drawLine($XPos-1,$YPos,$XPos + 8,$YPos + $TextOffset + 1.2,$R,$G,$B);
     $this->drawFilledRectangle($XPos + 8,$YPos - $TextOffset - 1.2,$XPos + 12 + $TextWidth,$YPos + $TextOffset + 1.2,$R,$G,$B);

     imagettftext($this->Picture,$this->FontSize,0,$XPos + 10,$YPos + $TextOffset,$C_TextColor,$this->FontName,$Caption);
    }

   /* This function draw a plot graph */
   function drawPlotGraph($Data,$DataDescription,$BigRadius=5,$SmallRadius=2,$R2=-1,$G2=-1,$B2=-1,$Shadow=FALSE)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawPlotGraph",$DataDescription);
     $this->validateData("drawPlotGraph",$Data);

     $GraphID = 0;
     $Ro = $R2; $Go = $G2; $Bo = $B2;

     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $R = $this->Palette[$ColorID]["R"];
       $G = $this->Palette[$ColorID]["G"];
       $B = $this->Palette[$ColorID]["B"];
       $R2 = $Ro; $G2 = $Go; $B2 = $Bo;

       if ( isset($DataDescription["Symbol"][$ColName]) )
        {
         $Is_Alpha = ((ord ( file_get_contents ($DataDescription["Symbol"][$ColName], false, null, 25, 1)) & 6) & 4) == 4;

         $Infos       = getimagesize($DataDescription["Symbol"][$ColName]);
         $ImageWidth  = $Infos[0];
         $ImageHeight = $Infos[1];
         $Symbol      = imagecreatefromgif($DataDescription["Symbol"][$ColName]);
        }

       $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
       $Hsize = round($BigRadius/2);
       $R3 = -1; $G3 = -1; $B3 = -1;
       foreach ( $Data as $Key => $Values )
        {
         $Value = $Data[$Key][$ColName];
         $YPos  = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

         /* Save point into the image map if option activated */
         if ( $this->BuildMap )
          $this->addToImageMap($XPos-$Hsize,$YPos-$Hsize,$XPos+1+$Hsize,$YPos+$Hsize+1,$DataDescription["Description"][$ColName],$Data[$Key][$ColName].$DataDescription["Unit"]["Y"],"Plot");

         if ( is_numeric($Value) )
          {
           if ( !isset($DataDescription["Symbol"][$ColName]) )
            {

             if ( $Shadow )
              {
               if ( $R3 !=-1 && $G3 !=-1 && $B3 !=-1 )
                $this->drawFilledCircle($XPos+2,$YPos+2,$BigRadius,$R3,$G3,$B3);
               else
                {
                 $R3 = $this->Palette[$ColorID]["R"]-20; if ( $R3 < 0 ) { $R3 = 0; }
                 $G3 = $this->Palette[$ColorID]["G"]-20; if ( $G3 < 0 ) { $G3 = 0; }
                 $B3 = $this->Palette[$ColorID]["B"]-20; if ( $B3 < 0 ) { $B3 = 0; }
                 $this->drawFilledCircle($XPos+2,$YPos+2,$BigRadius,$R3,$G3,$B3);
                }
              }

             $this->drawFilledCircle($XPos+1,$YPos+1,$BigRadius,$R,$G,$B);

             if ( $SmallRadius != 0 )
              {
               if ( $R2 !=-1 && $G2 !=-1 && $B2 !=-1 )
                $this->drawFilledCircle($XPos+1,$YPos+1,$SmallRadius,$R2,$G2,$B2);
               else
                {
                 $R2 = $this->Palette[$ColorID]["R"]-15; if ( $R2 < 0 ) { $R2 = 0; }
                 $G2 = $this->Palette[$ColorID]["G"]-15; if ( $G2 < 0 ) { $G2 = 0; }
                 $B2 = $this->Palette[$ColorID]["B"]-15; if ( $B2 < 0 ) { $B2 = 0; }

                 $this->drawFilledCircle($XPos+1,$YPos+1,$SmallRadius,$R2,$G2,$B2);
                }
              }
            }
           else
            {
             imagecopymerge($this->Picture,$Symbol,$XPos+1-$ImageWidth/2,$YPos+1-$ImageHeight/2,0,0,$ImageWidth,$ImageHeight,100);
            }
          }

         $XPos = $XPos + $this->DivisionWidth;
        }
       $GraphID++;
      }
    }

   /* This function draw a plot graph in an X/Y space */
   function drawXYPlotGraph($Data,$DataDescription,$YSerieName,$XSerieName,$PaletteID=0,$BigRadius=5,$SmallRadius=2,$R2=-1,$G2=-1,$B2=-1,$Shadow=TRUE)
    {
     $R = $this->Palette[$PaletteID]["R"];
     $G = $this->Palette[$PaletteID]["G"];
     $B = $this->Palette[$PaletteID]["B"];
     $R3 = -1; $G3 = -1; $B3 = -1;

     $YLast = -1; $XLast = -1;
     foreach ( $Data as $Key => $Values )
      {
       if ( isset($Data[$Key][$YSerieName]) && isset($Data[$Key][$XSerieName]) )
        {
         $X = $Data[$Key][$XSerieName];
         $Y = $Data[$Key][$YSerieName];

         $Y = $this->GArea_Y2 - (($Y-$this->VMin) * $this->DivisionRatio);
         $X = $this->GArea_X1 + (($X-$this->VXMin) * $this->XDivisionRatio);


         if ( $Shadow )
          {
           if ( $R3 !=-1 && $G3 !=-1 && $B3 !=-1 )
            $this->drawFilledCircle($X+2,$Y+2,$BigRadius,$R3,$G3,$B3);
           else
            {
             $R3 = $this->Palette[$PaletteID]["R"]-20; if ( $R < 0 ) { $R = 0; }
             $G3 = $this->Palette[$PaletteID]["G"]-20; if ( $G < 0 ) { $G = 0; }
             $B3 = $this->Palette[$PaletteID]["B"]-20; if ( $B < 0 ) { $B = 0; }
             $this->drawFilledCircle($X+2,$Y+2,$BigRadius,$R3,$G3,$B3);
            }
          }

         $this->drawFilledCircle($X+1,$Y+1,$BigRadius,$R,$G,$B);

         if ( $R2 !=-1 && $G2 !=-1 && $B2 !=-1 )
          $this->drawFilledCircle($X+1,$Y+1,$SmallRadius,$R2,$G2,$B2);
         else
          {
           $R2 = $this->Palette[$PaletteID]["R"]+20; if ( $R > 255 ) { $R = 255; }
           $G2 = $this->Palette[$PaletteID]["G"]+20; if ( $G > 255 ) { $G = 255; }
           $B2 = $this->Palette[$PaletteID]["B"]+20; if ( $B > 255 ) { $B = 255; }
           $this->drawFilledCircle($X+1,$Y+1,$SmallRadius,$R2,$G2,$B2);
          }
        }
      }

    }

   /* This function draw an area between two series */
   function drawArea($Data,$Serie1,$Serie2,$R,$G,$B,$Alpha = 50)
    {
     /* Validate the Data and DataDescription array */
     $this->validateData("drawArea",$Data);

     $LayerWidth  = $this->GArea_X2-$this->GArea_X1;
     $LayerHeight = $this->GArea_Y2-$this->GArea_Y1;

     $this->Layers[0] = imagecreatetruecolor($LayerWidth,$LayerHeight);
     $C_White         =$this->AllocateColor($this->Layers[0],255,255,255);
     imagefilledrectangle($this->Layers[0],0,0,$LayerWidth,$LayerHeight,$C_White);
     imagecolortransparent($this->Layers[0],$C_White);

     $C_Graph =$this->AllocateColor($this->Layers[0],$R,$G,$B);

     $XPos     = $this->GAreaXOffset;
     $LastXPos = -1;
     foreach ( $Data as $Key => $Values )
      {
       $Value1 = $Data[$Key][$Serie1];
       $Value2 = $Data[$Key][$Serie2];
       $YPos1  = $LayerHeight - (($Value1-$this->VMin) * $this->DivisionRatio);
       $YPos2  = $LayerHeight - (($Value2-$this->VMin) * $this->DivisionRatio);

       if ( $LastXPos != -1 )
        {
         $Points   = "";
         $Points[] = $LastXPos; $Points[] = $LastYPos1;
         $Points[] = $LastXPos; $Points[] = $LastYPos2;
         $Points[] = $XPos; $Points[] = $YPos2;
         $Points[] = $XPos; $Points[] = $YPos1;

         imagefilledpolygon($this->Layers[0],$Points,4,$C_Graph);
        }

       $LastYPos1 = $YPos1;
       $LastYPos2 = $YPos2;
       $LastXPos  = $XPos;

       $XPos = $XPos + $this->DivisionWidth;
      }

     imagecopymerge($this->Picture,$this->Layers[0],$this->GArea_X1,$this->GArea_Y1,0,0,$LayerWidth,$LayerHeight,$Alpha);
     imagedestroy($this->Layers[0]);
    }


   /* This function write the values of the specified series */
   function writeValues($Data,$DataDescription,$Series)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("writeValues",$DataDescription);
     $this->validateData("writeValues",$Data);

     if ( !is_array($Series) ) { $Series = array($Series); }     

     foreach($Series as $Key => $Serie)
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $Serie ) { $ColorID = $ID; }; $ID++; }

       $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
       $XLast = -1;
       foreach ( $Data as $Key => $Values )
        {
         if ( isset($Data[$Key][$Serie]) && is_numeric($Data[$Key][$Serie]))
          {
           $Value = $Data[$Key][$Serie];
           $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

           $Positions = imagettfbbox($this->FontSize,0,$this->FontName,$Value);
           $Width  = $Positions[2] - $Positions[6]; $XOffset = $XPos - ($Width/2); 
           $Height = $Positions[3] - $Positions[7]; $YOffset = $YPos - 4;

           $C_TextColor =$this->AllocateColor($this->Picture,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
           imagettftext($this->Picture,$this->FontSize,0,$XOffset,$YOffset,$C_TextColor,$this->FontName,$Value);
          }
         $XPos = $XPos + $this->DivisionWidth;
        }

      }
    }

   /* This function draw a line graph */
   function drawLineGraph($Data,$DataDescription,$SerieName="")
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawLineGraph",$DataDescription);
     $this->validateData("drawLineGraph",$Data);

     $GraphID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       if ( $SerieName == "" || $SerieName == $ColName )
        {
         $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
         $XLast = -1;
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]))
            {
             $Value = $Data[$Key][$ColName];
             $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

             /* Save point into the image map if option activated */
             if ( $this->BuildMap )
              $this->addToImageMap($XPos-3,$YPos-3,$XPos+3,$YPos+3,$DataDescription["Description"][$ColName],$Data[$Key][$ColName].$DataDescription["Unit"]["Y"],"Line");

             if (!is_numeric($Value)) { $XLast = -1; }
             if ( $XLast != -1 )
              $this->drawLine($XLast,$YLast,$XPos,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE);

             $XLast = $XPos;
             $YLast = $YPos;
             if (!is_numeric($Value)) { $XLast = -1; }
            }
           $XPos = $XPos + $this->DivisionWidth;
          }
         $GraphID++;
        }
      }
    }

   /* This function draw a line graph */
   function drawXYGraph($Data,$DataDescription,$YSerieName,$XSerieName,$PaletteID=0)
    {
     $YLast = -1; $XLast = -1;
     foreach ( $Data as $Key => $Values )
      {
       if ( isset($Data[$Key][$YSerieName]) && isset($Data[$Key][$XSerieName]) )
        {
         $X = $Data[$Key][$XSerieName];
         $Y = $Data[$Key][$YSerieName];

         $Y = $this->GArea_Y2 - (($Y-$this->VMin) * $this->DivisionRatio);
         $X = $this->GArea_X1 + (($X-$this->VXMin) * $this->XDivisionRatio);

         if ($XLast != -1 && $YLast != -1)
          {
           $this->drawLine($XLast,$YLast,$X,$Y,$this->Palette[$PaletteID]["R"],$this->Palette[$PaletteID]["G"],$this->Palette[$PaletteID]["B"],TRUE);
          }

         $XLast = $X;
         $YLast = $Y;
        }
      }
    }

   /* This function draw a cubic curve */
   function drawCubicCurve($Data,$DataDescription,$Accuracy=.1,$SerieName="")
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawCubicCurve",$DataDescription);
     $this->validateData("drawCubicCurve",$Data);

     $GraphID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       if ( $SerieName == "" || $SerieName == $ColName )
        {
         $XIn = ""; $Yin = ""; $Yt = ""; $U = "";
         $XIn[0] = 0; $YIn[0] = 0;

         $ID = 0;
         foreach ( $DataDescription["Description"] as $keyI => $ValueI )
          { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

         $Index = 1;
         $XLast = -1; $Missing = "";
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]) )
            {
             $Value = $Data[$Key][$ColName];
             $XIn[$Index] = $Index;
             $YIn[$Index] = $Value;
             if ( !is_numeric($Value) ) { $Missing[$Index] = TRUE; }
             $Index++;
            }
          }
         $Index--;

         $Yt[0] = 0;
         $Yt[1] = 0;
         $U[1]  = 0;
         for($i=2;$i<=$Index-1;$i++)
          {
           $Sig    = ($XIn[$i] - $XIn[$i-1]) / ($XIn[$i+1] - $XIn[$i-1]);
           $p      = $Sig * $Yt[$i-1] + 2;
           $Yt[$i] = ($Sig - 1) / $p;
           $U[$i]  = ($YIn[$i+1] - $YIn[$i]) / ($XIn[$i+1] - $XIn[$i]) - ($YIn[$i] - $YIn[$i-1]) / ($XIn[$i] - $XIn[$i-1]);
           $U[$i]  = (6 * $U[$i] / ($XIn[$i+1] - $XIn[$i-1]) - $Sig * $U[$i-1]) / $p;
          }

         $qn = 0;
         $un = 0;
         $Yt[$Index] = ($un - $qn * $U[$Index-1]) / ($qn * $Yt[$Index-1] + 1);

         for($k=$Index-1;$k>=1;$k--)
          $Yt[$k] = $Yt[$k] * $Yt[$k+1] + $U[$k];

         $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
         for($X=1;$X<=$Index;$X=$X+$Accuracy)
          {
           $klo = 1;
           $khi = $Index;
           $k   = $khi - $klo;
           while($k > 1)
            {
             $k = $khi - $klo;
             If ( $XIn[$k] >= $X )
              $khi = $k;
             else
              $klo = $k;
            }
           $klo = $khi - 1;

           $h     = $XIn[$khi] - $XIn[$klo];
           $a     = ($XIn[$khi] - $X) / $h;
           $b     = ($X - $XIn[$klo]) / $h;
           $Value = $a * $YIn[$klo] + $b * $YIn[$khi] + (($a*$a*$a - $a) * $Yt[$klo] + ($b*$b*$b - $b) * $Yt[$khi]) * ($h*$h) / 6;

           $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

           if ( $XLast != -1 && !isset($Missing[floor($X)]) && !isset($Missing[floor($X+1)]) )
            $this->drawLine($XLast,$YLast,$XPos,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE);

           $XLast = $XPos;
           $YLast = $YPos;
           $XPos  = $XPos + $this->DivisionWidth * $Accuracy;
          }

         // Add potentialy missing values
         $XPos  = $XPos - $this->DivisionWidth * $Accuracy;
         if ( $XPos < ($this->GArea_X2 - $this->GAreaXOffset) )
          {
           $YPos = $this->GArea_Y2 - (($YIn[$Index]-$this->VMin) * $this->DivisionRatio);
           $this->drawLine($XLast,$YLast,$this->GArea_X2-$this->GAreaXOffset,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE);
          }

         $GraphID++;
        }
      }
    }

   /* This function draw a filled cubic curve */
   function drawFilledCubicCurve($Data,$DataDescription,$Accuracy=.1,$Alpha=100,$AroundZero=FALSE)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawFilledCubicCurve",$DataDescription);
     $this->validateData("drawFilledCubicCurve",$Data);

     $LayerWidth  = $this->GArea_X2-$this->GArea_X1;
     $LayerHeight = $this->GArea_Y2-$this->GArea_Y1;
     $YZero = $LayerHeight - ((0-$this->VMin) * $this->DivisionRatio);
     if ( $YZero > $LayerHeight ) { $YZero = $LayerHeight; }

     $GraphID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $XIn = ""; $Yin = ""; $Yt = ""; $U = "";
       $XIn[0] = 0; $YIn[0] = 0;

       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $Index = 1;
       $XLast = -1; $Missing = "";
       foreach ( $Data as $Key => $Values )
        {
         $Value = $Data[$Key][$ColName];
         $XIn[$Index] = $Index;
         $YIn[$Index] = $Value;
         if ( !is_numeric($Value) ) { $Missing[$Index] = TRUE; }
         $Index++;
        }
       $Index--;
 
       $Yt[0] = 0;
       $Yt[1] = 0;
       $U[1]  = 0;
       for($i=2;$i<=$Index-1;$i++)
        {
         $Sig    = ($XIn[$i] - $XIn[$i-1]) / ($XIn[$i+1] - $XIn[$i-1]);
         $p      = $Sig * $Yt[$i-1] + 2;
         $Yt[$i] = ($Sig - 1) / $p;
         $U[$i]  = ($YIn[$i+1] - $YIn[$i]) / ($XIn[$i+1] - $XIn[$i]) - ($YIn[$i] - $YIn[$i-1]) / ($XIn[$i] - $XIn[$i-1]);
         $U[$i]  = (6 * $U[$i] / ($XIn[$i+1] - $XIn[$i-1]) - $Sig * $U[$i-1]) / $p;
        }

       $qn = 0;
       $un = 0;
       $Yt[$Index] = ($un - $qn * $U[$Index-1]) / ($qn * $Yt[$Index-1] + 1);

       for($k=$Index-1;$k>=1;$k--)
        $Yt[$k] = $Yt[$k] * $Yt[$k+1] + $U[$k];

       $Points   = "";
       $Points[] = $this->GAreaXOffset;
       $Points[] = $LayerHeight;

       $this->Layers[0] = imagecreatetruecolor($LayerWidth,$LayerHeight);
       $C_White         =$this->AllocateColor($this->Layers[0],255,255,255);
       imagefilledrectangle($this->Layers[0],0,0,$LayerWidth,$LayerHeight,$C_White);
       imagecolortransparent($this->Layers[0],$C_White);

       $YLast = NULL;
       $XPos  = $this->GAreaXOffset; $PointsCount = 2;
       for($X=1;$X<=$Index;$X=$X+$Accuracy)
        {
         $klo = 1;
         $khi = $Index;
         $k   = $khi - $klo;
         while($k > 1)
          {
           $k = $khi - $klo;
           If ( $XIn[$k] >= $X )
            $khi = $k;
           else
            $klo = $k;
          }
         $klo = $khi - 1;

         $h     = $XIn[$khi] - $XIn[$klo];
         $a     = ($XIn[$khi] - $X) / $h;
         $b     = ($X - $XIn[$klo]) / $h;
         $Value = $a * $YIn[$klo] + $b * $YIn[$khi] + (($a*$a*$a - $a) * $Yt[$klo] + ($b*$b*$b - $b) * $Yt[$khi]) * ($h*$h) / 6;

         $YPos = $LayerHeight - (($Value-$this->VMin) * $this->DivisionRatio);

         if ( $YLast != NULL && $AroundZero && !isset($Missing[floor($X)]) && !isset($Missing[floor($X+1)]))
          {
           $aPoints   = "";
           $aPoints[] = $XLast;
           $aPoints[] = $YLast;
           $aPoints[] = $XPos;
           $aPoints[] = $YPos;
           $aPoints[] = $XPos;
           $aPoints[] = $YZero;
           $aPoints[] = $XLast;
           $aPoints[] = $YZero;

           $C_Graph =$this->AllocateColor($this->Layers[0],$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
           imagefilledpolygon($this->Layers[0],$aPoints,4,$C_Graph);
          }

         if ( !isset($Missing[floor($X)]) || $YLast == NULL )
          {
           $PointsCount++;
           $Points[] = $XPos;
           $Points[] = $YPos;
          }
         else
          {
           $PointsCount++; $Points[] = $XLast; $Points[] = $LayerHeight;
          }

         $YLast = $YPos; $XLast = $XPos; 
         $XPos  = $XPos + $this->DivisionWidth * $Accuracy;
        }

       // Add potentialy missing values
       $XPos  = $XPos - $this->DivisionWidth * $Accuracy;
       if ( $XPos < ($LayerWidth-$this->GAreaXOffset) )
        {
         $YPos = $LayerHeight - (($YIn[$Index]-$this->VMin) * $this->DivisionRatio);

         if ( $YLast != NULL && $AroundZero )
          {
           $aPoints   = "";
           $aPoints[] = $XLast;
           $aPoints[] = $YLast;
           $aPoints[] = $LayerWidth-$this->GAreaXOffset;
           $aPoints[] = $YPos;
           $aPoints[] = $LayerWidth-$this->GAreaXOffset;
           $aPoints[] = $YZero;
           $aPoints[] = $XLast;
           $aPoints[] = $YZero;

           $C_Graph =$this->AllocateColor($this->Layers[0],$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
           imagefilledpolygon($this->Layers[0],$aPoints,4,$C_Graph);
          }

         if ( $YIn[$klo] != "" && $YIn[$khi] != "" || $YLast == NULL )
          {
           $PointsCount++;
           $Points[] = $LayerWidth-$this->GAreaXOffset;
           $Points[] = $YPos;
          }
        }

       $Points[] = $LayerWidth-$this->GAreaXOffset;
       $Points[] = $LayerHeight;

       if ( !$AroundZero )
        {
         $C_Graph =$this->AllocateColor($this->Layers[0],$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
         imagefilledpolygon($this->Layers[0],$Points,$PointsCount,$C_Graph);
        }

       imagecopymerge($this->Picture,$this->Layers[0],$this->GArea_X1,$this->GArea_Y1,0,0,$LayerWidth,$LayerHeight,$Alpha);
       imagedestroy($this->Layers[0]);

       $this->drawCubicCurve($Data,$DataDescription,$Accuracy,$ColName);

       $GraphID++;
      }
    }

   /* This function draw a filled line graph */
   function drawFilledLineGraph($Data,$DataDescription,$Alpha=100,$AroundZero=FALSE)
    {
     $Empty = -2147483647;

     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawFilledLineGraph",$DataDescription);
     $this->validateData("drawFilledLineGraph",$Data);

     $LayerWidth  = $this->GArea_X2-$this->GArea_X1;
     $LayerHeight = $this->GArea_Y2-$this->GArea_Y1;

     $GraphID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $aPoints   = "";
       $aPoints[] = $this->GAreaXOffset;
       $aPoints[] = $LayerHeight;

       $this->Layers[0] = imagecreatetruecolor($LayerWidth,$LayerHeight);
       $C_White         = $this->AllocateColor($this->Layers[0],255,255,255);
       imagefilledrectangle($this->Layers[0],0,0,$LayerWidth,$LayerHeight,$C_White);
       imagecolortransparent($this->Layers[0],$C_White);

       $XPos  = $this->GAreaXOffset;
       $XLast = -1; $PointsCount = 2;
       $YZero = $LayerHeight - ((0-$this->VMin) * $this->DivisionRatio);
       if ( $YZero > $LayerHeight ) { $YZero = $LayerHeight; }

       $YLast = $Empty;
       foreach ( $Data as $Key => $Values )
        {
         $Value = $Data[$Key][$ColName];
         $YPos = $LayerHeight - (($Value-$this->VMin) * $this->DivisionRatio);

         /* Save point into the image map if option activated */
         if ( $this->BuildMap )
          $this->addToImageMap($XPos-3,$YPos-3,$XPos+3,$YPos+3,$DataDescription["Description"][$ColName],$Data[$Key][$ColName].$DataDescription["Unit"]["Y"],"FLine");

         if ( !is_numeric($Value) )
          {
           $PointsCount++;
           $aPoints[] = $XLast;
           $aPoints[] = $LayerHeight;

           $YLast = $Empty;
          }
         else
          {
           $PointsCount++;
           if ( $YLast <> $Empty )
            { $aPoints[] = $XPos; $aPoints[] = $YPos; }
           else
            { $PointsCount++; $aPoints[] = $XPos; $aPoints[] = $LayerHeight; $aPoints[] = $XPos; $aPoints[] = $YPos; }

           if ($YLast <> $Empty && $AroundZero)
            {
             $Points   = "";
             $Points[] = $XLast; $Points[] = $YLast;
             $Points[] = $XPos;
             $Points[] = $YPos;
             $Points[] = $XPos;
             $Points[] = $YZero;
             $Points[] = $XLast;
             $Points[] = $YZero;

             $C_Graph = $this->AllocateColor($this->Layers[0],$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
             imagefilledpolygon($this->Layers[0],$Points,4,$C_Graph);
            }
           $YLast = $YPos;
          }

         $XLast = $XPos;
         $XPos  = $XPos + $this->DivisionWidth;
        }
       $aPoints[] = $LayerWidth - $this->GAreaXOffset;
       $aPoints[] = $LayerHeight;

       if ( $AroundZero == FALSE )
        {
         $C_Graph = $this->AllocateColor($this->Layers[0],$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
         imagefilledpolygon($this->Layers[0],$aPoints,$PointsCount,$C_Graph);
        }

       imagecopymerge($this->Picture,$this->Layers[0],$this->GArea_X1,$this->GArea_Y1,0,0,$LayerWidth,$LayerHeight,$Alpha);
       imagedestroy($this->Layers[0]);
       $GraphID++;
       $this->drawLineGraph($Data,$DataDescription,$ColName);
      }
    }

   /* This function draw a bar graph */
   function drawOverlayBarGraph($Data,$DataDescription,$Alpha=50)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawOverlayBarGraph",$DataDescription);
     $this->validateData("drawOverlayBarGraph",$Data);

     $LayerWidth  = $this->GArea_X2-$this->GArea_X1;
     $LayerHeight = $this->GArea_Y2-$this->GArea_Y1;

     $GraphID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $this->Layers[$GraphID] = imagecreatetruecolor($LayerWidth,$LayerHeight);
       $C_White                = $this->AllocateColor($this->Layers[$GraphID],255,255,255);
       $C_Graph                = $this->AllocateColor($this->Layers[$GraphID],$this->Palette[$GraphID]["R"],$this->Palette[$GraphID]["G"],$this->Palette[$GraphID]["B"]);
       imagefilledrectangle($this->Layers[$GraphID],0,0,$LayerWidth,$LayerHeight,$C_White);
       imagecolortransparent($this->Layers[$GraphID],$C_White);

       $XWidth = $this->DivisionWidth / 4;
       $XPos   = $this->GAreaXOffset;
       $YZero  = $LayerHeight - ((0-$this->VMin) * $this->DivisionRatio);
       $XLast  = -1; $PointsCount = 2;
       foreach ( $Data as $Key => $Values )
        {
         if ( isset($Data[$Key][$ColName]) )
          {
           $Value = $Data[$Key][$ColName];
           if ( is_numeric($Value) )
            {
             $YPos  = $LayerHeight - (($Value-$this->VMin) * $this->DivisionRatio);

             imagefilledrectangle($this->Layers[$GraphID],$XPos-$XWidth,$YPos,$XPos+$XWidth,$YZero,$C_Graph);

             $X1 = floor($XPos - $XWidth + $this->GArea_X1); $Y1 = floor($YPos+$this->GArea_Y1) + .2;
             $X2 = floor($XPos + $XWidth + $this->GArea_X1); $Y2 = $this->GArea_Y2 - ((0-$this->VMin) * $this->DivisionRatio);
             if ( $X1 <= $this->GArea_X1 ) { $X1 = $this->GArea_X1 + 1; }
             if ( $X2 >= $this->GArea_X2 ) { $X2 = $this->GArea_X2 - 1; }

             /* Save point into the image map if option activated */
             if ( $this->BuildMap )
              $this->addToImageMap($X1,min($Y1,$Y2),$X2,max($Y1,$Y2),$DataDescription["Description"][$ColName],$Data[$Key][$ColName].$DataDescription["Unit"]["Y"],"oBar");

             $this->drawLine($X1,$Y1,$X2,$Y1,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE);
            }
          }
         $XPos = $XPos + $this->DivisionWidth;
        }

       $GraphID++;
      }

     for($i=0;$i<=($GraphID-1);$i++)
      {
       imagecopymerge($this->Picture,$this->Layers[$i],$this->GArea_X1,$this->GArea_Y1,0,0,$LayerWidth,$LayerHeight,$Alpha);
       imagedestroy($this->Layers[$i]);
      }
    }

   /* This function draw a bar graph */
   function drawBarGraph($Data,$DataDescription,$Shadow=FALSE,$Alpha=100)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawBarGraph",$DataDescription);
     $this->validateData("drawBarGraph",$Data);

     $GraphID      = 0;
     $Series       = count($DataDescription["Values"]);
     $SeriesWidth  = $this->DivisionWidth / ($Series+1);
     $SerieXOffset = $this->DivisionWidth / 2 - $SeriesWidth / 2;

     $YZero  = $this->GArea_Y2 - ((0-$this->VMin) * $this->DivisionRatio);
     if ( $YZero > $this->GArea_Y2 ) { $YZero = $this->GArea_Y2; }

     $SerieID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $XPos  = $this->GArea_X1 + $this->GAreaXOffset - $SerieXOffset + $SeriesWidth * $SerieID;
       $XLast = -1;
       foreach ( $Data as $Key => $Values )
        {
         if ( isset($Data[$Key][$ColName]))
          {
           if ( is_numeric($Data[$Key][$ColName]) )
            {
             $Value = $Data[$Key][$ColName];
             $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

             /* Save point into the image map if option activated */
             if ( $this->BuildMap )
              {
               $this->addToImageMap($XPos+1,min($YZero,$YPos),$XPos+$SeriesWidth-1,max($YZero,$YPos),$DataDescription["Description"][$ColName],$Data[$Key][$ColName].$DataDescription["Unit"]["Y"],"Bar");
              }
           
             if ( $Shadow && $Alpha == 100 )
              $this->drawRectangle($XPos+1,$YZero,$XPos+$SeriesWidth-1,$YPos,25,25,25,TRUE,$Alpha);

             $this->drawFilledRectangle($XPos+1,$YZero,$XPos+$SeriesWidth-1,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE,$Alpha);
            }
          }
         $XPos = $XPos + $this->DivisionWidth;
        }
       $SerieID++;
      }
    }

   /* This function draw a stacked bar graph */
   function drawStackedBarGraph($Data,$DataDescription,$Alpha=50,$Contiguous=FALSE)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawBarGraph",$DataDescription);
     $this->validateData("drawBarGraph",$Data);

     $GraphID      = 0;
     $Series       = count($DataDescription["Values"]);
     if ( $Contiguous )
      $SeriesWidth  = $this->DivisionWidth;
     else
      $SeriesWidth  = $this->DivisionWidth * .8;

     $YZero  = $this->GArea_Y2 - ((0-$this->VMin) * $this->DivisionRatio);
     if ( $YZero > $this->GArea_Y2 ) { $YZero = $this->GArea_Y2; }

     $SerieID = 0; $LastValue = "";
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $XPos  = $this->GArea_X1 + $this->GAreaXOffset - $SeriesWidth / 2;
       $XLast = -1; 
       foreach ( $Data as $Key => $Values )
        {
         if ( isset($Data[$Key][$ColName]))
          {
           if ( is_numeric($Data[$Key][$ColName]) )
            {
             $Value = $Data[$Key][$ColName];

             if ( isset($LastValue[$Key]) )
              {
               $YPos    = $this->GArea_Y2 - ((($Value+$LastValue[$Key])-$this->VMin) * $this->DivisionRatio);
               $YBottom = $this->GArea_Y2 - (($LastValue[$Key]-$this->VMin) * $this->DivisionRatio);
               $LastValue[$Key] += $Value;
              }
             else
              {
               $YPos    = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);
               $YBottom = $YZero;
               $LastValue[$Key] = $Value;
              }

             /* Save point into the image map if option activated */
             if ( $this->BuildMap )
              $this->addToImageMap($XPos+1,min($YBottom,$YPos),$XPos+$SeriesWidth-1,max($YBottom,$YPos),$DataDescription["Description"][$ColName],$Data[$Key][$ColName].$DataDescription["Unit"]["Y"],"sBar");

             $this->drawFilledRectangle($XPos+1,$YBottom,$XPos+$SeriesWidth-1,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE,$Alpha);
            }
          }
         $XPos = $XPos + $this->DivisionWidth;
        }
       $SerieID++;
      }
    }

   /* This function draw a limits bar graphs */
   function drawLimitsGraph($Data,$DataDescription,$R=0,$G=0,$B=0)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawLimitsGraph",$DataDescription);
     $this->validateData("drawLimitsGraph",$Data);

     $XWidth = $this->DivisionWidth / 4;
     $XPos   = $this->GArea_X1 + $this->GAreaXOffset;

     foreach ( $Data as $Key => $Values )
      {
       $Min     = $Data[$Key][$DataDescription["Values"][0]];
       $Max     = $Data[$Key][$DataDescription["Values"][0]];
       $GraphID = 0; $MaxID = 0; $MinID = 0;
       foreach ( $DataDescription["Values"] as $Key2 => $ColName )
        {
         if ( isset($Data[$Key][$ColName]) )
          {
           if ( $Data[$Key][$ColName] > $Max && is_numeric($Data[$Key][$ColName]))
            { $Max = $Data[$Key][$ColName]; $MaxID = $GraphID; }
          }
         if ( isset($Data[$Key][$ColName]) && is_numeric($Data[$Key][$ColName]))
          {
           if ( $Data[$Key][$ColName] < $Min )
            { $Min = $Data[$Key][$ColName]; $MinID = $GraphID; }
           $GraphID++;
          }
        }

       $YPos = $this->GArea_Y2 - (($Max-$this->VMin) * $this->DivisionRatio);
       $X1 = floor($XPos - $XWidth); $Y1 = floor($YPos) - .2;
       $X2 = floor($XPos + $XWidth);
       if ( $X1 <= $this->GArea_X1 ) { $X1 = $this->GArea_X1 + 1; }
       if ( $X2 >= $this->GArea_X2 ) { $X2 = $this->GArea_X2 - 1; }

       $YPos = $this->GArea_Y2 - (($Min-$this->VMin) * $this->DivisionRatio);
       $Y2 = floor($YPos) + .2;

       $this->drawLine(floor($XPos)-.2,$Y1+1,floor($XPos)-.2,$Y2-1,$R,$G,$B,TRUE);
       $this->drawLine(floor($XPos)+.2,$Y1+1,floor($XPos)+.2,$Y2-1,$R,$G,$B,TRUE);
       $this->drawLine($X1,$Y1,$X2,$Y1,$this->Palette[$MaxID]["R"],$this->Palette[$MaxID]["G"],$this->Palette[$MaxID]["B"],FALSE);
       $this->drawLine($X1,$Y2,$X2,$Y2,$this->Palette[$MinID]["R"],$this->Palette[$MinID]["G"],$this->Palette[$MinID]["B"],FALSE);

       $XPos = $XPos + $this->DivisionWidth;
      }
    }

   /* This function draw radar axis centered on the graph area */
   function drawRadarAxis($Data,$DataDescription,$Mosaic=TRUE,$BorderOffset=10,$A_R=60,$A_G=60,$A_B=60,$S_R=200,$S_G=200,$S_B=200,$MaxValue=-1)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawRadarAxis",$DataDescription);
     $this->validateData("drawRadarAxis",$Data);

     $C_TextColor = $this->AllocateColor($this->Picture,$A_R,$A_G,$A_B);

     /* Draw radar axis */
     $Points  = count($Data);
     $Radius  = ( $this->GArea_Y2 - $this->GArea_Y1 ) / 2 - $BorderOffset;
     $XCenter = ( $this->GArea_X2 - $this->GArea_X1 ) / 2 + $this->GArea_X1;
     $YCenter = ( $this->GArea_Y2 - $this->GArea_Y1 ) / 2 + $this->GArea_Y1;

     /* Search for the max value */
     if ( $MaxValue == -1 )
      {
       foreach ( $DataDescription["Values"] as $Key2 => $ColName )
        {
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]))
            if ( $Data[$Key][$ColName] > $MaxValue ) { $MaxValue = $Data[$Key][$ColName]; }
          }
        }
      }

     /* Draw the mosaic */
     if ( $Mosaic )
      {
       $RadiusScale = $Radius / $MaxValue;
       for ( $t=1; $t<=$MaxValue-1; $t++)
        {
         $TRadius  = $RadiusScale * $t;
         $LastX1   = -1;

         for ( $i=0; $i<=$Points; $i++)
          {
           $Angle = -90 + $i * 360/$Points;
           $X1 = cos($Angle * 3.1418 / 180 ) * $TRadius + $XCenter;
           $Y1 = sin($Angle * 3.1418 / 180 ) * $TRadius + $YCenter;
           $X2 = cos($Angle * 3.1418 / 180 ) * ($TRadius+$RadiusScale) + $XCenter;
           $Y2 = sin($Angle * 3.1418 / 180 ) * ($TRadius+$RadiusScale) + $YCenter;

           if ( $t % 2 == 1 && $LastX1 != -1)
            {
             $Plots   = "";
             $Plots[] = $X1; $Plots[] = $Y1;
             $Plots[] = $X2; $Plots[] = $Y2;
             $Plots[] = $LastX2; $Plots[] = $LastY2;
             $Plots[] = $LastX1; $Plots[] = $LastY1;

             $C_Graph = $this->AllocateColor($this->Picture,250,250,250);
             imagefilledpolygon($this->Picture,$Plots,(count($Plots)+1)/2,$C_Graph);
            }

           $LastX1 = $X1; $LastY1= $Y1;
           $LastX2 = $X2; $LastY2= $Y2;
          }
        }
      }


     /* Draw the spider web */
     for ( $t=1; $t<=$MaxValue; $t++)
      {
       $TRadius = ( $Radius / $MaxValue ) * $t;
       $LastX   = -1;

       for ( $i=0; $i<=$Points; $i++)
        {
         $Angle = -90 + $i * 360/$Points;
         $X = cos($Angle * 3.1418 / 180 ) * $TRadius + $XCenter;
         $Y = sin($Angle * 3.1418 / 180 ) * $TRadius + $YCenter;

         if ( $LastX != -1 )
          $this->drawDottedLine($LastX,$LastY,$X,$Y,4,$S_R,$S_G,$S_B);

         $LastX = $X; $LastY= $Y;
        }
      }

     /* Draw the axis */
     for ( $i=0; $i<=$Points; $i++)
      {
       $Angle = -90 + $i * 360/$Points;
       $X = cos($Angle * 3.1418 / 180 ) * $Radius + $XCenter;
       $Y = sin($Angle * 3.1418 / 180 ) * $Radius + $YCenter;

       $this->drawLine($XCenter,$YCenter,$X,$Y,$A_R,$A_G,$A_B);

       $XOffset = 0; $YOffset = 0;
       if (isset($Data[$i][$DataDescription["Position"]]))
        {
         $Label = $Data[$i][$DataDescription["Position"]];

         $Positions = imagettfbbox($this->FontSize,0,$this->FontName,$Label);
         $Width  = $Positions[2] - $Positions[6];
         $Height = $Positions[3] - $Positions[7];

         if ( $Angle >= 0 && $Angle <= 90 )
          $YOffset = $Height;

         if ( $Angle > 90 && $Angle <= 180 )
          { $YOffset = $Height; $XOffset = -$Width; }

         if ( $Angle > 180 && $Angle <= 270 )
          { $XOffset = -$Width; }

         imagettftext($this->Picture,$this->FontSize,0,$X+$XOffset,$Y+$YOffset,$C_TextColor,$this->FontName,$Label);
        }
      }

     /* Write the values */
     for ( $t=1; $t<=$MaxValue; $t++)
      {
       $TRadius = ( $Radius / $MaxValue ) * $t;

       $Angle = -90 + 360 / $Points;
       $X1 = $XCenter;
       $Y1 = $YCenter - $TRadius;
       $X2 = cos($Angle * 3.1418 / 180 ) * $TRadius + $XCenter;
       $Y2 = sin($Angle * 3.1418 / 180 ) * $TRadius + $YCenter;

       $XPos = floor(($X2-$X1)/2) + $X1;
       $YPos = floor(($Y2-$Y1)/2) + $Y1;

       $Positions = imagettfbbox($this->FontSize,0,$this->FontName,$t);
       $X = $XPos - ( $X+$Positions[2] - $X+$Positions[6] ) / 2;
       $Y = $YPos + $this->FontSize;

       $this->drawFilledRoundedRectangle($X+$Positions[6]-2,$Y+$Positions[7]-1,$X+$Positions[2]+4,$Y+$Positions[3]+1,2,240,240,240);
       $this->drawRoundedRectangle($X+$Positions[6]-2,$Y+$Positions[7]-1,$X+$Positions[2]+4,$Y+$Positions[3]+1,2,220,220,220);
       imagettftext($this->Picture,$this->FontSize,0,$X,$Y,$C_TextColor,$this->FontName,$t);
      }
    }

   /* This function draw a radar graph centered on the graph area */
   function drawRadar($Data,$DataDescription,$BorderOffset=10,$MaxValue=-1)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawRadar",$DataDescription);
     $this->validateData("drawRadar",$Data);

     $Points  = count($Data);
     $Radius  = ( $this->GArea_Y2 - $this->GArea_Y1 ) / 2 - $BorderOffset;
     $XCenter = ( $this->GArea_X2 - $this->GArea_X1 ) / 2 + $this->GArea_X1;
     $YCenter = ( $this->GArea_Y2 - $this->GArea_Y1 ) / 2 + $this->GArea_Y1;

     /* Search for the max value */
     if ( $MaxValue == -1 )
      {
       foreach ( $DataDescription["Values"] as $Key2 => $ColName )
        {
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]))
            if ( $Data[$Key][$ColName] > $MaxValue ) { $MaxValue = $Data[$Key][$ColName]; }
          }
        }
      }

     $GraphID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $Angle = -90;
       $XLast = -1;
       foreach ( $Data as $Key => $Values )
        {
         if ( isset($Data[$Key][$ColName]))
          {
           $Value    = $Data[$Key][$ColName];
           $Strength = ( $Radius / $MaxValue ) * $Value;

           $XPos = cos($Angle * 3.1418 / 180 ) * $Strength + $XCenter;
           $YPos = sin($Angle * 3.1418 / 180 ) * $Strength + $YCenter;

           if ( $XLast != -1 )
            $this->drawLine($XLast,$YLast,$XPos,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);

           if ( $XLast == -1 )
            { $FirstX = $XPos; $FirstY = $YPos; }

           $Angle = $Angle + (360/$Points);
           $XLast = $XPos;
           $YLast = $YPos;
          }
        }
       $this->drawLine($XPos,$YPos,$FirstX,$FirstY,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
       $GraphID++;
      }
    }

   /* This function draw a radar graph centered on the graph area */
   function drawFilledRadar($Data,$DataDescription,$Alpha=50,$BorderOffset=10,$MaxValue=-1)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawFilledRadar",$DataDescription);
     $this->validateData("drawFilledRadar",$Data);

     $Points      = count($Data);
     $LayerWidth  = $this->GArea_X2-$this->GArea_X1;
     $LayerHeight = $this->GArea_Y2-$this->GArea_Y1;
     $Radius      = ( $this->GArea_Y2 - $this->GArea_Y1 ) / 2 - $BorderOffset;
     $XCenter     = ( $this->GArea_X2 - $this->GArea_X1 ) / 2;
     $YCenter     = ( $this->GArea_Y2 - $this->GArea_Y1 ) / 2;

     /* Search for the max value */
     if ( $MaxValue == -1 )
      {
       foreach ( $DataDescription["Values"] as $Key2 => $ColName )
        {
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]))
            if ( $Data[$Key][$ColName] > $MaxValue && is_numeric($Data[$Key][$ColName])) { $MaxValue = $Data[$Key][$ColName]; }
          }
        }
      }

     $GraphID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $Angle = -90;
       $XLast = -1;
       $Plots = "";
       foreach ( $Data as $Key => $Values )
        {
         if ( isset($Data[$Key][$ColName]))
          {
           $Value    = $Data[$Key][$ColName];
           if ( !is_numeric($Value) ) { $Value = 0; }
           $Strength = ( $Radius / $MaxValue ) * $Value;

           $XPos = cos($Angle * 3.1418 / 180 ) * $Strength + $XCenter;
           $YPos = sin($Angle * 3.1418 / 180 ) * $Strength + $YCenter;

           $Plots[] = $XPos;
           $Plots[] = $YPos;

           $Angle = $Angle + (360/$Points);
           $XLast = $XPos;
           $YLast = $YPos;
          }
        }

       if (isset($Plots[0]))
        {
         $Plots[] = $Plots[0];
         $Plots[] = $Plots[1];

         $this->Layers[0] = imagecreatetruecolor($LayerWidth,$LayerHeight);
         $C_White         = $this->AllocateColor($this->Layers[0],255,255,255);
         imagefilledrectangle($this->Layers[0],0,0,$LayerWidth,$LayerHeight,$C_White);
         imagecolortransparent($this->Layers[0],$C_White);

         $C_Graph = $this->AllocateColor($this->Layers[0],$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
         imagefilledpolygon($this->Layers[0],$Plots,(count($Plots)+1)/2,$C_Graph);

         imagecopymerge($this->Picture,$this->Layers[0],$this->GArea_X1,$this->GArea_Y1,0,0,$LayerWidth,$LayerHeight,$Alpha);
         imagedestroy($this->Layers[0]);

         for($i=0;$i<=count($Plots)-4;$i=$i+2)
          $this->drawLine($Plots[$i]+$this->GArea_X1,$Plots[$i+1]+$this->GArea_Y1,$Plots[$i+2]+$this->GArea_X1,$Plots[$i+3]+$this->GArea_Y1,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"]);
        }

       $GraphID++;
      }
    }

   /* This function draw a flat pie chart */
   function drawBasicPieGraph($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$R=255,$G=255,$B=255,$Decimals=0)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawBasicPieGraph",$DataDescription,FALSE);
     $this->validateData("drawBasicPieGraph",$Data);

     /* Determine pie sum */
     $Series = 0; $PieSum = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       if ( $ColName != $DataDescription["Position"] )
        {
         $Series++;
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]))
            $PieSum = $PieSum + $Data[$Key][$ColName]; $iValues[] = $Data[$Key][$ColName]; $iLabels[] = $Data[$Key][$DataDescription["Position"]];
          }
        }
      }

     /* Validate serie */
     if ( $Series != 1 )
      RaiseFatal("Pie chart can only accept one serie of data.");

     $SpliceRatio         = 360 / $PieSum;
     $SplicePercent       = 100 / $PieSum;

     /* Calculate all polygons */
     $Angle    = 0; $TopPlots = "";
     foreach($iValues as $Key => $Value)
      {
       $TopPlots[$Key][] = $XPos;
       $TopPlots[$Key][] = $YPos;

       /* Process labels position & size */
       $Caption = "";
       if ( !($DrawLabels == PIE_NOLABEL) )
        {
         $TAngle   = $Angle+($Value*$SpliceRatio/2);
         if ($DrawLabels == PIE_PERCENTAGE)
          $Caption  = (round($Value * pow(10,$Decimals) * $SplicePercent)/pow(10,$Decimals))."%";
         elseif ($DrawLabels == PIE_LABELS)
          $Caption  = $iLabels[$Key];
         elseif ($DrawLabels == PIE_PERCENTAGE_LABEL)
          $Caption  = $iLabels[$Key]."\r\n".(round($Value * pow(10,$Decimals) * $SplicePercent)/pow(10,$Decimals))."%";
         elseif ($DrawLabels == PIE_PERCENTAGE_LABEL)
          $Caption  = $iLabels[$Key]."\r\n".(round($Value * pow(10,$Decimals) * $SplicePercent)/pow(10,$Decimals))."%";

         $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Caption);
         $TextWidth  = $Position[2]-$Position[0];
         $TextHeight = abs($Position[1])+abs($Position[3]);

         $TX = cos(($TAngle) * 3.1418 / 180 ) * ($Radius+10) + $XPos;

         if ( $TAngle > 0 && $TAngle < 180 )
          $TY = sin(($TAngle) * 3.1418 / 180 ) * ($Radius+10) + $YPos + 4;
         else
          $TY = sin(($TAngle) * 3.1418 / 180 ) * ($Radius+4) + $YPos - ($TextHeight/2);

         if ( $TAngle > 90 && $TAngle < 270 )
          $TX = $TX - $TextWidth;

         $C_TextColor = $this->AllocateColor($this->Picture,70,70,70);
         imagettftext($this->Picture,$this->FontSize,0,$TX,$TY,$C_TextColor,$this->FontName,$Caption);
        }

       /* Process pie slices */
       for($iAngle=$Angle;$iAngle<=$Angle+$Value*$SpliceRatio;$iAngle=$iAngle+.5)
        {
         $TopX = cos($iAngle * 3.1418 / 180 ) * $Radius + $XPos;
         $TopY = sin($iAngle * 3.1418 / 180 ) * $Radius + $YPos;

         $TopPlots[$Key][] = $TopX; 
         $TopPlots[$Key][] = $TopY;
        }

       $TopPlots[$Key][] = $XPos;
       $TopPlots[$Key][] = $YPos;

       $Angle = $iAngle;
      }
     $PolyPlots = $TopPlots;

     /* Set array values type to float --- PHP Bug with imagefilledpolygon casting to integer */
     foreach ($TopPlots as $Key => $Value)
      { foreach ($TopPlots[$Key] as $Key2 => $Value2) { settype($TopPlots[$Key][$Key2],"float"); } }

     /* Draw Top polygons */
     foreach ($PolyPlots as $Key => $Value)
      { 
       $C_GraphLo = $this->AllocateColor($this->Picture,$this->Palette[$Key]["R"],$this->Palette[$Key]["G"],$this->Palette[$Key]["B"]);
       imagefilledpolygon($this->Picture,$PolyPlots[$Key],(count($PolyPlots[$Key])+1)/2,$C_GraphLo);
      }

     $this->drawCircle($XPos-.5,$YPos-.5,$Radius,$R,$G,$B);
     $this->drawCircle($XPos-.5,$YPos-.5,$Radius+.5,$R,$G,$B);

     /* Draw Top polygons */
     foreach ($TopPlots as $Key => $Value)
      { 
       for($j=0;$j<=count($TopPlots[$Key])-4;$j=$j+2)
        $this->drawLine($TopPlots[$Key][$j],$TopPlots[$Key][$j+1],$TopPlots[$Key][$j+2],$TopPlots[$Key][$j+3],$R,$G,$B);
      }
    }

   function drawFlatPieGraphWithShadow($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$SpliceDistance=0,$Decimals=0)
    {
     $this->drawFlatPieGraph($Data,$DataDescription,$XPos+$this->ShadowXDistance,$YPos+$this->ShadowYDistance,$Radius,PIE_NOLABEL,$SpliceDistance,$Decimals,TRUE);
     $this->drawFlatPieGraph($Data,$DataDescription,$XPos,$YPos,$Radius,$DrawLabels,$SpliceDistance,$Decimals,FALSE);
    }

   /* This function draw a flat pie chart */
   function drawFlatPieGraph($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$SpliceDistance=0,$Decimals=0,$AllBlack=FALSE)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawFlatPieGraph",$DataDescription,FALSE);
     $this->validateData("drawFlatPieGraph",$Data);

     $ShadowStatus = $this->ShadowActive ; $this->ShadowActive = FALSE;

     /* Determine pie sum */
     $Series = 0; $PieSum = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       if ( $ColName != $DataDescription["Position"] )
        {
         $Series++;
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]))
            $PieSum = $PieSum + $Data[$Key][$ColName]; $iValues[] = $Data[$Key][$ColName]; $iLabels[] = $Data[$Key][$DataDescription["Position"]];
          }
        }
      }

     /* Validate serie */
     if ( $Series != 1 )
      {
       RaiseFatal("Pie chart can only accept one serie of data.");
       return(0);
      }

     $SpliceRatio   = 360 / $PieSum;
     $SplicePercent = 100 / $PieSum;

     /* Calculate all polygons */
     $Angle = 0; $TopPlots = "";
     foreach($iValues as $Key => $Value)
      {
       $XOffset = cos(($Angle+($Value/2*$SpliceRatio)) * 3.1418 / 180 ) * $SpliceDistance;
       $YOffset = sin(($Angle+($Value/2*$SpliceRatio)) * 3.1418 / 180 ) * $SpliceDistance;

       $TopPlots[$Key][] = round($XPos + $XOffset);
       $TopPlots[$Key][] = round($YPos + $YOffset);

       if ( $AllBlack )
        { $Rc = $this->ShadowRColor; $Gc = $this->ShadowGColor; $Bc = $this->ShadowBColor; }
       else
        { $Rc = $this->Palette[$Key]["R"]; $Gc = $this->Palette[$Key]["G"]; $Bc = $this->Palette[$Key]["B"]; }

       $XLineLast = ""; $YLineLast = "";

       /* Process labels position & size */
       $Caption = "";
       if ( !($DrawLabels == PIE_NOLABEL) )
        {
         $TAngle   = $Angle+($Value*$SpliceRatio/2);
         if ($DrawLabels == PIE_PERCENTAGE)
          $Caption  = (round($Value * pow(10,$Decimals) * $SplicePercent)/pow(10,$Decimals))."%";
         elseif ($DrawLabels == PIE_LABELS)
          $Caption  = $iLabels[$Key];
         elseif ($DrawLabels == PIE_PERCENTAGE_LABEL)
          $Caption  = $iLabels[$Key]."\r\n".(round($Value * pow(10,$Decimals) * $SplicePercent)/pow(10,$Decimals))."%";
         elseif ($DrawLabels == PIE_PERCENTAGE_LABEL)
          $Caption  = $iLabels[$Key]."\r\n".(round($Value * pow(10,$Decimals) * $SplicePercent)/pow(10,$Decimals))."%";

         $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Caption);
         $TextWidth  = $Position[2]-$Position[0];
         $TextHeight = abs($Position[1])+abs($Position[3]);

         $TX = cos(($TAngle) * 3.1418 / 180 ) * ($Radius+10+$SpliceDistance) + $XPos;

         if ( $TAngle > 0 && $TAngle < 180 )
          $TY = sin(($TAngle) * 3.1418 / 180 ) * ($Radius+10+$SpliceDistance) + $YPos + 4;
         else
          $TY = sin(($TAngle) * 3.1418 / 180 ) * ($Radius+$SpliceDistance+4) + $YPos - ($TextHeight/2);

         if ( $TAngle > 90 && $TAngle < 270 )
          $TX = $TX - $TextWidth;

         $C_TextColor = $this->AllocateColor($this->Picture,70,70,70);
         imagettftext($this->Picture,$this->FontSize,0,$TX,$TY,$C_TextColor,$this->FontName,$Caption);
        }

       /* Process pie slices */
       if ( !$AllBlack )
        $LineColor = $this->AllocateColor($this->Picture,$Rc,$Gc,$Bc);
       else
        $LineColor = $this->AllocateColor($this->Picture,$Rc,$Gc,$Bc);

       $XLineLast = ""; $YLineLast = "";
       for($iAngle=$Angle;$iAngle<=$Angle+$Value*$SpliceRatio;$iAngle=$iAngle+.5)
        {
         $PosX = cos($iAngle * 3.1418 / 180 ) * $Radius + $XPos + $XOffset;
         $PosY = sin($iAngle * 3.1418 / 180 ) * $Radius + $YPos + $YOffset;

         $TopPlots[$Key][] = round($PosX); $TopPlots[$Key][] = round($PosY);

         if ( $iAngle == $Angle || $iAngle == $Angle+$Value*$SpliceRatio || $iAngle +.5 > $Angle+$Value*$SpliceRatio)
          $this->drawLine($XPos+$XOffset,$YPos+$YOffset,$PosX,$PosY,$Rc,$Gc,$Bc);

         if ( $XLineLast != "" )
          $this->drawLine($XLineLast,$YLineLast,$PosX,$PosY,$Rc,$Gc,$Bc);

         $XLineLast = $PosX; $YLineLast = $PosY;
        }

       $TopPlots[$Key][] = round($XPos + $XOffset);  $TopPlots[$Key][] = round($YPos + $YOffset);

       $Angle = $iAngle;
      }
     $PolyPlots = $TopPlots;

     /* Draw Top polygons */
     foreach ($PolyPlots as $Key => $Value)
      { 
       if ( !$AllBlack )
        $C_GraphLo = $this->AllocateColor($this->Picture,$this->Palette[$Key]["R"],$this->Palette[$Key]["G"],$this->Palette[$Key]["B"]);
       else
        $C_GraphLo = $this->AllocateColor($this->Picture,$this->ShadowRColor,$this->ShadowGColor,$this->ShadowBColor);

       imagefilledpolygon($this->Picture,$PolyPlots[$Key],(count($PolyPlots[$Key])+1)/2,$C_GraphLo);
      }
     $this->ShadowActive = $ShadowStatus;
    }

   /* This function draw a pseudo-3D pie chart */
   function drawPieGraph($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$EnhanceColors=TRUE,$Skew=60,$SpliceHeight=20,$SpliceDistance=0,$Decimals=0)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawPieGraph",$DataDescription,FALSE);
     $this->validateData("drawPieGraph",$Data);

     /* Determine pie sum */
     $Series = 0; $PieSum = 0; $rPieSum = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       if ( $ColName != $DataDescription["Position"] )
        {
         $Series++;
         foreach ( $Data as $Key => $Values )
          if ( isset($Data[$Key][$ColName]))
           {
            if ( $Data[$Key][$ColName] == 0 )
             { $iValues[] = 0; $rValues[] = 0; $iLabels[] = $Data[$Key][$DataDescription["Position"]]; }
              // Removed : $PieSum++; $rValues[] = 1;
            else
             { $PieSum += $Data[$Key][$ColName]; $iValues[] = $Data[$Key][$ColName]; $iLabels[] = $Data[$Key][$DataDescription["Position"]]; $rValues[] = $Data[$Key][$ColName]; $rPieSum += $Data[$Key][$ColName];}
           }
        }
      }

     /* Validate serie */
     if ( $Series != 1 )
      RaiseFatal("Pie chart can only accept one serie of data.");

     $SpliceDistanceRatio = $SpliceDistance;
     $SkewHeight          = ($Radius * $Skew) / 100;
     $SpliceRatio         = (360 - $SpliceDistanceRatio * count($iValues) ) / $PieSum;
     $SplicePercent       = 100 / $PieSum;
     $rSplicePercent      = 100 / $rPieSum;

     /* Calculate all polygons */
     $Angle    = 0; $CDev = 5;
     $TopPlots = ""; $BotPlots = "";
     $aTopPlots = ""; $aBotPlots = "";
     foreach($iValues as $Key => $Value)
      {
       $XCenterPos = cos(($Angle-$CDev+($Value*$SpliceRatio+$SpliceDistanceRatio)/2) * 3.1418 / 180 ) * $SpliceDistance + $XPos;
       $YCenterPos = sin(($Angle-$CDev+($Value*$SpliceRatio+$SpliceDistanceRatio)/2) * 3.1418 / 180 ) * $SpliceDistance + $YPos;
       $XCenterPos2 = cos(($Angle+$CDev+($Value*$SpliceRatio+$SpliceDistanceRatio)/2) * 3.1418 / 180 ) * $SpliceDistance + $XPos;
       $YCenterPos2 = sin(($Angle+$CDev+($Value*$SpliceRatio+$SpliceDistanceRatio)/2) * 3.1418 / 180 ) * $SpliceDistance + $YPos;

       $TopPlots[$Key][] = round($XCenterPos); $BotPlots[$Key][] = round($XCenterPos);
       $TopPlots[$Key][] = round($YCenterPos); $BotPlots[$Key][] = round($YCenterPos + $SpliceHeight);
       $aTopPlots[$Key][] = $XCenterPos; $aBotPlots[$Key][] = $XCenterPos;
       $aTopPlots[$Key][] = $YCenterPos; $aBotPlots[$Key][] = $YCenterPos + $SpliceHeight;

       /* Process labels position & size */
       $Caption = "";
       if ( !($DrawLabels == PIE_NOLABEL) )
        {
         $TAngle   = $Angle+($Value*$SpliceRatio/2);
         if ($DrawLabels == PIE_PERCENTAGE)
          $Caption  = (round($rValues[$Key] * pow(10,$Decimals) * $rSplicePercent)/pow(10,$Decimals))."%";
         elseif ($DrawLabels == PIE_LABELS)
          $Caption  = $iLabels[$Key];
         elseif ($DrawLabels == PIE_PERCENTAGE_LABEL)
          $Caption  = $iLabels[$Key]."\r\n".(round($Value * pow(10,$Decimals) * $SplicePercent)/pow(10,$Decimals))."%";

         $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Caption);
         $TextWidth  = $Position[2]-$Position[0];
         $TextHeight = abs($Position[1])+abs($Position[3]);

         $TX = cos(($TAngle) * 3.1418 / 180 ) * ($Radius + 10)+ $XPos;

         if ( $TAngle > 0 && $TAngle < 180 )
          $TY = sin(($TAngle) * 3.1418 / 180 ) * ($SkewHeight + 10) + $YPos + $SpliceHeight + 4;
         else
          $TY = sin(($TAngle) * 3.1418 / 180 ) * ($SkewHeight + 4) + $YPos - ($TextHeight/2);

         if ( $TAngle > 90 && $TAngle < 270 )
          $TX = $TX - $TextWidth;

         $C_TextColor = $this->AllocateColor($this->Picture,70,70,70);
         imagettftext($this->Picture,$this->FontSize,0,$TX,$TY,$C_TextColor,$this->FontName,$Caption);
        }

       /* Process pie slices */
       for($iAngle=$Angle;$iAngle<=$Angle+$Value*$SpliceRatio;$iAngle=$iAngle+.5)
        {
         $TopX = cos($iAngle * 3.1418 / 180 ) * $Radius + $XPos;
         $TopY = sin($iAngle * 3.1418 / 180 ) * $SkewHeight + $YPos;

         $TopPlots[$Key][] = round($TopX); $BotPlots[$Key][] = round($TopX);
         $TopPlots[$Key][] = round($TopY); $BotPlots[$Key][] = round($TopY + $SpliceHeight);
         $aTopPlots[$Key][] = $TopX; $aBotPlots[$Key][] = $TopX;
         $aTopPlots[$Key][] = $TopY; $aBotPlots[$Key][] = $TopY + $SpliceHeight;
        }

       $TopPlots[$Key][] = round($XCenterPos2); $BotPlots[$Key][] = round($XCenterPos2);
       $TopPlots[$Key][] = round($YCenterPos2); $BotPlots[$Key][] = round($YCenterPos2 + $SpliceHeight);
       $aTopPlots[$Key][] = $XCenterPos2; $aBotPlots[$Key][] = $XCenterPos2;
       $aTopPlots[$Key][] = $YCenterPos2; $aBotPlots[$Key][] = $YCenterPos2 + $SpliceHeight;

       $Angle = $iAngle + $SpliceDistanceRatio;
      }

     /* Draw Bottom polygons */
     foreach($iValues as $Key => $Value)
      {
       $C_GraphLo = $this->AllocateColor($this->Picture,$this->Palette[$Key]["R"],$this->Palette[$Key]["G"],$this->Palette[$Key]["B"],-20);
       imagefilledpolygon($this->Picture,$BotPlots[$Key],(count($BotPlots[$Key])+1)/2,$C_GraphLo);

       if ( $EnhanceColors ) { $En = -10; } else { $En = 0; }
 
       for($j=0;$j<=count($aBotPlots[$Key])-4;$j=$j+2)
        $this->drawLine($aBotPlots[$Key][$j],$aBotPlots[$Key][$j+1],$aBotPlots[$Key][$j+2],$aBotPlots[$Key][$j+3],$this->Palette[$Key]["R"]+$En,$this->Palette[$Key]["G"]+$En,$this->Palette[$Key]["B"]+$En);
      }

     /* Draw pie layers */
     if ( $EnhanceColors ) { $ColorRatio = 30 / $SpliceHeight; } else { $ColorRatio = 25 / $SpliceHeight; }
     for($i=$SpliceHeight-1;$i>=1;$i--)
      {
       foreach($iValues as $Key => $Value)
        {
         $C_GraphLo = $this->AllocateColor($this->Picture,$this->Palette[$Key]["R"],$this->Palette[$Key]["G"],$this->Palette[$Key]["B"],-10);
         $Plots = ""; $Plot = 0;
         foreach($TopPlots[$Key] as $Key2 => $Value2)
          {
           $Plot++;
           if ( $Plot % 2 == 1 )
            $Plots[] = $Value2;
           else
            $Plots[] = $Value2+$i;
          }
         imagefilledpolygon($this->Picture,$Plots,(count($Plots)+1)/2,$C_GraphLo);

         $Index       = count($Plots);
         if ($EnhanceColors ) {$ColorFactor = -20 + ($SpliceHeight - $i) * $ColorRatio; } else { $ColorFactor = 0; }

         $this->drawAntialiasPixel($Plots[0],$Plots[1],$this->Palette[$Key]["R"]+$ColorFactor,$this->Palette[$Key]["G"]+$ColorFactor,$this->Palette[$Key]["B"]+$ColorFactor);
         $this->drawAntialiasPixel($Plots[2],$Plots[3],$this->Palette[$Key]["R"]+$ColorFactor,$this->Palette[$Key]["G"]+$ColorFactor,$this->Palette[$Key]["B"]+$ColorFactor);
         $this->drawAntialiasPixel($Plots[$Index-4],$Plots[$Index-3],$this->Palette[$Key]["R"]+$ColorFactor,$this->Palette[$Key]["G"]+$ColorFactor,$this->Palette[$Key]["B"]+$ColorFactor);
        }
      }

     /* Draw Top polygons */
     for($Key=count($iValues)-1;$Key>=0;$Key--)
      { 
       $C_GraphLo = $this->AllocateColor($this->Picture,$this->Palette[$Key]["R"],$this->Palette[$Key]["G"],$this->Palette[$Key]["B"]);
       imagefilledpolygon($this->Picture,$TopPlots[$Key],(count($TopPlots[$Key])+1)/2,$C_GraphLo);

       if ( $EnhanceColors ) { $En = 10; } else { $En = 0; }
       for($j=0;$j<=count($aTopPlots[$Key])-4;$j=$j+2)
        $this->drawLine($aTopPlots[$Key][$j],$aTopPlots[$Key][$j+1],$aTopPlots[$Key][$j+2],$aTopPlots[$Key][$j+3],$this->Palette[$Key]["R"]+$En,$this->Palette[$Key]["G"]+$En,$this->Palette[$Key]["B"]+$En);
      }
    }

   /* This function can be used to set the background color */
   function drawBackground($R,$G,$B)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_Background = $this->AllocateColor($this->Picture,$R,$G,$B);
     imagefilledrectangle($this->Picture,0,0,$this->XSize,$this->YSize,$C_Background);
    }

   /* This function can be used to set the background color */
   function drawGraphAreaGradient($R,$G,$B,$Decay,$Target=TARGET_GRAPHAREA)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     if ( $Target == TARGET_GRAPHAREA )  { $X1 = $this->GArea_X1+1; $X2 = $this->GArea_X2-1; $Y1 = $this->GArea_Y1+1; $Y2 = $this->GArea_Y2; }
     if ( $Target == TARGET_BACKGROUND ) { $X1 = 0; $X2 = $this->XSize; $Y1 = 0; $Y2 = $this->YSize; }

     /* Positive gradient */
     if ( $Decay > 0 )
      {
       $YStep = ($Y2 - $Y1 - 2) / $Decay;
       for($i=0;$i<=$Decay;$i++)
        {
         $R-=1;$G-=1;$B-=1;
         $Yi1 = $Y1 + ( $i * $YStep );
         $Yi2 = ceil( $Yi1 + ( $i * $YStep ) + $YStep );
         if ( $Yi2 >= $Yi2 ) { $Yi2 = $Y2-1; }

         $C_Background = $this->AllocateColor($this->Picture,$R,$G,$B);
         imagefilledrectangle($this->Picture,$X1,$Yi1,$X2,$Yi2,$C_Background);
        }
      }

     /* Negative gradient */
     if ( $Decay < 0 )
      {
       $YStep = ($Y2 - $Y1 - 2) / -$Decay;
       $Yi1   = $Y1; $Yi2   = $Y1+$YStep;
       for($i=-$Decay;$i>=0;$i--)
        {
         $R+=1;$G+=1;$B+=1;
         $C_Background = $this->AllocateColor($this->Picture,$R,$G,$B);
         imagefilledrectangle($this->Picture,$X1,$Yi1,$X2,$Yi2,$C_Background);

         $Yi1+= $YStep;
         $Yi2+= $YStep;
         if ( $Yi2 >= $Yi2 ) { $Yi2 = $Y2-1; }
        }
      }
    }

   /* This function create a rectangle with antialias */
   function drawRectangle($X1,$Y1,$X2,$Y2,$R,$G,$B)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_Rectangle = $this->AllocateColor($this->Picture,$R,$G,$B);

     $X1=$X1-.2;$Y1=$Y1-.2;
     $X2=$X2+.2;$Y2=$Y2+.2;
     $this->drawLine($X1,$Y1,$X2,$Y1,$R,$G,$B);
     $this->drawLine($X2,$Y1,$X2,$Y2,$R,$G,$B);
     $this->drawLine($X2,$Y2,$X1,$Y2,$R,$G,$B);
     $this->drawLine($X1,$Y2,$X1,$Y1,$R,$G,$B);
    }

   /* This function create a filled rectangle with antialias */
   function drawFilledRectangle($X1,$Y1,$X2,$Y2,$R,$G,$B,$DrawBorder=TRUE,$Alpha=100,$NoFallBack=FALSE)
    {
     if ( $X2 < $X1 ) { list($X1, $X2) = array($X2, $X1); }
     if ( $Y2 < $Y1 ) { list($Y1, $Y2) = array($Y2, $Y1); }

     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     if ( $Alpha == 100 )
      {
       /* Process shadows */
       if ( $this->ShadowActive && !$NoFallBack )
        {
         $this->drawFilledRectangle($X1+$this->ShadowXDistance,$Y1+$this->ShadowYDistance,$X2+$this->ShadowXDistance,$Y2+$this->ShadowYDistance,$this->ShadowRColor,$this->ShadowGColor,$this->ShadowBColor,FALSE,$this->ShadowAlpha,TRUE);
         if ( $this->ShadowBlur != 0 )
          {
           $AlphaDecay = ($this->ShadowAlpha / $this->ShadowBlur);

           for($i=1; $i<=$this->ShadowBlur; $i++)
            $this->drawFilledRectangle($X1+$this->ShadowXDistance-$i/2,$Y1+$this->ShadowYDistance-$i/2,$X2+$this->ShadowXDistance-$i/2,$Y2+$this->ShadowYDistance-$i/2,$this->ShadowRColor,$this->ShadowGColor,$this->ShadowBColor,FALSE,$this->ShadowAlpha-$AlphaDecay*$i,TRUE);
           for($i=1; $i<=$this->ShadowBlur; $i++)
            $this->drawFilledRectangle($X1+$this->ShadowXDistance+$i/2,$Y1+$this->ShadowYDistance+$i/2,$X2+$this->ShadowXDistance+$i/2,$Y2+$this->ShadowYDistance+$i/2,$this->ShadowRColor,$this->ShadowGColor,$this->ShadowBColor,FALSE,$this->ShadowAlpha-$AlphaDecay*$i,TRUE);
          }
        }

       $C_Rectangle = $this->AllocateColor($this->Picture,$R,$G,$B);
       imagefilledrectangle($this->Picture,round($X1),round($Y1),round($X2),round($Y2),$C_Rectangle);
      }
     else
      {
       $LayerWidth  = abs($X2-$X1)+2;
       $LayerHeight = abs($Y2-$Y1)+2;

       $this->Layers[0] = imagecreatetruecolor($LayerWidth,$LayerHeight);
       $C_White         = $this->AllocateColor($this->Layers[0],255,255,255);
       imagefilledrectangle($this->Layers[0],0,0,$LayerWidth,$LayerHeight,$C_White);
       imagecolortransparent($this->Layers[0],$C_White);

       $C_Rectangle = $this->AllocateColor($this->Layers[0],$R,$G,$B);
       imagefilledrectangle($this->Layers[0],round(1),round(1),round($LayerWidth-1),round($LayerHeight-1),$C_Rectangle);

       imagecopymerge($this->Picture,$this->Layers[0],round(min($X1,$X2)-1),round(min($Y1,$Y2)-1),0,0,$LayerWidth,$LayerHeight,$Alpha);
       imagedestroy($this->Layers[0]);
      }

     if ( $DrawBorder )
      {
       $ShadowSettings = $this->ShadowActive; $this->ShadowActive = FALSE;
       $this->drawRectangle($X1,$Y1,$X2,$Y2,$R,$G,$B);
       $this->ShadowActive = $ShadowSettings;
      }
    }

   /* This function create a rectangle with rounded corners and antialias */
   function drawRoundedRectangle($X1,$Y1,$X2,$Y2,$Radius,$R,$G,$B)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_Rectangle = $this->AllocateColor($this->Picture,$R,$G,$B);

     $Step = 90 / ((3.1418 * $Radius)/2);

     for($i=0;$i<=90;$i=$i+$Step)
      {
       $X = cos(($i+180)*3.1418/180) * $Radius + $X1 + $Radius;
       $Y = sin(($i+180)*3.1418/180) * $Radius + $Y1 + $Radius;
       $this->drawAntialiasPixel($X,$Y,$R,$G,$B);

       $X = cos(($i-90)*3.1418/180) * $Radius + $X2 - $Radius;
       $Y = sin(($i-90)*3.1418/180) * $Radius + $Y1 + $Radius;
       $this->drawAntialiasPixel($X,$Y,$R,$G,$B);

       $X = cos(($i)*3.1418/180) * $Radius + $X2 - $Radius;
       $Y = sin(($i)*3.1418/180) * $Radius + $Y2 - $Radius;
       $this->drawAntialiasPixel($X,$Y,$R,$G,$B);

       $X = cos(($i+90)*3.1418/180) * $Radius + $X1 + $Radius;
       $Y = sin(($i+90)*3.1418/180) * $Radius + $Y2 - $Radius;
       $this->drawAntialiasPixel($X,$Y,$R,$G,$B);
      }

     $X1=$X1-.2;$Y1=$Y1-.2;
     $X2=$X2+.2;$Y2=$Y2+.2;
     $this->drawLine($X1+$Radius,$Y1,$X2-$Radius,$Y1,$R,$G,$B);
     $this->drawLine($X2,$Y1+$Radius,$X2,$Y2-$Radius,$R,$G,$B);
     $this->drawLine($X2-$Radius,$Y2,$X1+$Radius,$Y2,$R,$G,$B);
     $this->drawLine($X1,$Y2-$Radius,$X1,$Y1+$Radius,$R,$G,$B);
    }

   /* This function create a filled rectangle with rounded corners and antialias */
   function drawFilledRoundedRectangle($X1,$Y1,$X2,$Y2,$Radius,$R,$G,$B)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_Rectangle = $this->AllocateColor($this->Picture,$R,$G,$B);

     $Step = 90 / ((3.1418 * $Radius)/2);

     for($i=0;$i<=90;$i=$i+$Step)
      {
       $Xi1 = cos(($i+180)*3.1418/180) * $Radius + $X1 + $Radius;
       $Yi1 = sin(($i+180)*3.1418/180) * $Radius + $Y1 + $Radius;

       $Xi2 = cos(($i-90)*3.1418/180) * $Radius + $X2 - $Radius;
       $Yi2 = sin(($i-90)*3.1418/180) * $Radius + $Y1 + $Radius;

       $Xi3 = cos(($i)*3.1418/180) * $Radius + $X2 - $Radius;
       $Yi3 = sin(($i)*3.1418/180) * $Radius + $Y2 - $Radius;

       $Xi4 = cos(($i+90)*3.1418/180) * $Radius + $X1 + $Radius;
       $Yi4 = sin(($i+90)*3.1418/180) * $Radius + $Y2 - $Radius;

       imageline($this->Picture,$Xi1,$Yi1,$X1+$Radius,$Yi1,$C_Rectangle);
       imageline($this->Picture,$X2-$Radius,$Yi2,$Xi2,$Yi2,$C_Rectangle);
       imageline($this->Picture,$X2-$Radius,$Yi3,$Xi3,$Yi3,$C_Rectangle);
       imageline($this->Picture,$Xi4,$Yi4,$X1+$Radius,$Yi4,$C_Rectangle);

       $this->drawAntialiasPixel($Xi1,$Yi1,$R,$G,$B);
       $this->drawAntialiasPixel($Xi2,$Yi2,$R,$G,$B);
       $this->drawAntialiasPixel($Xi3,$Yi3,$R,$G,$B);
       $this->drawAntialiasPixel($Xi4,$Yi4,$R,$G,$B);
      }

     imagefilledrectangle($this->Picture,$X1,$Y1+$Radius,$X2,$Y2-$Radius,$C_Rectangle);
     imagefilledrectangle($this->Picture,$X1+$Radius,$Y1,$X2-$Radius,$Y2,$C_Rectangle);

     $X1=$X1-.2;$Y1=$Y1-.2;
     $X2=$X2+.2;$Y2=$Y2+.2;
     $this->drawLine($X1+$Radius,$Y1,$X2-$Radius,$Y1,$R,$G,$B);
     $this->drawLine($X2,$Y1+$Radius,$X2,$Y2-$Radius,$R,$G,$B);
     $this->drawLine($X2-$Radius,$Y2,$X1+$Radius,$Y2,$R,$G,$B);
     $this->drawLine($X1,$Y2-$Radius,$X1,$Y1+$Radius,$R,$G,$B);
    }

   /* This function create a circle with antialias */
   function drawCircle($Xc,$Yc,$Height,$R,$G,$B,$Width=0)
    {
     if ( $Width == 0 ) { $Width = $Height; }
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_Circle = $this->AllocateColor($this->Picture,$R,$G,$B);
     $Step     = 360 / (2 * 3.1418 * max($Width,$Height));

     for($i=0;$i<=360;$i=$i+$Step)
      {
       $X = cos($i*3.1418/180) * $Height + $Xc;
       $Y = sin($i*3.1418/180) * $Width + $Yc;
       $this->drawAntialiasPixel($X,$Y,$R,$G,$B);
      }
    }

   /* This function create a filled circle/ellipse with antialias */
   function drawFilledCircle($Xc,$Yc,$Height,$R,$G,$B,$Width=0)
    {
     if ( $Width == 0 ) { $Width = $Height; }
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_Circle = $this->AllocateColor($this->Picture,$R,$G,$B);
     $Step     = 360 / (2 * 3.1418 * max($Width,$Height));

     for($i=90;$i<=270;$i=$i+$Step)
      {
       $X1 = cos($i*3.1418/180) * $Height + $Xc;
       $Y1 = sin($i*3.1418/180) * $Width + $Yc;
       $X2 = cos((180-$i)*3.1418/180) * $Height + $Xc;
       $Y2 = sin((180-$i)*3.1418/180) * $Width + $Yc;

       $this->drawAntialiasPixel($X1-1,$Y1-1,$R,$G,$B);
       $this->drawAntialiasPixel($X2-1,$Y2-1,$R,$G,$B);

       if ( ($Y1-1) > $Yc - max($Width,$Height) )
        imageline($this->Picture,$X1,$Y1-1,$X2-1,$Y2-1,$C_Circle);
      }
    }

   /* This function will draw a filled ellipse */
   function drawEllipse($Xc,$Yc,$Height,$Width,$R,$G,$B)
    { $this->drawCircle($Xc,$Yc,$Height,$R,$G,$B,$Width); }

   /* This function will draw an ellipse */
   function drawFilledEllipse($Xc,$Yc,$Height,$Width,$R,$G,$B)
    { $this->drawFilledCircle($Xc,$Yc,$Height,$R,$G,$B,$Width); }

   /* This function create a line with antialias */
   function drawLine($X1,$Y1,$X2,$Y2,$R,$G,$B,$GraphFunction=FALSE)
    {
     if ( $this->LineDotSize > 1 ) { $this->drawDottedLine($X1,$Y1,$X2,$Y2,$this->LineDotSize,$R,$G,$B,$GraphFunction); return(0); }
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $Distance = sqrt(($X2-$X1)*($X2-$X1)+($Y2-$Y1)*($Y2-$Y1));  
     if ( $Distance == 0 )
      return(-1);
     $XStep = ($X2-$X1) / $Distance;
     $YStep = ($Y2-$Y1) / $Distance;

     for($i=0;$i<=$Distance;$i++)
      {
       $X = $i * $XStep + $X1;
       $Y = $i * $YStep + $Y1;

       if ( ($X >= $this->GArea_X1 && $X <= $this->GArea_X2 && $Y >= $this->GArea_Y1 && $Y <= $this->GArea_Y2) || !$GraphFunction )
        {
         if ( $this->LineWidth == 1 )
          $this->drawAntialiasPixel($X,$Y,$R,$G,$B);
         else
          {
           $StartOffset = -($this->LineWidth/2); $EndOffset = ($this->LineWidth/2);
           for($j=$StartOffset;$j<=$EndOffset;$j++)
            $this->drawAntialiasPixel($X+$j,$Y+$j,$R,$G,$B);
          }
        }
      }
    }

   /* This function create a line with antialias */
   function drawDottedLine($X1,$Y1,$X2,$Y2,$DotSize,$R,$G,$B,$GraphFunction=FALSE)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $Distance = sqrt(($X2-$X1)*($X2-$X1)+($Y2-$Y1)*($Y2-$Y1));  

     $XStep = ($X2-$X1) / $Distance;
     $YStep = ($Y2-$Y1) / $Distance;

     $DotIndex = 0;
     for($i=0;$i<=$Distance;$i++)
      {
       $X = $i * $XStep + $X1;
       $Y = $i * $YStep + $Y1;

       if ( $DotIndex <= $DotSize)
        {
         if ( ($X >= $this->GArea_X1 && $X <= $this->GArea_X2 && $Y >= $this->GArea_Y1 && $Y <= $this->GArea_Y2) || !$GraphFunction )
          {
           if ( $this->LineWidth == 1 )
            $this->drawAntialiasPixel($X,$Y,$R,$G,$B);
           else
            {
             $StartOffset = -($this->LineWidth/2); $EndOffset = ($this->LineWidth/2);
             for($j=$StartOffset;$j<=$EndOffset;$j++)
              $this->drawAntialiasPixel($X+$j,$Y+$j,$R,$G,$B);
            }
          }
        }

       $DotIndex++;
       if ( $DotIndex == $DotSize * 2 )
        $DotIndex = 0;        
      }
    }

   /* Load a PNG file and draw it over the chart */
   function drawFromPNG($FileName,$X,$Y,$Alpha=100)
    { $this->drawFromPicture(1,$FileName,$X,$Y,$Alpha); }

   /* Load a GIF file and draw it over the chart */
   function drawFromGIF($FileName,$X,$Y,$Alpha=100)
    { $this->drawFromPicture(2,$FileName,$X,$Y,$Alpha); }

   /* Load a JPEG file and draw it over the chart */
   function drawFromJPG($FileName,$X,$Y,$Alpha=100)
    { $this->drawFromPicture(3,$FileName,$X,$Y,$Alpha); }

   /* Generic loader function for external pictures */
   function drawFromPicture($PicType,$FileName,$X,$Y,$Alpha=100)
    {
     if ( file_exists($FileName))
      {
       $Infos  = getimagesize($FileName);
       $Width  = $Infos[0];
       $Height = $Infos[1];
       if ( $PicType == 1 ) { $Raster = imagecreatefrompng($FileName); }
       if ( $PicType == 2 ) { $Raster = imagecreatefromgif($FileName); }
       if ( $PicType == 3 ) { $Raster = imagecreatefromjpeg($FileName); }

       imagecopymerge($this->Picture,$Raster,$X,$Y,0,0,$Width,$Height,$Alpha);
       imagedestroy($Raster);
      }
    }

   /* Draw an alpha pixel */
   function drawAlphaPixel($X,$Y,$Alpha,$R,$G,$B)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     if ( $X < 0 || $Y < 0 || $X >= $this->XSize || $Y >= $this->YSize )
      return(-1);

     $RGB2 = imagecolorat($this->Picture, $X, $Y);
     $R2   = ($RGB2 >> 16) & 0xFF;
     $G2   = ($RGB2 >> 8) & 0xFF;
     $B2   = $RGB2 & 0xFF;

     $iAlpha = (100 - $Alpha)/100;
     $Alpha  = $Alpha / 100;

     $Ra   = floor($R*$Alpha+$R2*$iAlpha);
     $Ga   = floor($G*$Alpha+$G2*$iAlpha);
     $Ba   = floor($B*$Alpha+$B2*$iAlpha);

     $C_Aliased = $this->AllocateColor($this->Picture,$Ra,$Ga,$Ba);
     imagesetpixel($this->Picture,$X,$Y,$C_Aliased);
    }

   /* Color helper */
   function AllocateColor($Picture,$R,$G,$B,$Factor=0)
    {
     $R = $R + $Factor;
     $G = $G + $Factor;
     $B = $B + $Factor;
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     return(imagecolorallocate($Picture,$R,$G,$B));
    }

   /* Add a border to the picture */
   function addBorder($Size=3,$R=0,$G=0,$B=0)
    {
     $Width  = $this->XSize+2*$Size;
     $Height = $this->YSize+2*$Size;

     $Resampled    = imagecreatetruecolor($Width,$Height);
     $C_Background = $this->AllocateColor($Resampled,$R,$G,$B);
     imagefilledrectangle($Resampled,0,0,$Width,$Height,$C_Background);

     imagecopy($Resampled,$this->Picture,$Size,$Size,0,0,$this->XSize,$this->YSize);
     imagedestroy($this->Picture);

     $this->XSize = $Width;
     $this->YSize = $Height;

     $this->Picture = imagecreatetruecolor($this->XSize,$this->YSize);
     $C_White = $this->AllocateColor($this->Picture,255,255,255);
     imagefilledrectangle($this->Picture,0,0,$this->XSize,$this->YSize,$C_White);
     imagecolortransparent($this->Picture,$C_White);
     imagecopy($this->Picture,$Resampled,0,0,0,0,$this->XSize,$this->YSize);
    }

   /* Render the current picture to a file */
   function Render($FileName)
    {
     if ( $this->ErrorReporting )
      $this->printErrors($this->ErrorInterface);

     /* Save image map if requested */
     if ( $this->BuildMap )
      $this->SaveImageMap();

     imagepng($this->Picture,$FileName);
    }

   /* Render the current picture to STDOUT */
   function Stroke()
    {
     if ( $this->ErrorReporting )
      $this->printErrors("GD");

     /* Save image map if requested */
     if ( $this->BuildMap )
      $this->SaveImageMap();

     header('Content-type: image/png');
     imagepng($this->Picture);
    }

   /* Private functions for internal processing */
   function drawAntialiasPixel($X,$Y,$R,$G,$B,$Alpha=100,$NoFallBack=FALSE)
    {
     /* Process shadows */
     if ( $this->ShadowActive && !$NoFallBack )
      {
       $this->drawAntialiasPixel($X+$this->ShadowXDistance,$Y+$this->ShadowYDistance,$this->ShadowRColor,$this->ShadowGColor,$this->ShadowBColor,$this->ShadowAlpha,TRUE);
       if ( $this->ShadowBlur != 0 )
        {
         $AlphaDecay = ($this->ShadowAlpha / $this->ShadowBlur);

         for($i=1; $i<=$this->ShadowBlur; $i++)
          $this->drawAntialiasPixel($X+$this->ShadowXDistance-$i/2,$Y+$this->ShadowYDistance-$i/2,$this->ShadowRColor,$this->ShadowGColor,$this->ShadowBColor,$this->ShadowAlpha-$AlphaDecay*$i,TRUE);
         for($i=1; $i<=$this->ShadowBlur; $i++)
          $this->drawAntialiasPixel($X+$this->ShadowXDistance+$i/2,$Y+$this->ShadowYDistance+$i/2,$this->ShadowRColor,$this->ShadowGColor,$this->ShadowBColor,$this->ShadowAlpha-$AlphaDecay*$i,TRUE);
        }
      }

     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $Plot = "";
     $Xi   = floor($X);
     $Yi   = floor($Y);

     if ( $Xi == $X && $Yi == $Y)
      {
       if ( $Alpha == 100 )
        {
         $C_Aliased = $this->AllocateColor($this->Picture,$R,$G,$B);
         imagesetpixel($this->Picture,$X,$Y,$C_Aliased);
        }
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

   /* Validate data contained in the description array */
   function validateDataDescription($FunctionName,&$DataDescription,$DescriptionRequired=TRUE)
    {
     if (!isset($DataDescription["Position"]))
      {
       $this->Errors[] = "[Warning] ".$FunctionName." - Y Labels are not set.";
       $DataDescription["Position"] = "Name";
      }

     if ( $DescriptionRequired )
      {
       if (!isset($DataDescription["Description"]))
        {
         $this->Errors[] = "[Warning] ".$FunctionName." - Series descriptions are not set.";
         foreach($DataDescription["Values"] as $key => $Value)
          {
           $DataDescription["Description"][$Value] = $Value;
          }
        }

       if (count($DataDescription["Description"]) < count($DataDescription["Values"]))
        {
         $this->Errors[] = "[Warning] ".$FunctionName." - Some series descriptions are not set.";
         foreach($DataDescription["Values"] as $key => $Value)
          {
           if ( !isset($DataDescription["Description"][$Value]))
            $DataDescription["Description"][$Value] = $Value;
          }
        }
      }
    }

   /* Validate data contained in the data array */
   function validateData($FunctionName,&$Data)
    {
     $DataSummary = array();

     foreach($Data as $key => $Values)
      {
       foreach($Values as $key2 => $Value)
        {
         if (!isset($DataSummary[$key2]))
          $DataSummary[$key2] = 1;
         else
          $DataSummary[$key2]++;
        }
      }

     if ( max($DataSummary) == 0 )
      $this->Errors[] = "[Warning] ".$FunctionName." - No data set.";

     foreach($DataSummary as $key => $Value)
      {
       if ($Value < max($DataSummary))
        {
         $this->Errors[] = "[Warning] ".$FunctionName." - Missing data in serie ".$key.".";
        }
      }
    }

   /* Print all error messages on the CLI or graphically */
   function printErrors($Mode="CLI")
    {
     if (count($this->Errors) == 0)
      return(0);

     if ( $Mode == "CLI" )
      {
       foreach($this->Errors as $key => $Value)
        echo $Value."\r\n";
      }
     elseif ( $Mode == "GD" )
      {
       $this->setLineStyle($Width=1);
       $MaxWidth = 0;
       foreach($this->Errors as $key => $Value)
        {
         $Position  = imageftbbox($this->ErrorFontSize,0,$this->ErrorFontName,$Value);
         $TextWidth = $Position[2]-$Position[0];
         if ( $TextWidth > $MaxWidth ) { $MaxWidth = $TextWidth; }
        }
       $this->drawFilledRoundedRectangle($this->XSize-($MaxWidth+20),$this->YSize-(20+(($this->ErrorFontSize+4)*count($this->Errors))),$this->XSize-10,$this->YSize-10,6,233,185,185);
       $this->drawRoundedRectangle($this->XSize-($MaxWidth+20),$this->YSize-(20+(($this->ErrorFontSize+4)*count($this->Errors))),$this->XSize-10,$this->YSize-10,6,193,145,145);

       $C_TextColor = $this->AllocateColor($this->Picture,133,85,85);
       $YPos        = $this->YSize - (18 + (count($this->Errors)-1) * ($this->ErrorFontSize + 4));
       foreach($this->Errors as $key => $Value)
        {
         imagettftext($this->Picture,$this->ErrorFontSize,0,$this->XSize-($MaxWidth+15),$YPos,$C_TextColor,$this->ErrorFontName,$Value);
         $YPos = $YPos + ($this->ErrorFontSize + 4);
        }
      }
    }

   /* Activate the image map creation process */
   function setImageMap($Mode=TRUE,$GraphID="MyGraph")
    {
     $this->BuildMap = $Mode;
     $this->MapID    = $GraphID;
    }

   /* Add a box into the image map */
   function addToImageMap($X1,$Y1,$X2,$Y2,$SerieName,$Value,$CallerFunction)
    {
     if ( $this->MapFunction == NULL || $this->MapFunction == $CallerFunction )
      {
       $this->ImageMap[]  = round($X1).",".round($Y1).",".round($X2).",".round($Y2).",".$SerieName.",".$Value;
       $this->MapFunction = $CallerFunction;
      }
    }

   /* Load and cleanup the image map from disk */
   function getImageMap($MapName,$Flush=TRUE)
    {
     /* Strip HTML query strings */
     $Values   = $this->tmpFolder.$MapName;
     $Value    = split("\?",$Values);
     $FileName = $Value[0];

     if ( file_exists($FileName) )
      {
       $Handle     = fopen($FileName, "r");
       $MapContent = fread($Handle, filesize($FileName));
       fclose($Handle);
       echo $MapContent;

       if ( $Flush )
        unlink($FileName);

       exit();
      }
     else
      {
       header("HTTP/1.0 404 Not Found");
       exit();
      }
    }

   /* Save the image map to the disk */
   function SaveImageMap()
    {
     if ( !$this->BuildMap ) { return(-1); }

     if ( $this->ImageMap == NULL )
      {
       $this->Errors[] = "[Warning] SaveImageMap - Image map is empty.";
       return(-1);
      }

     $Handle = fopen($this->tmpFolder.$this->MapID, 'w');
     if ( !$Handle )
      {
       $this->Errors[] = "[Warning] SaveImageMap - Cannot save the image map.";
       return(-1);
      }
     else
      {
       foreach($this->ImageMap as $Key => $Value)
        fwrite($Handle, htmlentities($Value)."\r");
      }
     fclose ($Handle);
    }

   /* Convert seconds to a time format string */
   function ToTime($Value)
    {
     $Hour   = floor($Value/3600);
     $Minute = floor(($Value - $Hour*3600)/60);
     $Second = floor($Value - $Hour*3600 - $Minute*60);

     if (strlen($Hour) == 1 )   { $Hour = "0".$Hour; }
     if (strlen($Minute) == 1 ) { $Minute = "0".$Minute; }
     if (strlen($Second) == 1 ) { $Second = "0".$Second; }

     return($Hour.":".$Minute.":".$Second);
    }

   /* Convert to metric system */
   function ToMetric($Value)
    {
     $Go = floor($Value/1000000000);
     $Mo = floor(($Value - $Go*1000000000)/1000000);
     $Ko = floor(($Value - $Go*1000000000 - $Mo*1000000)/1000);
     $o  = floor($Value - $Go*1000000000 - $Mo*1000000 - $Ko*1000);

     if ($Go != 0)   { return($Go.".".$Mo."g"); }
     if ($Mo != 0)   { return($Mo.".".$ko."m"); }
     if ($Ko != 0)   { return($Ko.".".$o)."k"; }
     return($o);
    }

   /* Convert to curency */
   function ToCurrency($Value)
    {
     $Go = floor($Value/1000000000);
     $Mo = floor(($Value - $Go*1000000000)/1000000);
     $Ko = floor(($Value - $Go*1000000000 - $Mo*1000000)/1000);
     $o  = floor($Value - $Go*1000000000 - $Mo*1000000 - $Ko*1000);

     if ( strlen($o) == 1 ) { $o = "00".$o; }
     if ( strlen($o) == 2 ) { $o = "0".$o; }

     $ResultString = $o;
     if ( $Ko != 0 ) { $ResultString = $Ko.".".$ResultString; }
     if ( $Mo != 0 ) { $ResultString = $Mo.".".$ResultString; }
     if ( $Go != 0 ) { $ResultString = $Go.".".$ResultString; }

     $ResultString = $this->Currency.$ResultString;
     return($ResultString);
    }

   /* Set date format for axis labels */
   function setDateFormat($Format)
    {
     $this->DateFormat = $Format;
    }

   /* Convert TS to a date format string */
   function ToDate($Value)
    {
     return(date($this->DateFormat,$Value));
    }

   /* Check if a number is a full integer (for scaling) */
   function isRealInt($Value)
    {
     if ($Value == floor($Value))
      return(TRUE);
     return(FALSE);
    }
  }

 function RaiseFatal($Message)
  {
   echo "[FATAL] ".$Message."\r\n";
   exit();
  }
?>