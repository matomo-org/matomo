<?php
/*
 * Sparkline PHP Graphing Library
 * Copyright 2004 James Byers <jbyers@gmail.com>
 * http://sparkline.org
 *
 * Dual-licensed under the BSD (LICENSE-BSD.txt) and GPL (LICENSE-GPL.txt)
 * licenses.
 *
 * $Id: Sparkline_Line.php,v 1.10 2008/03/11 19:12:49 jbyers Exp $
 *
 */

require_once dirname(__FILE__).'/Sparkline.php';

class Sparkline_Line extends Sparkline {

  var $dataSeries;
  var $dataSeriesStats;
  var $dataSeriesConverted;
  var $yMin;
  var $yMax;
  var $featurePoint;

  ////////////////////////////////////////////////////////////////////////////
  // constructor
  //
  function Sparkline_Line($catch_errors = true) {
    parent::Sparkline($catch_errors);

    $this->dataSeries          = array();
    $this->dataSeriesStats     = array();
    $this->dataSeriesConverted = array();

    $this->featurePoint        = array();
  } // function Sparkline

  ////////////////////////////////////////////////////////////////////////////
  // data setting
  //
  function SetData($x, $y, $series = 1) {
	if(!is_numeric($x)) {
	    $x = trim($x);
	}
	if(!is_numeric($y)) {
	    $y = trim($y);
	}

    $this->Debug("Sparkline_Line :: SetData($x, $y, $series)", DEBUG_SET);

    if (!is_numeric($x) || 
        !is_numeric($y)) {
      $this->Debug("Sparkline_Line :: SetData rejected values($x, $y) in series $series", DEBUG_WARNING);
      return false;
    } // if

    $this->dataSeries[$series][$x] = $y;
   
    if (!isset($this->dataSeriesStats[$series]['yMin']) ||
        $y < $this->dataSeriesStats[$series]['yMin']) {
      $this->dataSeriesStats[$series]['yMin'] = $y;
    }

    if (!isset($this->dataSeriesStats[$series]['xMin']) ||
        $x < $this->dataSeriesStats[$series]['xMin']) {
      $this->dataSeriesStats[$series]['xMin'] = $x;
    }

    if (!isset($this->dataSeriesStats[$series]['yMax']) ||
        $y > $this->dataSeriesStats[$series]['yMax']) {
      $this->dataSeriesStats[$series]['yMax'] = $y;
    }

    if (!isset($this->dataSeriesStats[$series]['xMax']) ||
        $x > $this->dataSeriesStats[$series]['xMax']) {
      $this->dataSeriesStats[$series]['xMax'] = $x;
    }
  } // function SetData

  function SetYMin($value) {
    $this->Debug("Sparkline_Line :: SetYMin($value)", DEBUG_SET);
    $this->yMin = $value;
  } // function SetYMin

  function SetYMax($value) {
    $this->Debug("Sparkline_Line :: SetYMax($value)", DEBUG_SET);
    $this->yMax = $value;
  } // function SetYMin

  function ConvertDataSeries($series, $xBound, $yBound) {
    $this->Debug("Sparkline_Line :: ConvertDataSeries($series, $xBound, $yBound)", DEBUG_CALLS);

    if (!isset($this->yMin)) {
      $this->yMin = $this->dataSeriesStats[$series]['yMin'];
    }

    if (!isset($this->xMin)) {
      $this->xMin = $this->dataSeriesStats[$series]['XMin'];
    }

    if (!isset($this->yMax)) {
      $this->yMax = $this->dataSeriesStats[$series]['yMax'];
    }

    if (!isset($this->xMax)) {
      $this->xMax = $this->dataSeriesStats[$series]['xMax'];
    }

    $this->yRange = $this->yMax + ($this->yMin * -1);

    for ($i = 0; $i < sizeof($this->dataSeries[$series]); $i++) {
      $y = round(($this->dataSeries[$series][$i] + ($this->yMin * -1)) * (($yBound-1) / $this->yRange));
      $x = round($i * $xBound / (sizeof($this->dataSeries[$series])));
      $this->dataSeriesConverted[$series][] = array($x, $y);
      $this->Debug("Sparkline :: ConvertDataSeries series $series value $i ($x, $y)", DEBUG_SET);
    }
  } // function ConvertDataSeries

