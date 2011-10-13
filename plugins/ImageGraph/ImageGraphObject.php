<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */

require_once PIWIK_INCLUDE_PATH."/libs/pChart.1.27d/pChart/pData.php";
require_once PIWIK_INCLUDE_PATH."/libs/pChart.1.27d/pChart/pChart.php";

class Piwik_ImageGraph_ImageGraphObject extends pChart
{
	private $data = null;
	private $abscissaSerie = null;
	private $ordinateSerie = null;
	private $abscissaName = null;
	private $ordinateName = null;
	private $widthImg;
	private $heightImg;
	private $fontSize;
	private $title;
	private $metricTitle;
	private $imageFontHeight;
	private $imageOrdinateLabelMaxWidth;
	private $aliasedGraph;
	
	public function __construct(
						$width = Piwik_ImageGraph_API::GRAPH_WIDTH, 
						$height = Piwik_ImageGraph_API::GRAPH_HEIGHT, 
						$fontSize = Piwik_ImageGraph_API::GRAPH_FONT_SIZE)
	{
		//validate $width and $height
		$minWidth = 150;
		$maxWidth = 1500;
		if(	!is_numeric($width) ||
			$width < $minWidth ||
			$width > $maxWidth
		)
		{
			throw new Exception(Piwik_Translate("General_ParameterMustIntegerBetween", array('$width', $minWidth, $maxWidth)));
		}
		$minHeight = 150;
		$maxHeight = 1500;
		if(	!is_numeric($height) ||
			$height < $minHeight ||
			$height > $maxHeight
		)
		{
			throw new Exception(Piwik_Translate("General_ParameterMustIntegerBetween", array('$height', $minHeight, $maxHeight)));
		}
		$minFontSize = 3;
		$maxFontSize = 25; 
		if(	!is_numeric($fontSize) ||
			$fontSize < $minFontSize ||
			$fontSize > $maxFontSize
		)
		{
			throw new Exception(Piwik_Translate("General_ParameterMustIntegerBetween", array('$fontSize', $minFontSize, $maxFontSize)));
		}
		$this->widthImg = $width;
		$this->heightImg = $height;
		
		//Contruct the inherited pChart
		//parent::__construct($this->widthImg, $this->heightImg);
		//Workaround for white background
		$this->XSize   = $this->widthImg;
		$this->YSize   = $this->heightImg;
		$this->Picture = imagecreatetruecolor($this->widthImg,$this->heightImg);
		$C_White =$this->AllocateColor($this->Picture,255,255,255);
		imagefilledrectangle($this->Picture,0,0,$this->widthImg,$this->heightImg,$C_White);
		
		//Set font and properties
		$this->fontSize = $fontSize;
		$this->setFontProperties(PIWIK_INCLUDE_PATH."/libs/pChart.1.27d/Fonts/tahoma.ttf", $fontSize);
		$Position = imageftbbox($this->FontSize, 0, $this->FontName, "Test");
		$this->imageFontHeight = $Position[1]-$Position[7];
	}
	
	public function setData($abscissaSerie, $ordinateSerie, $abscissaName, $ordinateName, $title = false, $metricTitle = false, $aliasedGraph = false)
	{
		$sum = 0;
		foreach($ordinateSerie as $osVal)
		{
			$sum += $osVal;
		}
		if($sum == 0)
		{
			throw new Exception(Piwik_Translate("General_NoDataForGraph"));
		}
		
		$this->abscissaSerie = $abscissaSerie;
		$this->ordinateSerie = $ordinateSerie;
		$this->abscissaName = $abscissaName;
		$this->ordinateName = $ordinateName;
		$this->title = "";
		$this->metricTitle = "";
		$this->aliasedGraph = false;
		if(!empty($title) && is_string($title))
			$this->title = $title;
		if(!empty($metricTitle) && is_string($metricTitle))
			$this->metricTitle = $metricTitle;
		if(!empty($aliasedGraph) && $aliasedGraph)
			$this->aliasedGraph = true;
		
		$this->imageOrdinateLabelMaxWidth = 0;
		foreach($ordinateSerie as $oName)
		{
			$Position = imageftbbox($this->FontSize, 0, $this->FontName, $oName);
			$oTemp = $Position[2] - $Position[0];
			$this->imageOrdinateLabelMaxWidth = $oTemp > $this->imageOrdinateLabelMaxWidth ? $oTemp : $this->imageOrdinateLabelMaxWidth;
		}
	}
	
