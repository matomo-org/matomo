<?php
 /*
     pPie - class to draw pie charts

     Version     : 2.1.3
     Made by     : Jean-Damien POGOLOTTI
     Last Update : 09/09/11

     This file can be distributed under the license you can find at :

                       http://www.pchart.net/license

     You can find the whole class documentation on the pChart web site.
 */

 /* Class return codes */
 define("PIE_NO_ABSCISSA"	, 140001);
 define("PIE_NO_DATASERIE"	, 140002);
 define("PIE_SUMISNULL"		, 140003);
 define("PIE_RENDERED"		, 140000);

 define("PIE_LABEL_COLOR_AUTO"	, 140010);
 define("PIE_LABEL_COLOR_MANUAL", 140011);

 define("PIE_VALUE_NATURAL"	, 140020);
 define("PIE_VALUE_PERCENTAGE"	, 140021);

 define("PIE_VALUE_INSIDE"      , 140030);
 define("PIE_VALUE_OUTSIDE"     , 140031);

 /* pPie class definition */
 class pPie
  {
   var $pChartObject;
   var $pDataObject;
   var $LabelPos = "" ;

   /* Class creator */
   function pPie($Object,$pDataObject)
    {
     /* Cache the pChart object reference */
     $this->pChartObject = $Object;

     /* Cache the pData object reference */
     $this->pDataObject  = $pDataObject;
    }

   /* Draw a pie chart */
   function draw2DPie($X,$Y,$Format="")
    {
     $Radius		= isset($Format["Radius"]) ? $Format["Radius"] : 60;
     $Precision		= isset($Format["Precision"]) ? $Format["Precision"] : 0;
     $DataGapAngle	= isset($Format["DataGapAngle"]) ? $Format["DataGapAngle"] : 0;
     $DataGapRadius	= isset($Format["DataGapRadius"]) ? $Format["DataGapRadius"] : 0;
     $SecondPass	= isset($Format["SecondPass"]) ? $Format["SecondPass"] : TRUE;
     $Border		= isset($Format["Border"]) ? $Format["Border"] : FALSE;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : 255;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : 255;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : 255;
     $Shadow		= isset($Format["Shadow"]) ? $Format["Shadow"] : FALSE;
     $DrawLabels	= isset($Format["DrawLabels"]) ? $Format["DrawLabels"] : FALSE;
     $LabelStacked	= isset($Format["LabelStacked"]) ? $Format["LabelStacked"] : FALSE;
     $LabelColor	= isset($Format["LabelColor"]) ? $Format["LabelColor"] : PIE_LABEL_COLOR_MANUAL;
     $LabelR		= isset($Format["LabelR"]) ? $Format["LabelR"] : 0;
     $LabelG		= isset($Format["LabelG"]) ? $Format["LabelG"] : 0;
     $LabelB		= isset($Format["LabelB"]) ? $Format["LabelB"] : 0;
     $LabelAlpha	= isset($Format["LabelAlpha"]) ? $Format["LabelAlpha"] : 100;
     $WriteValues	= isset($Format["WriteValues"]) ? $Format["WriteValues"] : NULL;
     $ValuePosition	= isset($Format["ValuePosition"]) ? $Format["ValuePosition"] : PIE_VALUE_OUTSIDE;
     $ValuePadding	= isset($Format["ValuePadding"]) ? $Format["ValuePadding"] : 15;
     $ValueSuffix	= isset($Format["ValueSuffix"]) ? $Format["ValueSuffix"] : "";
     $ValueR		= isset($Format["ValueR"]) ? $Format["ValueR"] : 255;
     $ValueG		= isset($Format["ValueG"]) ? $Format["ValueG"] : 255;
     $ValueB		= isset($Format["ValueB"]) ? $Format["ValueB"] : 255;
     $ValueAlpha	= isset($Format["ValueAlpha"]) ? $Format["ValueAlpha"] : 100;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;

     /* Data Processing */
     $Data    = $this->pDataObject->getData();
     $Palette = $this->pDataObject->getPalette();

     /* Do we have an abscissa serie defined? */
     if ( $Data["Abscissa"] == "" ) { return(PIE_NO_ABSCISSA); }

     /* Try to find the data serie */
     $DataSerie = "";
     foreach ($Data["Series"] as $SerieName => $SerieData)
      { if ( $SerieName != $Data["Abscissa"]) { $DataSerie = $SerieName; } }

     /* Do we have data to compute? */
     if ( $DataSerie == "" ) { return(PIE_NO_DATASERIE); }

     /* Remove unused data */
     list($Data,$Palette) = $this->clean0Values($Data,$Palette,$DataSerie,$Data["Abscissa"]);

     /* Compute the pie sum */
     $SerieSum = $this->pDataObject->getSum($DataSerie);

     /* Do we have data to draw? */
     if ( $SerieSum == 0 ) { return(PIE_SUMISNULL); }

     /* Dump the real number of data to draw */
     $Values = "";
     foreach ($Data["Series"][$DataSerie]["Data"] as $Key => $Value)
      { if ($Value != 0) { $Values[] = $Value; } }

     /* Compute the wasted angular space between series */
     if (count($Values)==1) { $WastedAngular = 0; } else { $WastedAngular = count($Values) * $DataGapAngle; }

     /* Compute the scale */
     $ScaleFactor = (360 - $WastedAngular) / $SerieSum;

     $RestoreShadow = $this->pChartObject->Shadow;
     if ( $this->pChartObject->Shadow )
      {
       $this->pChartObject->Shadow = FALSE;

       $ShadowFormat = $Format; $ShadowFormat["Shadow"] = TRUE;
       $this->draw2DPie($X+$this->pChartObject->ShadowX,$Y+$this->pChartObject->ShadowY,$ShadowFormat);
      }

     /* Draw the polygon pie elements */
     $Step = 360 / (2 * PI * $Radius);
     $Offset = 0; $ID = 0;
     foreach($Values as $Key => $Value)
      {
       if ( $Shadow )
        $Settings = array("R"=>$this->pChartObject->ShadowR,"G"=>$this->pChartObject->ShadowG,"B"=>$this->pChartObject->ShadowB,"Alpha"=>$this->pChartObject->Shadowa);
       else
        {
         if ( !isset($Palette[$ID]["R"]) ) { $Color = $this->pChartObject->getRandomColor(); $Palette[$ID] = $Color; $this->pDataObject->savePalette($ID,$Color); }
         $Settings = array("R"=>$Palette[$ID]["R"],"G"=>$Palette[$ID]["G"],"B"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);
        }

       if ( !$SecondPass && !$Shadow )
        {
         if ( !$Border )
          $Settings["Surrounding"] = 10;
         else
          { $Settings["BorderR"] = $BorderR; $Settings["BorderG"] = $BorderG; $Settings["BorderB"] = $BorderB; }
        }

       $Plots = "";
       $EndAngle = $Offset+($Value*$ScaleFactor); if ( $EndAngle > 360 ) { $EndAngle = 360; }

       $Angle = ($EndAngle - $Offset)/2 + $Offset;
       if ($DataGapAngle == 0)
        { $X0 = $X; $Y0 = $Y; }
       else
        {
         $X0 = cos(($Angle-90)*PI/180) * $DataGapRadius + $X;
         $Y0 = sin(($Angle-90)*PI/180) * $DataGapRadius + $Y;
        }

       $Plots[] = $X0; $Plots[] = $Y0;


       for($i=$Offset;$i<=$EndAngle;$i=$i+$Step)
        {
         $Xc = cos(($i-90)*PI/180) * $Radius + $X;
         $Yc = sin(($i-90)*PI/180) * $Radius + $Y;

         if ( $SecondPass && ( $i<90 )) { $Yc++; }
         if ( $SecondPass && ( $i>180 && $i<270 )) { $Xc++; }
         if ( $SecondPass && ( $i>=270 )) { $Xc++; $Yc++; }

         $Plots[] = $Xc; $Plots[] = $Yc;
        }

       $this->pChartObject->drawPolygon($Plots,$Settings);
       if ( $RecordImageMap && !$Shadow ) { $this->pChartObject->addToImageMap("POLY",$this->arraySerialize($Plots),$this->pChartObject->toHTMLColor($Palette[$ID]["R"],$Palette[$ID]["G"],$Palette[$ID]["B"]),$Data["Series"][$Data["Abscissa"]]["Data"][$Key],$Value); }

       if ( $DrawLabels && !$Shadow && !$SecondPass )
        {
         if ( $LabelColor == PIE_LABEL_COLOR_AUTO )
          { $Settings = array("FillR"=>$Palette[$ID]["R"],"FillG"=>$Palette[$ID]["G"],"FillB"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);}
         else
          { $Settings = array("FillR"=>$LabelR,"FillG"=>$LabelG,"FillB"=>$LabelB,"Alpha"=>$LabelAlpha); }

         $Angle = ($EndAngle - $Offset)/2 + $Offset;
         $Xc = cos(($Angle-90)*PI/180) * $Radius + $X;
         $Yc = sin(($Angle-90)*PI/180) * $Radius + $Y;

         $Label = $Data["Series"][$Data["Abscissa"]]["Data"][$Key];

         if ( $LabelStacked )
          $this->writePieLabel($Xc,$Yc,$Label,$Angle,$Settings,TRUE,$X,$Y,$Radius);
         else
          $this->writePieLabel($Xc,$Yc,$Label,$Angle,$Settings,FALSE);
        }

       $Offset = $i + $DataGapAngle; $ID++;
      }

     /* Second pass to smooth the angles */
     if ( $SecondPass )
      {
       $Step = 360 / (2 * PI * $Radius);
       $Offset = 0; $ID = 0;
       foreach($Values as $Key => $Value)
        {
         $FirstPoint = TRUE;
         if ( $Shadow )
          $Settings = array("R"=>$this->pChartObject->ShadowR,"G"=>$this->pChartObject->ShadowG,"B"=>$this->pChartObject->ShadowB,"Alpha"=>$this->pChartObject->Shadowa);
         else
          {
           if ( $Border )
            $Settings = array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB);
           else
            $Settings = array("R"=>$Palette[$ID]["R"],"G"=>$Palette[$ID]["G"],"B"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);
          }

         $EndAngle = $Offset+($Value*$ScaleFactor); if ( $EndAngle > 360 ) { $EndAngle = 360; }

         if ($DataGapAngle == 0)
          { $X0 = $X; $Y0 = $Y; }
         else
          {
           $Angle = ($EndAngle - $Offset)/2 + $Offset;
           $X0 = cos(($Angle-90)*PI/180) * $DataGapRadius + $X;
           $Y0 = sin(($Angle-90)*PI/180) * $DataGapRadius + $Y;
          }
         $Plots[] = $X0; $Plots[] = $Y0;

         for($i=$Offset;$i<=$EndAngle;$i=$i+$Step)
          {
           $Xc = cos(($i-90)*PI/180) * $Radius + $X;
           $Yc = sin(($i-90)*PI/180) * $Radius + $Y;

           if ( $FirstPoint ) { $this->pChartObject->drawLine($Xc,$Yc,$X0,$Y0,$Settings); } { $FirstPoint = FALSE; }
  
           $this->pChartObject->drawAntialiasPixel($Xc,$Yc,$Settings);
          }
         $this->pChartObject->drawLine($Xc,$Yc,$X0,$Y0,$Settings);

         if ( $DrawLabels && !$Shadow )
          {
           if ( $LabelColor == PIE_LABEL_COLOR_AUTO )
            { $Settings = array("FillR"=>$Palette[$ID]["R"],"FillG"=>$Palette[$ID]["G"],"FillB"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);}
           else
            { $Settings = array("FillR"=>$LabelR,"FillG"=>$LabelG,"FillB"=>$LabelB,"Alpha"=>$LabelAlpha); }

           $Angle = ($EndAngle - $Offset)/2 + $Offset;
           $Xc = cos(($Angle-90)*PI/180) * $Radius + $X;
           $Yc = sin(($Angle-90)*PI/180) * $Radius + $Y;

           $Label = $Data["Series"][$Data["Abscissa"]]["Data"][$Key];

           if ( $LabelStacked )
            $this->writePieLabel($Xc,$Yc,$Label,$Angle,$Settings,TRUE,$X,$Y,$Radius);
           else
            $this->writePieLabel($Xc,$Yc,$Label,$Angle,$Settings,FALSE);
          }
  
         $Offset = $i + $DataGapAngle; $ID++;
        }
      }

     if ( $WriteValues != NULL && !$Shadow )
      {
       $Step = 360 / (2 * PI * $Radius);
       $Offset = 0; $ID = count($Values)-1;
       $Settings = array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"R"=>$ValueR,"G"=>$ValueG,"B"=>$ValueB,"Alpha"=>$ValueAlpha);
       foreach($Values as $Key => $Value)
        {
         $EndAngle = ($Value*$ScaleFactor) + $Offset; if ( $EndAngle > 360 ) { $EndAngle = 0; }
         $Angle    = ($EndAngle - $Offset)/2 + $Offset;

         if ( $ValuePosition == PIE_VALUE_OUTSIDE )
          {
           $Xc = cos(($Angle-90)*PI/180) * ($Radius+$ValuePadding) + $X;
           $Yc = sin(($Angle-90)*PI/180) * ($Radius+$ValuePadding) + $Y;
          }
         else
          {
           $Xc = cos(($Angle-90)*PI/180) * ($Radius)/2 + $X;
           $Yc = sin(($Angle-90)*PI/180) * ($Radius)/2 + $Y;
          }

         if ( $WriteValues == PIE_VALUE_PERCENTAGE )
          $Display = round(( 100 / $SerieSum ) * $Value,$Precision)."%";
         elseif ( $WriteValues == PIE_VALUE_NATURAL )
          $Display = $Value.$ValueSuffix;

         $this->pChartObject->drawText($Xc,$Yc,$Display,$Settings);

         $Offset = $EndAngle + $DataGapAngle; $ID--;
        }
      }

     if ( $DrawLabels && $LabelStacked ) { $this->writeShiftedLabels(); }

     $this->pChartObject->Shadow = $RestoreShadow;

     return(PIE_RENDERED);
    }

   /* Draw a 3D pie chart */
   function draw3DPie($X,$Y,$Format="")
    {
     /* Rendering layout */
     $Radius		= isset($Format["Radius"]) ? $Format["Radius"] : 80;
     $Precision		= isset($Format["Precision"]) ? $Format["Precision"] : 0;
     $SkewFactor	= isset($Format["SkewFactor"]) ? $Format["SkewFactor"] : .5;
     $SliceHeight	= isset($Format["SliceHeight"]) ? $Format["SliceHeight"] : 20;
     $DataGapAngle	= isset($Format["DataGapAngle"]) ? $Format["DataGapAngle"] : 0;
     $DataGapRadius	= isset($Format["DataGapRadius"]) ? $Format["DataGapRadius"] : 0;
     $SecondPass	= isset($Format["SecondPass"]) ? $Format["SecondPass"] : TRUE;
     $Border		= isset($Format["Border"]) ? $Format["Border"] : FALSE;
     $Shadow		= isset($Format["Shadow"]) ? $Format["Shadow"] : FALSE;
     $DrawLabels	= isset($Format["DrawLabels"]) ? $Format["DrawLabels"] : FALSE;
     $LabelStacked	= isset($Format["LabelStacked"]) ? $Format["LabelStacked"] : FALSE;
     $LabelColor	= isset($Format["LabelColor"]) ? $Format["LabelColor"] : PIE_LABEL_COLOR_MANUAL;
     $LabelR		= isset($Format["LabelR"]) ? $Format["LabelR"] : 0;
     $LabelG		= isset($Format["LabelG"]) ? $Format["LabelG"] : 0;
     $LabelB		= isset($Format["LabelB"]) ? $Format["LabelB"] : 0;
     $LabelAlpha	= isset($Format["LabelAlpha"]) ? $Format["LabelAlpha"] : 100;
     $WriteValues	= isset($Format["WriteValues"]) ? $Format["WriteValues"] : NULL; //PIE_VALUE_PERCENTAGE
     $ValuePosition	= isset($Format["ValuePosition"]) ? $Format["ValuePosition"] : PIE_VALUE_INSIDE;
     $ValuePadding	= isset($Format["ValuePadding"]) ? $Format["ValuePadding"] : 15;
     $ValueSuffix	= isset($Format["ValueSuffix"]) ? $Format["ValueSuffix"] : "";
     $ValueR		= isset($Format["ValueR"]) ? $Format["ValueR"] : 255;
     $ValueG		= isset($Format["ValueG"]) ? $Format["ValueG"] : 255;
     $ValueB		= isset($Format["ValueB"]) ? $Format["ValueB"] : 255;
     $ValueAlpha	= isset($Format["ValueAlpha"]) ? $Format["ValueAlpha"] : 100;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;

     /* Error correction for overlaying rounded corners */
     if ( $SkewFactor < .5 ) { $SkewFactor = .5; }

     /* Data Processing */
     $Data    = $this->pDataObject->getData();
     $Palette = $this->pDataObject->getPalette();

     /* Do we have an abscissa serie defined? */
     if ( $Data["Abscissa"] == "" ) { return(PIE_NO_ABSCISSA); }

     /* Try to find the data serie */
     $DataSerie = "";
     foreach ($Data["Series"] as $SerieName => $SerieData)
      { if ( $SerieName != $Data["Abscissa"]) { $DataSerie = $SerieName; } }

     /* Do we have data to compute? */
     if ( $DataSerie == "" ) { return(PIE_NO_DATASERIE); }

     /* Remove unused data */
     list($Data,$Palette) = $this->clean0Values($Data,$Palette,$DataSerie,$Data["Abscissa"]);

     /* Compute the pie sum */
     $SerieSum = $this->pDataObject->getSum($DataSerie);

     /* Do we have data to draw? */
     if ( $SerieSum == 0 ) { return(PIE_SUMISNULL); }

     /* Dump the real number of data to draw */
     $Values = "";
     foreach ($Data["Series"][$DataSerie]["Data"] as $Key => $Value)
      { if ($Value != 0) { $Values[] = $Value; } }

     /* Compute the wasted angular space between series */
     if (count($Values)==1) { $WastedAngular = 0; } else { $WastedAngular = count($Values) * $DataGapAngle; }

     /* Compute the scale */
     $ScaleFactor = (360 - $WastedAngular) / $SerieSum;

     $RestoreShadow = $this->pChartObject->Shadow;
     if ( $this->pChartObject->Shadow ) { $this->pChartObject->Shadow = FALSE; }

     /* Draw the polygon pie elements */
     $Step   = 360 / (2 * PI * $Radius);
     $Offset = 360; $ID = count($Values)-1;
     $Values = array_reverse($Values);
     $Slice  = 0; $Slices = ""; $SliceColors = ""; $Visible = ""; $SliceAngle = "";
     foreach($Values as $Key => $Value)
      {
       if ( !isset($Palette[$ID]["R"]) ) { $Color = $this->pChartObject->getRandomColor(); $Palette[$ID] = $Color; $this->pDataObject->savePalette($ID,$Color); }
       $Settings = array("R"=>$Palette[$ID]["R"],"G"=>$Palette[$ID]["G"],"B"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);

       $SliceColors[$Slice] = $Settings;

       $StartAngle = $Offset;
       $EndAngle   = $Offset-($Value*$ScaleFactor); if ( $EndAngle < 0 ) { $EndAngle = 0; }

       if ( $StartAngle > 180 ) { $Visible[$Slice]["Start"] = TRUE; } else { $Visible[$Slice]["Start"] = TRUE; }
       if ( $EndAngle < 180 )   { $Visible[$Slice]["End"] = FALSE; } else { $Visible[$Slice]["End"] = TRUE; }

       if ($DataGapAngle == 0)
        { $X0 = $X; $Y0 = $Y; }
       else
        {
         $Angle = ($EndAngle - $Offset)/2 + $Offset;
         $X0 = cos(($Angle-90)*PI/180) * $DataGapRadius + $X;
         $Y0 = sin(($Angle-90)*PI/180) * $DataGapRadius*$SkewFactor + $Y;
        }
       $Slices[$Slice][] = $X0; $Slices[$Slice][] = $Y0; $SliceAngle[$Slice][] = 0;

       for($i=$Offset;$i>=$EndAngle;$i=$i-$Step)
        {
         $Xc = cos(($i-90)*PI/180) * $Radius + $X;
         $Yc = sin(($i-90)*PI/180) * $Radius*$SkewFactor + $Y;

         if ( ($SecondPass || $RestoreShadow ) && ( $i<90 )) { $Yc++; }
         if ( ($SecondPass || $RestoreShadow ) && ( $i>90 && $i<180 )) { $Xc++; }
         if ( ($SecondPass || $RestoreShadow ) && ( $i>180 && $i<270 )) { $Xc++; }
         if ( ($SecondPass || $RestoreShadow ) && ( $i>=270 )) { $Xc++; $Yc++; }

         $Slices[$Slice][] = $Xc; $Slices[$Slice][] = $Yc; $SliceAngle[$Slice][] = $i;
        }

       $Offset = $i - $DataGapAngle; $ID--; $Slice++;
      }

     /* Draw the bottom shadow if needed */
     if ( $RestoreShadow && ($this->pChartObject->ShadowX != 0 || $this->pChartObject->ShadowY !=0 ))
      {
       foreach($Slices as $SliceID => $Plots)
        {
         $ShadowPie = "";
         for($i=0;$i<count($Plots);$i=$i+2)
          { $ShadowPie[] = $Plots[$i]+$this->pChartObject->ShadowX; $ShadowPie[] = $Plots[$i+1]+$this->pChartObject->ShadowY; }

         $Settings = array("R"=>$this->pChartObject->ShadowR,"G"=>$this->pChartObject->ShadowG,"B"=>$this->pChartObject->ShadowB,"Alpha"=>$this->pChartObject->Shadowa,"NoBorder"=>TRUE);
         $this->pChartObject->drawPolygon($ShadowPie,$Settings);
        }

       $Step = 360 / (2 * PI * $Radius);
       $Offset = 360;
       foreach($Values as $Key => $Value)
        {
         $EndAngle = $Offset-($Value*$ScaleFactor); if ( $EndAngle < 0 ) { $EndAngle = 0; }

         for($i=$Offset;$i>=$EndAngle;$i=$i-$Step)
          {
           $Xc = cos(($i-90)*PI/180) * $Radius + $X + $this->pChartObject->ShadowX;
           $Yc = sin(($i-90)*PI/180) * $Radius*$SkewFactor + $Y + $this->pChartObject->ShadowY;

           $this->pChartObject->drawAntialiasPixel($Xc,$Yc,$Settings);
          }

         $Offset = $i - $DataGapAngle; $ID--;
        }
      }

     /* Draw the bottom pie splice */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];  $Settings["NoBorder"] = TRUE;
       $this->pChartObject->drawPolygon($Plots,$Settings);

       if ( $SecondPass )
        {
         $Settings = $SliceColors[$SliceID];
         if ( $Border )
          { $Settings["R"]+= 30; $Settings["G"]+= 30; $Settings["B"]+= 30;; }
  
         if ( isset($SliceAngle[$SliceID][1]) ) /* Empty error handling */
          {
           $Angle = $SliceAngle[$SliceID][1];
           $Xc = cos(($Angle-90)*PI/180) * $Radius + $X;
           $Yc = sin(($Angle-90)*PI/180) * $Radius*$SkewFactor + $Y;
           $this->pChartObject->drawLine($Plots[0],$Plots[1],$Xc,$Yc,$Settings);

           $Angle = $SliceAngle[$SliceID][count($SliceAngle[$SliceID])-1];
           $Xc = cos(($Angle-90)*PI/180) * $Radius + $X;
           $Yc = sin(($Angle-90)*PI/180) * $Radius*$SkewFactor + $Y;
           $this->pChartObject->drawLine($Plots[0],$Plots[1],$Xc,$Yc,$Settings);
          }
        }
      }

     /* Draw the two vertical edges */
     $Slices      = array_reverse($Slices);
     $SliceColors = array_reverse($SliceColors);
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];
       $Settings["R"]+= 10; $Settings["G"]+= 10; $Settings["B"]+= 10; $Settings["NoBorder"] = TRUE;

       if ( $Visible[$SliceID]["Start"] && isset($Plots[2])) /* Empty error handling */
        {
         $this->pChartObject->drawLine($Plots[2],$Plots[3],$Plots[2],$Plots[3]- $SliceHeight,array("R"=>$Settings["R"],"G"=>$Settings["G"],"B"=>$Settings["B"]));
         $Border = "";
         $Border[] = $Plots[0]; $Border[] = $Plots[1]; $Border[] = $Plots[0]; $Border[] = $Plots[1] - $SliceHeight;
         $Border[] = $Plots[2]; $Border[] = $Plots[3] - $SliceHeight; $Border[] = $Plots[2]; $Border[] = $Plots[3]; 
         $this->pChartObject->drawPolygon($Border,$Settings);
        }
      }

     $Slices      = array_reverse($Slices);
     $SliceColors = array_reverse($SliceColors);
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];
       $Settings["R"]+= 10; $Settings["G"]+= 10; $Settings["B"]+= 10; $Settings["NoBorder"] = TRUE;
       if ( $Visible[$SliceID]["End"] )
        {
         $this->pChartObject->drawLine($Plots[count($Plots)-2],$Plots[count($Plots)-1],$Plots[count($Plots)-2],$Plots[count($Plots)-1]- $SliceHeight,array("R"=>$Settings["R"],"G"=>$Settings["G"],"B"=>$Settings["B"]));

         $Border = "";
         $Border[] = $Plots[0]; $Border[] = $Plots[1]; $Border[] = $Plots[0]; $Border[] = $Plots[1] - $SliceHeight;
         $Border[] = $Plots[count($Plots)-2]; $Border[] = $Plots[count($Plots)-1] - $SliceHeight; $Border[] = $Plots[count($Plots)-2]; $Border[] = $Plots[count($Plots)-1]; 
         $this->pChartObject->drawPolygon($Border,$Settings);
        }
      }

     /* Draw the rounded edges */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];
       $Settings["R"]+= 10; $Settings["G"]+= 10; $Settings["B"]+= 10; $Settings["NoBorder"] = TRUE;

       for ($j=2;$j<count($Plots)-2;$j=$j+2)
        {
         $Angle = $SliceAngle[$SliceID][$j/2];
         if ( $Angle < 270 && $Angle > 90 )
          {
           $Border = "";
           $Border[] = $Plots[$j];   $Border[] = $Plots[$j+1];
           $Border[] = $Plots[$j+2]; $Border[] = $Plots[$j+3];
           $Border[] = $Plots[$j+2]; $Border[] = $Plots[$j+3] - $SliceHeight;
           $Border[] = $Plots[$j];   $Border[] = $Plots[$j+1] - $SliceHeight;
           $this->pChartObject->drawPolygon($Border,$Settings);
          }
        }

       if ( $SecondPass )
        {
         $Settings = $SliceColors[$SliceID];
         if ( $Border )
          { $Settings["R"]+= 30; $Settings["G"]+= 30; $Settings["B"]+= 30; }
  
         if ( isset($SliceAngle[$SliceID][1]) ) /* Empty error handling */
          {
           $Angle = $SliceAngle[$SliceID][1];
           if ( $Angle < 270 && $Angle > 90 )
            {
             $Xc = cos(($Angle-90)*PI/180) * $Radius + $X;
             $Yc = sin(($Angle-90)*PI/180) * $Radius*$SkewFactor + $Y;
             $this->pChartObject->drawLine($Xc,$Yc,$Xc,$Yc-$SliceHeight,$Settings);
            }
          }

         $Angle = $SliceAngle[$SliceID][count($SliceAngle[$SliceID])-1];
         if ( $Angle < 270 && $Angle > 90 )
          {
           $Xc = cos(($Angle-90)*PI/180) * $Radius + $X;
           $Yc = sin(($Angle-90)*PI/180) * $Radius*$SkewFactor + $Y;
           $this->pChartObject->drawLine($Xc,$Yc,$Xc,$Yc-$SliceHeight,$Settings);
          }

         if ( isset($SliceAngle[$SliceID][1]) && $SliceAngle[$SliceID][1] > 270 && $SliceAngle[$SliceID][count($SliceAngle[$SliceID])-1] < 270 )
          {
           $Xc = cos((270-90)*PI/180) * $Radius + $X;
           $Yc = sin((270-90)*PI/180) * $Radius*$SkewFactor + $Y;
           $this->pChartObject->drawLine($Xc,$Yc,$Xc,$Yc-$SliceHeight,$Settings);
          }

         if ( isset($SliceAngle[$SliceID][1]) && $SliceAngle[$SliceID][1] > 90 && $SliceAngle[$SliceID][count($SliceAngle[$SliceID])-1] < 90 )
          {
           $Xc = cos((0)*PI/180) * $Radius + $X;
           $Yc = sin((0)*PI/180) * $Radius*$SkewFactor + $Y;
           $this->pChartObject->drawLine($Xc,$Yc,$Xc,$Yc-$SliceHeight,$Settings);
          }

        }
      }

     /* Draw the top splice */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];
       $Settings["R"]+= 20; $Settings["G"]+= 20; $Settings["B"]+= 20;

       $Top = "";
       for($j=0;$j<count($Plots);$j=$j+2) { $Top[] = $Plots[$j]; $Top[] = $Plots[$j+1]- $SliceHeight; }
       $this->pChartObject->drawPolygon($Top,$Settings);

       if ( $RecordImageMap && !$Shadow ) { $this->pChartObject->addToImageMap("POLY",$this->arraySerialize($Top),$this->pChartObject->toHTMLColor($Settings["R"],$Settings["G"],$Settings["B"]),$Data["Series"][$Data["Abscissa"]]["Data"][count($Slices)-$SliceID-1],$Values[$SliceID]); }
      }


     /* Second pass to smooth the angles */
     if ( $SecondPass )
      {
       $Step = 360 / (2 * PI * $Radius);
       $Offset = 360; $ID = count($Values)-1;
       foreach($Values as $Key => $Value)
        {
         $FirstPoint = TRUE;
         if ( $Shadow )
          $Settings = array("R"=>$this->pChartObject->ShadowR,"G"=>$this->pChartObject->ShadowG,"B"=>$this->pChartObject->ShadowB,"Alpha"=>$this->pChartObject->Shadowa);
         else
          {
           if ( $Border )
            { $Settings = array("R"=>$Palette[$ID]["R"]+30,"G"=>$Palette[$ID]["G"]+30,"B"=>$Palette[$ID]["B"]+30,"Alpha"=>$Palette[$ID]["Alpha"]); }
           else
            $Settings = array("R"=>$Palette[$ID]["R"],"G"=>$Palette[$ID]["G"],"B"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);
          }

         $EndAngle = $Offset-($Value*$ScaleFactor); if ( $EndAngle < 0 ) { $EndAngle = 0; }

         if ($DataGapAngle == 0)
          { $X0 = $X; $Y0 = $Y- $SliceHeight; }
         else
          {
           $Angle = ($EndAngle - $Offset)/2 + $Offset;
           $X0 = cos(($Angle-90)*PI/180) * $DataGapRadius + $X;
           $Y0 = sin(($Angle-90)*PI/180) * $DataGapRadius*$SkewFactor + $Y - $SliceHeight;
          }
         $Plots[] = $X0; $Plots[] = $Y0;

         for($i=$Offset;$i>=$EndAngle;$i=$i-$Step)
          {
           $Xc = cos(($i-90)*PI/180) * $Radius + $X;
           $Yc = sin(($i-90)*PI/180) * $Radius*$SkewFactor + $Y - $SliceHeight;

           if ( $FirstPoint ) { $this->pChartObject->drawLine($Xc,$Yc,$X0,$Y0,$Settings); } { $FirstPoint = FALSE; }
  
           $this->pChartObject->drawAntialiasPixel($Xc,$Yc,$Settings);
           if ($i < 270 && $i > 90 ) { $this->pChartObject->drawAntialiasPixel($Xc,$Yc+$SliceHeight,$Settings); }
          }
         $this->pChartObject->drawLine($Xc,$Yc,$X0,$Y0,$Settings);

         $Offset = $i - $DataGapAngle; $ID--;
        }
      }

     if ( $WriteValues != NULL )
      {
       $Step = 360 / (2 * PI * $Radius);
       $Offset = 360; $ID = count($Values)-1;
       $Settings = array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE,"R"=>$ValueR,"G"=>$ValueG,"B"=>$ValueB,"Alpha"=>$ValueAlpha);
       foreach($Values as $Key => $Value)
        {
         $EndAngle = $Offset-($Value*$ScaleFactor); if ( $EndAngle < 0 ) { $EndAngle = 0; }

         $Angle = ($EndAngle - $Offset)/2 + $Offset;

         if ( $ValuePosition == PIE_VALUE_OUTSIDE )
          {
           $Xc = cos(($Angle-90)*PI/180) * ($Radius+$ValuePadding) + $X;
           $Yc = sin(($Angle-90)*PI/180) * (($Radius*$SkewFactor)+$ValuePadding) + $Y - $SliceHeight;
          }
         else
          {
           $Xc = cos(($Angle-90)*PI/180) * ($Radius)/2 + $X;
           $Yc = sin(($Angle-90)*PI/180) * ($Radius*$SkewFactor)/2 + $Y - $SliceHeight;
          }

         if ( $WriteValues == PIE_VALUE_PERCENTAGE )
          $Display = round(( 100 / $SerieSum ) * $Value,$Precision)."%";
         elseif ( $WriteValues == PIE_VALUE_NATURAL )
          $Display = $Value.$ValueSuffix;

         $this->pChartObject->drawText($Xc,$Yc,$Display,$Settings);

         $Offset = $EndAngle - $DataGapAngle; $ID--;
        }
      }

     if ( $DrawLabels )
      {
       $Step = 360 / (2 * PI * $Radius);
       $Offset = 360; $ID = count($Values)-1;
       foreach($Values as $Key => $Value)
        {
         if ( $LabelColor == PIE_LABEL_COLOR_AUTO )
          { $Settings = array("FillR"=>$Palette[$ID]["R"],"FillG"=>$Palette[$ID]["G"],"FillB"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);}
         else
          { $Settings = array("FillR"=>$LabelR,"FillG"=>$LabelG,"FillB"=>$LabelB,"Alpha"=>$LabelAlpha); }

         $EndAngle = $Offset-($Value*$ScaleFactor); if ( $EndAngle < 0 ) { $EndAngle = 0; }

         $Angle = ($EndAngle - $Offset)/2 + $Offset;
         $Xc = cos(($Angle-90)*PI/180) * $Radius + $X;
         $Yc = sin(($Angle-90)*PI/180) * $Radius*$SkewFactor + $Y - $SliceHeight;

         if ( isset($Data["Series"][$Data["Abscissa"]]["Data"][$ID]) )
          {
           $Label = $Data["Series"][$Data["Abscissa"]]["Data"][$ID];

           if ( $LabelStacked )
            $this->writePieLabel($Xc,$Yc,$Label,$Angle,$Settings,TRUE,$X,$Y,$Radius,TRUE);
           else
            $this->writePieLabel($Xc,$Yc,$Label,$Angle,$Settings,FALSE);
          }

         $Offset = $EndAngle - $DataGapAngle; $ID--;
        }
      }

     if ( $DrawLabels && $LabelStacked ) { $this->writeShiftedLabels(); }

     $this->pChartObject->Shadow = $RestoreShadow;

     return(PIE_RENDERED);
    }

   /* Draw the legend of pie chart */
   function drawPieLegend($X,$Y,$Format="")
    {
     $FontName		= isset($Format["FontName"]) ? $Format["FontName"] : $this->pChartObject->FontName;
     $FontSize		= isset($Format["FontSize"]) ? $Format["FontSize"] : $this->pChartObject->FontSize;
     $FontR		= isset($Format["FontR"]) ? $Format["FontR"] : $this->pChartObject->FontColorR;
     $FontG		= isset($Format["FontG"]) ? $Format["FontG"] : $this->pChartObject->FontColorG;
     $FontB		= isset($Format["FontB"]) ? $Format["FontB"] : $this->pChartObject->FontColorB;
     $BoxSize		= isset($Format["BoxSize"]) ? $Format["BoxSize"] : 5;
     $Margin		= isset($Format["Margin"]) ? $Format["Margin"] : 5;
     $R			= isset($Format["R"]) ? $Format["R"] : 200;
     $G			= isset($Format["G"]) ? $Format["G"] : 200;
     $B			= isset($Format["B"]) ? $Format["B"] : 200;
     $Alpha		= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : 255;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : 255;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : 255;
     $Surrounding	= isset($Format["Surrounding"]) ? $Format["Surrounding"] : NULL;
     $Style		= isset($Format["Style"]) ? $Format["Style"] : LEGEND_ROUND;
     $Mode		= isset($Format["Mode"]) ? $Format["Mode"] : LEGEND_VERTICAL;

     if ( $Surrounding != NULL ) { $BorderR = $R + $Surrounding; $BorderG = $G + $Surrounding; $BorderB = $B + $Surrounding; }

     $YStep = max($this->pChartObject->FontSize,$BoxSize) + 5;
     $XStep = $BoxSize + 5;

     /* Data Processing */
     $Data    = $this->pDataObject->getData();
     $Palette = $this->pDataObject->getPalette();

     /* Do we have an abscissa serie defined? */
     if ( $Data["Abscissa"] == "" ) { return(PIE_NO_ABSCISSA); }

     $Boundaries = ""; $Boundaries["L"] = $X; $Boundaries["T"] = $Y; $Boundaries["R"] = 0; $Boundaries["B"] = 0; $vY = $Y; $vX = $X;
     foreach($Data["Series"][$Data["Abscissa"]]["Data"] as $Key => $Value)
      {
       $BoxArray = $this->pChartObject->getTextBox($vX+$BoxSize+4,$vY+$BoxSize/2,$FontName,$FontSize,0,$Value);

       if ( $Mode == LEGEND_VERTICAL )
        {
         if ( $Boundaries["T"] > $BoxArray[2]["Y"]+$BoxSize/2 ) { $Boundaries["T"] = $BoxArray[2]["Y"]+$BoxSize/2; }
         if ( $Boundaries["R"] < $BoxArray[1]["X"]+2 ) { $Boundaries["R"] = $BoxArray[1]["X"]+2; }
         if ( $Boundaries["B"] < $BoxArray[1]["Y"]+2+$BoxSize/2 ) { $Boundaries["B"] = $BoxArray[1]["Y"]+2+$BoxSize/2; }
         $vY=$vY+$YStep;
        }
       elseif ( $Mode == LEGEND_HORIZONTAL )
        {
         if ( $Boundaries["T"] > $BoxArray[2]["Y"]+$BoxSize/2 ) { $Boundaries["T"] = $BoxArray[2]["Y"]+$BoxSize/2; }
         if ( $Boundaries["R"] < $BoxArray[1]["X"]+2 ) { $Boundaries["R"] = $BoxArray[1]["X"]+2; }
         if ( $Boundaries["B"] < $BoxArray[1]["Y"]+2+$BoxSize/2 ) { $Boundaries["B"] = $BoxArray[1]["Y"]+2+$BoxSize/2; }
         $vX=$Boundaries["R"]+$XStep;
        }
      }
     $vY=$vY-$YStep; $vX=$vX-$XStep;

     $TopOffset  = $Y - $Boundaries["T"];
     if ( $Boundaries["B"]-($vY+$BoxSize) < $TopOffset ) { $Boundaries["B"] = $vY+$BoxSize+$TopOffset; }

     if ( $Style == LEGEND_ROUND )
      $this->pChartObject->drawRoundedFilledRectangle($Boundaries["L"]-$Margin,$Boundaries["T"]-$Margin,$Boundaries["R"]+$Margin,$Boundaries["B"]+$Margin,$Margin,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"BorderR"=>$BorderR,"BorderG"=>$BorderG,"BorderB"=>$BorderB));
     elseif ( $Style == LEGEND_BOX )
      $this->pChartObject->drawFilledRectangle($Boundaries["L"]-$Margin,$Boundaries["T"]-$Margin,$Boundaries["R"]+$Margin,$Boundaries["B"]+$Margin,array("R"=>$R,"G"=>$G,"B"=>$B,"Alpha"=>$Alpha,"BorderR"=>$BorderR,"BorderG"=>$BorderG,"BorderB"=>$BorderB));

     $RestoreShadow = $this->pChartObject->Shadow; $this->pChartObject->Shadow = FALSE;
     foreach($Data["Series"][$Data["Abscissa"]]["Data"] as $Key => $Value)
      {
       $R = $Palette[$Key]["R"]; $G = $Palette[$Key]["G"]; $B = $Palette[$Key]["B"];

       $this->pChartObject->drawFilledRectangle($X+1,$Y+1,$X+$BoxSize+1,$Y+$BoxSize+1,array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
       $this->pChartObject->drawFilledRectangle($X,$Y,$X+$BoxSize,$Y+$BoxSize,array("R"=>$R,"G"=>$G,"B"=>$B,"Surrounding"=>20));
       if ( $Mode == LEGEND_VERTICAL )
        {
         $this->pChartObject->drawText($X+$BoxSize+4,$Y+$BoxSize/2,$Value,array("R"=>$FontR,"G"=>$FontG,"B"=>$FontB,"Align"=>TEXT_ALIGN_MIDDLELEFT,"FontName"=>$FontName,"FontSize"=>$FontSize));
         $Y=$Y+$YStep;
        }
       elseif ( $Mode == LEGEND_HORIZONTAL )
        {
         $BoxArray = $this->pChartObject->drawText($X+$BoxSize+4,$Y+$BoxSize/2,$Value,array("R"=>$FontR,"G"=>$FontG,"B"=>$FontB,"Align"=>TEXT_ALIGN_MIDDLELEFT,"FontName"=>$FontName,"FontSize"=>$FontSize));
         $X=$BoxArray[1]["X"]+2+$XStep;
        }
      }

     $this->Shadow = $RestoreShadow;
    }

   /* Set the color of the specified slice */
   function setSliceColor($SliceID,$Format="")
    {
     $R		= isset($Format["R"]) ? $Format["R"] : 0;
     $G		= isset($Format["G"]) ? $Format["G"] : 0;
     $B		= isset($Format["B"]) ? $Format["B"] : 0;
     $Alpha	= isset($Format["Alpha"]) ? $Format["Alpha"] : 100;

     $this->pDataObject->Palette[$SliceID]["R"]     = $R;
     $this->pDataObject->Palette[$SliceID]["G"]     = $G;
     $this->pDataObject->Palette[$SliceID]["B"]     = $B;
     $this->pDataObject->Palette[$SliceID]["Alpha"] = $Alpha;
    }

   /* Internally used compute the label positions */
   function writePieLabel($X,$Y,$Label,$Angle,$Settings,$Stacked,$Xc=0,$Yc=0,$Radius=0,$Reversed=FALSE)
    {
     $LabelOffset	= 30;
     $FontName		= $this->pChartObject->FontName;
     $FontSize		= $this->pChartObject->FontSize;

     if ( !$Stacked )
      {
       $Settings["Angle"]  = 360-$Angle;
       $Settings["Length"] = 25;
       $Settings["Size"]   = 8;

       $this->pChartObject->drawArrowLabel($X,$Y," ".$Label." ",$Settings);
      }
     else
      {
       $X2 = cos(deg2rad($Angle-90))*20+$X;
       $Y2 = sin(deg2rad($Angle-90))*20+$Y;

       $TxtPos = $this->pChartObject->getTextBox($X,$Y,$FontName,$FontSize,0,$Label);
       $Height = $TxtPos[0]["Y"] - $TxtPos[2]["Y"];
       $YTop    = $Y2 - $Height/2 - 2;
       $YBottom = $Y2 + $Height/2 + 2;

       if ( $this->LabelPos != "" )
        {
         $Done = FALSE;
         foreach($this->LabelPos as $Key => $Settings)
          {
           if ( !$Done )
            {
             if ( $Angle <= 90 && (($YTop >= $Settings["YTop"] && $YTop <= $Settings["YBottom"]) || ($YBottom >= $Settings["YTop"] && $YBottom <= $Settings["YBottom"])))
              { $this->shift(0,180,-($Height+2),$Reversed); $Done = TRUE; }
             if ( $Angle > 90 && $Angle <= 180 && (($YTop >= $Settings["YTop"] && $YTop <= $Settings["YBottom"]) || ($YBottom >= $Settings["YTop"] && $YBottom <= $Settings["YBottom"])))
              { $this->shift(0,180,-($Height+2),$Reversed); $Done = TRUE; }
             if ( $Angle > 180 && $Angle <= 270 && (($YTop >= $Settings["YTop"] && $YTop <= $Settings["YBottom"]) || ($YBottom >= $Settings["YTop"] && $YBottom <= $Settings["YBottom"])))
              { $this->shift(180,360,($Height+2),$Reversed); $Done = TRUE; }
             if ( $Angle > 270 && $Angle <= 360 && (($YTop >= $Settings["YTop"] && $YTop <= $Settings["YBottom"]) || ($YBottom >= $Settings["YTop"] && $YBottom <= $Settings["YBottom"])))
              { $this->shift(180,360,($Height+2),$Reversed); $Done = TRUE; }
            }
          }
        }

       $LabelSettings = array("YTop"=>$YTop,"YBottom"=>$YBottom,"Label"=>$Label,"Angle"=>$Angle,"X1"=>$X,"Y1"=>$Y,"X2"=>$X2,"Y2"=>$Y2);
       if ( $Angle <= 180 ) { $LabelSettings["X3"] = $Xc+$Radius+$LabelOffset; }
       if ( $Angle > 180 )  { $LabelSettings["X3"] = $Xc-$Radius-$LabelOffset; }
       $this->LabelPos[] = $LabelSettings;
      }
    }

   /* Internally used to shift label positions */
   function shift($StartAngle,$EndAngle,$Offset,$Reversed)
    {
     if ( $Reversed ) { $Offset = -$Offset; }
     foreach($this->LabelPos as $Key => $Settings)
      {
       if ( $Settings["Angle"] > $StartAngle && $Settings["Angle"] <= $EndAngle ) { $this->LabelPos[$Key]["YTop"] = $Settings["YTop"] + $Offset; $this->LabelPos[$Key]["YBottom"] = $Settings["YBottom"] + $Offset; $this->LabelPos[$Key]["Y2"] = $Settings["Y2"] + $Offset; }
      }
    }

   /* Internally used to write the re-computed labels */
   function writeShiftedLabels()
    {
     if ( $this->LabelPos == "" ) { return(0); }
     foreach($this->LabelPos as $Key => $Settings)
      {
       $X1 = $Settings["X1"]; $Y1 = $Settings["Y1"];
       $X2 = $Settings["X2"]; $Y2 = $Settings["Y2"];
       $X3 = $Settings["X3"];
       $Angle = $Settings["Angle"];
       $Label = $Settings["Label"];

       $this->pChartObject->drawArrow($X2,$Y2,$X1,$Y1,array("Size"=>8));
       if ( $Angle <= 180 )
        {
         $this->pChartObject->drawLine($X2,$Y2,$X3,$Y2);
         $this->pChartObject->drawText($X3+2,$Y2,$Label,array("Align"=>TEXT_ALIGN_MIDDLELEFT));
        }
       else
        {
         $this->pChartObject->drawLine($X2,$Y2,$X3,$Y2);
         $this->pChartObject->drawText($X3-2,$Y2,$Label,array("Align"=>TEXT_ALIGN_MIDDLERIGHT));
        }
      }
    }

   /* Draw a ring chart */
   function draw2DRing($X,$Y,$Format="")
    {
     $OuterRadius	= isset($Format["Radius"]) ? $Format["Radius"] : 60;
     $Precision		= isset($Format["Precision"]) ? $Format["Precision"] : 0;
     $InnerRadius	= isset($Format["Radius"]) ? $Format["Radius"] : 30;
     $Border		= isset($Format["Border"]) ? $Format["Border"] : FALSE;
     $BorderR		= isset($Format["BorderR"]) ? $Format["BorderR"] : 255;
     $BorderG		= isset($Format["BorderG"]) ? $Format["BorderG"] : 255;
     $BorderB		= isset($Format["BorderB"]) ? $Format["BorderB"] : 255;
     $BorderAlpha	= isset($Format["BorderAlpha"]) ? $Format["BorderAlpha"] : 100;
     $Shadow		= isset($Format["Shadow"]) ? $Format["Shadow"] : FALSE;
     $DrawLabels	= isset($Format["DrawLabels"]) ? $Format["DrawLabels"] : FALSE;
     $LabelStacked	= isset($Format["LabelStacked"]) ? $Format["LabelStacked"] : FALSE;
     $LabelColor	= isset($Format["LabelColor"]) ? $Format["LabelColor"] : PIE_LABEL_COLOR_MANUAL;
     $LabelR		= isset($Format["LabelR"]) ? $Format["LabelR"] : 0;
     $LabelG		= isset($Format["LabelG"]) ? $Format["LabelG"] : 0;
     $LabelB		= isset($Format["LabelB"]) ? $Format["LabelB"] : 0;
     $LabelAlpha	= isset($Format["LabelAlpha"]) ? $Format["LabelAlpha"] : 100;
     $WriteValues	= isset($Format["WriteValues"]) ? $Format["WriteValues"] : NULL; //PIE_VALUE_PERCENTAGE
     $ValuePadding	= isset($Format["ValuePadding"]) ? $Format["ValuePadding"] : 5;
     $ValuePosition	= isset($Format["ValuePosition"]) ? $Format["ValuePosition"] : PIE_VALUE_OUTSIDE;
     $ValueSuffix	= isset($Format["ValueSuffix"]) ? $Format["ValueSuffix"] : "";
     $ValueR		= isset($Format["ValueR"]) ? $Format["ValueR"] : 255;
     $ValueG		= isset($Format["ValueG"]) ? $Format["ValueG"] : 255;
     $ValueB		= isset($Format["ValueB"]) ? $Format["ValueB"] : 255;
     $ValueAlpha	= isset($Format["ValueAlpha"]) ? $Format["ValueAlpha"] : 100;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;

     /* Data Processing */
     $Data    = $this->pDataObject->getData();
     $Palette = $this->pDataObject->getPalette();

     /* Do we have an abscissa serie defined? */
     if ( $Data["Abscissa"] == "" ) { return(PIE_NO_ABSCISSA); }

     /* Try to find the data serie */
     $DataSerie = "";
     foreach ($Data["Series"] as $SerieName => $SerieData)
      { if ( $SerieName != $Data["Abscissa"]) { $DataSerie = $SerieName; } }

     /* Do we have data to compute? */
     if ( $DataSerie == "" ) { return(PIE_NO_DATASERIE); }

     /* Remove unused data */
     list($Data,$Palette) = $this->clean0Values($Data,$Palette,$DataSerie,$Data["Abscissa"]);

     /* Compute the pie sum */
     $SerieSum = $this->pDataObject->getSum($DataSerie);

     /* Do we have data to draw? */
     if ( $SerieSum == 0 ) { return(PIE_SUMISNULL); }

     /* Dump the real number of data to draw */
     $Values = "";
     foreach ($Data["Series"][$DataSerie]["Data"] as $Key => $Value)
      { if ($Value != 0) { $Values[] = $Value; } }

     /* Compute the wasted angular space between series */
     if (count($Values)==1) { $WastedAngular = 0; } else { $WastedAngular = 0; } // count($Values)

     /* Compute the scale */
     $ScaleFactor = (360 - $WastedAngular) / $SerieSum;

     $RestoreShadow = $this->pChartObject->Shadow;
     if ( $this->pChartObject->Shadow )
      {
       $this->pChartObject->Shadow = FALSE;

       $ShadowFormat = $Format; $ShadowFormat["Shadow"] = TRUE;
       $this->draw2DRing($X+$this->pChartObject->ShadowX,$Y+$this->pChartObject->ShadowY,$ShadowFormat);
      }

     /* Draw the polygon pie elements */
     $Step = 360 / (2 * PI * $OuterRadius);
     $Offset = 0; $ID = 0;
     foreach($Values as $Key => $Value)
      {
       if ( $Shadow )
        {
         $Settings    = array("R"=>$this->pChartObject->ShadowR,"G"=>$this->pChartObject->ShadowG,"B"=>$this->pChartObject->ShadowB,"Alpha"=>$this->pChartObject->Shadowa);
         $BorderColor = $Settings;
        }
       else
        {
         if ( !isset($Palette[$ID]["R"]) ) { $Color = $this->pChartObject->getRandomColor(); $Palette[$ID] = $Color; $this->pDataObject->savePalette($ID,$Color); }
         $Settings = array("R"=>$Palette[$ID]["R"],"G"=>$Palette[$ID]["G"],"B"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);

         if ( $Border )
          $BorderColor = array("R"=>$BorderR,"G"=>$BorderG,"B"=>$BorderB,"Alpha"=>$BorderAlpha);
         else
          $BorderColor = $Settings;
        }

       $Plots = ""; $Boundaries = ""; $AAPixels = "";
       $EndAngle = $Offset+($Value*$ScaleFactor); if ( $EndAngle > 360 ) { $EndAngle = 360; }
       for($i=$Offset;$i<=$EndAngle;$i=$i+$Step)
        {
         $Xc = cos(($i-90)*PI/180) * $OuterRadius + $X;
         $Yc = sin(($i-90)*PI/180) * $OuterRadius + $Y;

         if ( !isset($Boundaries[0]["X1"]) ) { $Boundaries[0]["X1"] = $Xc; $Boundaries[0]["Y1"] = $Yc; }
         $AAPixels[] = array($Xc,$Yc);

         if ( $i<90 ) { $Yc++; }
         if ( $i>180 && $i<270 ) { $Xc++; }
         if ( $i>=270 ) { $Xc++; $Yc++; }

         $Plots[] = $Xc; $Plots[] = $Yc;
        }
       $Boundaries[1]["X1"] = $Xc; $Boundaries[1]["Y1"] = $Yc;
       $Lasti = $EndAngle;

       for($i=$EndAngle;$i>=$Offset;$i=$i-$Step)
        {
         $Xc = cos(($i-90)*PI/180) * ($InnerRadius-1) + $X;
         $Yc = sin(($i-90)*PI/180) * ($InnerRadius-1) + $Y;

         if ( !isset($Boundaries[1]["X2"]) ) { $Boundaries[1]["X2"] = $Xc; $Boundaries[1]["Y2"] = $Yc; }
         $AAPixels[] = array($Xc,$Yc);

         $Xc = cos(($i-90)*PI/180) * $InnerRadius + $X;
         $Yc = sin(($i-90)*PI/180) * $InnerRadius + $Y;

         if ( $i<90 ) { $Yc++; }
         if ( $i>180 && $i<270 ) { $Xc++; }
         if ( $i>=270 ) { $Xc++; $Yc++; }

         $Plots[] = $Xc; $Plots[] = $Yc;
        }
       $Boundaries[0]["X2"] = $Xc; $Boundaries[0]["Y2"] = $Yc;

       /* Draw the polygon */
       $this->pChartObject->drawPolygon($Plots,$Settings);
       if ( $RecordImageMap && !$Shadow ) { $this->pChartObject->addToImageMap("POLY",$this->arraySerialize($Plots),$this->pChartObject->toHTMLColor($Palette[$ID]["R"],$Palette[$ID]["G"],$Palette[$ID]["B"]),$Data["Series"][$Data["Abscissa"]]["Data"][$Key],$Value); }

       /* Smooth the edges using AA */
       foreach($AAPixels as $iKey => $Pos ) { $this->pChartObject->drawAntialiasPixel($Pos[0],$Pos[1],$BorderColor); }
       $this->pChartObject->drawLine($Boundaries[0]["X1"],$Boundaries[0]["Y1"],$Boundaries[0]["X2"],$Boundaries[0]["Y2"],$BorderColor);
       $this->pChartObject->drawLine($Boundaries[1]["X1"],$Boundaries[1]["Y1"],$Boundaries[1]["X2"],$Boundaries[1]["Y2"],$BorderColor);

       if ( $DrawLabels && !$Shadow )
        {
         if ( $LabelColor == PIE_LABEL_COLOR_AUTO )
          { $Settings = array("FillR"=>$Palette[$ID]["R"],"FillG"=>$Palette[$ID]["G"],"FillB"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);}
         else
          { $Settings = array("FillR"=>$LabelR,"FillG"=>$LabelG,"FillB"=>$LabelB,"Alpha"=>$LabelAlpha); }

         $Angle = ($EndAngle - $Offset)/2 + $Offset;
         $Xc = cos(($Angle-90)*PI/180) * $OuterRadius + $X;
         $Yc = sin(($Angle-90)*PI/180) * $OuterRadius + $Y;

         $Label = $Data["Series"][$Data["Abscissa"]]["Data"][$Key];

         if ( $LabelStacked )
          $this->writePieLabel($Xc,$Yc,$Label,$Angle,$Settings,TRUE,$X,$Y,$OuterRadius);
         else
          $this->writePieLabel($Xc,$Yc,$Label,$Angle,$Settings,FALSE);
        }

       $Offset = $Lasti; $ID++;
      }

     if ( $DrawLabels && $LabelStacked ) { $this->writeShiftedLabels(); }

     if ( $WriteValues && !$Shadow )
      {
       $Step = 360 / (2 * PI * $OuterRadius);
       $Offset = 0;
       foreach($Values as $Key => $Value)
        {
         $EndAngle = $Offset+($Value*$ScaleFactor);
         if ( $EndAngle > 360 ) { $EndAngle = 360; }

         $Angle = $Offset+($Value*$ScaleFactor)/2;
         if ( $ValuePosition == PIE_VALUE_OUTSIDE )
          {
           $Xc = cos(($Angle-90)*PI/180) * ($OuterRadius+$ValuePadding) + $X;
           $Yc = sin(($Angle-90)*PI/180) * ($OuterRadius+$ValuePadding) + $Y;
           if ( $Angle >=0 && $Angle <= 90 ) { $Align = TEXT_ALIGN_BOTTOMLEFT; }
           if ( $Angle > 90 && $Angle <= 180 ) { $Align = TEXT_ALIGN_TOPLEFT; }
           if ( $Angle > 180 && $Angle <= 270 ) { $Align = TEXT_ALIGN_TOPRIGHT; }
           if ( $Angle > 270 ) { $Align = TEXT_ALIGN_BOTTOMRIGHT; }
          }
         else
          {
           $Xc = cos(($Angle-90)*PI/180) * (($OuterRadius-$InnerRadius)/2+$InnerRadius) + $X;
           $Yc = sin(($Angle-90)*PI/180) * (($OuterRadius-$InnerRadius)/2+$InnerRadius) + $Y;
           $Align = TEXT_ALIGN_MIDDLEMIDDLE;
          }

         if ( $WriteValues == PIE_VALUE_PERCENTAGE )
          $Display = round(( 100 / $SerieSum ) * $Value,$Precision)."%";
         elseif ( $WriteValues == PIE_VALUE_NATURAL )
          $Display = $Value.$ValueSuffix;
         else
          $Label = "";

         $this->pChartObject->drawText($Xc,$Yc,$Display,array("Align"=>$Align,"R"=>$ValueR,"G"=>$ValueG,"B"=>$ValueB));
         $Offset = $EndAngle;
        }
      }

     $this->pChartObject->Shadow = $RestoreShadow;

     return(PIE_RENDERED);
    }

   /* Draw a 3D ring chart */
   function draw3DRing($X,$Y,$Format="")
    {
     $OuterRadius	= isset($Format["OuterRadius"]) ? $Format["OuterRadius"] : 100;
     $Precision		= isset($Format["Precision"]) ? $Format["Precision"] : 0;
     $InnerRadius	= isset($Format["InnerRadius"]) ? $Format["InnerRadius"] : 30;
     $SkewFactor	= isset($Format["SkewFactor"]) ? $Format["SkewFactor"] : .6;
     $SliceHeight	= isset($Format["SliceHeight"]) ? $Format["SliceHeight"] : 10;
     $DataGapAngle	= isset($Format["DataGapAngle"]) ? $Format["DataGapAngle"] : 10;
     $DataGapRadius	= isset($Format["DataGapRadius"]) ? $Format["DataGapRadius"] : 10;
     $Border		= isset($Format["Border"]) ? $Format["Border"] : FALSE;
     $Shadow		= isset($Format["Shadow"]) ? $Format["Shadow"] : FALSE;
     $DrawLabels	= isset($Format["DrawLabels"]) ? $Format["DrawLabels"] : FALSE;
     $LabelStacked	= isset($Format["LabelStacked"]) ? $Format["LabelStacked"] : FALSE;
     $LabelColor	= isset($Format["LabelColor"]) ? $Format["LabelColor"] : PIE_LABEL_COLOR_MANUAL;
     $LabelR		= isset($Format["LabelR"]) ? $Format["LabelR"] : 0;
     $LabelG		= isset($Format["LabelG"]) ? $Format["LabelG"] : 0;
     $LabelB		= isset($Format["LabelB"]) ? $Format["LabelB"] : 0;
     $LabelAlpha	= isset($Format["LabelAlpha"]) ? $Format["LabelAlpha"] : 100;
     $Cf		= isset($Format["Cf"]) ? $Format["Cf"] : 20;
     $WriteValues	= isset($Format["WriteValues"]) ? $Format["WriteValues"] : PIE_VALUE_NATURAL;
     $ValuePadding	= isset($Format["ValuePadding"]) ? $Format["ValuePadding"] : $SliceHeight + 15;
     $ValuePosition	= isset($Format["ValuePosition"]) ? $Format["ValuePosition"] : PIE_VALUE_OUTSIDE;
     $ValueSuffix	= isset($Format["ValueSuffix"]) ? $Format["ValueSuffix"] : "";
     $ValueR		= isset($Format["ValueR"]) ? $Format["ValueR"] : 255;
     $ValueG		= isset($Format["ValueG"]) ? $Format["ValueG"] : 255;
     $ValueB		= isset($Format["ValueB"]) ? $Format["ValueB"] : 255;
     $ValueAlpha	= isset($Format["ValueAlpha"]) ? $Format["ValueAlpha"] : 100;
     $RecordImageMap	= isset($Format["RecordImageMap"]) ? $Format["RecordImageMap"] : FALSE;

     /* Error correction for overlaying rounded corners */
     if ( $SkewFactor < .5 ) { $SkewFactor = .5; }

     /* Data Processing */
     $Data    = $this->pDataObject->getData();
     $Palette = $this->pDataObject->getPalette();

     /* Do we have an abscissa serie defined? */
     if ( $Data["Abscissa"] == "" ) { return(PIE_NO_ABSCISSA); }

     /* Try to find the data serie */
     $DataSerie = "";
     foreach ($Data["Series"] as $SerieName => $SerieData)
      { if ( $SerieName != $Data["Abscissa"]) { $DataSerie = $SerieName; } }

     /* Do we have data to compute? */
     if ( $DataSerie == "" ) { return(PIE_NO_DATASERIE); }

     /* Remove unused data */
     list($Data,$Palette) = $this->clean0Values($Data,$Palette,$DataSerie,$Data["Abscissa"]);

     /* Compute the pie sum */
     $SerieSum = $this->pDataObject->getSum($DataSerie);

     /* Do we have data to draw? */
     if ( $SerieSum == 0 ) { return(PIE_SUMISNULL); }

     /* Dump the real number of data to draw */
     $Values = "";
     foreach ($Data["Series"][$DataSerie]["Data"] as $Key => $Value)
      { if ($Value != 0) { $Values[] = $Value; } }

     /* Compute the wasted angular space between series */
     if (count($Values)==1) { $WastedAngular = 0; } else { $WastedAngular = count($Values) * $DataGapAngle; }

     /* Compute the scale */
     $ScaleFactor = (360 - $WastedAngular) / $SerieSum;

     $RestoreShadow = $this->pChartObject->Shadow;
     if ( $this->pChartObject->Shadow ) { $this->pChartObject->Shadow = FALSE; }

     /* Draw the polygon ring elements */
     $Offset = 360; $ID = count($Values)-1;
     $Values = array_reverse($Values);
     $Slice  = 0; $Slices = ""; $SliceColors = ""; $Visible = ""; $SliceAngle = "";
     foreach($Values as $Key => $Value)
      {
       if ( !isset($Palette[$ID]["R"]) ) { $Color = $this->pChartObject->getRandomColor(); $Palette[$ID] = $Color; $this->pDataObject->savePalette($ID,$Color); }
       $Settings = array("R"=>$Palette[$ID]["R"],"G"=>$Palette[$ID]["G"],"B"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);

       $SliceColors[$Slice] = $Settings;

       $StartAngle = $Offset;
       $EndAngle   = $Offset-($Value*$ScaleFactor); if ( $EndAngle < 0 ) { $EndAngle = 0; }

       if ( $StartAngle > 180 ) { $Visible[$Slice]["Start"] = TRUE; } else { $Visible[$Slice]["Start"] = TRUE; }
       if ( $EndAngle < 180 )   { $Visible[$Slice]["End"] = FALSE; } else { $Visible[$Slice]["End"] = TRUE; }

       $Step   = (360 / (2 * PI * $OuterRadius))/2;
       $OutX1 = VOID; $OutY1 = VOID;
       for($i=$Offset;$i>=$EndAngle;$i=$i-$Step)
        {
         $Xc = cos(($i-90)*PI/180) * ($OuterRadius+$DataGapRadius-2) + $X;
         $Yc = sin(($i-90)*PI/180) * ($OuterRadius+$DataGapRadius-2)*$SkewFactor + $Y;
         $Slices[$Slice]["AA"][] = array($Xc,$Yc);

         $Xc = cos(($i-90)*PI/180) * ($OuterRadius+$DataGapRadius-1) + $X;
         $Yc = sin(($i-90)*PI/180) * ($OuterRadius+$DataGapRadius-1)*$SkewFactor + $Y;
         $Slices[$Slice]["AA"][] = array($Xc,$Yc);

         $Xc = cos(($i-90)*PI/180) * ($OuterRadius+$DataGapRadius) + $X;
         $Yc = sin(($i-90)*PI/180) * ($OuterRadius+$DataGapRadius)*$SkewFactor + $Y;
         $this->pChartObject->drawAntialiasPixel($Xc,$Yc,$Settings);

         if ( $OutX1 == VOID ) { $OutX1 = $Xc; $OutY1 = $Yc; }

         if ( $i<90 ) { $Yc++; }
         if ( $i>90 && $i<180 ) { $Xc++; }
         if ( $i>180 && $i<270 ) { $Xc++; }
         if ( $i>=270 ) { $Xc++; $Yc++; }

         $Slices[$Slice]["BottomPoly"][] = floor($Xc); $Slices[$Slice]["BottomPoly"][] = floor($Yc);
         $Slices[$Slice]["TopPoly"][] = floor($Xc); $Slices[$Slice]["TopPoly"][] = floor($Yc)-$SliceHeight;
         $Slices[$Slice]["Angle"][] = $i;
        }
       $OutX2 = $Xc; $OutY2 = $Yc;

       $Slices[$Slice]["Angle"][] = VOID;
       $Lasti = $i;

       $Step   = (360 / (2 * PI * $InnerRadius))/2;
       $InX1 = VOID; $InY1 = VOID;
       for($i=$EndAngle;$i<=$Offset;$i=$i+$Step)
        {
         $Xc = cos(($i-90)*PI/180) * ($InnerRadius+$DataGapRadius-1) + $X;
         $Yc = sin(($i-90)*PI/180) * ($InnerRadius+$DataGapRadius-1)*$SkewFactor + $Y;
         $Slices[$Slice]["AA"][] = array($Xc,$Yc);

         $Xc = cos(($i-90)*PI/180) * ($InnerRadius+$DataGapRadius) + $X;
         $Yc = sin(($i-90)*PI/180) * ($InnerRadius+$DataGapRadius)*$SkewFactor + $Y;
         $Slices[$Slice]["AA"][] = array($Xc,$Yc);

         if ( $InX1 == VOID ) { $InX1 = $Xc; $InY1 = $Yc; }

         if ( $i<90 ) { $Yc++; }
         if ( $i>90 && $i<180 ) { $Xc++; }
         if ( $i>180 && $i<270 ) { $Xc++; }
         if ( $i>=270 ) { $Xc++; $Yc++; }

         $Slices[$Slice]["BottomPoly"][] = floor($Xc); $Slices[$Slice]["BottomPoly"][] = floor($Yc);
         $Slices[$Slice]["TopPoly"][] = floor($Xc); $Slices[$Slice]["TopPoly"][] = floor($Yc)-$SliceHeight;
         $Slices[$Slice]["Angle"][] = $i;
        }
       $InX2 = $Xc; $InY2 = $Yc;

       $Slices[$Slice]["InX1"] = $InX1;   $Slices[$Slice]["InY1"] = $InY1;
       $Slices[$Slice]["InX2"] = $InX2;   $Slices[$Slice]["InY2"] = $InY2;
       $Slices[$Slice]["OutX1"] = $OutX1; $Slices[$Slice]["OutY1"] = $OutY1;
       $Slices[$Slice]["OutX2"] = $OutX2; $Slices[$Slice]["OutY2"] = $OutY2;

       $Offset = $Lasti - $DataGapAngle; $ID--; $Slice++;
      }

     /* Draw the bottom pie splice */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];  $Settings["NoBorder"] = TRUE;
       $this->pChartObject->drawPolygon($Plots["BottomPoly"],$Settings);

       foreach($Plots["AA"] as $Key => $Pos)
        $this->pChartObject->drawAntialiasPixel($Pos[0],$Pos[1],$Settings);

       $this->pChartObject->drawLine($Plots["InX1"],$Plots["InY1"],$Plots["OutX2"],$Plots["OutY2"],$Settings);
       $this->pChartObject->drawLine($Plots["InX2"],$Plots["InY2"],$Plots["OutX1"],$Plots["OutY1"],$Settings);
      }

     $Slices      = array_reverse($Slices);
     $SliceColors = array_reverse($SliceColors);

     /* Draw the vertical edges (semi-visible) */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];  $Settings["NoBorder"] = TRUE;
       $Settings["R"] = $Settings["R"]+$Cf; $Settings["G"] = $Settings["G"]+$Cf; $Settings["B"] = $Settings["B"]+$Cf;

       $StartAngle = $Plots["Angle"][0];
       foreach($Plots["Angle"] as $Key =>$Angle) { if ($Angle == VOID) { $EndAngle = $Plots["Angle"][$Key-1]; } }

       if ( $StartAngle >= 270 || $StartAngle <= 90 )
        $this->pChartObject->drawLine($Plots["OutX1"],$Plots["OutY1"],$Plots["OutX1"],$Plots["OutY1"]-$SliceHeight,$Settings);
       if ( $StartAngle >= 270 || $StartAngle <= 90 )
        $this->pChartObject->drawLine($Plots["OutX2"],$Plots["OutY2"],$Plots["OutX2"],$Plots["OutY2"]-$SliceHeight,$Settings);

        $this->pChartObject->drawLine($Plots["InX1"],$Plots["InY1"],$Plots["InX1"],$Plots["InY1"]-$SliceHeight,$Settings);
        $this->pChartObject->drawLine($Plots["InX2"],$Plots["InY2"],$Plots["InX2"],$Plots["InY2"]-$SliceHeight,$Settings);
      }

     /* Draw the inner vertical slices */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];  $Settings["NoBorder"] = TRUE;
       $Settings["R"] = $Settings["R"]+$Cf; $Settings["G"] = $Settings["G"]+$Cf; $Settings["B"] = $Settings["B"]+$Cf;

       $Outer = TRUE; $Inner = FALSE;
       $InnerPlotsA = ""; $InnerPlotsB = "";
       foreach($Plots["Angle"] as $ID => $Angle)
        {
         if ( $Angle == VOID )
          { $Outer = FALSE; $Inner = TRUE; }
         elseif( $Inner )
          {
           if (( $Angle < 90 || $Angle > 270 ) && isset($Plots["BottomPoly"][$ID*2]) )
            {
             $Xo = $Plots["BottomPoly"][$ID*2];
             $Yo = $Plots["BottomPoly"][$ID*2+1];

             $InnerPlotsA[] = $Xo; $InnerPlotsA[] = $Yo;
             $InnerPlotsB[] = $Xo; $InnerPlotsB[] = $Yo-$SliceHeight;
            }
          }
        }

       if ( $InnerPlotsA != "" )
        { $InnerPlots = array_merge($InnerPlotsA,$this->arrayReverse($InnerPlotsB)); $this->pChartObject->drawPolygon($InnerPlots,$Settings); }
      }

     /* Draw the splice top and left poly */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];  $Settings["NoBorder"] = TRUE;
       $Settings["R"] = $Settings["R"]+$Cf*1.5; $Settings["G"] = $Settings["G"]+$Cf*1.5; $Settings["B"] = $Settings["B"]+$Cf*1.5;

       $StartAngle = $Plots["Angle"][0];
       foreach($Plots["Angle"] as $Key =>$Angle) { if ($Angle == VOID) { $EndAngle = $Plots["Angle"][$Key-1]; } }

       if ( $StartAngle < 180 )
        {
         $Points = "";
         $Points[] = $Plots["InX2"];
         $Points[] = $Plots["InY2"];
         $Points[] = $Plots["InX2"];
         $Points[] = $Plots["InY2"]-$SliceHeight;
         $Points[] = $Plots["OutX1"];
         $Points[] = $Plots["OutY1"]-$SliceHeight;
         $Points[] = $Plots["OutX1"];
         $Points[] = $Plots["OutY1"];

         $this->pChartObject->drawPolygon($Points,$Settings);
        }

       if ( $EndAngle > 180 )
        {
         $Points = "";
         $Points[] = $Plots["InX1"];
         $Points[] = $Plots["InY1"];
         $Points[] = $Plots["InX1"];
         $Points[] = $Plots["InY1"]-$SliceHeight;
         $Points[] = $Plots["OutX2"];
         $Points[] = $Plots["OutY2"]-$SliceHeight;
         $Points[] = $Plots["OutX2"];
         $Points[] = $Plots["OutY2"];

         $this->pChartObject->drawPolygon($Points,$Settings);
        }
      }


     /* Draw the vertical edges (visible) */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];  $Settings["NoBorder"] = TRUE;
       $Settings["R"] = $Settings["R"]+$Cf; $Settings["G"] = $Settings["G"]+$Cf; $Settings["B"] = $Settings["B"]+$Cf;

       $StartAngle = $Plots["Angle"][0];
       foreach($Plots["Angle"] as $Key =>$Angle) { if ($Angle == VOID) { $EndAngle = $Plots["Angle"][$Key-1]; } }

       if ( $StartAngle <= 270 && $StartAngle >= 90 )
        $this->pChartObject->drawLine($Plots["OutX1"],$Plots["OutY1"],$Plots["OutX1"],$Plots["OutY1"]-$SliceHeight,$Settings);
       if ( $EndAngle <= 270 && $EndAngle >= 90 )
        $this->pChartObject->drawLine($Plots["OutX2"],$Plots["OutY2"],$Plots["OutX2"],$Plots["OutY2"]-$SliceHeight,$Settings);
      }


     /* Draw the outer vertical slices */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];  $Settings["NoBorder"] = TRUE;
       $Settings["R"] = $Settings["R"]+$Cf; $Settings["G"] = $Settings["G"]+$Cf; $Settings["B"] = $Settings["B"]+$Cf;

       $Outer = TRUE; $Inner = FALSE;
       $OuterPlotsA = ""; $OuterPlotsB = ""; $InnerPlotsA = ""; $InnerPlotsB = "";
       foreach($Plots["Angle"] as $ID => $Angle)
        {
         if ( $Angle == VOID )
          { $Outer = FALSE; $Inner = TRUE; }
         elseif( $Outer )
          {
           if ( ( $Angle > 90 && $Angle < 270 ) && isset($Plots["BottomPoly"][$ID*2]) )
            {
             $Xo = $Plots["BottomPoly"][$ID*2];
             $Yo = $Plots["BottomPoly"][$ID*2+1];

             $OuterPlotsA[] = $Xo; $OuterPlotsA[] = $Yo;
             $OuterPlotsB[] = $Xo; $OuterPlotsB[] = $Yo-$SliceHeight;
            }
          }
        }
       if ( $OuterPlotsA != "" )
        { $OuterPlots = array_merge($OuterPlotsA,$this->arrayReverse($OuterPlotsB)); $this->pChartObject->drawPolygon($OuterPlots,$Settings); }
      }

     $Slices      = array_reverse($Slices);
     $SliceColors = array_reverse($SliceColors);


     /* Draw the top pie splice */
     foreach($Slices as $SliceID => $Plots)
      {
       $Settings = $SliceColors[$SliceID];  $Settings["NoBorder"] = TRUE;
       $Settings["R"] = $Settings["R"]+$Cf*2; $Settings["G"] = $Settings["G"]+$Cf*2; $Settings["B"] = $Settings["B"]+$Cf*2;
 
       $this->pChartObject->drawPolygon($Plots["TopPoly"],$Settings);

       if ( $RecordImageMap ) { $this->pChartObject->addToImageMap("POLY",$this->arraySerialize($Plots["TopPoly"]),$this->pChartObject->toHTMLColor($Settings["R"],$Settings["G"],$Settings["B"]),$Data["Series"][$Data["Abscissa"]]["Data"][$SliceID],$Data["Series"][$DataSerie]["Data"][count($Slices)-$SliceID-1]); }

       foreach($Plots["AA"] as $Key => $Pos)
        $this->pChartObject->drawAntialiasPixel($Pos[0],$Pos[1]-$SliceHeight,$Settings);

       $this->pChartObject->drawLine($Plots["InX1"],$Plots["InY1"]-$SliceHeight,$Plots["OutX2"],$Plots["OutY2"]-$SliceHeight,$Settings);
       $this->pChartObject->drawLine($Plots["InX2"],$Plots["InY2"]-$SliceHeight,$Plots["OutX1"],$Plots["OutY1"]-$SliceHeight,$Settings);
      }

     if ( $DrawLabels )
      {
       $Offset = 360;
       foreach($Values as $Key => $Value)
        {
         $StartAngle = $Offset;
         $EndAngle   = $Offset-($Value*$ScaleFactor); if ( $EndAngle < 0 ) { $EndAngle = 0; }

         if ( $LabelColor == PIE_LABEL_COLOR_AUTO )
          { $Settings = array("FillR"=>$Palette[$ID]["R"],"FillG"=>$Palette[$ID]["G"],"FillB"=>$Palette[$ID]["B"],"Alpha"=>$Palette[$ID]["Alpha"]);}
         else
          { $Settings = array("FillR"=>$LabelR,"FillG"=>$LabelG,"FillB"=>$LabelB,"Alpha"=>$LabelAlpha); }

         $Angle = ($EndAngle - $Offset)/2 + $Offset;
         $Xc = cos(($Angle-90)*PI/180) * ($OuterRadius+$DataGapRadius) + $X;
         $Yc = sin(($Angle-90)*PI/180) * ($OuterRadius+$DataGapRadius)*$SkewFactor + $Y;

         if ( $WriteValues == PIE_VALUE_PERCENTAGE )
          $Label = $Display = round(( 100 / $SerieSum ) * $Value,$Precision)."%";
         elseif ( $WriteValues == PIE_VALUE_NATURAL )
          $Label = $Data["Series"][$Data["Abscissa"]]["Data"][$Key];
         else
          $Label = "";

         if ( $LabelStacked )
          $this->writePieLabel($Xc,$Yc-$SliceHeight,$Label,$Angle,$Settings,TRUE,$X,$Y,$OuterRadius);
         else
          $this->writePieLabel($Xc,$Yc-$SliceHeight,$Label,$Angle,$Settings,FALSE);

         $Offset = $EndAngle - $DataGapAngle; $ID--; $Slice++;
        }
      }
     if ( $DrawLabels && $LabelStacked ) { $this->writeShiftedLabels(); }

     $this->pChartObject->Shadow = $RestoreShadow;

     return(PIE_RENDERED);
    }

  /* Serialize an array */
  function arraySerialize($Data)
   {
    $Result = "";
    foreach($Data as $Key => $Value)
     { if ($Result == "") { $Result = floor($Value); } else { $Result = $Result.",".floor($Value); } }

    return($Result);
   }

  /* Reverse an array */
  function arrayReverse($Plots)
   {
    $Result = "";

    for($i=count($Plots)-1;$i>=0;$i=$i-2)
     { $Result[] = $Plots[$i-1]; $Result[] = $Plots[$i]; }

    return($Result);
   }

  /* Remove unused series & values */
  function clean0Values($Data,$Palette,$DataSerie,$AbscissaSerie)
   {
    $NewPalette = ""; $NewData = ""; $NewAbscissa = "";

    /* Remove unused series */
    foreach($Data["Series"] as $SerieName => $SerieSettings)
     { if ( $SerieName != $DataSerie && $SerieName != $AbscissaSerie ) { unset($Data["Series"][$SerieName]); } }

    /* Remove NULL values */
    foreach($Data["Series"][$DataSerie]["Data"] as $Key => $Value)
     {
      if ($Value != 0 )
       {
        $NewData[]     = $Value;
        $NewAbscissa[] = $Data["Series"][$AbscissaSerie]["Data"][$Key];
        if ( isset($Palette[$Key]) ) { $NewPalette[]  = $Palette[$Key]; }
       }
     }
    $Data["Series"][$DataSerie]["Data"]     = $NewData;
    $Data["Series"][$AbscissaSerie]["Data"] = $NewAbscissa;

    return(array($Data,$NewPalette));
   }
  }
?>