  ////////////////////////////////////////////////////////////////////////////
  // features
  // 
  function SetFeaturePoint($x, $y, $color, $diameter, $text = '', $position = TEXT_TOP, $font = FONT_1) {
    $this->Debug("Sparkline_Line :: SetFeaturePoint($x, $y, '$color', $diameter, '$text')", DEBUG_CALLS);

    $this->featurePoint[] = array('ptX'      => $x,
                                  'ptY'      => $y,
                                  'color'    => $color,
                                  'diameter' => $diameter,
                                  'text'     => $text,
                                  'textpos'  => $position,
                                  'font'     => $font);
  } // function SetFeaturePoint

  ////////////////////////////////////////////////////////////////////////////
  // low quality rendering
  //
  function Render($x, $y) {
    $this->Debug("Sparkline_Line :: Render($x, $y)", DEBUG_CALLS);

    if (!parent::Init($x, $y)) {
      return false;
    }

    // convert based on graphAreaPx bounds
    //
    $this->ConvertDataSeries(1, $this->GetGraphWidth(), $this->GetGraphHeight());

    // stats debugging
    //
    $this->Debug('Sparkline_Line :: Draw' . 
                 ' series: 1 min: ' . $this->dataSeriesStats[1]['yMin'] . 
                 ' max: ' .           $this->dataSeriesStats[1]['yMax'] . 
                 ' offset: ' .        ($this->dataSeriesStats[1]['yMin'] * -1) . 
                 ' height: ' .        $this->GetGraphHeight() + 1 . 
                 ' yfactor: ' .       ($this->GetGraphHeight() / ($this->dataSeriesStats[1]['yMax'] + ($this->dataSeriesStats[1]['yMin'] * -1))));
    $this->Debug('Sparkline_Line :: Draw' .
                 ' drawing area:' . 
                 ' (' . $this->graphAreaPx[0][0] . ',' . $this->graphAreaPx[0][1] .  '), ' . 
                 ' (' . $this->graphAreaPx[1][0] . ',' . $this->graphAreaPx[1][1] .  ')');

    $this->DrawBackground();

    // draw graph
    //
    for ($i = 0; $i < sizeof($this->dataSeriesConverted[1]) - 1; $i++) {
      $this->DrawLine($this->dataSeriesConverted[1][$i][0] + $this->graphAreaPx[0][0], 
                      $this->dataSeriesConverted[1][$i][1] + $this->graphAreaPx[0][1], 
                      $this->dataSeriesConverted[1][$i+1][0] + $this->graphAreaPx[0][0], 
                      $this->dataSeriesConverted[1][$i+1][1] + $this->graphAreaPx[0][1],  
                      'black');
    }

    // draw features
    //
    while (list(, $v) = each($this->featurePoint)) {
      $pxY = round(($v['ptY'] + ($this->yMin * -1)) * ($this->GetGraphHeight() / $this->yRange));
      $pxX = round($v['ptX'] * $this->GetGraphWidth() / sizeof($this->dataSeries[1]));

      $this->DrawCircleFilled($pxX + $this->graphAreaPx[0][0], 
                              $pxY + $this->graphAreaPx[0][1], 
                              $v['diameter'], 
                              $v['color'], 
                              $this->imageHandle);
      $this->DrawTextRelative($v['text'],
                              $pxX + $this->graphAreaPx[0][0], 
                              $pxY + $this->graphAreaPx[0][1], 
                              $v['color'], 
                              $v['textpos'], 
                              round($v['diameter'] / 2),
                              $v['font'],
                              $this->imageHandle);
    }
  } // function Render

