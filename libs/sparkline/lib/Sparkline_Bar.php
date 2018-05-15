<?php
/*
 * Sparkline PHP Graphing Library
 * Copyright 2004 James Byers <jbyers@gmail.com>
 * http://sparkline.org
 *
 * Dual-licensed under the BSD (LICENSE-BSD.txt) and GPL (LICENSE-GPL.txt)
 * licenses.
 *
 * $Id: Sparkline_Bar.php,v 1.3 2008/03/11 19:12:49 jbyers Exp $
 *
 */

require_once dirname(__FILE__).'/Sparkline.php';

class Sparkline_Bar extends Sparkline {

  var $dataSeries;
  var $dataSeriesStats;
  var $dataSeriesConverted;
  var $yMin;
  var $yMax;
  var $barWidth;
  var $barSpacing;
  var $barColorDefault;
  var $barColorUnderscoreDefault;

  ////////////////////////////////////////////////////////////////////////////
  // constructor
  //
  function __construct($catch_errors = true) {
    parent::__construct($catch_errors);

    $this->dataSeries                = array();
    $this->dataSeriesStats           = array();
    $this->dataSeriesConverted       = array();
    $this->barWidth                  = 1;
    $this->barSpacing                = 1;
    $this->barColorDefault           = 'black';
    $this->barColorUnderscoreDefault = 'black';
  } // function Sparkline

  ////////////////////////////////////////////////////////////////////////////
  // color, image property setting
  //
  function SetBarWidth($value) {
    $this->Debug("Sparkline_Bar :: SetBarWidth($value)", DEBUG_SET);
    $this->barWidth = $value;
  } // function SetBarWidth

  function SetBarSpacing($value) {
    $this->Debug("Sparkline_Bar :: SetBarSpacing($value)", DEBUG_SET);
    $this->barSpacing = $value;
  } // function SetBarSpacing

  function SetBarColorDefault($value) {
    $this->Debug("Sparkline_Bar :: SetBarColorDefault($value)", DEBUG_SET);
    $this->barColorDefault = $value;
  } // function SetBarColorDefault

  function SetBarColorUnderscoreDefault($value) {
    $this->Debug("Sparkline_Bar :: SetBarColorUnderscoreDefault($value)", DEBUG_SET);
    $this->barColorUnderscoreDefault = $value;
  } // function SetBarColorUnderscoreDefault

  ////////////////////////////////////////////////////////////////////////////
  // data setting
  //
  function SetData($x, $y, $color = null, $underscore = false, $series = 1) {
	if(!is_numeric($x)) {
	    $x = trim($x);
	}
	if(!is_numeric($y)) {
	    $y = trim($y);
	}

    $this->Debug("Sparkline_Bar :: SetData($x, $y, $series)", DEBUG_SET);

    if (!is_numeric($x) || 
        !is_numeric($y)) {
      $this->Debug("Sparkline_Bar :: SetData rejected values($x, $y) in series $series", DEBUG_WARNING);
      return false;
    } // if

    if ($color == null) {
      $color = $this->barColorDefault;
    }

    $this->dataSeries[$series][$x] = array('value'      => $y,
                                           'color'      => $color,
                                           'underscore' => $underscore);

    if (!isset($this->dataSeriesStats[$series]['min']) ||
        $y < $this->dataSeriesStats[$series]['min']) {
      $this->dataSeriesStats[$series]['min'] = $y;
    }

    if (!isset($this->dataSeriesStats[$series]['max']) ||
        abs($y) > $this->dataSeriesStats[$series]['max']) {
      $this->dataSeriesStats[$series]['max'] = abs($y);
    }
  } // function SetData

  function SetYMin($value) {
    $this->Debug("Sparkline_Bar :: SetYMin($value)", DEBUG_SET);
    $this->yMin = $value;
  }

  function SetYMax($value) {
    $this->Debug("Sparkline_Bar :: SetYMax($value)", DEBUG_SET);
    $this->yMax = $value;
  }
  
  function ConvertDataSeries($series, $xBound, $yBound) {
    $this->Debug("Sparkline_Bar :: ConvertDataSeries($series, $xBound, $yBound)", DEBUG_CALLS);

    if (!isset($this->yMin)) {
      $this->yMin = $this->dataSeriesStats[$series]['min'];
    }

    if (!isset($this->yMax)) {
      $this->yMax = $this->dataSeriesStats[$series]['max'];
    }

    while (list(, $v) = each($this->dataSeries[$series])) {
      $y = floor($v['value'] * ($yBound / (abs($this->yMax) + abs($this->yMin))));
      $this->dataSeriesConverted[$series][] = array('value'      => $y,
                                                    'color'      => $v['color'],
                                                    'underscore' => $v['underscore']);

      if (!isset($this->dataSeriesStats[$series]['min_converted']) ||
          $y < $this->dataSeriesStats[$series]['min_converted']) {
        $this->dataSeriesStats[$series]['min_converted'] = $y;
      }
      
      if (!isset($this->dataSeriesStats[$series]['max_converted']) ||
          abs($y) > $this->dataSeriesStats[$series]['max_converted']) {
        $this->dataSeriesStats[$series]['max_converted'] = abs($y);
      }
    }
    reset($this->dataSeries[$series]);

  } // function ConvertDataSeries

  function CalculateImageWidth() {
    $this->Debug("Sparkline_Bar :: CalculateImageWidth()", DEBUG_CALLS);

    $count = sizeof($this->dataSeries[1]); 
    return (($count - 1) * $this->barSpacing) + ($count * $this->barWidth);
  } // function CalculateImageWidth
  
  ////////////////////////////////////////////////////////////////////////////
  // rendering
  //
  function Render($y) {
    $this->Debug("Sparkline_Bar :: Render($y)", DEBUG_CALLS);

    // calculate size based on sets for init
    //
    if (!parent::Init($this->CalculateImageWidth(), $y)) {
      return false;
    }

    // convert based on actual canvas size
    //
    $this->ConvertDataSeries(1, $this->GetGraphWidth(), $this->GetGraphHeight());

    // stats debugging
    //
    $this->Debug('Sparkline_Bar :: Draw' . 
                 ' series: 1 min: ' . $this->dataSeriesStats[1]['min'] . 
                 ' max: ' . $this->dataSeriesStats[1]['max'] . 
                 ' height: ' . $this->GetGraphHeight() . 
                 ' yfactor: ' . ($this->GetGraphHeight() / (abs($this->dataSeriesStats[1]['max']) + abs($this->dataSeriesStats[1]['min']))));

    $this->DrawBackground();

    $yAxis = abs(min($this->dataSeriesStats[1]['min_converted'], 0));
    for ($i = 0; $i < sizeof($this->dataSeriesConverted[1]); $i++) {
      $this->DrawRectangleFilled($i * ($this->barWidth + $this->barSpacing), 
                                 $yAxis, 
                                 $i * ($this->barWidth + $this->barSpacing) + $this->barWidth - 1, 
                                 $yAxis + $this->dataSeriesConverted[1][$i]['value'], 
                                 $this->dataSeriesConverted[1][$i]['color']);
      if ($this->dataSeriesConverted[1][$i]['underscore']) {
        $this->DrawLine(max(0, $i * ($this->barWidth + $this->barSpacing) - ($this->barSpacing / 2)),
                        $yAxis,
                        min($this->GetGraphWidth(), $i * ($this->barWidth + $this->barSpacing) + ($this->barSpacing / 2)),
                        $yAxis,
                        $this->barColorUnderscoreDefault);
      }
    }
  } // function Render
} // class Sparkline_Bar

?>