	private function _getDataObj()
	{
		//Setup the pData - Object (pChart Framework at http://pchart.sourceforge.net/) to feed the graph
		$data = new pData;
		$data->AddPoint($this->ordinateSerie, "ORDINATE");
		$data->AddSerie("ORDINATE");
		$data->SetYAxisName($this->ordinateName);
		$data->SetSerieName($this->metricTitle, "ORDINATE");
		$data->AddPoint($this->abscissaSerie, "ABSCISSA");
		$data->SetAbsciseLabelSerie("ABSCISSA");
		$data->SetXAxisName($this->abscissaName);
		return $data;
	}
	
	public function print3dPieGraph(	$hexColor0 = false,
										$hexColor1 = false,
										$hexColor2 = false,
										$hexColor3 = false,
										$hexColor4 = false,
										$hexColor5 = false,
										$hexColor6 = false
	)
	{
		$this->_truncateSmallValues();
		$this->data = $this->_getDataObj();
		
		$rgbColor = $this->_hex2rgb($hexColor0, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_0);
		$this->setColorPalette(0, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor1, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_1);
		$this->setColorPalette(1, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor2, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_2);
		$this->setColorPalette(2, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor3, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_3);
		$this->setColorPalette(3, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor4, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_4);
		$this->setColorPalette(4, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor5, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_5);
		$this->setColorPalette(5, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		
		$radius = $this->heightImg < ($this->widthImg - 100) ? $this->heightImg/1.75 : ($this->widthImg - 100)/2.5;
		
		$this->drawPieGraph($this->data->GetData(), $this->data->GetDataDescription(), $this->widthImg/2, $this->heightImg/2 , $radius, PIE_PERCENTAGE, true, 40, 20, 10);
		$rgbColor = $this->_hex2rgb($hexColor6, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_6);
		$this->drawPieLegend(5, 25, $this->data->GetData(), $this->data->GetDataDescription(), $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
	}
	
	public function printBasicPieGraph(	$hexColor0 = false,
										$hexColor1 = false,
										$hexColor2 = false,
										$hexColor3 = false,
										$hexColor4 = false,
										$hexColor5 = false,
										$hexColor6 = false
	)
	{
		$this->_truncateSmallValues();
		$this->data = $this->_getDataObj();
		
		$rgbColor = $this->_hex2rgb($hexColor0, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_0);
		$this->setColorPalette(0, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor1, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_1);
		$this->setColorPalette(1, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor2, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_2);
		$this->setColorPalette(2, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor3, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_3);
		$this->setColorPalette(3, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor4, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_4);
		$this->setColorPalette(4, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor5, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_5);
		$this->setColorPalette(5, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		
		$radius = $this->heightImg < ($this->widthImg - 100) ? $this->heightImg/2.5 : ($this->widthImg - 100)/2.5;
		
		$rgbColor = $this->_hex2rgb($hexColor6, Piwik_ImageGraph_API::GRAPH_COLOR_PIE_6);
		$this->drawBasicPieGraph($this->data->GetData(), $this->data->GetDataDescription(), $this->widthImg/2, $this->heightImg/2 , $radius, PIE_LABELS, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
	}
	
	public function printBasicBarGraph(	$hexColor0 = false,
										$hexColor1 = false
	)
	{
		$this->_computeAndApplyAbscissaModVal();
		$this->data = $this->_getDataObj();
		
		$rgbColor = $this->_hex2rgb($hexColor0, Piwik_ImageGraph_API::GRAPH_COLOR_BAR_0, 0.5);
		$this->setColorPalette(0, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		$rgbColor = $this->_hex2rgb($hexColor1, Piwik_ImageGraph_API::GRAPH_COLOR_BAR_1, 0.5);
		$this->setColorPalette(1, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		
		$maxMarginTop = $this->imageFontHeight/2;
		$maxMarginTop = $maxMarginTop > 5 ? $maxMarginTop : 5;
		if(!empty($this->metricTitle))
			$this->setGraphArea(5 + $this->imageOrdinateLabelMaxWidth, $maxMarginTop + (3 + $this->imageFontHeight), $this->widthImg, $this->heightImg - (11 + $this->imageFontHeight));
		else
			$this->setGraphArea(5 + $this->imageOrdinateLabelMaxWidth, $maxMarginTop, $this->widthImg, $this->heightImg - (11 + $this->imageFontHeight));
		$this->drawGraphArea(255, 255, 255);
		$this->drawScale($this->data->GetData(), $this->data->GetDataDescription(), SCALE_START0, 0, 0, 0, true, 0, 2, true);
		$this->DivisionCount = 2;
		$this->drawGrid(4, false);
		$this->drawBarGraph($this->data->GetData(), $this->data->GetDataDescription(), true);
		if(!empty($this->metricTitle))
			$this->drawLegend(10 + $this->imageOrdinateLabelMaxWidth,0, $this->data->GetDataDescription(), 255,255,255, -1,-1,-1, 0,0,0, false);
	}
	
	public function printBasicLineGraph($hexColor = false)
	{
		$this->_computeAndApplyAbscissaModVal();
		$this->data = $this->_getDataObj();
		
		$rgbColor = $this->_hex2rgb($hexColor, Piwik_ImageGraph_API::GRAPH_COLOR_LINE);
		$this->setColorPalette(0, $rgbColor['r'], $rgbColor['g'], $rgbColor['b']);
		
		$maxMarginTop = $this->imageFontHeight/2;
		$maxMarginTop = $maxMarginTop > 5 ? $maxMarginTop : 5;
		if(!empty($this->metricTitle))
			$this->setGraphArea(5 + $this->imageOrdinateLabelMaxWidth, $maxMarginTop + (3 + $this->imageFontHeight), $this->widthImg, $this->heightImg - (11 + $this->imageFontHeight));
		else
			$this->setGraphArea(5 + $this->imageOrdinateLabelMaxWidth, $maxMarginTop, $this->widthImg, $this->heightImg - (11 + $this->imageFontHeight));
		$this->drawGraphArea(255, 255, 255);
		$this->drawScale($this->data->GetData(), $this->data->GetDataDescription(), SCALE_START0, 0, 0, 0, true, 0, 2, true);
		$this->DivisionCount = 2;
		$this->drawGrid(4, false);
		$this->drawLineGraph($this->data->GetData(), $this->data->GetDataDescription(), true);
		$this->drawPlotGraph($this->data->GetData(), $this->data->GetDataDescription(), 3, 2, 255, 255, 255);
		if(!empty($this->metricTitle))
			$this->drawLegend(10 + $this->imageOrdinateLabelMaxWidth,0, $this->data->GetDataDescription(), 255,255,255, -1,-1,-1, 0,0,0, false);
	}
	
	public function printException($e)
	{
		$C_TextColor = $this->AllocateColor($this->Picture,0,0,0);
		imagettftext($this->Picture,$this->FontSize,0,5,$this->FontSize+5,$C_TextColor,$this->FontName,$e->getMessage());
	}
	
	private function _truncateSmallValues()
	{
		$others = 0;
		$currCount = count($this->ordinateSerie);
		
		$newOrdinateSerie = array();
		$newAbscissaSerie = array();
		$tmpCount = 0;
		$tmpTmpCount = 0;
		$sum = 0;
		foreach($this->ordinateSerie as $osVal)
		{
			$sum += $osVal;
		}
		foreach($this->ordinateSerie as $osVal)
		{
			if($tmpCount == ($currCount-1))
			{
				break;
			}
			
			if(($osVal / $sum) > 0.01)
			{
				$newOrdinateSerie[$tmpTmpCount] = $osVal;
				$newAbscissaSerie[$tmpTmpCount] = $this->abscissaSerie[$tmpCount];
				$tmpTmpCount++;
			}
			else
			{
				$others += $osVal;
			}
			$tmpCount++;
		}
		$others += $this->ordinateSerie[$currCount-1];
		if(($others / $sum) > 0.01)
		{
			$newOrdinateSerie[$tmpTmpCount] = $others;
			$newAbscissaSerie[$tmpTmpCount] = $this->abscissaSerie[$currCount-1];
		}
		$this->ordinateSerie = $newOrdinateSerie;
		$this->abscissaSerie = $newAbscissaSerie;
	}
	
	private function _computeAndApplyAbscissaModVal()
	{
		//Compute and apply the $abscissaModVal to $abscissaSerie
		//if the graphType is bar or line
		$maxL = 0;
		$abscissaModVal = 1;
		$rowCount = @count($this->abscissaSerie);
		foreach($this->abscissaSerie as $val)
		{
			$temp = strlen($val);
			$maxL = $temp > $maxL ? $temp : $maxL;
		}
		$abscissaModVal = round(($maxL*$this->fontSize)/(($this->widthImg*0.85)/$rowCount));
		if($abscissaModVal <= 0)
		{
			$abscissaModVal = 1;
		}
		foreach($this->abscissaSerie as $idx => &$val)
		{
			$val = ($idx % $abscissaModVal == 0) ? substr($val, 0, 22).(strlen($val) > 22 ? "..." : "") : "";
		}
	}
	
	private function _hex2rgb($hexColor, $default, $alpha = 1.0, $backGrey = 255)
	{
		if(	!is_string($hexColor) ||
			strlen($hexColor) != 6
		)
		{
			$hexColor = $default;
		}
		$hexColor = strtolower($hexColor);
		if(strspn($hexColor, '0123456789abcdef') != 6)
		{
			return false;
		}
		
		$hexR = substr($hexColor, 0, 2);
		$hexG = substr($hexColor, 2, 2);
		$hexB = substr($hexColor, 4, 2);
		
		$r = hexdec($hexR);
		$g = hexdec($hexG);
		$b = hexdec($hexB);
		
		if(	is_numeric($alpha) &&
			$alpha < 1.0 &&
			$alpha > 0.0 &&
			is_numeric($backGrey) &&
			$backGrey < 256 &&
			$backGrey >= 0
		)
		{
			$r *= $alpha; $r += ((1.0-$alpha)*$backGrey);
			$g *= $alpha; $g += ((1.0-$alpha)*$backGrey);
			$b *= $alpha; $b += ((1.0-$alpha)*$backGrey);
		}
		
		return array("r" => $r, "g" => $g, "b" => $b);
	}
	
	/*
	 * Override
	 */
	function drawGrid($LineWidth,$Mosaic=TRUE,$R=220,$G=220,$B=220,$Alpha=100)
    {
     //Draw mosaic
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

     //Horizontal lines
     $YPos = $this->GArea_Y2 - $this->DivisionHeight;
     for($i=1;$i<=($this->DivisionCount+1);$i++)
      {
       if ( $YPos >= $this->GArea_Y1 && $YPos <= $this->GArea_Y2 )
        $this->drawLine($this->GArea_X1,$YPos,$this->GArea_X2,$YPos,$R,$G,$B);//$this->drawDottedLine($this->GArea_X1,$YPos,$this->GArea_X2,$YPos,$LineWidth,$R,$G,$B);
        
       $YPos = $YPos - $this->DivisionHeight;
      }

     /* Vertical lines */
     if ( $this->GAreaXOffset == 0 )
      { $XPos = $this->GArea_X1 + $this->DivisionWidth + $this->GAreaXOffset; $ColCount = $this->DataCount-2; }
     else
      { $XPos = $this->GArea_X1 + $this->GAreaXOffset; $ColCount = floor( ($this->GArea_X2 - $this->GArea_X1) / $this->DivisionWidth ); }

      
      $dataTmp = $this->data->getData();
     for($i=1;$i<=$ColCount;$i++)
      {
      	if ( $XPos > $this->GArea_X1 && $XPos < $this->GArea_X2 )
	       if(strlen($dataTmp[$i-1]['ABSCISSA']) > 0)
	       		$this->drawLine(floor($XPos),$this->GArea_Y1,floor($XPos),$this->GArea_Y2,$R,$G,$B);//$this->drawDottedLine(floor($XPos),$this->GArea_Y1,floor($XPos),$this->GArea_Y2,$LineWidth,$R,$G,$B);
	    $XPos = $XPos + $this->DivisionWidth;
      }
    }
    
    /*
     * Override
     */
	function drawScale($Data,$DataDescription,$ScaleMode,$R,$G,$B,$DrawTicks=TRUE,$Angle=0,$Decimals=1,$WithMargin=FALSE,$SkipLabels=1,$RightScale=FALSE)
    {
     //Validate the Data and DataDescription array
     $this->validateData("drawScale",$Data);

     $C_TextColor         =$this->AllocateColor($this->Picture,$R,$G,$B);

     //$this->drawLine($this->GArea_X1,$this->GArea_Y1,$this->GArea_X1,$this->GArea_Y2,$R,$G,$B);
     $this->drawLine($this->GArea_X1,$this->GArea_Y2,$this->GArea_X2,$this->GArea_Y2,$R,$G,$B);

     if ( $this->VMin == NULL && $this->VMax == NULL)
      {
       if (isset($DataDescription["Values"][0]))
        {
         $this->VMin = $Data[0][$DataDescription["Values"][0]];
         $this->VMax = $Data[0][$DataDescription["Values"][0]];
        }
       else { $this->VMin = 2147483647; $this->VMax = -2147483647; }

       //Compute Min and Max values
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
       elseif ( $ScaleMode == SCALE_ADDALL || $ScaleMode == SCALE_ADDALLSTART0 ) //Experimental
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

       //If all values are the same
       if ( $this->VMax == $this->VMin )
        {
         if ( $this->VMax >= 0 ) { $this->VMax++; }
         else { $this->VMin--; }
        }

       $DataRange = $this->VMax - $this->VMin;
       if ( $DataRange == 0 ) { $DataRange = .1; }

       //Compute automatic scaling
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
            
            if($Factor == 10)
            {
            	$Divisions = 2;
            	$Scale = 1;
            	break;
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
       else //Can occurs for small graphs
        $Scale = 1;

       if ( !isset($Divisions) )
        $Divisions = 2;

       if ($Scale == 1 && $Divisions%2 == 1)
        $Divisions--;
      }
     else
      $Divisions = $this->Divisions;

     $this->DivisionCount = 2;
     $Divisions = 2;

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
        //$this->drawLine($this->GArea_X1,$YPos,$this->GArea_X1-5,$YPos,$R,$G,$B);

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
         imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X2+5,$YPos+($this->FontSize/2),$C_TextColor,$this->FontName,$Value);
         if ( $XMin < $this->GArea_X2+5+$TextWidth || $XMin == NULL ) { $XMin = $this->GArea_X2+5+$TextWidth; }
        }
       else
        {
         imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X1-5-$TextWidth,$YPos+($this->FontSize/2),$C_TextColor,$this->FontName,$Value);
         if ( $XMin > $this->GArea_X1-5-$TextWidth || $XMin == NULL ) { $XMin = $this->GArea_X1-5-$TextWidth; }
        }

       $YPos = $YPos - $this->DivisionHeight;
      }

     //Write the Y Axis caption if set 
     if ( isset($DataDescription["Axis"]["Y"]) )
      {
       $Position   = imageftbbox($this->FontSize,90,$this->FontName,$DataDescription["Axis"]["Y"]);
       $TextHeight = abs($Position[1])+abs($Position[3]);
       $TextTop    = (($this->GArea_Y2 - $this->GArea_Y1) / 2) + $this->GArea_Y1 + ($TextHeight/2);

       if ( $RightScale )
        imagettftext($this->Picture,$this->FontSize,90,$XMin+$this->FontSize,$TextTop,$C_TextColor,$this->FontName,$DataDescription["Axis"]["Y"]);
       else
        ;//imagettftext($this->Picture,$this->FontSize,90,$XMin-$this->FontSize,$TextTop,$C_TextColor,$this->FontName,$DataDescription["Axis"]["Y"]);
      }

     //Horizontal Axis
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
           //$YPos = $this->GArea_Y2+18;
           $YPos = $this->GArea_Y2 + $this->imageFontHeight + 9;
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

    //Write the X Axis caption if set 
    if ( isset($DataDescription["Axis"]["X"]) )
      {
       $Position   = imageftbbox($this->FontSize,90,$this->FontName,$DataDescription["Axis"]["X"]);
       $TextWidth  = abs($Position[2])+abs($Position[0]);
       $TextLeft   = (($this->GArea_X2 - $this->GArea_X1) / 2) + $this->GArea_X1 + ($TextWidth/2);
       //imagettftext($this->Picture,$this->FontSize,0,$TextLeft,$YMax+$this->FontSize+5,$C_TextColor,$this->FontName,$DataDescription["Axis"]["X"]);
      }
    }
    
    /*
     * Override
     */
	function drawGraphArea($R,$G,$B,$Stripe=FALSE)
    {
     $this->drawFilledRectangle($this->GArea_X1,$this->GArea_Y1,$this->GArea_X2,$this->GArea_Y2,$R,$G,$B,FALSE);
     //$this->drawRectangle($this->GArea_X1,$this->GArea_Y1,$this->GArea_X2,$this->GArea_Y2,$R-40,$G-40,$B-40);

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
    
    /*
     * Override
     */
	function drawBarGraph($Data,$DataDescription,$Shadow=FALSE,$Alpha=100)
    {
     //Validate the Data and DataDescription array
     $this->validateDataDescription("drawBarGraph",$DataDescription);
     $this->validateData("drawBarGraph",$Data);

     $GraphID      = 0;
     $Series       = count($DataDescription["Values"]);
     $SeriesWidth  = $this->DivisionWidth / ($Series+1);
     $SeriesWidth *= 1.7;
     $SerieXOffset = $this->DivisionWidth*1.7 / 2 - $SeriesWidth / 2;
     $SeriesWidth = round($SeriesWidth); //EDIT
     $SerieXOffset = round($SerieXOffset); //EDIT
     
     $YZero  = $this->GArea_Y2 - ((0-$this->VMin) * $this->DivisionRatio);
     if ( $YZero > $this->GArea_Y2 ) { $YZero = $this->GArea_Y2; }

     $SerieID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       $XPos  = $this->GArea_X1 + $this->GAreaXOffset - $SerieXOffset + $SeriesWidth * $SerieID;
       $XPos = round($XPos); //EDIT
       $XLast = -1;
       foreach ( $Data as $Key => $Values )
        {
         if ( isset($Data[$Key][$ColName]))
          {
           if ( is_numeric($Data[$Key][$ColName]) )
            {
             $Value = $Data[$Key][$ColName];
             $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);
             $YPos = round($YPos); //EDIT

             //Save point into the image map if option activated
             if ( $this->BuildMap )
              {
               $this->addToImageMap($XPos+1,min($YZero,$YPos),$XPos+$SeriesWidth-1,max($YZero,$YPos),$DataDescription["Description"][$ColName],$Data[$Key][$ColName].$DataDescription["Unit"]["Y"],"Bar");
              }
           
             $this->drawRectangle($XPos,$YZero,$XPos+$SeriesWidth,$YPos-1,25,25,25,$this->Palette[$ColorID+1]["R"],$this->Palette[$ColorID+1]["G"],$this->Palette[$ColorID+1]["B"],TRUE,$Alpha);
             $this->drawFilledRectangle($XPos+1,$YZero-1,$XPos+$SeriesWidth-1,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE,$Alpha);
            }
          }
         $XPos = $XPos + $this->DivisionWidth;
        }
       $SerieID++;
      }
    }
    
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
     $Angle    = 35; $TopPlots = "";
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

         $C_TextColor = $this->AllocateColor($this->Picture,0,0,0);
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
             {
              /*if(abs(($YPos-$YLast) / ($XPos-$XLast)) > 1)
              {
              	$this->drawLine($XLast-1,$YLast,$XPos-1,$YPos,($this->Palette[$ColorID]["R"]*0.15)+216,($this->Palette[$ColorID]["G"]*0.15)+216,($this->Palette[$ColorID]["B"]*0.15)+216,TRUE);
              	$this->drawLine($XLast+1,$YLast,$XPos+1,$YPos,($this->Palette[$ColorID]["R"]*0.15)+216,($this->Palette[$ColorID]["G"]*0.15)+216,($this->Palette[$ColorID]["B"]*0.15)+216,TRUE);
              }
              else
              {
              	$this->drawLine($XLast,$YLast-1,$XPos,$YPos-1,($this->Palette[$ColorID]["R"]*0.15)+216,($this->Palette[$ColorID]["G"]*0.15)+216,($this->Palette[$ColorID]["B"]*0.15)+216,TRUE);
              	$this->drawLine($XLast,$YLast+1,$XPos,$YPos+1,($this->Palette[$ColorID]["R"]*0.15)+216,($this->Palette[$ColorID]["G"]*0.15)+216,($this->Palette[$ColorID]["B"]*0.15)+216,TRUE);
              }*/
              $this->drawLine($XLast,$YLast,$XPos,$YPos,$this->Palette[$ColorID]["R"],$this->Palette[$ColorID]["G"],$this->Palette[$ColorID]["B"],TRUE);
             }

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
    
	function drawLine($X1,$Y1,$X2,$Y2,$R,$G,$B,$GraphFunction=FALSE)
    {
    	if(!$this->aliasedGraph)
    	{
    		imageline($this->Picture, $X1, $Y1, $X2, $Y2, imagecolorallocate($this->Picture, $R, $G, $B));
    		return;
    	}
    	
    	parent::drawLine($X1,$Y1,$X2,$Y2,$R,$G,$B,$GraphFunction);
    }
    
    function drawRectangle($X1,$Y1,$X2,$Y2,$R,$G,$B)
    {
     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_Rectangle = $this->AllocateColor($this->Picture,$R,$G,$B);
     
     imagerectangle($this->Picture, $X1,$Y1,$X2,$Y2, $C_Rectangle);

     /*$X1=$X1-.2;$Y1=$Y1-.2;
     $X2=$X2+.2;$Y2=$Y2+.2;
     $this->drawLine($X1,$Y1,$X2,$Y1,$R,$G,$B);
     $this->drawLine($X2,$Y1,$X2,$Y2,$R,$G,$B);
     $this->drawLine($X2,$Y2,$X1,$Y2,$R,$G,$B);
     $this->drawLine($X1,$Y2,$X1,$Y1,$R,$G,$B);*/
    }

   /* This function create a filled rectangle with antialias */
   function drawFilledRectangle($X1,$Y1,$X2,$Y2,$R,$G,$B,$DrawBorder=TRUE,$Alpha=100,$NoFallBack=FALSE)
    {
     if ( $X2 < $X1 ) { list($X1, $X2) = array($X2, $X1); }
     if ( $Y2 < $Y1 ) { list($Y1, $Y2) = array($Y2, $Y1); }

     if ( $R < 0 ) { $R = 0; } if ( $R > 255 ) { $R = 255; }
     if ( $G < 0 ) { $G = 0; } if ( $G > 255 ) { $G = 255; }
     if ( $B < 0 ) { $B = 0; } if ( $B > 255 ) { $B = 255; }

     $C_Rectangle = $this->AllocateColor($this->Picture,$R,$G,$B);
     
     imagefilledrectangle($this->Picture, $X1,$Y1,$X2,$Y2, $C_Rectangle);

     /*if ( $Alpha == 100 )
      {
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
      }*/
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

     $YOffset = $this->FontSize; $ID = 0;
     foreach($DataDescription["Description"] as $Key => $Value)
      {
       $Position   = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextHeight = $Position[1]-$Position[7];

       $this->drawFilledRectangle($XPos,$YPos+$YOffset-($TextHeight/2)+1,$XPos+8,$YPos+$YOffset-($TextHeight/2),$this->Palette[$ID]["R"],$this->Palette[$ID]["G"],$this->Palette[$ID]["B"]);
       //$this->drawFilledRoundedRectangle($XPos+10,$YPos+$YOffset-4,$XPos+14,$YPos+$YOffset-4,2,$this->Palette[$ID]["R"],$this->Palette[$ID]["G"],$this->Palette[$ID]["B"]);
       imagettftext($this->Picture,$this->FontSize,0,$XPos+12,$YPos+$YOffset,$C_TextColor,$this->FontName,$Value);

       $YOffset = $YOffset + $TextHeight + 4;
       $ID++;
      }
    }
	
}