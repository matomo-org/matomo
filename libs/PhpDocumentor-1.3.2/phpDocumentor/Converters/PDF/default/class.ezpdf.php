<?php
/**
 * @package Cpdf
 */
/**
 * Cpdf class
 */
include_once('phpDocumentor/Converters/PDF/default/class.pdf.php');

/**
 * This class will take the basic interaction facilities of the Cpdf class
 * and make more useful functions so that the user does not have to 
 * know all the ins and outs of pdf presentation to produce something pretty.
 *
 * IMPORTANT NOTE
 * there is no warranty, implied or otherwise with this software.
 * 
 * @version 009 (versioning is linked to class.pdf.php)
 * released under a public domain licence.
 * @author Wayne Munro, R&OS Ltd, {@link http://www.ros.co.nz/pdf}
 * @package Cpdf
 */
class Cezpdf extends Cpdf {
//==============================================================================
// this class will take the basic interaction facilities of the Cpdf class
// and make more useful functions so that the user does not have to 
// know all the ins and outs of pdf presentation to produce something pretty.
//
// IMPORTANT NOTE
// there is no warranty, implied or otherwise with this software.
// 
// version 009 (versioning is linked to class.pdf.php)
//
// released under a public domain licence.
//
// Wayne Munro, R&OS Ltd, http://www.ros.co.nz/pdf
//==============================================================================

var $ez=array('fontSize'=>10); // used for storing most of the page configuration parameters
var $y; // this is the current vertical positon on the page of the writing point, very important
var $ezPages=array(); // keep an array of the ids of the pages, making it easy to go back and add page numbers etc.
var $ezPageCount=0;

// ------------------------------------------------------------------------------

function Cezpdf($paper='a4',$orientation='portrait'){
	// Assuming that people don't want to specify the paper size using the absolute coordinates
	// allow a couple of options:
	// orientation can be 'portrait' or 'landscape'
	// or, to actually set the coordinates, then pass an array in as the first parameter.
	// the defaults are as shown.
	// 
	// -------------------------
	// 2002-07-24 - Nicola Asuni (info@tecnick.com):
	// Added new page formats (45 standard ISO paper formats and 4 american common formats)
	// paper cordinates are calculated in this way: (inches * 72) where 1 inch = 2.54 cm
	// 
	// Now you may also pass a 2 values array containing the page width and height in centimeters
	// -------------------------

	if (!is_array($paper)){
		switch (strtoupper($paper)){
			case '4A0': {$size = array(0,0,4767.87,6740.79); break;}
			case '2A0': {$size = array(0,0,3370.39,4767.87); break;}
			case 'A0': {$size = array(0,0,2383.94,3370.39); break;}
			case 'A1': {$size = array(0,0,1683.78,2383.94); break;}
			case 'A2': {$size = array(0,0,1190.55,1683.78); break;}
			case 'A3': {$size = array(0,0,841.89,1190.55); break;}
			case 'A4': default: {$size = array(0,0,595.28,841.89); break;}
			case 'A5': {$size = array(0,0,419.53,595.28); break;}
			case 'A6': {$size = array(0,0,297.64,419.53); break;}
			case 'A7': {$size = array(0,0,209.76,297.64); break;}
			case 'A8': {$size = array(0,0,147.40,209.76); break;}
			case 'A9': {$size = array(0,0,104.88,147.40); break;}
			case 'A10': {$size = array(0,0,73.70,104.88); break;}
			case 'B0': {$size = array(0,0,2834.65,4008.19); break;}
			case 'B1': {$size = array(0,0,2004.09,2834.65); break;}
			case 'B2': {$size = array(0,0,1417.32,2004.09); break;}
			case 'B3': {$size = array(0,0,1000.63,1417.32); break;}
			case 'B4': {$size = array(0,0,708.66,1000.63); break;}
			case 'B5': {$size = array(0,0,498.90,708.66); break;}
			case 'B6': {$size = array(0,0,354.33,498.90); break;}
			case 'B7': {$size = array(0,0,249.45,354.33); break;}
			case 'B8': {$size = array(0,0,175.75,249.45); break;}
			case 'B9': {$size = array(0,0,124.72,175.75); break;}
			case 'B10': {$size = array(0,0,87.87,124.72); break;}
			case 'C0': {$size = array(0,0,2599.37,3676.54); break;}
			case 'C1': {$size = array(0,0,1836.85,2599.37); break;}
			case 'C2': {$size = array(0,0,1298.27,1836.85); break;}
			case 'C3': {$size = array(0,0,918.43,1298.27); break;}
			case 'C4': {$size = array(0,0,649.13,918.43); break;}
			case 'C5': {$size = array(0,0,459.21,649.13); break;}
			case 'C6': {$size = array(0,0,323.15,459.21); break;}
			case 'C7': {$size = array(0,0,229.61,323.15); break;}
			case 'C8': {$size = array(0,0,161.57,229.61); break;}
			case 'C9': {$size = array(0,0,113.39,161.57); break;}
			case 'C10': {$size = array(0,0,79.37,113.39); break;}
			case 'RA0': {$size = array(0,0,2437.80,3458.27); break;}
			case 'RA1': {$size = array(0,0,1729.13,2437.80); break;}
			case 'RA2': {$size = array(0,0,1218.90,1729.13); break;}
			case 'RA3': {$size = array(0,0,864.57,1218.90); break;}
			case 'RA4': {$size = array(0,0,609.45,864.57); break;}
			case 'SRA0': {$size = array(0,0,2551.18,3628.35); break;}
			case 'SRA1': {$size = array(0,0,1814.17,2551.18); break;}
			case 'SRA2': {$size = array(0,0,1275.59,1814.17); break;}
			case 'SRA3': {$size = array(0,0,907.09,1275.59); break;}
			case 'SRA4': {$size = array(0,0,637.80,907.09); break;}
			case 'LETTER': {$size = array(0,0,612.00,792.00); break;}
			case 'LEGAL': {$size = array(0,0,612.00,1008.00); break;}
			case 'EXECUTIVE': {$size = array(0,0,521.86,756.00); break;}
			case 'FOLIO': {$size = array(0,0,612.00,936.00); break;}
		}
		switch (strtolower($orientation)){
			case 'landscape':
				$a=$size[3];
				$size[3]=$size[2];
				$size[2]=$a;
				break;
		}
	} else {
		if (count($paper)>2) {
			// then an array was sent it to set the size
			$size = $paper;
		}
		else { //size in centimeters has been passed
			$size[0] = 0;
			$size[1] = 0;
			$size[2] = ( $paper[0] / 2.54 ) * 72;
			$size[3] = ( $paper[1] / 2.54 ) * 72;
		}
	}
	$this->Cpdf($size);
	$this->ez['pageWidth']=$size[2];
	$this->ez['pageHeight']=$size[3];
	
	// also set the margins to some reasonable defaults
	$this->ez['topMargin']=30;
	$this->ez['bottomMargin']=30;
	$this->ez['leftMargin']=30;
	$this->ez['rightMargin']=30;
	
	// set the current writing position to the top of the first page
	$this->y = $this->ez['pageHeight']-$this->ez['topMargin'];
	// and get the ID of the page that was created during the instancing process.
	$this->ezPages[1]=$this->getFirstPageId();
	$this->ezPageCount=1;
}

// ------------------------------------------------------------------------------
// 2002-07-24: Nicola Asuni (info@tecnick.com)
// Set Margins in centimeters
function ezSetCmMargins($top,$bottom,$left,$right){
	$top = ( $top / 2.54 ) * 72;
	$bottom = ( $bottom / 2.54 ) * 72;
	$left = ( $left / 2.54 ) * 72;
	$right = ( $right / 2.54 ) * 72;
	$this->ezSetMargins($top,$bottom,$left,$right);
}
// ------------------------------------------------------------------------------


function ezColumnsStart($options=array()){
  // start from the current y-position, make the set number of columne
  if (isset($this->ez['columns']) && $this->ez['columns']==1){
    // if we are already in a column mode then just return.
    return;
  }
  $def=array('gap'=>10,'num'=>2);
  foreach($def as $k=>$v){
    if (!isset($options[$k])){
      $options[$k]=$v;
    }
  }
  // setup the columns
  $this->ez['columns']=array('on'=>1,'colNum'=>1);

  // store the current margins
  $this->ez['columns']['margins']=array(
     $this->ez['leftMargin']
    ,$this->ez['rightMargin']
    ,$this->ez['topMargin']
    ,$this->ez['bottomMargin']
  );
  // and store the settings for the columns
  $this->ez['columns']['options']=$options;
  // then reset the margins to suit the new columns
  // safe enough to assume the first column here, but start from the current y-position
  $this->ez['topMargin']=$this->ez['pageHeight']-$this->y;
  $width=($this->ez['pageWidth']-$this->ez['leftMargin']-$this->ez['rightMargin']-($options['num']-1)*$options['gap'])/$options['num'];
  $this->ez['columns']['width']=$width;
  $this->ez['rightMargin']=$this->ez['pageWidth']-$this->ez['leftMargin']-$width;
  
}
// ------------------------------------------------------------------------------
function ezColumnsStop(){
  if (isset($this->ez['columns']) && $this->ez['columns']['on']==1){
    $this->ez['columns']['on']=0;
    $this->ez['leftMargin']=$this->ez['columns']['margins'][0];
    $this->ez['rightMargin']=$this->ez['columns']['margins'][1];
    $this->ez['topMargin']=$this->ez['columns']['margins'][2];
    $this->ez['bottomMargin']=$this->ez['columns']['margins'][3];
  }
}
// ------------------------------------------------------------------------------
function ezInsertMode($status=1,$pageNum=1,$pos='before'){
  // puts the document into insert mode. new pages are inserted until this is re-called with status=0
  // by default pages wil be inserted at the start of the document
  switch($status){
    case '1':
      if (isset($this->ezPages[$pageNum])){
        $this->ez['insertMode']=1;
        $this->ez['insertOptions']=array('id'=>$this->ezPages[$pageNum],'pos'=>$pos);
      }
      break;
    case '0':
      $this->ez['insertMode']=0;
      break;
  }
}
// ------------------------------------------------------------------------------

function ezNewPage(){
  $pageRequired=1;
  if (isset($this->ez['columns']) && $this->ez['columns']['on']==1){
    // check if this is just going to a new column
    // increment the column number
//echo 'HERE<br>';
    $this->ez['columns']['colNum']++;
//echo $this->ez['columns']['colNum'].'<br>';
    if ($this->ez['columns']['colNum'] <= $this->ez['columns']['options']['num']){
      // then just reset to the top of the next column
      $pageRequired=0;
    } else {
      $this->ez['columns']['colNum']=1;
      $this->ez['topMargin']=$this->ez['columns']['margins'][2];
    }

    $width = $this->ez['columns']['width'];
    $this->ez['leftMargin']=$this->ez['columns']['margins'][0]+($this->ez['columns']['colNum']-1)*($this->ez['columns']['options']['gap']+$width);
    $this->ez['rightMargin']=$this->ez['pageWidth']-$this->ez['leftMargin']-$width;
  }
//echo 'left='.$this->ez['leftMargin'].'   right='.$this->ez['rightMargin'].'<br>';

  if ($pageRequired){
    // make a new page, setting the writing point back to the top
    $this->y = $this->ez['pageHeight']-$this->ez['topMargin'];
    // make the new page with a call to the basic class.
    $this->ezPageCount++;
    if (isset($this->ez['insertMode']) && $this->ez['insertMode']==1){
      $id = $this->ezPages[$this->ezPageCount] = $this->newPage(1,$this->ez['insertOptions']['id'],$this->ez['insertOptions']['pos']);
      // then manipulate the insert options so that inserted pages follow each other
      $this->ez['insertOptions']['id']=$id;
      $this->ez['insertOptions']['pos']='after';
    } else {
      $this->ezPages[$this->ezPageCount] = $this->newPage();
    }
  } else {
    $this->y = $this->ez['pageHeight']-$this->ez['topMargin'];
  }
}

// ------------------------------------------------------------------------------

function ezSetMargins($top,$bottom,$left,$right){
  // sets the margins to new values
  $this->ez['topMargin']=$top;
  $this->ez['bottomMargin']=$bottom;
  $this->ez['leftMargin']=$left;
  $this->ez['rightMargin']=$right;
  // check to see if this means that the current writing position is outside the 
  // writable area
  if ($this->y > $this->ez['pageHeight']-$top){
    // then move y down
    $this->y = $this->ez['pageHeight']-$top;
  }
  if ( $this->y < $bottom){
    // then make a new page
    $this->ezNewPage();
  }
}  

// ------------------------------------------------------------------------------

function ezGetCurrentPageNumber(){
  // return the strict numbering (1,2,3,4..) number of the current page
  return $this->ezPageCount;
}

// ------------------------------------------------------------------------------

function ezStartPageNumbers($x,$y,$size,$pos='left',$pattern='{PAGENUM} of {TOTALPAGENUM}',$num=''){
  // put page numbers on the pages from here.
  // place then on the 'pos' side of the coordinates (x,y).
  // pos can be 'left' or 'right'
  // use the given 'pattern' for display, where (PAGENUM} and {TOTALPAGENUM} are replaced
  // as required.
  // if $num is set, then make the first page this number, the number of total pages will
  // be adjusted to account for this.
  // Adjust this function so that each time you 'start' page numbers then you effectively start a different batch
  // return the number of the batch, so that they can be stopped in a different order if required.
  if (!$pos || !strlen($pos)){
    $pos='left';
  }
  if (!$pattern || !strlen($pattern)){
    $pattern='{PAGENUM} of {TOTALPAGENUM}';
  }
  if (!isset($this->ez['pageNumbering'])){
    $this->ez['pageNumbering']=array();
  }
  $i = count($this->ez['pageNumbering']);
  $this->ez['pageNumbering'][$i][$this->ezPageCount]=array('x'=>$x,'y'=>$y,'pos'=>$pos,'pattern'=>$pattern,'num'=>$num,'size'=>$size);
  return $i;
}

// ------------------------------------------------------------------------------

function ezWhatPageNumber($pageNum,$i=0){
  // given a particular generic page number (ie, document numbered sequentially from beginning),
  // return the page number under a particular page numbering scheme ($i)
  $num=0;
  $start=1;
  $startNum=1;
  if (!isset($this->ez['pageNumbering']))
  {
    $this->addMessage('WARNING: page numbering called for and wasn\'t started with ezStartPageNumbers');
    return 0;
  }
  foreach($this->ez['pageNumbering'][$i] as $k=>$v){
    if ($k<=$pageNum){
      if (is_array($v)){
        // start block
        if (strlen($v['num'])){
          // a start was specified
          $start=$v['num'];
          $startNum=$k;
          $num=$pageNum-$startNum+$start;
        }
      } else {
        // stop block
        $num=0;
      }
    }
  }
  return $num;
}

// ------------------------------------------------------------------------------

function ezStopPageNumbers($stopTotal=0,$next=0,$i=0){
  // if stopTotal=1 then the totalling of pages for this number will stop too
  // if $next=1, then do this page, but not the next, else do not do this page either
  // if $i is set, then stop that particular pagenumbering sequence.
  if (!isset($this->ez['pageNumbering'])){
    $this->ez['pageNumbering']=array();
  }
  if ($next && isset($this->ez['pageNumbering'][$i][$this->ezPageCount]) && is_array($this->ez['pageNumbering'][$i][$this->ezPageCount])){
    // then this has only just been started, this will over-write the start, and nothing will appear
    // add a special command to the start block, telling it to stop as well
    if ($stopTotal){
      $this->ez['pageNumbering'][$i][$this->ezPageCount]['stoptn']=1;
    } else {
      $this->ez['pageNumbering'][$i][$this->ezPageCount]['stopn']=1;
    }
  } else {
    if ($stopTotal){
      $this->ez['pageNumbering'][$i][$this->ezPageCount]='stopt';
    } else {
      $this->ez['pageNumbering'][$i][$this->ezPageCount]='stop';
    }
    if ($next){
      $this->ez['pageNumbering'][$i][$this->ezPageCount].='n';
    }
  }
}

// ------------------------------------------------------------------------------

function ezPRVTpageNumberSearch($lbl,&$tmp){
  foreach($tmp as $i=>$v){
    if (is_array($v)){
      if (isset($v[$lbl])){
        return $i;
      }
    } else {
      if ($v==$lbl){
        return $i;
      }
    }
  }
  return 0;
}

// ------------------------------------------------------------------------------

function ezPRVTaddPageNumbers(){
  // this will go through the pageNumbering array and add the page numbers are required
  if (isset($this->ez['pageNumbering'])){
    $totalPages1 = $this->ezPageCount;
    $tmp1=$this->ez['pageNumbering'];
    $status=0;
    foreach($tmp1 as $i=>$tmp){
      // do each of the page numbering systems
      // firstly, find the total pages for this one
      $k = $this->ezPRVTpageNumberSearch('stopt',$tmp);
      if ($k && $k>0){
        $totalPages = $k-1;
      } else {
        $l = $this->ezPRVTpageNumberSearch('stoptn',$tmp);
        if ($l && $l>0){
          $totalPages = $l;
        } else {
          $totalPages = $totalPages1;
        }
      }
      foreach ($this->ezPages as $pageNum=>$id){
        if (isset($tmp[$pageNum])){
          if (is_array($tmp[$pageNum])){
            // then this must be starting page numbers
            $status=1;
            $info = $tmp[$pageNum];
            $info['dnum']=$info['num']-$pageNum;
            // also check for the special case of the numbering stopping and starting on the same page
            if (isset($info['stopn']) || isset($info['stoptn']) ){
              $status=2;
            }
          } else if ($tmp[$pageNum]=='stop' || $tmp[$pageNum]=='stopt'){
            // then we are stopping page numbers
            $status=0;
          } else if ($status==1 && ($tmp[$pageNum]=='stoptn' || $tmp[$pageNum]=='stopn')){
            // then we are stopping page numbers
            $status=2;
          }
        }
        if ($status){
          // then add the page numbering to this page
          if (strlen($info['num'])){
            $num=$pageNum+$info['dnum'];
          } else {
            $num=$pageNum;
          }
          $total = $totalPages+$num-$pageNum;
          $pat = str_replace('{PAGENUM}',$num,$info['pattern']);
          $pat = str_replace('{TOTALPAGENUM}',$total,$pat);
          $this->reopenObject($id);
          switch($info['pos']){
            case 'right':
              $this->addText($info['x'],$info['y'],$info['size'],$pat);
              break;
            default:
              $w=$this->getTextWidth($info['size'],$pat);
              $this->addText($info['x']-$w,$info['y'],$info['size'],$pat);
              break;
          }
          $this->closeObject();
        }
        if ($status==2){
          $status=0;
        }
      }
    }
  }
}

// ------------------------------------------------------------------------------

function ezPRVTcleanUp(){
  $this->ezPRVTaddPageNumbers();
}

// ------------------------------------------------------------------------------

function ezStream($options=''){
  $this->ezPRVTcleanUp();
  $this->stream($options);
}

// ------------------------------------------------------------------------------

function ezOutput($options=0){
  $this->ezPRVTcleanUp();
  return $this->output($options);
}

// ------------------------------------------------------------------------------

function ezSetY($y){
  // used to change the vertical position of the writing point.
  $this->y = $y;
  if ( $this->y < $this->ez['bottomMargin']){
    // then make a new page
    $this->ezNewPage();
  }
}

// ------------------------------------------------------------------------------

function ezSetDy($dy,$mod=''){
  // used to change the vertical position of the writing point.
  // changes up by a positive increment, so enter a negative number to go
  // down the page
  // if $mod is set to 'makeSpace' and a new page is forced, then the pointed will be moved 
  // down on the new page, this will allow space to be reserved for graphics etc.
  $this->y += $dy;
  if ( $this->y < $this->ez['bottomMargin']){
    // then make a new page
    $this->ezNewPage();
    if ($mod=='makeSpace'){
      $this->y += $dy;
    }
  }
}

// ------------------------------------------------------------------------------

function ezPrvtTableDrawLines($pos,$gap,$x0,$x1,$y0,$y1,$y2,$col,$inner,$outer,$opt=1){
  $x0=1000;
  $x1=0;
  $this->setStrokeColor($col[0],$col[1],$col[2]);
  $cnt=0;
  $n = count($pos);
  foreach($pos as $x){
    $cnt++;
    if ($cnt==1 || $cnt==$n){
      $this->setLineStyle($outer);
    } else {
      $this->setLineStyle($inner);
    }
    $this->line($x-$gap/2,$y0,$x-$gap/2,$y2);
    if ($x>$x1){ $x1=$x; };
    if ($x<$x0){ $x0=$x; };
  }
  $this->setLineStyle($outer);
  $this->line($x0-$gap/2-$outer/2,$y0,$x1-$gap/2+$outer/2,$y0);
  // only do the second line if it is different to the first, AND each row does not have
  // a line on it.
  if ($y0!=$y1 && $opt<2){
    $this->line($x0-$gap/2,$y1,$x1-$gap/2,$y1);
  }
  $this->line($x0-$gap/2-$outer/2,$y2,$x1-$gap/2+$outer/2,$y2);
}

// ------------------------------------------------------------------------------

function ezPrvtTableColumnHeadings($cols,$pos,$maxWidth,$height,$decender,$gap,$size,&$y,$optionsAll=array()){
  // uses ezText to add the text, and returns the height taken by the largest heading
  // this page will move the headings to a new page if they will not fit completely on this one
  // transaction support will be used to implement this

  if (isset($optionsAll['cols'])){
    $options = $optionsAll['cols'];
  } else {
    $options = array();
  }
  
  $mx=0;
  $startPage = $this->ezPageCount;
  $secondGo=0;

  // $y is the position at which the top of the table should start, so the base
  // of the first text, is $y-$height-$gap-$decender, but ezText starts by dropping $height
  
  // the return from this function is the total cell height, including gaps, and $y is adjusted
  // to be the postion of the bottom line
  
  // begin the transaction
  $this->transaction('start');
  $ok=0;
//  $y-=$gap-$decender;
  $y-=$gap;
  while ($ok==0){
    foreach($cols as $colName=>$colHeading){
      $this->ezSetY($y);
      if (isset($options[$colName]) && isset($options[$colName]['justification'])){
        $justification = $options[$colName]['justification'];
      } else {
        $justification = 'left';
      }
      $this->ezText($colHeading,$size,array('aleft'=> $pos[$colName],'aright'=>($maxWidth[$colName]+$pos[$colName]),'justification'=>$justification));
      $dy = $y-$this->y;
      if ($dy>$mx){
        $mx=$dy;
      }
    }
    $y = $y - $mx - $gap + $decender;
//    $y -= $mx-$gap+$decender;
    
    // now, if this has moved to a new page, then abort the transaction, move to a new page, and put it there
    // do not check on the second time around, to avoid an infinite loop
    if ($this->ezPageCount != $startPage && $secondGo==0){
      $this->transaction('rewind');
      $this->ezNewPage();
      $y = $this->y - $gap-$decender;
      $ok=0;
      $secondGo=1;
//      $y = $store_y;
      $mx=0;

    } else {
      $this->transaction('commit');
      $ok=1;
    }
  }

  return $mx+$gap*2-$decender;
}

// ------------------------------------------------------------------------------

function ezPrvtGetTextWidth($size,$text){
  // will calculate the maximum width, taking into account that the text may be broken
  // by line breaks.
  $mx=0;
  $lines = explode("\n",$text);
  foreach ($lines as $line){
    $w = $this->getTextWidth($size,$line);
    if ($w>$mx){
      $mx=$w;
    }
  }
  return $mx;
}

// ------------------------------------------------------------------------------

function ezTable(&$data,$cols='',$title='',$options=''){
  // add a table of information to the pdf document
  // $data is a two dimensional array
  // $cols (optional) is an associative array, the keys are the names of the columns from $data
  // to be presented (and in that order), the values are the titles to be given to the columns
  // $title (optional) is the title to be put on the top of the table
  //
  // $options is an associative array which can contain:
  // 'showLines'=> 0,1,2, default is 1 (show outside and top lines only), 2=> lines on each row
  // 'showHeadings' => 0 or 1
  // 'shaded'=> 0,1,2,3 default is 1 (1->alternate lines are shaded, 0->no shading, 2-> both shaded, second uses shadeCol2)
  // 'shadeCol' => (r,g,b) array, defining the colour of the shading, default is (0.8,0.8,0.8)
  // 'shadeCol2' => (r,g,b) array, defining the colour of the shading of the other blocks, default is (0.7,0.7,0.7)
  // 'fontSize' => 10
  // 'textCol' => (r,g,b) array, text colour
  // 'titleFontSize' => 12
  // 'rowGap' => 2 , the space added at the top and bottom of each row, between the text and the lines
  // 'colGap' => 5 , the space on the left and right sides of each cell
  // 'lineCol' => (r,g,b) array, defining the colour of the lines, default, black.
  // 'xPos' => 'left','right','center','centre',or coordinate, reference coordinate in the x-direction
  // 'xOrientation' => 'left','right','center','centre', position of the table w.r.t 'xPos' 
  // 'width'=> <number> which will specify the width of the table, if it turns out to not be this
  //    wide, then it will stretch the table to fit, if it is wider then each cell will be made
  //    proportionalty smaller, and the content may have to wrap.
  // 'maxWidth'=> <number> similar to 'width', but will only make table smaller than it wants to be
  // 'options' => array(<colname>=>array('justification'=>'left','width'=>100,'link'=>linkDataName),<colname>=>....)
  //             allow the setting of other paramaters for the individual columns
  // 'minRowSpace'=> the minimum space between the bottom of each row and the bottom margin, in which a new row will be started
  //                  if it is less, then a new page would be started, default=-100
  // 'innerLineThickness'=>1
  // 'outerLineThickness'=>1
  // 'splitRows'=>0, 0 or 1, whether or not to allow the rows to be split across page boundaries
  // 'protectRows'=>number, the number of rows to hold with the heading on page, ie, if there less than this number of
  //                rows on the page, then move the whole lot onto the next page, default=1
  //
  // note that the user will have had to make a font selection already or this will not 
  // produce a valid pdf file.
  
  if (!is_array($data)){
    return;
  }
  
  if (!is_array($cols)){
    // take the columns from the first row of the data set
    reset($data);
    list($k,$v)=each($data);
    if (!is_array($v)){
      return;
    }
    $cols=array();
    foreach($v as $k1=>$v1){
      $cols[$k1]=$k1;
    }
  }
  
  if (!is_array($options)){
    $options=array();
  }

  $defaults = array(
    'shaded'=>1,'showLines'=>1,'shadeCol'=>array(0.8,0.8,0.8),'shadeCol2'=>array(0.7,0.7,0.7),'fontSize'=>10,'titleFontSize'=>12
    ,'titleGap'=>5,'lineCol'=>array(0,0,0),'gap'=>5,'xPos'=>'centre','xOrientation'=>'centre'
    ,'showHeadings'=>1,'textCol'=>array(0,0,0),'width'=>0,'maxWidth'=>0,'cols'=>array(),'minRowSpace'=>-100,'rowGap'=>2,'colGap'=>5
    ,'innerLineThickness'=>1,'outerLineThickness'=>1,'splitRows'=>0,'protectRows'=>1
    );

  foreach($defaults as $key=>$value){
    if (is_array($value)){
      if (!isset($options[$key]) || !is_array($options[$key])){
        $options[$key]=$value;
      }
    } else {
      if (!isset($options[$key])){
        $options[$key]=$value;
      }
    }
  }
  $options['gap']=2*$options['colGap'];
  
  $middle = ($this->ez['pageWidth']-$this->ez['rightMargin'])/2+($this->ez['leftMargin'])/2;
  // figure out the maximum widths of the text within each column
  $maxWidth=array();
  foreach($cols as $colName=>$colHeading){
    $maxWidth[$colName]=0;
  }
  // find the maximum cell widths based on the data
  foreach($data as $row){
    foreach($cols as $colName=>$colHeading){
      $w = $this->ezPrvtGetTextWidth($options['fontSize'],(string)$row[$colName])*1.01;
      if ($w > $maxWidth[$colName]){
        $maxWidth[$colName]=$w;
      }
    }
  }
  // and the maximum widths to fit in the headings
  foreach($cols as $colName=>$colTitle){
    $w = $this->ezPrvtGetTextWidth($options['fontSize'],(string)$colTitle)*1.01;
    if ($w > $maxWidth[$colName]){
      $maxWidth[$colName]=$w;
    }
  }
  
  // calculate the start positions of each of the columns
  $pos=array();
  $x=0;
  $t=$x;
  $adjustmentWidth=0;
  $setWidth=0;
  foreach($maxWidth as $colName => $w){
    $pos[$colName]=$t;
    // if the column width has been specified then set that here, also total the
    // width avaliable for adjustment
    if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['width']) && $options['cols'][$colName]['width']>0){
      $t=$t+$options['cols'][$colName]['width'];
      $maxWidth[$colName] = $options['cols'][$colName]['width']-$options['gap'];
      $setWidth += $options['cols'][$colName]['width'];
    } else {
      $t=$t+$w+$options['gap'];
      $adjustmentWidth += $w;
      $setWidth += $options['gap'];
    }
  }
  $pos['_end_']=$t;

  // if maxWidth is specified, and the table is too wide, and the width has not been set,
  // then set the width.
  if ($options['width']==0 && $options['maxWidth'] && ($t-$x)>$options['maxWidth']){
    // then need to make this one smaller
    $options['width']=$options['maxWidth'];
  }

  if ($options['width'] && $adjustmentWidth>0 && $setWidth<$options['width']){
    // first find the current widths of the columns involved in this mystery
    $cols0 = array();
    $cols1 = array();
    $xq=0;
    $presentWidth=0;
    $last='';
    foreach($pos as $colName=>$p){
      if (!isset($options['cols'][$last]) || !isset($options['cols'][$last]['width']) || $options['cols'][$last]['width']<=0){
        if (strlen($last)){
          $cols0[$last]=$p-$xq -$options['gap'];
          $presentWidth += ($p-$xq - $options['gap']);
        }
      } else {
        $cols1[$last]=$p-$xq;
      }
      $last=$colName;
      $xq=$p;
    }
    // $cols0 contains the widths of all the columns which are not set
    $neededWidth = $options['width']-$setWidth;
    // if needed width is negative then add it equally to each column, else get more tricky
    if ($presentWidth<$neededWidth){
      foreach($cols0 as $colName=>$w){
        $cols0[$colName]+= ($neededWidth-$presentWidth)/count($cols0);
      }
    } else {
    
      $cnt=0;
      while ($presentWidth>$neededWidth && $cnt<100){
        $cnt++; // insurance policy
        // find the widest columns, and the next to widest width
        $aWidest = array();
        $nWidest=0;
        $widest=0;
        foreach($cols0 as $colName=>$w){
          if ($w>$widest){
            $aWidest=array($colName);
            $nWidest = $widest;
            $widest=$w;
          } else if ($w==$widest){
            $aWidest[]=$colName;
          }
        }
        // then figure out what the width of the widest columns would have to be to take up all the slack
        $newWidestWidth = $widest - ($presentWidth-$neededWidth)/count($aWidest);
        if ($newWidestWidth > $nWidest){
          // then there is space to set them to this
          foreach($aWidest as $colName){
            $cols0[$colName] = $newWidestWidth;
          }
          $presentWidth=$neededWidth;
        } else {
          // there is not space, reduce the size of the widest ones down to the next size down, and we
          // will go round again
          foreach($aWidest as $colName){
            $cols0[$colName] = $nWidest;
          }
          $presentWidth=$presentWidth-($widest-$nWidest)*count($aWidest);
        }
      }
    }
    // $cols0 now contains the new widths of the constrained columns.
    // now need to update the $pos and $maxWidth arrays
    $xq=0;
    foreach($pos as $colName=>$p){
      $pos[$colName]=$xq;
      if (!isset($options['cols'][$colName]) || !isset($options['cols'][$colName]['width']) || $options['cols'][$colName]['width']<=0){
        if (isset($cols0[$colName])){
          $xq += $cols0[$colName] + $options['gap'];
          $maxWidth[$colName]=$cols0[$colName];
        }
      } else {
        if (isset($cols1[$colName])){
          $xq += $cols1[$colName];
        }
      }
    }

    $t=$x+$options['width'];
    $pos['_end_']=$t;
  }

  // now adjust the table to the correct location across the page
  switch ($options['xPos']){
    case 'left':
      $xref = $this->ez['leftMargin'];
      break;
    case 'right':
      $xref = $this->ez['pageWidth'] - $this->ez['rightMargin'];
      break;
    case 'centre':
    case 'center':
      $xref = $middle;
      break;
    default:
      $xref = $options['xPos'];
      break;
  }
  switch ($options['xOrientation']){
    case 'left':
      $dx = $xref-$t;
      break;
    case 'right':
      $dx = $xref;
      break;
    case 'centre':
    case 'center':
      $dx = $xref-$t/2;
      break;
  }


  foreach($pos as $k=>$v){
    $pos[$k]=$v+$dx;
  }
  $x0=$x+$dx;
  $x1=$t+$dx;

  $baseLeftMargin = $this->ez['leftMargin'];
  $basePos = $pos;
  $baseX0 = $x0;
  $baseX1 = $x1;
  
  // ok, just about ready to make me a table
  $this->setColor($options['textCol'][0],$options['textCol'][1],$options['textCol'][2]);
  $this->setStrokeColor($options['shadeCol'][0],$options['shadeCol'][1],$options['shadeCol'][2]);

  $middle = ($x1+$x0)/2;

  // start a transaction which will be used to regress the table, if there are not enough rows protected
  if ($options['protectRows']>0){
    $this->transaction('start');
    $movedOnce=0;
  }
  $abortTable = 1;
  while ($abortTable){
  $abortTable=0;

  $dm = $this->ez['leftMargin']-$baseLeftMargin;
  foreach($basePos as $k=>$v){
    $pos[$k]=$v+$dm;
  }
  $x0=$baseX0+$dm;
  $x1=$baseX1+$dm;
  $middle = ($x1+$x0)/2;


  // if the title is set, then do that
  if (strlen($title)){
    $w = $this->getTextWidth($options['titleFontSize'],$title);
    $this->y -= $this->getFontHeight($options['titleFontSize']);
    if ($this->y < $this->ez['bottomMargin']){
      $this->ezNewPage();
        // margins may have changed on the newpage
        $dm = $this->ez['leftMargin']-$baseLeftMargin;
        foreach($basePos as $k=>$v){
          $pos[$k]=$v+$dm;
        }
        $x0=$baseX0+$dm;
        $x1=$baseX1+$dm;
        $middle = ($x1+$x0)/2;
      $this->y -= $this->getFontHeight($options['titleFontSize']);
    }
    $this->addText($middle-$w/2,$this->y,$options['titleFontSize'],$title);
    $this->y -= $options['titleGap'];
  }

        // margins may have changed on the newpage
        $dm = $this->ez['leftMargin']-$baseLeftMargin;
        foreach($basePos as $k=>$v){
          $pos[$k]=$v+$dm;
        }
        $x0=$baseX0+$dm;
        $x1=$baseX1+$dm;
  
  $y=$this->y; // to simplify the code a bit
  
  // make the table
  $height = $this->getFontHeight($options['fontSize']);
  $decender = $this->getFontDecender($options['fontSize']);

  
  
  $y0=$y+$decender;
  $dy=0;
  if ($options['showHeadings']){
    // this function will move the start of the table to a new page if it does not fit on this one
    $headingHeight = $this->ezPrvtTableColumnHeadings($cols,$pos,$maxWidth,$height,$decender,$options['rowGap'],$options['fontSize'],$y,$options);
    $y0 = $y+$headingHeight;
    $y1 = $y;


    $dm = $this->ez['leftMargin']-$baseLeftMargin;
    foreach($basePos as $k=>$v){
      $pos[$k]=$v+$dm;
    }
    $x0=$baseX0+$dm;
    $x1=$baseX1+$dm;

  } else {
    $y1 = $y0;
  }
  $firstLine=1;

  
  // open an object here so that the text can be put in over the shading
  if ($options['shaded']){
    $this->saveState();
    $textObjectId = $this->openObject();
    $this->closeObject();
    $this->addObject($textObjectId);
    $this->reopenObject($textObjectId);
  }
  
  $cnt=0;
  $newPage=0;
  foreach($data as $row){
    $cnt++;
    // the transaction support will be used to prevent rows being split
    if ($options['splitRows']==0){
      $pageStart = $this->ezPageCount;
      if (isset($this->ez['columns']) && $this->ez['columns']['on']==1){
        $columnStart = $this->ez['columns']['colNum'];
      }
      $this->transaction('start');
      $row_orig = $row;
      $y_orig = $y;
      $y0_orig = $y0;
      $y1_orig = $y1;
    }
    $ok=0;
    $secondTurn=0;
    while(!$abortTable && $ok == 0){

    $mx=0;
    $newRow=1;
    while(!$abortTable && ($newPage || $newRow)){
      
      $y-=$height;
      if ($newPage || $y<$this->ez['bottomMargin'] || (isset($options['minRowSpace']) && $y<($this->ez['bottomMargin']+$options['minRowSpace'])) ){
        // check that enough rows are with the heading
        if ($options['protectRows']>0 && $movedOnce==0 && $cnt<=$options['protectRows']){
          // then we need to move the whole table onto the next page
          $movedOnce = 1;
          $abortTable = 1;
        }
      
        $y2=$y-$mx+2*$height+$decender-$newRow*$height;
        if ($options['showLines']){
          if (!$options['showHeadings']){
            $y0=$y1;
          }
          $this->ezPrvtTableDrawLines($pos,$options['gap'],$x0,$x1,$y0,$y1,$y2,$options['lineCol'],$options['innerLineThickness'],$options['outerLineThickness'],$options['showLines']);
        }
        if ($options['shaded']){
          $this->closeObject();
          $this->restoreState();
        }
        $this->ezNewPage();
        // and the margins may have changed, this is due to the possibility of the columns being turned on
        // as the columns are managed by manipulating the margins

        $dm = $this->ez['leftMargin']-$baseLeftMargin;
        foreach($basePos as $k=>$v){
          $pos[$k]=$v+$dm;
        }
//        $x0=$x0+$dm;
//        $x1=$x1+$dm;
        $x0=$baseX0+$dm;
        $x1=$baseX1+$dm;
  
        if ($options['shaded']){
          $this->saveState();
          $textObjectId = $this->openObject();
          $this->closeObject();
          $this->addObject($textObjectId);
          $this->reopenObject($textObjectId);
        }
        $this->setColor($options['textCol'][0],$options['textCol'][1],$options['textCol'][2],1);
        $y = $this->ez['pageHeight']-$this->ez['topMargin'];
        $y0=$y+$decender;
        $mx=0;
        if ($options['showHeadings']){
          $this->ezPrvtTableColumnHeadings($cols,$pos,$maxWidth,$height,$decender,$options['rowGap'],$options['fontSize'],$y,$options);
          $y1=$y;
        } else {
          $y1=$y0;
        }
        $firstLine=1;
        $y -= $height;
      }
      $newRow=0;
      // write the actual data
      // if these cells need to be split over a page, then $newPage will be set, and the remaining
      // text will be placed in $leftOvers
      $newPage=0;
      $leftOvers=array();

      foreach($cols as $colName=>$colTitle){
        $this->ezSetY($y+$height);
        $colNewPage=0;
        if (isset($row[$colName])){
          if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['link']) && strlen($options['cols'][$colName]['link'])){
            
            $lines = explode("\n",$row[$colName]);
            if (isset($row[$options['cols'][$colName]['link']]) && strlen($row[$options['cols'][$colName]['link']])){
              foreach($lines as $k=>$v){
                $lines[$k]='<c:alink:'.$row[$options['cols'][$colName]['link']].'>'.$v.'</c:alink>';
              }
            }
          } else {
            $lines = explode("\n",$row[$colName]);
          }
        } else {
          $lines = array();
        }
        $this->y -= $options['rowGap'];
        foreach ($lines as $line){
          $line = $this->ezProcessText($line);
          $start=1;

          while (strlen($line) || $start){
            $start=0;
            if (!$colNewPage){
              $this->y=$this->y-$height;
            }
            if ($this->y < $this->ez['bottomMargin']){
  //            $this->ezNewPage();
              $newPage=1;  // whether a new page is required for any of the columns
              $colNewPage=1; // whether a new page is required for this column
            }
            if ($colNewPage){
              if (isset($leftOvers[$colName])){
                $leftOvers[$colName].="\n".$line;
              } else {
                $leftOvers[$colName] = $line;
              }
              $line='';
            } else {
              if (isset($options['cols'][$colName]) && isset($options['cols'][$colName]['justification']) ){
                $just = $options['cols'][$colName]['justification'];
              } else {
                $just='left';
              }

              $line=$this->addTextWrap($pos[$colName],$this->y,$maxWidth[$colName],$options['fontSize'],$line,$just);
            }
          }
        }
  
        $dy=$y+$height-$this->y+$options['rowGap'];
        if ($dy-$height*$newPage>$mx){
          $mx=$dy-$height*$newPage;
        }
      }
      // set $row to $leftOvers so that they will be processed onto the new page
      $row = $leftOvers;
      // now add the shading underneath
      if ($options['shaded'] && $cnt%2==0){
        $this->closeObject();
        $this->setColor($options['shadeCol'][0],$options['shadeCol'][1],$options['shadeCol'][2],1);
        $this->filledRectangle($x0-$options['gap']/2,$y+$decender+$height-$mx,$x1-$x0,$mx);
        $this->reopenObject($textObjectId);
      }

      if ($options['shaded']==2 && $cnt%2==1){
        $this->closeObject();
        $this->setColor($options['shadeCol2'][0],$options['shadeCol2'][1],$options['shadeCol2'][2],1);
        $this->filledRectangle($x0-$options['gap']/2,$y+$decender+$height-$mx,$x1-$x0,$mx);
        $this->reopenObject($textObjectId);
      }

      if ($options['showLines']>1){
        // then draw a line on the top of each block
//        $this->closeObject();
        $this->saveState();
        $this->setStrokeColor($options['lineCol'][0],$options['lineCol'][1],$options['lineCol'][2],1);
//        $this->line($x0-$options['gap']/2,$y+$decender+$height-$mx,$x1-$x0,$mx);
        if ($firstLine){
          $this->setLineStyle($options['outerLineThickness']);
          $firstLine=0;
        } else {
          $this->setLineStyle($options['innerLineThickness']);
        }
        $this->line($x0-$options['gap']/2,$y+$decender+$height,$x1-$options['gap']/2,$y+$decender+$height);
        $this->restoreState();
//        $this->reopenObject($textObjectId);
      }
    } // end of while 
    $y=$y-$mx+$height;
    
    // checking row split over pages
    if ($options['splitRows']==0){
      if ( ( ($this->ezPageCount != $pageStart) || (isset($this->ez['columns']) && $this->ez['columns']['on']==1 && $columnStart != $this->ez['columns']['colNum'] ))  && $secondTurn==0){
        // then we need to go back and try that again !
        $newPage=1;
        $secondTurn=1;
        $this->transaction('rewind');
        $row = $row_orig;
        $y = $y_orig;
        $y0 = $y0_orig;
        $y1 = $y1_orig;
        $ok=0;

        $dm = $this->ez['leftMargin']-$baseLeftMargin;
        foreach($basePos as $k=>$v){
          $pos[$k]=$v+$dm;
        }
        $x0=$baseX0+$dm;
        $x1=$baseX1+$dm;

      } else {
        $this->transaction('commit');
        $ok=1;
      }
    } else {
      $ok=1;  // don't go round the loop if splitting rows is allowed
    }
    
    }  // end of while to check for row splitting
    if ($abortTable){
      if ($ok==0){
        $this->transaction('abort');
      }
      // only the outer transaction should be operational
      $this->transaction('rewind');
      $this->ezNewPage();
      break;
    }
    
  } // end of foreach ($data as $row)
  
  } // end of while ($abortTable)

  // table has been put on the page, the rows guarded as required, commit.
  $this->transaction('commit');

  $y2=$y+$decender;
  if ($options['showLines']){
    if (!$options['showHeadings']){
      $y0=$y1;
    }
    $this->ezPrvtTableDrawLines($pos,$options['gap'],$x0,$x1,$y0,$y1,$y2,$options['lineCol'],$options['innerLineThickness'],$options['outerLineThickness'],$options['showLines']);
  }

  // close the object for drawing the text on top
  if ($options['shaded']){
    $this->closeObject();
    $this->restoreState();
  }
  
  $this->y=$y;
  return $y;
}