  ////////////////////////////////////////////////////////////////////////////
  // high quality rendering
  //
  function RenderResampled($x, $y) {
    $this->Debug("Sparkline_Line :: RenderResampled($x, $y)", DEBUG_CALLS);

    if (!parent::Init($x, $y)) {
      return false;
    }

    // draw background on standard image in case of resample blit miss
    //
    $this->DrawBackground($this->imageHandle);

    // convert based on virtual canvas: x based on size of dataset, y scaled proportionately
    // if size of data set is small, default to 4X target canvas size
    //
    $xVC = max(sizeof($this->dataSeries[1]), 4 * $x);
    $yVC = floor($xVC * ($this->GetGraphHeight() / $this->GetGraphWidth()));
    $this->ConvertDataSeries(1, $xVC, $yVC);

    // stats debugging
    //
    $this->Debug('Sparkline_Line :: DrawResampled' . 
                 ' series: 1 min: ' . $this->dataSeriesStats[1]['yMin'] . 
                 ' max: ' . $this->dataSeriesStats[1]['yMax'] . 
                 ' offset: ' . ($this->dataSeriesStats[1]['yMin'] * -1) . 
                 ' height: ' . $this->GetGraphHeight() . 
                 ' yfactor: ' . ($this->GetGraphHeight() / ($this->dataSeriesStats[1]['yMax'] + ($this->dataSeriesStats[1]['yMin'] * -1))), DEBUG_STATS);
    $this->Debug('Sparkline_Line :: DrawResampled' .
                 ' drawing area:' . 
                 ' (' . $this->graphAreaPx[0][0] . ',' . $this->graphAreaPx[0][1] .  '), ' . 
                 ' (' . $this->graphAreaPx[1][0] . ',' . $this->graphAreaPx[1][1] .  ')');

    // create virtual image
    // allocate colors
    // draw background, graph
    // resample and blit onto original graph
    //
    $imageVCHandle = $this->CreateImageHandle($xVC, $yVC);

    while (list($k, $v) = each($this->colorList)) {
      $this->SetColorHandle($k, $this->DrawColorAllocate($k, $imageVCHandle));
    }
    reset($this->colorList);

    $this->DrawBackground($imageVCHandle);

    for ($i = 0; $i < sizeof($this->dataSeriesConverted[1]) - 1; $i++) {
      $this->DrawLine($this->dataSeriesConverted[1][$i][0],
                      $this->dataSeriesConverted[1][$i][1],
                      $this->dataSeriesConverted[1][$i+1][0],
                      $this->dataSeriesConverted[1][$i+1][1],
                      'black', 
                      $this->GetLineSize(), 
                      $imageVCHandle);
    }

    $this->DrawImageCopyResampled($this->imageHandle, 
                                  $imageVCHandle, 
                                  $this->graphAreaPx[0][0], // dest x
                                  $this->GetImageHeight() - $this->graphAreaPx[1][1], // dest y
                                  0, 0,                     // src x, y
                                  $this->GetGraphWidth(),   // dest width
                                  $this->GetGraphHeight(),  // dest height
                                  $xVC,                     // src  width
                                  $yVC);                    // src  height

    // draw features
    //
    while (list(, $v) = each($this->featurePoint)) {
      $pxY = round(($v['ptY'] + ($this->yMin * -1)) * ($this->GetGraphHeight() / $this->yRange));
      $pxX = round($v['ptX'] * $this->GetGraphWidth() / sizeof($this->dataSeries[1]));

      $this->DrawCircleFilled($pxX + $this->graphAreaPx[0][0], 
                              $pxY + $this->graphAreaPx[0][1], 
                              $v['diameter'], 
                              $v['color'], 
                              $this->imageHandle);
      $this->DrawTextRelative($v['text'],
                              $pxX + $this->graphAreaPx[0][0], 
                              $pxY + $this->graphAreaPx[0][1], 
                              $v['color'], 
                              $v['textpos'], 
                              round($v['diameter'] / 2),
                              $v['font'],
                              $this->imageHandle);
    }
  } // function RenderResampled
} // class Sparkline_Line

?>