// ------------------------------------------------------------------------------
function ezProcessText($text){
  // this function will intially be used to implement underlining support, but could be used for a range of other
  // purposes
  $search = array('<u>','<U>','</u>','</U>');
  $replace = array('<c:uline>','<c:uline>','</c:uline>','</c:uline>');
  return str_replace($search,$replace,$text);
}

// ------------------------------------------------------------------------------

function ezText($text,$size=0,$options=array(),$test=0){
  // this will add a string of text to the document, starting at the current drawing
  // position.
  // it will wrap to keep within the margins, including optional offsets from the left
  // and the right, if $size is not specified, then it will be the last one used, or
  // the default value (12 I think).
  // the text will go to the start of the next line when a return code "\n" is found.
  // possible options are:
  // 'left'=> number, gap to leave from the left margin
  // 'right'=> number, gap to leave from the right margin
  // 'aleft'=> number, absolute left position (overrides 'left')
  // 'aright'=> number, absolute right position (overrides 'right')
  // 'justification' => 'left','right','center','centre','full'

  // only set one of the next two items (leading overrides spacing)
  // 'leading' => number, defines the total height taken by the line, independent of the font height.
  // 'spacing' => a real number, though usually set to one of 1, 1.5, 2 (line spacing as used in word processing)
  
  // if $test is set then this should just check if the text is going to flow onto a new page or not, returning true or false
  
  // apply the filtering which will make the underlining function.
  $text = $this->ezProcessText($text);
  
  $newPage=false;
  $store_y = $this->y;
  
  if (is_array($options) && isset($options['aleft'])){
    $left=$options['aleft'];
  } else {
    $left = $this->ez['leftMargin'] + ((is_array($options) && isset($options['left']))?$options['left']:0);
  }
  if (is_array($options) && isset($options['aright'])){
    $right=$options['aright'];
  } else {
    $right = $this->ez['pageWidth'] - $this->ez['rightMargin'] - ((is_array($options) && isset($options['right']))?$options['right']:0);
  }
  if ($size<=0){
    $size = $this->ez['fontSize'];
  } else {
    $this->ez['fontSize']=$size;
  }
  
  if (is_array($options) && isset($options['justification'])){
    $just = $options['justification'];
  } else {
    $just = 'left';
  }
  
  // modifications to give leading and spacing based on those given by Craig Heydenburg 1/1/02
  if (is_array($options) && isset($options['leading'])) { ## use leading instead of spacing
    $height = $options['leading'];
	} else if (is_array($options) && isset($options['spacing'])) {
    $height = $this->getFontHeight($size) * $options['spacing'];
	} else {
		$height = $this->getFontHeight($size);
	}

  
  $lines = explode("\n",$text);
  foreach ($lines as $line){
    $start=1;
    while (strlen($line) || $start){
      $start=0;
      $this->y=$this->y-$height;
      if ($this->y < $this->ez['bottomMargin']){
        if ($test){
          $newPage=true;
        } else {
          $this->ezNewPage();
          // and then re-calc the left and right, in case they have changed due to columns
        }
      }
      if (is_array($options) && isset($options['aleft'])){
        $left=$options['aleft'];
      } else {
        $left = $this->ez['leftMargin'] + ((is_array($options) && isset($options['left']))?$options['left']:0);
      }
      if (is_array($options) && isset($options['aright'])){
        $right=$options['aright'];
      } else {
        $right = $this->ez['pageWidth'] - $this->ez['rightMargin'] - ((is_array($options) && isset($options['right']))?$options['right']:0);
      }
      $line=$this->addTextWrap($left,$this->y,$right-$left,$size,$line,$just,0,$test);
    }
  }

  if ($test){
    $this->y=$store_y;
    return $newPage;
  } else {
    return $this->y;
  }
}

// ------------------------------------------------------------------------------

function ezImage($image,$pad = 5,$width = 0,$resize = 'full',$just = 'center',$border = ''){
	//beta ezimage function
	if (stristr($image,'://'))//copy to temp file
	{
		$fp = @fopen($image,"rb");
		while(!feof($fp))
   		{
      			$cont.= fread($fp,1024);
   		}
   		fclose($fp);
		$image = tempnam ("/tmp", "php-pdf");
		$fp2 = @fopen($image,"w");
   		fwrite($fp2,$cont);
  		fclose($fp2);
		$temp = true;
	}

	if (!(file_exists($image))) return false; //return immediately if image file does not exist
	$imageInfo = getimagesize($image);
	switch ($imageInfo[2]){
		case 2:
			$type = "jpeg";
			break;
		case 3:
			$type = "png";
			break;
		default:
			return false; //return if file is not jpg or png
	}
	if ($width == 0) $width = $imageInfo[0]; //set width
	$ratio = $imageInfo[0]/$imageInfo[1];

	//get maximum width of image
	if (isset($this->ez['columns']) && $this->ez['columns']['on'] == 1)
	{
		$bigwidth = $this->ez['columns']['width'] - ($pad * 2);
	}
	else
	{
		$bigwidth = $this->ez['pageWidth'] - ($pad * 2);
	}
	//fix width if larger than maximum or if $resize=full
	if ($resize == 'full' || $resize == 'width' || $width > $bigwidth)
	{
		$width = $bigwidth;

	}

	$height = ($width/$ratio); //set height

	//fix size if runs off page
	if ($height > ($this->y - $this->ez['bottomMargin'] - ($pad * 2)))
	{
		if ($resize != 'full')
		{
			$this->ezNewPage();
		}
		else
		{
			$height = ($this->y - $this->ez['bottomMargin'] - ($pad * 2)); //shrink height
			$width = ($height*$ratio); //fix width
		}
	}

	//fix x-offset if image smaller than bigwidth
	if ($width < $bigwidth)
	{
		//center if justification=center
		if ($just == 'center')
		{
			$offset = ($bigwidth - $width) / 2;
		}
		//move to right if justification=right
		if ($just == 'right')
		{
			$offset = ($bigwidth - $width);
		}
		//leave at left if justification=left
		if ($just == 'left')
		{
			$offset = 0;
		}
	}


	//call appropriate function
	if ($type == "jpeg"){
		$this->addJpegFromFile($image,$this->ez['leftMargin'] + $pad + $offset, $this->y + $this->getFontHeight($this->ez['fontSize']) - $pad - $height,$width);
	}

	if ($type == "png"){
		$this->addPngFromFile($image,$this->ez['leftMargin'] + $pad + $offset, $this->y + $this->getFontHeight($this->ez['fontSize']) - $pad - $height,$width);
	}
	//draw border
	if ($border != '')
	{
	if (!(isset($border['color'])))
	{
		$border['color']['red'] = .5;
		$border['color']['blue'] = .5;
		$border['color']['green'] = .5;
	}
	if (!(isset($border['width']))) $border['width'] = 1;
	if (!(isset($border['cap']))) $border['cap'] = 'round';
	if (!(isset($border['join']))) $border['join'] = 'round';
	

	$this->setStrokeColor($border['color']['red'],$border['color']['green'],$border['color']['blue']);
	$this->setLineStyle($border['width'],$border['cap'],$border['join']);
	$this->rectangle($this->ez['leftMargin'] + $pad + $offset, $this->y + $this->getFontHeight($this->ez['fontSize']) - $pad - $height,$width,$height);

	}
	// move y below image
	$this->y = $this->y - $pad - $height;
	//remove tempfile for remote images
	if ($temp == true) unlink($image);

}
// ------------------------------------------------------------------------------

// note that templating code is still considered developmental - have not really figured
// out a good way of doing this yet.
function loadTemplate($templateFile){
  // this function will load the requested template ($file includes full or relative pathname)
  // the code for the template will be modified to make it name safe, and then stored in 
  // an array for later use
  // The id of the template will be returned for the user to operate on it later
  if (!file_exists($templateFile)){
    return -1;
  }

  $code = implode('',file($templateFile));
  if (!strlen($code)){
    return;
  }

  $code = trim($code);
  if (substr($code,0,5)=='<?php'){
    $code = substr($code,5);
  }
  if (substr($code,-2)=='?>'){
    $code = substr($code,0,strlen($code)-2);
  }
  if (isset($this->ez['numTemplates'])){
    $newNum = $this->ez['numTemplates'];
    $this->ez['numTemplates']++;
  } else {
    $newNum=0;
    $this->ez['numTemplates']=1;
    $this->ez['templates']=array();
  }

  $this->ez['templates'][$newNum]['code']=$code;

  return $newNum;
}

// ------------------------------------------------------------------------------

function execTemplate($id,$data=array(),$options=array()){
  // execute the given template on the current document.
  if (!isset($this->ez['templates'][$id])){
    return;
  }
  eval($this->ez['templates'][$id]['code']);
}

// ------------------------------------------------------------------------------
function ilink($info){
  $this->alink($info,1);
}

function alink($info,$internal=0){
  // a callback function to support the formation of clickable links within the document
  $lineFactor=0.05; // the thickness of the line as a proportion of the height. also the drop of the line.
  switch($info['status']){
    case 'start':
    case 'sol':
      // the beginning of the link
      // this should contain the URl for the link as the 'p' entry, and will also contain the value of 'nCallback'
      if (!isset($this->ez['links'])){
        $this->ez['links']=array();
      }
      $i = $info['nCallback'];
      $this->ez['links'][$i] = array('x'=>$info['x'],'y'=>$info['y'],'angle'=>$info['angle'],'decender'=>$info['decender'],'height'=>$info['height'],'url'=>$info['p']);
        $this->saveState();
        $this->setColor(0,0,1);
        $this->setStrokeColor(0,0,1);
        $thick = $info['height']*$lineFactor;
        $this->setLineStyle($thick);
      break;
    case 'end':
    case 'eol':
      // the end of the link
      // assume that it is the most recent opening which has closed
      $i = $info['nCallback'];
      $start = $this->ez['links'][$i];
      // add underlining
        $a = deg2rad((float)$start['angle']-90.0);
        $drop = $start['height']*$lineFactor*1.5;
        $dropx = cos($a)*$drop;
        $dropy = -sin($a)*$drop;
        $this->line($start['x']-$dropx,$start['y']-$dropy,$info['x']-$dropx,$info['y']-$dropy);
        if ($internal) {
             $this->addInternalLink($start['url'],$start['x'],$start['y']+$start['decender'],$info['x'],$start['y']+$start['decender']+$start['height']);
        } else {
             $this->addLink($start['url'],$start['x'],$start['y']+$start['decender'],$info['x'],$start['y']+$start['decender']+$start['height']);
      }
      $this->restoreState();
      break;
  }
}

// ------------------------------------------------------------------------------

function uline($info){
  // a callback function to support underlining
  $lineFactor=0.05; // the thickness of the line as a proportion of the height. also the drop of the line.
  switch($info['status']){
    case 'start':
    case 'sol':
    
      // the beginning of the underline zone
      if (!isset($this->ez['links'])){
        $this->ez['links']=array();
      }
      $i = $info['nCallback'];
      $this->ez['links'][$i] = array('x'=>$info['x'],'y'=>$info['y'],'angle'=>$info['angle'],'decender'=>$info['decender'],'height'=>$info['height']);
      $this->saveState();
      $thick = $info['height']*$lineFactor;
      $this->setLineStyle($thick);
      break;
    case 'end':
    case 'eol':
      // the end of the link
      // assume that it is the most recent opening which has closed
      $i = $info['nCallback'];
      $start = $this->ez['links'][$i];
      // add underlining
      $a = deg2rad((float)$start['angle']-90.0);
      $drop = $start['height']*$lineFactor*1.5;
      $dropx = cos($a)*$drop;
      $dropy = -sin($a)*$drop;
      $this->line($start['x']-$dropx,$start['y']-$dropy,$info['x']-$dropx,$info['y']-$dropy);
      $this->restoreState();
      break;
  }
}

// ------------------------------------------------------------------------------

}
?>