<?php
/**
* Cpdf
*
*
* A PHP class to provide the basic functionality to create a pdf document without
* any requirement for additional modules.
*
* Note that they companion class CezPdf can be used to extend this class and dramatically
* simplify the creation of documents.
*
* IMPORTANT NOTE
* there is no warranty, implied or otherwise with this software.
* 
* LICENCE
* This code has been placed in the Public Domain for all to enjoy.
*
* @author		Wayne Munro <pdf@ros.co.nz>
* @version 	009
* @package Cpdf
* @link http://www.ros.co.nz/pdf
*/
class Cpdf {

/**
* the current number of pdf objects in the document
*/
var $numObj=0;
/**
* this array contains all of the pdf objects, ready for final assembly
*/
var $objects = array();
/**
* the objectId (number within the objects array) of the document catalog
*/
var $catalogId;
/**
* array carrying information about the fonts that the system currently knows about
* used to ensure that a font is not loaded twice, among other things
*/
var $fonts=array(); 
/**
* a record of the current font
*/
var $currentFont='';
/**
* the current base font
*/
var $currentBaseFont='';
/**
* the number of the current font within the font array
*/
var $currentFontNum=0;
/**
* 
*/
var $currentNode;
/**
* object number of the current page
*/
var $currentPage;
/**
* object number of the currently active contents block
*/
var $currentContents;
/**
* number of fonts within the system
*/
var $numFonts=0;
/**
* current colour for fill operations, defaults to inactive value, all three components should be between 0 and 1 inclusive when active
*/
var $currentColour=array('r'=>-1,'g'=>-1,'b'=>-1);
/**
* current colour for stroke operations (lines etc.)
*/
var $currentStrokeColour=array('r'=>-1,'g'=>-1,'b'=>-1);
/**
* current style that lines are drawn in
*/
var $currentLineStyle='';
/**
* an array which is used to save the state of the document, mainly the colours and styles
* it is used to temporarily change to another state, the change back to what it was before
*/
var $stateStack = array();
/**
* number of elements within the state stack
*/
var $nStateStack = 0;
/**
* number of page objects within the document
*/
var $numPages=0;
/**
* object Id storage stack
*/
var $stack=array();
/**
* number of elements within the object Id storage stack
*/
var $nStack=0;
/**
* an array which contains information about the objects which are not firmly attached to pages
* these have been added with the addObject function
*/
var $looseObjects=array();
/**
* array contains infomation about how the loose objects are to be added to the document
*/
var $addLooseObjects=array();
/**
* the objectId of the information object for the document
* this contains authorship, title etc.
*/
var $infoObject=0;
/**
* number of images being tracked within the document
*/
var $numImages=0;
/**
* an array containing options about the document
* it defaults to turning on the compression of the objects
*/
var $options=array('compression'=>1);
/**
* the objectId of the first page of the document
*/
var $firstPageId;
/**
* used to track the last used value of the inter-word spacing, this is so that it is known
* when the spacing is changed.
*/
var $wordSpaceAdjust=0;
/**
* the object Id of the procset object
*/
var $procsetObjectId;
/**
* store the information about the relationship between font families
* this used so that the code knows which font is the bold version of another font, etc.
* the value of this array is initialised in the constuctor function.
*/
var $fontFamilies = array();
/**
* track if the current font is bolded or italicised
*/
var $currentTextState = ''; 
/**
* messages are stored here during processing, these can be selected afterwards to give some useful debug information
*/
var $messages='';
/**
* the ancryption array for the document encryption is stored here
*/
var $arc4='';
/**
* the object Id of the encryption information
*/
var $arc4_objnum=0;
/**
* the file identifier, used to uniquely identify a pdf document
*/
var $fileIdentifier='';
/**
* a flag to say if a document is to be encrypted or not
*/
var $encrypted=0;
/**
* the ancryption key for the encryption of all the document content (structure is not encrypted)
*/
var $encryptionKey='';
/**
* array which forms a stack to keep track of nested callback functions
*/
var $callback = array();
/**
* the number of callback functions in the callback array
*/
var $nCallback = 0;
/**
* store label->id pairs for named destinations, these will be used to replace internal links
* done this way so that destinations can be defined after the location that links to them
*/
var $destinations = array();
/**
* store the stack for the transaction commands, each item in here is a record of the values of all the 
* variables within the class, so that the user can rollback at will (from each 'start' command)
* note that this includes the objects array, so these can be large.
*/
var $checkpoint = '';
/**
* class constructor
* this will start a new document
* @var array array of 4 numbers, defining the bottom left and upper right corner of the page. first two are normally zero.
*/
function Cpdf ($pageSize=array(0,0,612,792)){
  $this->newDocument($pageSize);
  
  // also initialize the font families that are known about already
  $this->setFontFamily('init');
//  $this->fileIdentifier = md5('xxxxxxxx'.time());

}

/**
* Document object methods (internal use only)
*
* There is about one object method for each type of object in the pdf document
* Each function has the same call list ($id,$action,$options).
* $id = the object ID of the object, or what it is to be if it is being created
* $action = a string specifying the action to be performed, though ALL must support:
*           'new' - create the object with the id $id
*           'out' - produce the output for the pdf object
* $options = optional, a string or array containing the various parameters for the object
*
* These, in conjunction with the output function are the ONLY way for output to be produced 
* within the pdf 'file'.
*/

/**
*destination object, used to specify the location for the user to jump to, presently on opening
*/
function o_destination($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch($action){
    case 'new':
      $this->objects[$id]=array('t'=>'destination','info'=>array());
      $tmp = '';
      switch ($options['type']){
        case 'XYZ':
        case 'FitR':
          $tmp =  ' '.$options['p3'].$tmp;
        case 'FitH':
        case 'FitV':
        case 'FitBH':
        case 'FitBV':
          $tmp =  ' '.$options['p1'].' '.$options['p2'].$tmp;
        case 'Fit':
        case 'FitB':
          $tmp =  $options['type'].$tmp;
          $this->objects[$id]['info']['string']=$tmp;
          $this->objects[$id]['info']['page']=$options['page'];
      }
      break;
    case 'out':
      $tmp = $o['info'];
      $res="\n".$id." 0 obj\n".'['.$tmp['page'].' 0 R /'.$tmp['string']."]\nendobj\n";
      return $res;
      break;
  }
}

/**
* set the viewer preferences
*/
function o_viewerPreferences($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->objects[$id]=array('t'=>'viewerPreferences','info'=>array());
      break;
    case 'add':
      foreach($options as $k=>$v){
        switch ($k){
          case 'HideToolbar':
          case 'HideMenubar':
          case 'HideWindowUI':
          case 'FitWindow':
          case 'CenterWindow':
          case 'NonFullScreenPageMode':
          case 'Direction':
            $o['info'][$k]=$v;
          break;
        }
      }
      break;
    case 'out':

      $res="\n".$id." 0 obj\n".'<< ';
      foreach($o['info'] as $k=>$v){
        $res.="\n/".$k.' '.$v;
      }
      $res.="\n>>\n";
      return $res;
      break;
  }
}

/**
* define the document catalog, the overall controller for the document
*/
function o_catalog($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->objects[$id]=array('t'=>'catalog','info'=>array());
      $this->catalogId=$id;
      break;
    case 'outlines':
    case 'pages':
    case 'openHere':
      $o['info'][$action]=$options;
      break;
    case 'viewerPreferences':
      if (!isset($o['info']['viewerPreferences'])){
        $this->numObj++;
        $this->o_viewerPreferences($this->numObj,'new');
        $o['info']['viewerPreferences']=$this->numObj;
      }
      $vp = $o['info']['viewerPreferences'];
      $this->o_viewerPreferences($vp,'add',$options);
      break;
    case 'out':
      $res="\n".$id." 0 obj\n".'<< /Type /Catalog';
      foreach($o['info'] as $k=>$v){
        switch($k){
          case 'outlines':
            $res.="\n".'/Outlines '.$v.' 0 R';
            break;
          case 'pages':
            $res.="\n".'/Pages '.$v.' 0 R';
            break;
          case 'viewerPreferences':
            $res.="\n".'/ViewerPreferences '.$o['info']['viewerPreferences'].' 0 R';
            break;
          case 'openHere':
            $res.="\n".'/OpenAction '.$o['info']['openHere'].' 0 R';
            break;
        }
      }
      $res.=" >>\nendobj";
      return $res;
      break;
  }
}

/**
* object which is a parent to the pages in the document
*/
function o_pages($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->objects[$id]=array('t'=>'pages','info'=>array());
      $this->o_catalog($this->catalogId,'pages',$id);
      break;
    case 'page':
      if (!is_array($options)){
        // then it will just be the id of the new page
        $o['info']['pages'][]=$options;
      } else {
        // then it should be an array having 'id','rid','pos', where rid=the page to which this one will be placed relative
        // and pos is either 'before' or 'after', saying where this page will fit.
        if (isset($options['id']) && isset($options['rid']) && isset($options['pos'])){
          $i = array_search($options['rid'],$o['info']['pages']);
          if (isset($o['info']['pages'][$i]) && $o['info']['pages'][$i]==$options['rid']){
            // then there is a match
            // make a space
            switch ($options['pos']){
              case 'before':
                $k = $i;
                break;
              case 'after':
                $k=$i+1;
                break;
              default:
                $k=-1;
                break;
            }
            if ($k>=0){
              for ($j=count($o['info']['pages'])-1;$j>=$k;$j--){
                $o['info']['pages'][$j+1]=$o['info']['pages'][$j];
              }
              $o['info']['pages'][$k]=$options['id'];
            }
          }
        } 
      }
      break;
    case 'procset':
      $o['info']['procset']=$options;
      break;
    case 'mediaBox':
      $o['info']['mediaBox']=$options; // which should be an array of 4 numbers
      break;
    case 'font':
      $o['info']['fonts'][]=array('objNum'=>$options['objNum'],'fontNum'=>$options['fontNum']);
      break;
    case 'xObject':
      $o['info']['xObjects'][]=array('objNum'=>$options['objNum'],'label'=>$options['label']);
      break;
    case 'out':
      if (count($o['info']['pages'])){
        $res="\n".$id." 0 obj\n<< /Type /Pages\n/Kids [";
        foreach($o['info']['pages'] as $k=>$v){
          $res.=$v." 0 R\n";
        }
        $res.="]\n/Count ".count($this->objects[$id]['info']['pages']);
        if ((isset($o['info']['fonts']) && count($o['info']['fonts'])) || isset($o['info']['procset'])){
          $res.="\n/Resources <<";
          if (isset($o['info']['procset'])){
            $res.="\n/ProcSet ".$o['info']['procset']." 0 R";
          }
          if (isset($o['info']['fonts']) && count($o['info']['fonts'])){
            $res.="\n/Font << ";
            foreach($o['info']['fonts'] as $finfo){
              $res.="\n/F".$finfo['fontNum']." ".$finfo['objNum']." 0 R";
            }
            $res.=" >>";
          }
          if (isset($o['info']['xObjects']) && count($o['info']['xObjects'])){
            $res.="\n/XObject << ";
            foreach($o['info']['xObjects'] as $finfo){
              $res.="\n/".$finfo['label']." ".$finfo['objNum']." 0 R";
            }
            $res.=" >>";
          }
          $res.="\n>>";
          if (isset($o['info']['mediaBox'])){
            $tmp=$o['info']['mediaBox'];
            $res.="\n/MediaBox [".sprintf('%.3f',$tmp[0]).' '.sprintf('%.3f',$tmp[1]).' '.sprintf('%.3f',$tmp[2]).' '.sprintf('%.3f',$tmp[3]).']';
          }
        }
        $res.="\n >>\nendobj";
      } else {
        $res="\n".$id." 0 obj\n<< /Type /Pages\n/Count 0\n>>\nendobj";
      }
      return $res;
    break;
  }
}

/**
* define the outlines in the doc, empty for now
*/
function o_outlines($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->objects[$id]=array('t'=>'outlines','info'=>array('outlines'=>array()));
      $this->o_catalog($this->catalogId,'outlines',$id);
      break;
    case 'outline':
      $o['info']['outlines'][]=$options;
      break;
    case 'out':
      if (count($o['info']['outlines'])){
        $res="\n".$id." 0 obj\n<< /Type /Outlines /Kids [";
        foreach($o['info']['outlines'] as $k=>$v){
          $res.=$v." 0 R ";
        }
        $res.="] /Count ".count($o['info']['outlines'])." >>\nendobj";
      } else {
        $res="\n".$id." 0 obj\n<< /Type /Outlines /Count 0 >>\nendobj";
      }
      return $res;
      break;
  }
}

/**
* an object to hold the font description
*/
function o_font($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->objects[$id]=array('t'=>'font','info'=>array('name'=>$options['name'],'SubType'=>'Type1'));
      $fontNum=$this->numFonts;
      $this->objects[$id]['info']['fontNum']=$fontNum;
      // deal with the encoding and the differences
      if (isset($options['differences'])){
        // then we'll need an encoding dictionary
        $this->numObj++;
        $this->o_fontEncoding($this->numObj,'new',$options);
        $this->objects[$id]['info']['encodingDictionary']=$this->numObj;
      } else if (isset($options['encoding'])){
        // we can specify encoding here
        switch($options['encoding']){
          case 'WinAnsiEncoding':
          case 'MacRomanEncoding':
          case 'MacExpertEncoding':
            $this->objects[$id]['info']['encoding']=$options['encoding'];
            break;
          case 'none':
            break;
          default:
            $this->objects[$id]['info']['encoding']='WinAnsiEncoding';
            break;
        }
      } else {
        $this->objects[$id]['info']['encoding']='WinAnsiEncoding';
      }
      // also tell the pages node about the new font
      $this->o_pages($this->currentNode,'font',array('fontNum'=>$fontNum,'objNum'=>$id));
      break;
    case 'add':
      foreach ($options as $k=>$v){
        switch ($k){
          case 'BaseFont':
            $o['info']['name'] = $v;
            break;
          case 'FirstChar':
          case 'LastChar':
          case 'Widths':
          case 'FontDescriptor':
          case 'SubType':
          $this->addMessage('o_font '.$k." : ".$v);
            $o['info'][$k] = $v;
            break;
        }
     }
      break;
    case 'out':
      $res="\n".$id." 0 obj\n<< /Type /Font\n/Subtype /".$o['info']['SubType']."\n";
      $res.="/Name /F".$o['info']['fontNum']."\n";
      $res.="/BaseFont /".$o['info']['name']."\n";
      if (isset($o['info']['encodingDictionary'])){
        // then place a reference to the dictionary
        $res.="/Encoding ".$o['info']['encodingDictionary']." 0 R\n";
      } else if (isset($o['info']['encoding'])){
        // use the specified encoding
        $res.="/Encoding /".$o['info']['encoding']."\n";
      }
      if (isset($o['info']['FirstChar'])){
        $res.="/FirstChar ".$o['info']['FirstChar']."\n";
      }
      if (isset($o['info']['LastChar'])){
        $res.="/LastChar ".$o['info']['LastChar']."\n";
      }
      if (isset($o['info']['Widths'])){
        $res.="/Widths ".$o['info']['Widths']." 0 R\n";
      }
      if (isset($o['info']['FontDescriptor'])){
        $res.="/FontDescriptor ".$o['info']['FontDescriptor']." 0 R\n";
      }
      $res.=">>\nendobj";
      return $res;
      break;
  }
}

/**
* a font descriptor, needed for including additional fonts
*/
function o_fontDescriptor($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->objects[$id]=array('t'=>'fontDescriptor','info'=>$options);
      break;
    case 'out':
      $res="\n".$id." 0 obj\n<< /Type /FontDescriptor\n";
      foreach ($o['info'] as $label => $value){
        switch ($label){
          case 'Ascent':
          case 'CapHeight':
          case 'Descent':
          case 'Flags':
          case 'ItalicAngle':
          case 'StemV':
          case 'AvgWidth':
          case 'Leading':
          case 'MaxWidth':
          case 'MissingWidth':
          case 'StemH':
          case 'XHeight':
          case 'CharSet':
            if (strlen($value)){
              $res.='/'.$label.' '.$value."\n";
            }
            break;
          case 'FontFile':
          case 'FontFile2':
          case 'FontFile3':
            $res.='/'.$label.' '.$value." 0 R\n";
            break;
          case 'FontBBox':
            $res.='/'.$label.' ['.$value[0].' '.$value[1].' '.$value[2].' '.$value[3]."]\n";
            break;
          case 'FontName':
            $res.='/'.$label.' /'.$value."\n";
            break;
        }
      }
      $res.=">>\nendobj";
      return $res;
      break;
  }
}

/**
* the font encoding
*/
function o_fontEncoding($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      // the options array should contain 'differences' and maybe 'encoding'
      $this->objects[$id]=array('t'=>'fontEncoding','info'=>$options);
      break;
    case 'out':
      $res="\n".$id." 0 obj\n<< /Type /Encoding\n";
      if (!isset($o['info']['encoding'])){
        $o['info']['encoding']='WinAnsiEncoding';
      }
      if ($o['info']['encoding']!='none'){
        $res.="/BaseEncoding /".$o['info']['encoding']."\n";
      }
      $res.="/Differences \n[";
      $onum=-100;
      foreach($o['info']['differences'] as $num=>$label){
        if ($num!=$onum+1){
          // we cannot make use of consecutive numbering
          $res.= "\n".$num." /".$label;
        } else {
          $res.= " /".$label;
        }
        $onum=$num;
      }
      $res.="\n]\n>>\nendobj";
      return $res;
      break;
  }
}

/**
* the document procset, solves some problems with printing to old PS printers
*/
function o_procset($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->objects[$id]=array('t'=>'procset','info'=>array('PDF'=>1,'Text'=>1));
      $this->o_pages($this->currentNode,'procset',$id);
      $this->procsetObjectId=$id;
      break;
    case 'add':
      // this is to add new items to the procset list, despite the fact that this is considered
      // obselete, the items are required for printing to some postscript printers
      switch ($options) {
        case 'ImageB':
        case 'ImageC':
        case 'ImageI':
          $o['info'][$options]=1;
          break;
      }
      break;
    case 'out':
      $res="\n".$id." 0 obj\n[";
      foreach ($o['info'] as $label=>$val){
        $res.='/'.$label.' ';
      }
      $res.="]\nendobj";
      return $res;
      break;
  }
}

/**
* define the document information
*/
function o_info($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->infoObject=$id;
      $date='D:'.date('Ymd');
      $this->objects[$id]=array('t'=>'info','info'=>array('Creator'=>'R and OS php pdf writer, http://www.ros.co.nz','CreationDate'=>$date));
      break;
    case 'Title':
    case 'Author':
    case 'Subject':
    case 'Keywords':
    case 'Creator':
    case 'Producer':
    case 'CreationDate':
    case 'ModDate':
    case 'Trapped':
      $o['info'][$action]=$options;
      break;
    case 'out':
      if ($this->encrypted){
        $this->encryptInit($id);
      }
      $res="\n".$id." 0 obj\n<<\n";
      foreach ($o['info']  as $k=>$v){
        $res.='/'.$k.' (';
        if ($this->encrypted){
          $res.=$this->filterText($this->ARC4($v));
        } else {
          $res.=$this->filterText($v);
        }
        $res.=")\n";
      }
      $res.=">>\nendobj";
      return $res;
      break;
  }
}

/**
* an action object, used to link to URLS initially
*/
function o_action($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      if (is_array($options)){
        $this->objects[$id]=array('t'=>'action','info'=>$options,'type'=>$options['type']);
      } else {
        // then assume a URI action
        $this->objects[$id]=array('t'=>'action','info'=>$options,'type'=>'URI');
      }
      break;
    case 'out':
      if ($this->encrypted){
        $this->encryptInit($id);
      }
      $res="\n".$id." 0 obj\n<< /Type /Action";
      switch($o['type']){
        case 'ilink':
          // there will be an 'label' setting, this is the name of the destination
          $res.="\n/S /GoTo\n/D ".$this->destinations[(string)$o['info']['label']]." 0 R";
          break;
        case 'URI':
          $res.="\n/S /URI\n/URI (";
          if ($this->encrypted){
            $res.=$this->filterText($this->ARC4($o['info']));
          } else {
            $res.=$this->filterText($o['info']);
          }
          $res.=")";
          break;
      }
      $res.="\n>>\nendobj";
      return $res;
      break;
  }
}

/**
* an annotation object, this will add an annotation to the current page.
* initially will support just link annotations 
*/
function o_annotation($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      // add the annotation to the current page
      $pageId = $this->currentPage;
      $this->o_page($pageId,'annot',$id);
      // and add the action object which is going to be required
      switch($options['type']){
        case 'link':
          $this->objects[$id]=array('t'=>'annotation','info'=>$options);
          $this->numObj++;
          $this->o_action($this->numObj,'new',$options['url']);
          $this->objects[$id]['info']['actionId']=$this->numObj;
          break;
        case 'ilink':
          // this is to a named internal link
          $label = $options['label'];
          $this->objects[$id]=array('t'=>'annotation','info'=>$options);
          $this->numObj++;
          $this->o_action($this->numObj,'new',array('type'=>'ilink','label'=>$label));
          $this->objects[$id]['info']['actionId']=$this->numObj;
          break;
      }
      break;
    case 'out':
      $res="\n".$id." 0 obj\n<< /Type /Annot";
      switch($o['info']['type']){
        case 'link':
        case 'ilink':
          $res.= "\n/Subtype /Link";
          break;
      }
      $res.="\n/A ".$o['info']['actionId']." 0 R";
      $res.="\n/Border [0 0 0]";
      $res.="\n/H /I";
      $res.="\n/Rect [ ";
      foreach($o['info']['rect'] as $v){
        $res.= sprintf("%.4f ",$v);
      }
      $res.="]";
      $res.="\n>>\nendobj";
      return $res;
      break;
  }
}

/**
* a page object, it also creates a contents object to hold its contents
*/
function o_page($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->numPages++;
      $this->objects[$id]=array('t'=>'page','info'=>array('parent'=>$this->currentNode,'pageNum'=>$this->numPages));
      if (is_array($options)){
        // then this must be a page insertion, array shoudl contain 'rid','pos'=[before|after]
        $options['id']=$id;
        $this->o_pages($this->currentNode,'page',$options);
      } else {
        $this->o_pages($this->currentNode,'page',$id);
      }
      $this->currentPage=$id;
      //make a contents object to go with this page
      $this->numObj++;
      $this->o_contents($this->numObj,'new',$id);
      $this->currentContents=$this->numObj;
      $this->objects[$id]['info']['contents']=array();
      $this->objects[$id]['info']['contents'][]=$this->numObj;
      $match = ($this->numPages%2 ? 'odd' : 'even');
      foreach($this->addLooseObjects as $oId=>$target){
        if ($target=='all' || $match==$target){
          $this->objects[$id]['info']['contents'][]=$oId;
        }
      }
      break;
    case 'content':
      $o['info']['contents'][]=$options;
      break;
    case 'annot':
      // add an annotation to this page
      if (!isset($o['info']['annot'])){
        $o['info']['annot']=array();
      }
      // $options should contain the id of the annotation dictionary
      $o['info']['annot'][]=$options;
      break;
    case 'out':
      $res="\n".$id." 0 obj\n<< /Type /Page";
      $res.="\n/Parent ".$o['info']['parent']." 0 R";
      if (isset($o['info']['annot'])){
        $res.="\n/Annots [";
        foreach($o['info']['annot'] as $aId){
          $res.=" ".$aId." 0 R";
        }
        $res.=" ]";
      }
      $count = count($o['info']['contents']);
      if ($count==1){
        $res.="\n/Contents ".$o['info']['contents'][0]." 0 R";
      } else if ($count>1){
        $res.="\n/Contents [\n";
        foreach ($o['info']['contents'] as $cId){
          $res.=$cId." 0 R\n";
        }
        $res.="]";
      }
      $res.="\n>>\nendobj";
      return $res;
      break;
  }
}

/**
* the contents objects hold all of the content which appears on pages
*/
function o_contents($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch ($action){
    case 'new':
      $this->objects[$id]=array('t'=>'contents','c'=>'','info'=>array());
      if (strlen($options) && intval($options)){
        // then this contents is the primary for a page
        $this->objects[$id]['onPage']=$options;
      } else if ($options=='raw'){
        // then this page contains some other type of system object
        $this->objects[$id]['raw']=1;
      }
      break;
    case 'add':
      // add more options to the decleration
      foreach ($options as $k=>$v){
        $o['info'][$k]=$v;
      }
    case 'out':
      $tmp=$o['c'];
      $res= "\n".$id." 0 obj\n";
      if (isset($this->objects[$id]['raw'])){
        $res.=$tmp;
      } else {
        $res.= "<<";
        if (function_exists('gzcompress') && $this->options['compression']){
          // then implement ZLIB based compression on this content stream
          $res.=" /Filter /FlateDecode";
          $tmp = gzcompress($tmp);
        }
        if ($this->encrypted){
          $this->encryptInit($id);
          $tmp = $this->ARC4($tmp);
        }
        foreach($o['info'] as $k=>$v){
          $res .= "\n/".$k.' '.$v;
        }
        $res.="\n/Length ".strlen($tmp)." >>\nstream\n".$tmp."\nendstream";
      }
      $res.="\nendobj\n";
      return $res;
      break;
  }
}

/**
* an image object, will be an XObject in the document, includes description and data
*/
function o_image($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch($action){
    case 'new':
      // make the new object
      $this->objects[$id]=array('t'=>'image','data'=>$options['data'],'info'=>array());
      $this->objects[$id]['info']['Type']='/XObject';
      $this->objects[$id]['info']['Subtype']='/Image';
      $this->objects[$id]['info']['Width']=$options['iw'];
      $this->objects[$id]['info']['Height']=$options['ih'];
      if (!isset($options['type']) || $options['type']=='jpg'){
        if (!isset($options['channels'])){
          $options['channels']=3;
        }
        switch($options['channels']){
          case 1:
            $this->objects[$id]['info']['ColorSpace']='/DeviceGray';
            break;
          default:
            $this->objects[$id]['info']['ColorSpace']='/DeviceRGB';
            break;
        }
        $this->objects[$id]['info']['Filter']='/DCTDecode';
        $this->objects[$id]['info']['BitsPerComponent']=8;
      } else if ($options['type']=='png'){
        $this->objects[$id]['info']['Filter']='/FlateDecode';
        $this->objects[$id]['info']['DecodeParms']='<< /Predictor 15 /Colors '.$options['ncolor'].' /Columns '.$options['iw'].' /BitsPerComponent '.$options['bitsPerComponent'].'>>';
        if (strlen($options['pdata'])){
          $tmp = ' [ /Indexed /DeviceRGB '.(strlen($options['pdata'])/3-1).' ';
          $this->numObj++;
          $this->o_contents($this->numObj,'new');
          $this->objects[$this->numObj]['c']=$options['pdata'];
          $tmp.=$this->numObj.' 0 R';
          $tmp .=' ]';
          $this->objects[$id]['info']['ColorSpace'] = $tmp;
          if (isset($options['transparency'])){
            switch($options['transparency']['type']){
              case 'indexed':
                $tmp=' [ '.$options['transparency']['data'].' '.$options['transparency']['data'].'] ';
                $this->objects[$id]['info']['Mask'] = $tmp;
                break;
            }
          }
        } else {
          $this->objects[$id]['info']['ColorSpace']='/'.$options['color'];
        }
        $this->objects[$id]['info']['BitsPerComponent']=$options['bitsPerComponent'];
      }
      // assign it a place in the named resource dictionary as an external object, according to
      // the label passed in with it.
      $this->o_pages($this->currentNode,'xObject',array('label'=>$options['label'],'objNum'=>$id));
      // also make sure that we have the right procset object for it.
      $this->o_procset($this->procsetObjectId,'add','ImageC');
      break;
    case 'out':
      $tmp=$o['data'];
      $res= "\n".$id." 0 obj\n<<";
      foreach($o['info'] as $k=>$v){
        $res.="\n/".$k.' '.$v;
      }
      if ($this->encrypted){
        $this->encryptInit($id);
        $tmp = $this->ARC4($tmp);
      }
      $res.="\n/Length ".strlen($tmp)." >>\nstream\n".$tmp."\nendstream\nendobj\n";
      return $res;
      break;
  }
}

/**
* encryption object.
*/
function o_encryption($id,$action,$options=''){
  if ($action!='new'){
    $o =& $this->objects[$id];
  }
  switch($action){
    case 'new':
      // make the new object
      $this->objects[$id]=array('t'=>'encryption','info'=>$options);
      $this->arc4_objnum=$id;
      // figure out the additional paramaters required
      $pad = chr(0x28).chr(0xBF).chr(0x4E).chr(0x5E).chr(0x4E).chr(0x75).chr(0x8A).chr(0x41).chr(0x64).chr(0x00).chr(0x4E).chr(0x56).chr(0xFF).chr(0xFA).chr(0x01).chr(0x08).chr(0x2E).chr(0x2E).chr(0x00).chr(0xB6).chr(0xD0).chr(0x68).chr(0x3E).chr(0x80).chr(0x2F).chr(0x0C).chr(0xA9).chr(0xFE).chr(0x64).chr(0x53).chr(0x69).chr(0x7A);
      $len = strlen($options['owner']);
      if ($len>32){
        $owner = substr($options['owner'],0,32);
      } else if ($len<32){
        $owner = $options['owner'].substr($pad,0,32-$len);
      } else {
        $owner = $options['owner'];
      }
      $len = strlen($options['user']);
      if ($len>32){
        $user = substr($options['user'],0,32);
      } else if ($len<32){
        $user = $options['user'].substr($pad,0,32-$len);
      } else {
        $user = $options['user'];
      }
      $tmp = $this->md5_16($owner);
      $okey = substr($tmp,0,5);
      $this->ARC4_init($okey);
      $ovalue=$this->ARC4($user);
      $this->objects[$id]['info']['O']=$ovalue;
      // now make the u value, phew.
      $tmp = $this->md5_16($user.$ovalue.chr($options['p']).chr(255).chr(255).chr(255).$this->fileIdentifier);
      $ukey = substr($tmp,0,5);

      $this->ARC4_init($ukey);
      $this->encryptionKey = $ukey;
      $this->encrypted=1;
      $uvalue=$this->ARC4($pad);

      $this->objects[$id]['info']['U']=$uvalue;
      $this->encryptionKey=$ukey;
     
      // initialize the arc4 array
      break;
    case 'out':
      $res= "\n".$id." 0 obj\n<<";
      $res.="\n/Filter /Standard";
      $res.="\n/V 1";
      $res.="\n/R 2";
      $res.="\n/O (".$this->filterText($o['info']['O']).')';
      $res.="\n/U (".$this->filterText($o['info']['U']).')';
      // and the p-value needs to be converted to account for the twos-complement approach
      $o['info']['p'] = (($o['info']['p']^255)+1)*-1;
      $res.="\n/P ".($o['info']['p']);
      $res.="\n>>\nendobj\n";
      
      return $res;
      break;
  }
}
      
/**
* ARC4 functions
* A series of function to implement ARC4 encoding in PHP
*/

/**
* calculate the 16 byte version of the 128 bit md5 digest of the string
*/
function md5_16($string){
  $tmp = md5($string);
  $out='';
  for ($i=0;$i<=30;$i=$i+2){
    $out.=chr(hexdec(substr($tmp,$i,2)));
  }
  return $out;
}

/**
* initialize the encryption for processing a particular object 
*/
function encryptInit($id){
  $tmp = $this->encryptionKey;
  $hex = dechex($id);
  if (strlen($hex)<6){
    $hex = substr('000000',0,6-strlen($hex)).$hex;
  }
  $tmp.= chr(hexdec(substr($hex,4,2))).chr(hexdec(substr($hex,2,2))).chr(hexdec(substr($hex,0,2))).chr(0).chr(0);
  $key = $this->md5_16($tmp);
  $this->ARC4_init(substr($key,0,10));
}

/**
* initialize the ARC4 encryption
*/
function ARC4_init($key=''){
  $this->arc4 = '';
  // setup the control array
  if (strlen($key)==0){
    return;
  }
  $k = '';
  while(strlen($k)<256){
    $k.=$key;
  }
  $k=substr($k,0,256);
  for ($i=0;$i<256;$i++){
    $this->arc4 .= chr($i);
  }
  $j=0;
  for ($i=0;$i<256;$i++){
    $t = $this->arc4[$i];
    $j = ($j + ord($t) + ord($k[$i]))%256;
    $this->arc4[$i]=$this->arc4[$j];
    $this->arc4[$j]=$t;
  }    
}

/**
* ARC4 encrypt a text string
*/
function ARC4($text){
  $len=strlen($text);
  $a=0;
  $b=0;
  $c = $this->arc4;
  $out='';
  for ($i=0;$i<$len;$i++){
    $a = ($a+1)%256;
    $t= $c[$a];
    $b = ($b+ord($t))%256;
    $c[$a]=$c[$b];
    $c[$b]=$t;
    $k = ord($c[(ord($c[$a])+ord($c[$b]))%256]);
    $out.=chr(ord($text[$i]) ^ $k);
  }
  
  return $out;
}

/**
* functions which can be called to adjust or add to the document
*/

/**
* add a link in the document to an external URL
*/
function addLink($url,$x0,$y0,$x1,$y1){
  $this->numObj++;
  $info = array('type'=>'link','url'=>$url,'rect'=>array($x0,$y0,$x1,$y1));
  $this->o_annotation($this->numObj,'new',$info);
}

/**
* add a link in the document to an internal destination (ie. within the document)
*/
function addInternalLink($label,$x0,$y0,$x1,$y1){
  $this->numObj++;
  $info = array('type'=>'ilink','label'=>$label,'rect'=>array($x0,$y0,$x1,$y1));
  $this->o_annotation($this->numObj,'new',$info);
}

/**
* set the encryption of the document
* can be used to turn it on and/or set the passwords which it will have.
* also the functions that the user will have are set here, such as print, modify, add
*/
function setEncryption($userPass='',$ownerPass='',$pc=array()){
  $p=bindec(11000000);

  $options = array(
     'print'=>4
    ,'modify'=>8
    ,'copy'=>16
    ,'add'=>32
  );
  foreach($pc as $k=>$v){
    if ($v && isset($options[$k])){
      $p+=$options[$k];
    } else if (isset($options[$v])){
      $p+=$options[$v];
    }
  }
  // implement encryption on the document
  if ($this->arc4_objnum == 0){
    // then the block does not exist already, add it.
    $this->numObj++;
    if (strlen($ownerPass)==0){
      $ownerPass=$userPass;
    }
    $this->o_encryption($this->numObj,'new',array('user'=>$userPass,'owner'=>$ownerPass,'p'=>$p));
  }
}

/**
* should be used for internal checks, not implemented as yet
*/
function checkAllHere(){
}

/**
* return the pdf stream as a string returned from the function
*/
function output($debug=0){

  if ($debug){
    // turn compression off
    $this->options['compression']=0;
  }

  if ($this->arc4_objnum){
    $this->ARC4_init($this->encryptionKey);
  }

  $this->checkAllHere();

  $xref=array();
  $content="%PDF-1.3\n%âãÏÓ\n";
//  $content="%PDF-1.3\n";
  $pos=strlen($content);
  foreach($this->objects as $k=>$v){
    $tmp='o_'.$v['t'];
    $cont=$this->$tmp($k,'out');
    $content.=$cont;
    $xref[]=$pos;
    $pos+=strlen($cont);
  }
  $content.="\nxref\n0 ".(count($xref)+1)."\n0000000000 65535 f \n";
  foreach($xref as $p){
    $content.=substr('0000000000',0,10-strlen($p)).$p." 00000 n \n";
  }
  $content.="\ntrailer\n  << /Size ".(count($xref)+1)."\n     /Root 1 0 R\n     /Info ".$this->infoObject." 0 R\n";
  // if encryption has been applied to this document then add the marker for this dictionary
  if ($this->arc4_objnum > 0){
    $content .= "/Encrypt ".$this->arc4_objnum." 0 R\n";
  }
  if (strlen($this->fileIdentifier)){
    $content .= "/ID[<".$this->fileIdentifier."><".$this->fileIdentifier.">]\n";
  }
  $content .= "  >>\nstartxref\n".$pos."\n%%EOF\n";
  return $content;
}

/**
* intialize a new document
* if this is called on an existing document results may be unpredictable, but the existing document would be lost at minimum
* this function is called automatically by the constructor function
*
* @access private
*/
function newDocument($pageSize=array(0,0,612,792)){
  $this->numObj=0;
  $this->objects = array();

  $this->numObj++;
  $this->o_catalog($this->numObj,'new');

  $this->numObj++;
  $this->o_outlines($this->numObj,'new');

  $this->numObj++;
  $this->o_pages($this->numObj,'new');

  $this->o_pages($this->numObj,'mediaBox',$pageSize);
  $this->currentNode = 3;

  $this->numObj++;
  $this->o_procset($this->numObj,'new');

  $this->numObj++;
  $this->o_info($this->numObj,'new');

  $this->numObj++;
  $this->o_page($this->numObj,'new');

  // need to store the first page id as there is no way to get it to the user during 
  // startup
  $this->firstPageId = $this->currentContents;
}

/**
* open the font file and return a php structure containing it.
* first check if this one has been done before and saved in a form more suited to php
* note that if a php serialized version does not exist it will try and make one, but will
* require write access to the directory to do it... it is MUCH faster to have these serialized
* files.
*
* @access private
*/
function openFont($font){
  // assume that $font contains both the path and perhaps the extension to the file, split them
  $pos=strrpos($font,'/');
  if ($pos===false){
    $dir = './';
    $name = $font;
  } else {
    $dir=substr($font,0,$pos+1);
    $name=substr($font,$pos+1);
  }

  if (substr($name,-4)=='.afm'){
    $name=substr($name,0,strlen($name)-4);
  }
  $this->addMessage('openFont: '.$font.' - '.$name);
  if (file_exists($dir.'php_'.$name.'.afm')){
    $this->addMessage('openFont: php file exists '.$dir.'php_'.$name.'.afm');
    $tmp = file($dir.'php_'.$name.'.afm');
    $this->fonts[$font]=unserialize($tmp[0]);
    if (!isset($this->fonts[$font]['_version_']) || $this->fonts[$font]['_version_']<1){
      // if the font file is old, then clear it out and prepare for re-creation
      $this->addMessage('openFont: clear out, make way for new version.');
      unset($this->fonts[$font]);
    }
  }
  if (!isset($this->fonts[$font]) && file_exists($dir.$name.'.afm')){
    // then rebuild the php_<font>.afm file from the <font>.afm file
    $this->addMessage('openFont: build php file from '.$dir.$name.'.afm');
    $data = array();
    $file = file($dir.$name.'.afm');
    foreach ($file as $rowA){
      $row=trim($rowA);
      $pos=strpos($row,' ');
      if ($pos){
        // then there must be some keyword
        $key = substr($row,0,$pos);
        switch ($key){
          case 'FontName':
          case 'FullName':
          case 'FamilyName':
          case 'Weight':
          case 'ItalicAngle':
          case 'IsFixedPitch':
          case 'CharacterSet':
          case 'UnderlinePosition':
          case 'UnderlineThickness':
          case 'Version':
          case 'EncodingScheme':
          case 'CapHeight':
          case 'XHeight':
          case 'Ascender':
          case 'Descender':
          case 'StdHW':
          case 'StdVW':
          case 'StartCharMetrics':
            $data[$key]=trim(substr($row,$pos));
            break;
          case 'FontBBox':
            $data[$key]=explode(' ',trim(substr($row,$pos)));
            break;
          case 'C':
            //C 39 ; WX 222 ; N quoteright ; B 53 463 157 718 ;
            $bits=explode(';',trim($row));
            $dtmp=array();
            foreach($bits as $bit){
              $bits2 = explode(' ',trim($bit));
              if (strlen($bits2[0])){
                if (count($bits2)>2){
                  $dtmp[$bits2[0]]=array();
                  for ($i=1;$i<count($bits2);$i++){
                    $dtmp[$bits2[0]][]=$bits2[$i];
                  }
                } else if (count($bits2)==2){
                  $dtmp[$bits2[0]]=$bits2[1];
                }
              }
            }
            if ($dtmp['C']>=0){
              $data['C'][$dtmp['C']]=$dtmp;
              $data['C'][$dtmp['N']]=$dtmp;
            } else {
              $data['C'][$dtmp['N']]=$dtmp;
            }
            break;
          case 'KPX':
            //KPX Adieresis yacute -40
            $bits=explode(' ',trim($row));
            $data['KPX'][$bits[1]][$bits[2]]=$bits[3];
            break;
        }
      }
    }
    $data['_version_']=1;
    $this->fonts[$font]=$data;
    $fp = fopen($dir.'php_'.$name.'.afm','w');
    fwrite($fp,serialize($data));
    fclose($fp);
  } else if (!isset($this->fonts[$font])){
    $this->addMessage('openFont: no font file found');
//    echo 'Font not Found '.$font;
  }
}

/**
* if the font is not loaded then load it and make the required object
* else just make it the current font
* the encoding array can contain 'encoding'=> 'none','WinAnsiEncoding','MacRomanEncoding' or 'MacExpertEncoding'
* note that encoding='none' will need to be used for symbolic fonts
* and 'differences' => an array of mappings between numbers 0->255 and character names.
*
*/
function selectFont($fontName,$encoding='',$set=1){
  if (!isset($this->fonts[$fontName])){
    // load the file
    $this->openFont($fontName);
    if (isset($this->fonts[$fontName])){
      $this->numObj++;
      $this->numFonts++;
      $pos=strrpos($fontName,'/');
//      $dir=substr($fontName,0,$pos+1);
      $name=substr($fontName,$pos+1);
      if (substr($name,-4)=='.afm'){
        $name=substr($name,0,strlen($name)-4);
      }
      $options=array('name'=>$name);
      if (is_array($encoding)){
        // then encoding and differences might be set
        if (isset($encoding['encoding'])){
          $options['encoding']=$encoding['encoding'];
        }
        if (isset($encoding['differences'])){
          $options['differences']=$encoding['differences'];
        }
      } else if (strlen($encoding)){
        // then perhaps only the encoding has been set
        $options['encoding']=$encoding;
      }
      $fontObj = $this->numObj;
      $this->o_font($this->numObj,'new',$options);
      $this->fonts[$fontName]['fontNum']=$this->numFonts;
      // if this is a '.afm' font, and there is a '.pfa' file to go with it ( as there
      // should be for all non-basic fonts), then load it into an object and put the
      // references into the font object
      $basefile = substr($fontName,0,strlen($fontName)-4);
      if (file_exists($basefile.'.pfb')){
        $fbtype = 'pfb';
      } else if (file_exists($basefile.'.ttf')){
        $fbtype = 'ttf';
      } else {
        $fbtype='';
      }
      $fbfile = $basefile.'.'.$fbtype;
      
//      $pfbfile = substr($fontName,0,strlen($fontName)-4).'.pfb';
//      $ttffile = substr($fontName,0,strlen($fontName)-4).'.ttf';
      $this->addMessage('selectFont: checking for - '.$fbfile);
      if (substr($fontName,-4)=='.afm' && strlen($fbtype) ){
        $adobeFontName = $this->fonts[$fontName]['FontName'];
//        $fontObj = $this->numObj;
        $this->addMessage('selectFont: adding font file - '.$fbfile.' - '.$adobeFontName);
        // find the array of fond widths, and put that into an object.
        $firstChar = -1;
        $lastChar = 0;
        $widths = array();
        foreach ($this->fonts[$fontName]['C'] as $num=>$d){
          if (intval($num)>0 || $num=='0'){
            if ($lastChar>0 && $num>$lastChar+1){
              for($i=$lastChar+1;$i<$num;$i++){
                $widths[] = 0;
              }
            }
            $widths[] = $d['WX'];
            if ($firstChar==-1){
              $firstChar = $num;
            }
            $lastChar = $num;
          }
        }
        // also need to adjust the widths for the differences array
        if (isset($options['differences'])){
          foreach($options['differences'] as $charNum=>$charName){
            if ($charNum>$lastChar){
              for($i=$lastChar+1;$i<=$charNum;$i++){
                $widths[]=0;
              }
              $lastChar=$charNum;
            }
            if (isset($this->fonts[$fontName]['C'][$charName])){
              $widths[$charNum-$firstChar]=$this->fonts[$fontName]['C'][$charName]['WX'];
            }
          }
        }
        $this->addMessage('selectFont: FirstChar='.$firstChar);
        $this->addMessage('selectFont: LastChar='.$lastChar);
        $this->numObj++;
        $this->o_contents($this->numObj,'new','raw');
        $this->objects[$this->numObj]['c'].='[';
        foreach($widths as $width){
          $this->objects[$this->numObj]['c'].=' '.$width;
        }
        $this->objects[$this->numObj]['c'].=' ]';
        $widthid = $this->numObj;

        // load the pfb file, and put that into an object too.
        // note that pdf supports only binary format type 1 font files, though there is a 
        // simple utility to convert them from pfa to pfb.
        $fp = fopen($fbfile,'rb');
        $tmp = get_magic_quotes_runtime();
        set_magic_quotes_runtime(0);
        $data = fread($fp,filesize($fbfile));
        set_magic_quotes_runtime($tmp);
        fclose($fp);

        // create the font descriptor
        $this->numObj++;
        $fontDescriptorId = $this->numObj;
        $this->numObj++;
        $pfbid = $this->numObj;
        // determine flags (more than a little flakey, hopefully will not matter much)
        $flags=0;
        if ($this->fonts[$fontName]['ItalicAngle']!=0){ $flags+=pow(2,6); }
        if ($this->fonts[$fontName]['IsFixedPitch']=='true'){ $flags+=1; }
        $flags+=pow(2,5); // assume non-sybolic

        $list = array('Ascent'=>'Ascender','CapHeight'=>'CapHeight','Descent'=>'Descender','FontBBox'=>'FontBBox','ItalicAngle'=>'ItalicAngle');
        $fdopt = array(
         'Flags'=>$flags
         ,'FontName'=>$adobeFontName
         ,'StemV'=>100  // don't know what the value for this should be!
        );
        foreach($list as $k=>$v){
          if (isset($this->fonts[$fontName][$v])){
            $fdopt[$k]=$this->fonts[$fontName][$v];
          }
        }

        if ($fbtype=='pfb'){
          $fdopt['FontFile']=$pfbid;
        } else if ($fbtype=='ttf'){
          $fdopt['FontFile2']=$pfbid;
        }
        $this->o_fontDescriptor($fontDescriptorId,'new',$fdopt);        

        // embed the font program
        $this->o_contents($this->numObj,'new');
        $this->objects[$pfbid]['c'].=$data;
        // determine the cruicial lengths within this file
        if ($fbtype=='pfb'){
          $l1 = strpos($data,'eexec')+6;
          $l2 = strpos($data,'00000000')-$l1;
          $l3 = strlen($data)-$l2-$l1;
          $this->o_contents($this->numObj,'add',array('Length1'=>$l1,'Length2'=>$l2,'Length3'=>$l3));
        } else if ($fbtype=='ttf'){
          $l1 = strlen($data);
          $this->o_contents($this->numObj,'add',array('Length1'=>$l1));
        }


        // tell the font object about all this new stuff
        $tmp = array('BaseFont'=>$adobeFontName,'Widths'=>$widthid
                                      ,'FirstChar'=>$firstChar,'LastChar'=>$lastChar
                                      ,'FontDescriptor'=>$fontDescriptorId);
        if ($fbtype=='ttf'){
          $tmp['SubType']='TrueType';
        }
        $this->addMessage('adding extra info to font.('.$fontObj.')');
        foreach($tmp as $fk=>$fv){
          $this->addMessage($fk." : ".$fv);
        }
        $this->o_font($fontObj,'add',$tmp);

      } else {
        $this->addMessage('selectFont: pfb or ttf file not found, ok if this is one of the 14 standard fonts');
      }


      // also set the differences here, note that this means that these will take effect only the 
      //first time that a font is selected, else they are ignored
      if (isset($options['differences'])){
        $this->fonts[$fontName]['differences']=$options['differences'];
      }
    }
  }
  if ($set && isset($this->fonts[$fontName])){
    // so if for some reason the font was not set in the last one then it will not be selected
    $this->currentBaseFont=$fontName;
    // the next line means that if a new font is selected, then the current text state will be
    // applied to it as well.
    $this->setCurrentFont();
  }
  return $this->currentFontNum;
}

/**
* sets up the current font, based on the font families, and the current text state
* note that this system is quite flexible, a <<b>><<i>> font can be completely different to a
* <<i>><<b>> font, and even <<b>><<b>> will have to be defined within the family to have meaning
* This function is to be called whenever the currentTextState is changed, it will update
* the currentFont setting to whatever the appropriatte family one is.
* If the user calls selectFont themselves then that will reset the currentBaseFont, and the currentFont
* This function will change the currentFont to whatever it should be, but will not change the 
* currentBaseFont.
*
* @access private
*/
function setCurrentFont(){
  if (strlen($this->currentBaseFont)==0){
    // then assume an initial font
    $this->selectFont('./fonts/Helvetica.afm');
  }
  $cf = substr($this->currentBaseFont,strrpos($this->currentBaseFont,'/')+1);
  if (strlen($this->currentTextState)
    && isset($this->fontFamilies[$cf]) 
      && isset($this->fontFamilies[$cf][$this->currentTextState])){
    // then we are in some state or another
    // and this font has a family, and the current setting exists within it
    // select the font, then return it
    $nf = substr($this->currentBaseFont,0,strrpos($this->currentBaseFont,'/')+1).$this->fontFamilies[$cf][$this->currentTextState];
    $this->selectFont($nf,'',0);
    $this->currentFont = $nf;
    $this->currentFontNum = $this->fonts[$nf]['fontNum'];
  } else {
    // the this font must not have the right family member for the current state
    // simply assume the base font
    $this->currentFont = $this->currentBaseFont;
    $this->currentFontNum = $this->fonts[$this->currentFont]['fontNum'];    
  }
}

/**
* function for the user to find out what the ID is of the first page that was created during
* startup - useful if they wish to add something to it later.
*/
function getFirstPageId(){
  return $this->firstPageId;
}

/**
* add content to the currently active object
*
* @access private
*/
function addContent($content){
  $this->objects[$this->currentContents]['c'].=$content;
}

/**
* sets the colour for fill operations
*/
function setColor($r,$g,$b,$force=0){
  if ($r>=0 && ($force || $r!=$this->currentColour['r'] || $g!=$this->currentColour['g'] || $b!=$this->currentColour['b'])){
    $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$r).' '.sprintf('%.3f',$g).' '.sprintf('%.3f',$b).' rg';
    $this->currentColour=array('r'=>$r,'g'=>$g,'b'=>$b);
  }
}

/**
* sets the colour for stroke operations
*/
function setStrokeColor($r,$g,$b,$force=0){
  if ($r>=0 && ($force || $r!=$this->currentStrokeColour['r'] || $g!=$this->currentStrokeColour['g'] || $b!=$this->currentStrokeColour['b'])){
    $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$r).' '.sprintf('%.3f',$g).' '.sprintf('%.3f',$b).' RG';
    $this->currentStrokeColour=array('r'=>$r,'g'=>$g,'b'=>$b);
  }
}

/**
* draw a line from one set of coordinates to another
*/
function line($x1,$y1,$x2,$y2){
  $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$x1).' '.sprintf('%.3f',$y1).' m '.sprintf('%.3f',$x2).' '.sprintf('%.3f',$y2).' l S';
}

/**
* draw a bezier curve based on 4 control points
*/
function curve($x0,$y0,$x1,$y1,$x2,$y2,$x3,$y3){
  // in the current line style, draw a bezier curve from (x0,y0) to (x3,y3) using the other two points
  // as the control points for the curve.
  $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$x0).' '.sprintf('%.3f',$y0).' m '.sprintf('%.3f',$x1).' '.sprintf('%.3f',$y1);
  $this->objects[$this->currentContents]['c'].= ' '.sprintf('%.3f',$x2).' '.sprintf('%.3f',$y2).' '.sprintf('%.3f',$x3).' '.sprintf('%.3f',$y3).' c S';
}

/**
* draw a part of an ellipse
*/
function partEllipse($x0,$y0,$astart,$afinish,$r1,$r2=0,$angle=0,$nSeg=8){
  $this->ellipse($x0,$y0,$r1,$r2,$angle,$nSeg,$astart,$afinish,0);
}

/**
* draw a filled ellipse
*/
function filledEllipse($x0,$y0,$r1,$r2=0,$angle=0,$nSeg=8,$astart=0,$afinish=360){
  return $this->ellipse($x0,$y0,$r1,$r2=0,$angle,$nSeg,$astart,$afinish,1,1);
}

/**
* draw an ellipse
* note that the part and filled ellipse are just special cases of this function
*
* draws an ellipse in the current line style
* centered at $x0,$y0, radii $r1,$r2
* if $r2 is not set, then a circle is drawn
* nSeg is not allowed to be less than 2, as this will simply draw a line (and will even draw a 
* pretty crappy shape at 2, as we are approximating with bezier curves.
*/
function ellipse($x0,$y0,$r1,$r2=0,$angle=0,$nSeg=8,$astart=0,$afinish=360,$close=1,$fill=0){
  if ($r1==0){
    return;
  }
  if ($r2==0){
    $r2=$r1;
  }
  if ($nSeg<2){
    $nSeg=2;
  }

  $astart = deg2rad((float)$astart);
  $afinish = deg2rad((float)$afinish);
  $totalAngle =$afinish-$astart;

  $dt = $totalAngle/$nSeg;
  $dtm = $dt/3;

  if ($angle != 0){
    $a = -1*deg2rad((float)$angle);
    $tmp = "\n q ";
    $tmp .= sprintf('%.3f',cos($a)).' '.sprintf('%.3f',(-1.0*sin($a))).' '.sprintf('%.3f',sin($a)).' '.sprintf('%.3f',cos($a)).' ';
    $tmp .= sprintf('%.3f',$x0).' '.sprintf('%.3f',$y0).' cm';
    $this->objects[$this->currentContents]['c'].= $tmp;
    $x0=0;
    $y0=0;
  }

  $t1 = $astart;
  $a0 = $x0+$r1*cos($t1);
  $b0 = $y0+$r2*sin($t1);
  $c0 = -$r1*sin($t1);
  $d0 = $r2*cos($t1);

  $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$a0).' '.sprintf('%.3f',$b0).' m ';
  for ($i=1;$i<=$nSeg;$i++){
    // draw this bit of the total curve
    $t1 = $i*$dt+$astart;
    $a1 = $x0+$r1*cos($t1);
    $b1 = $y0+$r2*sin($t1);
    $c1 = -$r1*sin($t1);
    $d1 = $r2*cos($t1);
    $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',($a0+$c0*$dtm)).' '.sprintf('%.3f',($b0+$d0*$dtm));
    $this->objects[$this->currentContents]['c'].= ' '.sprintf('%.3f',($a1-$c1*$dtm)).' '.sprintf('%.3f',($b1-$d1*$dtm)).' '.sprintf('%.3f',$a1).' '.sprintf('%.3f',$b1).' c';
    $a0=$a1;
    $b0=$b1;
    $c0=$c1;
    $d0=$d1;    
  }
  if ($fill){
    $this->objects[$this->currentContents]['c'].=' f';
  } else {
    if ($close){
      $this->objects[$this->currentContents]['c'].=' s'; // small 's' signifies closing the path as well
    } else {
      $this->objects[$this->currentContents]['c'].=' S';
    }
  }
  if ($angle !=0){
    $this->objects[$this->currentContents]['c'].=' Q';
  }
}

/**
* this sets the line drawing style.
* width, is the thickness of the line in user units
* cap is the type of cap to put on the line, values can be 'butt','round','square'
*    where the diffference between 'square' and 'butt' is that 'square' projects a flat end past the
*    end of the line.
* join can be 'miter', 'round', 'bevel'
* dash is an array which sets the dash pattern, is a series of length values, which are the lengths of the
*   on and off dashes.
*   (2) represents 2 on, 2 off, 2 on , 2 off ...
*   (2,1) is 2 on, 1 off, 2 on, 1 off.. etc
* phase is a modifier on the dash pattern which is used to shift the point at which the pattern starts. 
*/
function setLineStyle($width=1,$cap='',$join='',$dash='',$phase=0){

  // this is quite inefficient in that it sets all the parameters whenever 1 is changed, but will fix another day
  $string = '';
  if ($width>0){
    $string.= $width.' w';
  }
  $ca = array('butt'=>0,'round'=>1,'square'=>2);
  if (isset($ca[$cap])){
    $string.= ' '.$ca[$cap].' J';
  }
  $ja = array('miter'=>0,'round'=>1,'bevel'=>2);
  if (isset($ja[$join])){
    $string.= ' '.$ja[$join].' j';
  }
  if (is_array($dash)){
    $string.= ' [';
    foreach ($dash as $len){
      $string.=' '.$len;
    }
    $string.= ' ] '.$phase.' d';
  }
  $this->currentLineStyle = $string;
  $this->objects[$this->currentContents]['c'].="\n".$string;
}

/**
* draw a polygon, the syntax for this is similar to the GD polygon command
*/
function polygon($p,$np,$f=0){
  $this->objects[$this->currentContents]['c'].="\n";
  $this->objects[$this->currentContents]['c'].=sprintf('%.3f',$p[0]).' '.sprintf('%.3f',$p[1]).' m ';
  for ($i=2;$i<$np*2;$i=$i+2){
    $this->objects[$this->currentContents]['c'].= sprintf('%.3f',$p[$i]).' '.sprintf('%.3f',$p[$i+1]).' l ';
  }
  if ($f==1){
    $this->objects[$this->currentContents]['c'].=' f';
  } else {
    $this->objects[$this->currentContents]['c'].=' S';
  }
}

/**
* a filled rectangle, note that it is the width and height of the rectangle which are the secondary paramaters, not
* the coordinates of the upper-right corner
*/
function filledRectangle($x1,$y1,$width,$height){
  $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$x1).' '.sprintf('%.3f',$y1).' '.sprintf('%.3f',$width).' '.sprintf('%.3f',$height).' re f';
}

/**
* draw a rectangle, note that it is the width and height of the rectangle which are the secondary paramaters, not
* the coordinates of the upper-right corner
*/
function rectangle($x1,$y1,$width,$height){
  $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$x1).' '.sprintf('%.3f',$y1).' '.sprintf('%.3f',$width).' '.sprintf('%.3f',$height).' re S';
}

/**
* add a new page to the document
* this also makes the new page the current active object
*/
function newPage($insert=0,$id=0,$pos='after'){

  // if there is a state saved, then go up the stack closing them
  // then on the new page, re-open them with the right setings
  
  if ($this->nStateStack){
    for ($i=$this->nStateStack;$i>=1;$i--){
      $this->restoreState($i);
    }
  }

  $this->numObj++;
  if ($insert){
    // the id from the ezPdf class is the od of the contents of the page, not the page object itself
    // query that object to find the parent
    $rid = $this->objects[$id]['onPage'];
    $opt= array('rid'=>$rid,'pos'=>$pos);
    $this->o_page($this->numObj,'new',$opt);
  } else {
    $this->o_page($this->numObj,'new');
  }
  // if there is a stack saved, then put that onto the page
  if ($this->nStateStack){
    for ($i=1;$i<=$this->nStateStack;$i++){
      $this->saveState($i);
    }
  }  
  // and if there has been a stroke or fill colour set, then transfer them
  if ($this->currentColour['r']>=0){
    $this->setColor($this->currentColour['r'],$this->currentColour['g'],$this->currentColour['b'],1);
  }
  if ($this->currentStrokeColour['r']>=0){
    $this->setStrokeColor($this->currentStrokeColour['r'],$this->currentStrokeColour['g'],$this->currentStrokeColour['b'],1);
  }

  // if there is a line style set, then put this in too
  if (strlen($this->currentLineStyle)){
    $this->objects[$this->currentContents]['c'].="\n".$this->currentLineStyle;
  }

  // the call to the o_page object set currentContents to the present page, so this can be returned as the page id
  return $this->currentContents;
}

/**
* output the pdf code, streaming it to the browser
* the relevant headers are set so that hopefully the browser will recognise it
*/
function stream($options=''){
  // setting the options allows the adjustment of the headers
  // values at the moment are:
  // 'Content-Disposition'=>'filename'  - sets the filename, though not too sure how well this will 
  //        work as in my trial the browser seems to use the filename of the php file with .pdf on the end
  // 'Accept-Ranges'=>1 or 0 - if this is not set to 1, then this header is not included, off by default
  //    this header seems to have caused some problems despite tha fact that it is supposed to solve
  //    them, so I am leaving it off by default.
  // 'compress'=> 1 or 0 - apply content stream compression, this is on (1) by default
  if (!is_array($options)){
    $options=array();
  }
  if ( isset($options['compress']) && $options['compress']==0){
    $tmp = $this->output(1);
  } else {
    $tmp = $this->output();
  }
  header("Content-type: application/pdf");
  header("Content-Length: ".strlen(ltrim($tmp)));
  $fileName = (isset($options['Content-Disposition'])?$options['Content-Disposition']:'file.pdf');
  header("Content-Disposition: inline; filename=".$fileName);
  if (isset($options['Accept-Ranges']) && $options['Accept-Ranges']==1){
    header("Accept-Ranges: ".strlen(ltrim($tmp))); 
  }
  echo ltrim($tmp);
}

/**
* return the height in units of the current font in the given size
*/
function getFontHeight($size){
  if (!$this->numFonts){
    $this->selectFont('./fonts/Helvetica');
  }
  // for the current font, and the given size, what is the height of the font in user units
  $h = $this->fonts[$this->currentFont]['FontBBox'][3]-$this->fonts[$this->currentFont]['FontBBox'][1];
  return $size*$h/1000;
}

/**
* return the font decender, this will normally return a negative number
* if you add this number to the baseline, you get the level of the bottom of the font
* it is in the pdf user units
*/
function getFontDecender($size){
  // note that this will most likely return a negative value
  if (!$this->numFonts){
    $this->selectFont('./fonts/Helvetica');
  }
  $h = $this->fonts[$this->currentFont]['FontBBox'][1];
  return $size*$h/1000;
}

/**
* filter the text, this is applied to all text just before being inserted into the pdf document
* it escapes the various things that need to be escaped, and so on
*
* @access private
*/
function filterText($text){
  $text = str_replace('\\','\\\\',$text);
  $text = str_replace('(','\(',$text);
  $text = str_replace(')','\)',$text);
  $text = str_replace('&lt;','<',$text);
  $text = str_replace('&gt;','>',$text);
  $text = str_replace('&#039;','\'',$text);
  $text = str_replace('&quot;','"',$text);
  $text = str_replace('&amp;','&',$text);

  return $text;
}

/**
* given a start position and information about how text is to be laid out, calculate where 
* on the page the text will end
*
* @access private
*/
function PRVTgetTextPosition($x,$y,$angle,$size,$wa,$text){
  // given this information return an array containing x and y for the end position as elements 0 and 1
  $w = $this->getTextWidth($size,$text);
  // need to adjust for the number of spaces in this text
  $words = explode(' ',$text);
  $nspaces=count($words)-1;
  $w += $wa*$nspaces;
  $a = deg2rad((float)$angle);
  return array(cos($a)*$w+$x,-sin($a)*$w+$y);
}

/**
* wrapper function for PRVTcheckTextDirective1
*
* @access private
*/
function PRVTcheckTextDirective(&$text,$i,&$f){
  $x=0;
  $y=0;
  return $this->PRVTcheckTextDirective1($text,$i,$f,0,$x,$y);
}

/**
* checks if the text stream contains a control directive
* if so then makes some changes and returns the number of characters involved in the directive
* this has been re-worked to include everything neccesary to fins the current writing point, so that
* the location can be sent to the callback function if required
* if the directive does not require a font change, then $f should be set to 0
*
* @access private
*/
function PRVTcheckTextDirective1(&$text,$i,&$f,$final,&$x,&$y,$size=0,$angle=0,$wordSpaceAdjust=0){
  $directive = 0;
  $j=$i;
  if ($text[$j]=='<'){
    $j++;
    switch($text[$j]){
      case '/':
        $j++;
        if (strlen($text) <= $j){
          return $directive;
        }
        switch($text[$j]){
          case 'b':
          case 'i':
            $j++;
            if ($text[$j]=='>'){
              $p = strrpos($this->currentTextState,$text[$j-1]);
              if ($p !== false){
                // then there is one to remove
                $this->currentTextState = substr($this->currentTextState,0,$p).substr($this->currentTextState,$p+1);
              }
              $directive=$j-$i+1;
            }
            break;
          case 'c':
            // this this might be a callback function
            $j++;
            $k = strpos($text,'>',$j);
            if ($k!==false && $text[$j]==':'){
              // then this will be treated as a callback directive
              $directive = $k-$i+1;
              $f=0;
              // split the remainder on colons to get the function name and the paramater
              $tmp = substr($text,$j+1,$k-$j-1);
              $b1 = strpos($tmp,':');
              if ($b1!==false){
                $func = substr($tmp,0,$b1);
                $parm = substr($tmp,$b1+1);
              } else {
                $func=$tmp;
                $parm='';
              }
              if (!isset($func) || !strlen(trim($func))){
                $directive=0;
              } else {
                // only call the function if this is the final call
                if ($final){
                  // need to assess the text position, calculate the text width to this point
                  // can use getTextWidth to find the text width I think
                  $tmp = $this->PRVTgetTextPosition($x,$y,$angle,$size,$wordSpaceAdjust,substr($text,0,$i));
                  $info = array('x'=>$tmp[0],'y'=>$tmp[1],'angle'=>$angle,'status'=>'end','p'=>$parm,'nCallback'=>$this->nCallback);
                  $x=$tmp[0];
                  $y=$tmp[1];
                  $ret = $this->$func($info);
                  if (is_array($ret)){
                    // then the return from the callback function could set the position, to start with, later will do font colour, and font
                    foreach($ret as $rk=>$rv){
                      switch($rk){
                        case 'x':
                        case 'y':
                          $$rk=$rv;
                          break;
                      }
                    }
                  }
                  // also remove from to the stack
                  // for simplicity, just take from the end, fix this another day
                  $this->nCallback--;
                  if ($this->nCallback<0){
                    $this->nCallBack=0;
                  }
                }
              }
            }
            break;
        }
        break;
      case 'b':
      case 'i':
        $j++;
        if ($text[$j]=='>'){
          $this->currentTextState.=$text[$j-1];
          $directive=$j-$i+1;
        }
        break;
      case 'C':
        $noClose=1;
      case 'c':
        // this this might be a callback function
        $j++;
        $k = strpos($text,'>',$j);
        if ($k!==false && $text[$j]==':'){
          // then this will be treated as a callback directive
          $directive = $k-$i+1;
          $f=0;
          // split the remainder on colons to get the function name and the paramater
//          $bits = explode(':',substr($text,$j+1,$k-$j-1));
          $tmp = substr($text,$j+1,$k-$j-1);
          $b1 = strpos($tmp,':');
          if ($b1!==false){
            $func = substr($tmp,0,$b1);
            $parm = substr($tmp,$b1+1);
          } else {
            $func=$tmp;
            $parm='';
          }
          if (!isset($func) || !strlen(trim($func))){
            $directive=0;
          } else {
            // only call the function if this is the final call, ie, the one actually doing printing, not measurement
            if ($final){
              // need to assess the text position, calculate the text width to this point
              // can use getTextWidth to find the text width I think
              // also add the text height and decender
              $tmp = $this->PRVTgetTextPosition($x,$y,$angle,$size,$wordSpaceAdjust,substr($text,0,$i));
              $info = array('x'=>$tmp[0],'y'=>$tmp[1],'angle'=>$angle,'status'=>'start','p'=>$parm,'f'=>$func,'height'=>$this->getFontHeight($size),'decender'=>$this->getFontDecender($size));
              $x=$tmp[0];
              $y=$tmp[1];
              if (!isset($noClose) || !$noClose){
                // only add to the stack if this is a small 'c', therefore is a start-stop pair
                $this->nCallback++;
                $info['nCallback']=$this->nCallback;
                $this->callback[$this->nCallback]=$info;
              }
              $ret = $this->$func($info);
              if (is_array($ret)){
                // then the return from the callback function could set the position, to start with, later will do font colour, and font
                foreach($ret as $rk=>$rv){
                  switch($rk){
                    case 'x':
                    case 'y':
                      $$rk=$rv;
                      break;
                  }
                }
              }
            }
          }
        }
        break;
    }
  } 
  return $directive;
}

/**
* add text to the document, at a specified location, size and angle on the page
*/
function addText($x,$y,$size,$text,$angle=0,$wordSpaceAdjust=0){
  if (!$this->numFonts){$this->selectFont('./fonts/Helvetica');}

  // if there are any open callbacks, then they should be called, to show the start of the line
  if ($this->nCallback>0){
    for ($i=$this->nCallback;$i>0;$i--){
      // call each function
      $info = array('x'=>$x,'y'=>$y,'angle'=>$angle,'status'=>'sol','p'=>$this->callback[$i]['p'],'nCallback'=>$this->callback[$i]['nCallback'],'height'=>$this->callback[$i]['height'],'decender'=>$this->callback[$i]['decender']);
      $func = $this->callback[$i]['f'];
      $this->$func($info);
    }
  }
  if ($angle==0){
    $this->objects[$this->currentContents]['c'].="\n".'BT '.sprintf('%.3f',$x).' '.sprintf('%.3f',$y).' Td';
  } else {
    $a = deg2rad((float)$angle);
    $tmp = "\n".'BT ';
    $tmp .= sprintf('%.3f',cos($a)).' '.sprintf('%.3f',(-1.0*sin($a))).' '.sprintf('%.3f',sin($a)).' '.sprintf('%.3f',cos($a)).' ';
    $tmp .= sprintf('%.3f',$x).' '.sprintf('%.3f',$y).' Tm';
    $this->objects[$this->currentContents]['c'] .= $tmp;
  }
  if ($wordSpaceAdjust!=0 || $wordSpaceAdjust != $this->wordSpaceAdjust){
    $this->wordSpaceAdjust=$wordSpaceAdjust;
    $this->objects[$this->currentContents]['c'].=' '.sprintf('%.3f',$wordSpaceAdjust).' Tw';
  }
  $len=strlen($text);
  $start=0;
  for ($i=0;$i<$len;$i++){
    $f=1;
    $directive = $this->PRVTcheckTextDirective($text,$i,$f);
    if ($directive){
      // then we should write what we need to
      if ($i>$start){
        $part = substr($text,$start,$i-$start);
        $this->objects[$this->currentContents]['c'].=' /F'.$this->currentFontNum.' '.sprintf('%.1f',$size).' Tf ';
        $this->objects[$this->currentContents]['c'].=' ('.$this->filterText($part).') Tj';
      }
      if ($f){
        // then there was nothing drastic done here, restore the contents
        $this->setCurrentFont();
      } else {
        $this->objects[$this->currentContents]['c'] .= ' ET';
        $f=1;
        $xp=$x;
        $yp=$y;
        $directive = $this->PRVTcheckTextDirective1($text,$i,$f,1,$xp,$yp,$size,$angle,$wordSpaceAdjust);
        
        // restart the text object
          if ($angle==0){
            $this->objects[$this->currentContents]['c'].="\n".'BT '.sprintf('%.3f',$xp).' '.sprintf('%.3f',$yp).' Td';
          } else {
            $a = deg2rad((float)$angle);
            $tmp = "\n".'BT ';
            $tmp .= sprintf('%.3f',cos($a)).' '.sprintf('%.3f',(-1.0*sin($a))).' '.sprintf('%.3f',sin($a)).' '.sprintf('%.3f',cos($a)).' ';
            $tmp .= sprintf('%.3f',$xp).' '.sprintf('%.3f',$yp).' Tm';
            $this->objects[$this->currentContents]['c'] .= $tmp;
          }
          if ($wordSpaceAdjust!=0 || $wordSpaceAdjust != $this->wordSpaceAdjust){
            $this->wordSpaceAdjust=$wordSpaceAdjust;
            $this->objects[$this->currentContents]['c'].=' '.sprintf('%.3f',$wordSpaceAdjust).' Tw';
          }
      }
      // and move the writing point to the next piece of text
      $i=$i+$directive-1;
      $start=$i+1;
    }
    
  }
  if ($start<$len){
    $part = substr($text,$start);
    $this->objects[$this->currentContents]['c'].=' /F'.$this->currentFontNum.' '.sprintf('%.1f',$size).' Tf ';
    $this->objects[$this->currentContents]['c'].=' ('.$this->filterText($part).') Tj';
  }
  $this->objects[$this->currentContents]['c'].=' ET';

  // if there are any open callbacks, then they should be called, to show the end of the line
  if ($this->nCallback>0){
    for ($i=$this->nCallback;$i>0;$i--){
      // call each function
      $tmp = $this->PRVTgetTextPosition($x,$y,$angle,$size,$wordSpaceAdjust,$text);
      $info = array('x'=>$tmp[0],'y'=>$tmp[1],'angle'=>$angle,'status'=>'eol','p'=>$this->callback[$i]['p'],'nCallback'=>$this->callback[$i]['nCallback'],'height'=>$this->callback[$i]['height'],'decender'=>$this->callback[$i]['decender']);
      $func = $this->callback[$i]['f'];
      $this->$func($info);
    }
  }

}

/**
* calculate how wide a given text string will be on a page, at a given size.
* this can be called externally, but is alse used by the other class functions
*/
function getTextWidth($size,$text){
  // this function should not change any of the settings, though it will need to
  // track any directives which change during calculation, so copy them at the start
  // and put them back at the end.
  $store_currentTextState = $this->currentTextState;

  if (!$this->numFonts){
    $this->selectFont('./fonts/Helvetica');
  }

  // converts a number or a float to a string so it can get the width
  $text = "$text";

  // hmm, this is where it all starts to get tricky - use the font information to
  // calculate the width of each character, add them up and convert to user units
  $w=0;
  $len=strlen($text);
  $cf = $this->currentFont;
  for ($i=0;$i<$len;$i++){
    $f=1;
    $directive = $this->PRVTcheckTextDirective($text,$i,$f);
    if ($directive){
      if ($f){
        $this->setCurrentFont();
        $cf = $this->currentFont;
      }
      $i=$i+$directive-1;
    } else {
      $char=ord($text[$i]);
      if (isset($this->fonts[$cf]['differences'][$char])){
        // then this character is being replaced by another
        $name = $this->fonts[$cf]['differences'][$char];
        if (isset($this->fonts[$cf]['C'][$name]['WX'])){
          $w+=$this->fonts[$cf]['C'][$name]['WX'];
        }
      } else if (isset($this->fonts[$cf]['C'][$char]['WX'])){
        $w+=$this->fonts[$cf]['C'][$char]['WX'];
      }
    }
  }
  
  $this->currentTextState = $store_currentTextState;
  $this->setCurrentFont();

  return $w*$size/1000;
}

/**
* do a part of the calculation for sorting out the justification of the text
*
* @access private
*/
function PRVTadjustWrapText($text,$actual,$width,&$x,&$adjust,$justification){
  switch ($justification){
    case 'left':
      return;
      break;
    case 'right':
      $x+=$width-$actual;
      break;
    case 'center':
    case 'centre':
      $x+=($width-$actual)/2;
      break;
    case 'full':
      // count the number of words
      $words = explode(' ',$text);
      $nspaces=count($words)-1;
      if ($nspaces>0){
        $adjust = ($width-$actual)/$nspaces;
      } else {
        $adjust=0;
      }
      break;
  }
}

/**
* add text to the page, but ensure that it fits within a certain width
* if it does not fit then put in as much as possible, splitting at word boundaries
* and return the remainder.
* justification and angle can also be specified for the text
*/
function addTextWrap($x,$y,$width,$size,$text,$justification='left',$angle=0,$test=0){
  // this will display the text, and if it goes beyond the width $width, will backtrack to the 
  // previous space or hyphen, and return the remainder of the text.

  // $justification can be set to 'left','right','center','centre','full'

  // need to store the initial text state, as this will change during the width calculation
  // but will need to be re-set before printing, so that the chars work out right
  $store_currentTextState = $this->currentTextState;

  if (!$this->numFonts){$this->selectFont('./fonts/Helvetica');}
  if ($width<=0){
    // error, pretend it printed ok, otherwise risking a loop
    return '';
  }
  $w=0;
  $break=0;
  $breakWidth=0;
  $len=strlen($text);
  $cf = $this->currentFont;
  $tw = $width/$size*1000;
  for ($i=0;$i<$len;$i++){
    $f=1;
    $directive = $this->PRVTcheckTextDirective($text,$i,$f);
    if ($directive){
      if ($f){
        $this->setCurrentFont();
        $cf = $this->currentFont;
      }
      $i=$i+$directive-1;
    } else {
      $cOrd = ord($text[$i]);
      if (isset($this->fonts[$cf]['differences'][$cOrd])){
        // then this character is being replaced by another
        $cOrd2 = $this->fonts[$cf]['differences'][$cOrd];
      } else {
        $cOrd2 = $cOrd;
      }
  
      if (isset($this->fonts[$cf]['C'][$cOrd2]['WX'])){
        $w+=$this->fonts[$cf]['C'][$cOrd2]['WX'];
      }
      if ($w>$tw){
        // then we need to truncate this line
        if ($break>0){
          // then we have somewhere that we can split :)
          if ($text[$break]==' '){
            $tmp = substr($text,0,$break);
          } else {
            $tmp = substr($text,0,$break+1);
          }
          $adjust=0;
          $this->PRVTadjustWrapText($tmp,$breakWidth,$width,$x,$adjust,$justification);

          // reset the text state
          $this->currentTextState = $store_currentTextState;
          $this->setCurrentFont();
          if (!$test){
            $this->addText($x,$y,$size,$tmp,$angle,$adjust);
          }
          return substr($text,$break+1);
        } else {
          // just split before the current character
          $tmp = substr($text,0,$i);
          $adjust=0;
          $ctmp=ord($text[$i]);
          if (isset($this->fonts[$cf]['differences'][$ctmp])){
            $ctmp=$this->fonts[$cf]['differences'][$ctmp];
          }
          $tmpw=($w-$this->fonts[$cf]['C'][$ctmp]['WX'])*$size/1000;
          $this->PRVTadjustWrapText($tmp,$tmpw,$width,$x,$adjust,$justification);
          // reset the text state
          $this->currentTextState = $store_currentTextState;
          $this->setCurrentFont();
          if (!$test){
            $this->addText($x,$y,$size,$tmp,$angle,$adjust);
          }
          return substr($text,$i);
        }
      }
      if ($text[$i]=='-'){
        $break=$i;
        $breakWidth = $w*$size/1000;
      }
      if ($text[$i]==' '){
        $break=$i;
        $ctmp=ord($text[$i]);
        if (isset($this->fonts[$cf]['differences'][$ctmp])){
          $ctmp=$this->fonts[$cf]['differences'][$ctmp];
        }
        $breakWidth = ($w-$this->fonts[$cf]['C'][$ctmp]['WX'])*$size/1000;
      }
    }
  }
  // then there was no need to break this line
  if ($justification=='full'){
    $justification='left';
  }
  $adjust=0;
  $tmpw=$w*$size/1000;
  $this->PRVTadjustWrapText($text,$tmpw,$width,$x,$adjust,$justification);
  // reset the text state
  $this->currentTextState = $store_currentTextState;
  $this->setCurrentFont();
  if (!$test){
    $this->addText($x,$y,$size,$text,$angle,$adjust,$angle);
  }
  return '';
}

/**
* this will be called at a new page to return the state to what it was on the 
* end of the previous page, before the stack was closed down
* This is to get around not being able to have open 'q' across pages
*
*/
function saveState($pageEnd=0){
  if ($pageEnd){
    // this will be called at a new page to return the state to what it was on the 
    // end of the previous page, before the stack was closed down
    // This is to get around not being able to have open 'q' across pages
    $opt = $this->stateStack[$pageEnd]; // ok to use this as stack starts numbering at 1
    $this->setColor($opt['col']['r'],$opt['col']['g'],$opt['col']['b'],1);
    $this->setStrokeColor($opt['str']['r'],$opt['str']['g'],$opt['str']['b'],1);
    $this->objects[$this->currentContents]['c'].="\n".$opt['lin'];
//    $this->currentLineStyle = $opt['lin'];
  } else {
    $this->nStateStack++;
    $this->stateStack[$this->nStateStack]=array(
      'col'=>$this->currentColour
     ,'str'=>$this->currentStrokeColour
     ,'lin'=>$this->currentLineStyle
    );
  }
  $this->objects[$this->currentContents]['c'].="\nq";
}

/**
* restore a previously saved state
*/
function restoreState($pageEnd=0){
  if (!$pageEnd){
    $n = $this->nStateStack;
    $this->currentColour = $this->stateStack[$n]['col'];
    $this->currentStrokeColour = $this->stateStack[$n]['str'];
    $this->objects[$this->currentContents]['c'].="\n".$this->stateStack[$n]['lin'];
    $this->currentLineStyle = $this->stateStack[$n]['lin'];
    unset($this->stateStack[$n]);
    $this->nStateStack--;
  }
  $this->objects[$this->currentContents]['c'].="\nQ";
}

/**
* make a loose object, the output will go into this object, until it is closed, then will revert to
* the current one.
* this object will not appear until it is included within a page.
* the function will return the object number
*/
function openObject(){
  $this->nStack++;
  $this->stack[$this->nStack]=array('c'=>$this->currentContents,'p'=>$this->currentPage);
  // add a new object of the content type, to hold the data flow
  $this->numObj++;
  $this->o_contents($this->numObj,'new');
  $this->currentContents=$this->numObj;
  $this->looseObjects[$this->numObj]=1;
  
  return $this->numObj;
}

/**
* open an existing object for editing
*/
function reopenObject($id){
   $this->nStack++;
   $this->stack[$this->nStack]=array('c'=>$this->currentContents,'p'=>$this->currentPage);
   $this->currentContents=$id;
   // also if this object is the primary contents for a page, then set the current page to its parent
   if (isset($this->objects[$id]['onPage'])){
     $this->currentPage = $this->objects[$id]['onPage'];
   }
}

/**
* close an object
*/
function closeObject(){
  // close the object, as long as there was one open in the first place, which will be indicated by
  // an objectId on the stack.
  if ($this->nStack>0){
    $this->currentContents=$this->stack[$this->nStack]['c'];
    $this->currentPage=$this->stack[$this->nStack]['p'];
    $this->nStack--;
    // easier to probably not worry about removing the old entries, they will be overwritten
    // if there are new ones.
  }
}

/**
* stop an object from appearing on pages from this point on
*/
function stopObject($id){
  // if an object has been appearing on pages up to now, then stop it, this page will
  // be the last one that could contian it.
  if (isset($this->addLooseObjects[$id])){
    $this->addLooseObjects[$id]='';
  }
}

/**
* after an object has been created, it wil only show if it has been added, using this function.
*/
function addObject($id,$options='add'){
  // add the specified object to the page
  if (isset($this->looseObjects[$id]) && $this->currentContents!=$id){
    // then it is a valid object, and it is not being added to itself
    switch($options){
      case 'all':
        // then this object is to be added to this page (done in the next block) and 
        // all future new pages. 
        $this->addLooseObjects[$id]='all';
      case 'add':
        if (isset($this->objects[$this->currentContents]['onPage'])){
          // then the destination contents is the primary for the page
          // (though this object is actually added to that page)
          $this->o_page($this->objects[$this->currentContents]['onPage'],'content',$id);
        }
        break;
      case 'even':
        $this->addLooseObjects[$id]='even';
        $pageObjectId=$this->objects[$this->currentContents]['onPage'];
        if ($this->objects[$pageObjectId]['info']['pageNum']%2==0){
          $this->addObject($id); // hacky huh :)
        }
        break;
      case 'odd':
        $this->addLooseObjects[$id]='odd';
        $pageObjectId=$this->objects[$this->currentContents]['onPage'];
        if ($this->objects[$pageObjectId]['info']['pageNum']%2==1){
          $this->addObject($id); // hacky huh :)
        }
        break;
      case 'next':
        $this->addLooseObjects[$id]='all';
        break;
      case 'nexteven':
        $this->addLooseObjects[$id]='even';
        break;
      case 'nextodd':
        $this->addLooseObjects[$id]='odd';
        break;
    }
  }
}

/**
* add content to the documents info object
*/
function addInfo($label,$value=0){
  // this will only work if the label is one of the valid ones.
  // modify this so that arrays can be passed as well.
  // if $label is an array then assume that it is key=>value pairs
  // else assume that they are both scalar, anything else will probably error
  if (is_array($label)){
    foreach ($label as $l=>$v){
      $this->o_info($this->infoObject,$l,$v);
    }
  } else {
    $this->o_info($this->infoObject,$label,$value);
  }
}

/**
* set the viewer preferences of the document, it is up to the browser to obey these.
*/
function setPreferences($label,$value=0){
  // this will only work if the label is one of the valid ones.
  if (is_array($label)){
    foreach ($label as $l=>$v){
      $this->o_catalog($this->catalogId,'viewerPreferences',array($l=>$v));
    }
  } else {
    $this->o_catalog($this->catalogId,'viewerPreferences',array($label=>$value));
  }
}

/**
* extract an integer from a position in a byte stream
*
* @access private
*/
function PRVT_getBytes(&$data,$pos,$num){
  // return the integer represented by $num bytes from $pos within $data
  $ret=0;
  for ($i=0;$i<$num;$i++){
    $ret=$ret*256;
    $ret+=ord($data[$pos+$i]);
  }
  return $ret;
}

/**
* add a PNG image into the document, from a file
* this should work with remote files
*/
function addPngFromFile($file,$x,$y,$w=0,$h=0){
  // read in a png file, interpret it, then add to the system
  $error=0;
  $tmp = get_magic_quotes_runtime();
  set_magic_quotes_runtime(0);
  $fp = @fopen($file,'rb');
  if ($fp){
    $data='';
    while(!feof($fp)){
      $data .= fread($fp,1024);
    }
    fclose($fp);
  } else {
    $error = 1;
    $errormsg = 'trouble opening file: '.$file;
  }
  set_magic_quotes_runtime($tmp);
  
  if (!$error){
    $header = chr(137).chr(80).chr(78).chr(71).chr(13).chr(10).chr(26).chr(10);
    if (substr($data,0,8)!=$header){
      $error=1;
      $errormsg = 'this file does not have a valid header';
    }
  }

  if (!$error){
    // set pointer
    $p = 8;
    $len = strlen($data);
    // cycle through the file, identifying chunks
    $haveHeader=0;
    $info=array();
    $idata='';
    $pdata='';
    while ($p<$len){
      $chunkLen = $this->PRVT_getBytes($data,$p,4);
      $chunkType = substr($data,$p+4,4);
//      echo $chunkType.' - '.$chunkLen.'<br>';
    
      switch($chunkType){
        case 'IHDR':
          // this is where all the file information comes from
          $info['width']=$this->PRVT_getBytes($data,$p+8,4);
          $info['height']=$this->PRVT_getBytes($data,$p+12,4);
          $info['bitDepth']=ord($data[$p+16]);
          $info['colorType']=ord($data[$p+17]);
          $info['compressionMethod']=ord($data[$p+18]);
          $info['filterMethod']=ord($data[$p+19]);
          $info['interlaceMethod']=ord($data[$p+20]);
//print_r($info);
          $haveHeader=1;
          if ($info['compressionMethod']!=0){
            $error=1;
            $errormsg = 'unsupported compression method';
          }
          if ($info['filterMethod']!=0){
            $error=1;
            $errormsg = 'unsupported filter method';
          }
          break;
        case 'PLTE':
          $pdata.=substr($data,$p+8,$chunkLen);
          break;
        case 'IDAT':
          $idata.=substr($data,$p+8,$chunkLen);
          break;
        case 'tRNS': 
          //this chunk can only occur once and it must occur after the PLTE chunk and before IDAT chunk 
          //print "tRNS found, color type = ".$info['colorType']."<BR>"; 
          $transparency = array();
          if ($info['colorType'] == 3) { // indexed color, rbg 
          /* corresponding to entries in the plte chunk 
          Alpha for palette index 0: 1 byte 
          Alpha for palette index 1: 1 byte 
          ...etc... 
          */ 
            // there will be one entry for each palette entry. up until the last non-opaque entry.
            // set up an array, stretching over all palette entries which will be o (opaque) or 1 (transparent)
            $transparency['type']='indexed';
            $numPalette = strlen($pdata)/3;
            $trans=0;
            for ($i=$chunkLen;$i>=0;$i--){
              if (ord($data[$p+8+$i])==0){
                $trans=$i;
              }
            }
            $transparency['data'] = $trans;
            
          } elseif($info['colorType'] == 0) { // grayscale 
          /* corresponding to entries in the plte chunk 
          Gray: 2 bytes, range 0 .. (2^bitdepth)-1 
          */ 
//            $transparency['grayscale']=$this->PRVT_getBytes($data,$p+8,2); // g = grayscale 
            $transparency['type']='indexed';
            $transparency['data'] = ord($data[$p+8+1]);
          
          } elseif($info['colorType'] == 2) { // truecolor 
          /* corresponding to entries in the plte chunk 
          Red: 2 bytes, range 0 .. (2^bitdepth)-1 
          Green: 2 bytes, range 0 .. (2^bitdepth)-1 
          Blue: 2 bytes, range 0 .. (2^bitdepth)-1 
          */ 
            $transparency['r']=$this->PRVT_getBytes($data,$p+8,2); // r from truecolor 
            $transparency['g']=$this->PRVT_getBytes($data,$p+10,2); // g from truecolor 
            $transparency['b']=$this->PRVT_getBytes($data,$p+12,2); // b from truecolor 
          
          } else { 
          //unsupported transparency type 
          } 
          // KS End new code 
          break; 
        default:
          break;
      }
    
      $p += $chunkLen+12;
    }
    
    if(!$haveHeader){
      $error = 1;
      $errormsg = 'information header is missing';
    }
    if (isset($info['interlaceMethod']) && $info['interlaceMethod']){
      $error = 1;
      $errormsg = 'There appears to be no support for interlaced images in pdf.';
    }
  }

  if (!$error && $info['bitDepth'] > 8){
    $error = 1;
    $errormsg = 'only bit depth of 8 or less is supported';
  }

  if (!$error){
    if ($info['colorType']!=2 && $info['colorType']!=0 && $info['colorType']!=3){
      $error = 1;
      $errormsg = 'transparancey alpha channel not supported, transparency only supported for palette images.';
    } else {
      switch ($info['colorType']){
        case 3:
          $color = 'DeviceRGB';
          $ncolor=1;
          break;
        case 2:
          $color = 'DeviceRGB';
          $ncolor=3;
          break;
        case 0:
          $color = 'DeviceGray';
          $ncolor=1;
          break;
      }
    }
  }
  if ($error){
    $this->addMessage('PNG error - ('.$file.') '.$errormsg);
    return;
  }
  if ($w==0){
    $w=$h/$info['height']*$info['width'];
  }
  if ($h==0){
    $h=$w*$info['height']/$info['width'];
  }
//print_r($info);
  // so this image is ok... add it in.
  $this->numImages++;
  $im=$this->numImages;
  $label='I'.$im;
  $this->numObj++;
//  $this->o_image($this->numObj,'new',array('label'=>$label,'data'=>$idata,'iw'=>$w,'ih'=>$h,'type'=>'png','ic'=>$info['width']));
  $options = array('label'=>$label,'data'=>$idata,'bitsPerComponent'=>$info['bitDepth'],'pdata'=>$pdata
                                      ,'iw'=>$info['width'],'ih'=>$info['height'],'type'=>'png','color'=>$color,'ncolor'=>$ncolor);
  if (isset($transparency)){
    $options['transparency']=$transparency;
  }
  $this->o_image($this->numObj,'new',$options);

  $this->objects[$this->currentContents]['c'].="\nq";
  $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$w)." 0 0 ".sprintf('%.3f',$h)." ".sprintf('%.3f',$x)." ".sprintf('%.3f',$y)." cm";
  $this->objects[$this->currentContents]['c'].="\n/".$label.' Do';
  $this->objects[$this->currentContents]['c'].="\nQ";
}

/**
* add a JPEG image into the document, from a file
*/
function addJpegFromFile($img,$x,$y,$w=0,$h=0){
  // attempt to add a jpeg image straight from a file, using no GD commands
  // note that this function is unable to operate on a remote file.

  if (!file_exists($img)){
    return;
  }

  $tmp=getimagesize($img);
  $imageWidth=$tmp[0];
  $imageHeight=$tmp[1];

  if (isset($tmp['channels'])){
    $channels = $tmp['channels'];
  } else {
    $channels = 3;
  }

  if ($w<=0 && $h<=0){
    $w=$imageWidth;
  }
  if ($w==0){
    $w=$h/$imageHeight*$imageWidth;
  }
  if ($h==0){
    $h=$w*$imageHeight/$imageWidth;
  }

  $fp=fopen($img,'rb');

  $tmp = get_magic_quotes_runtime();
  set_magic_quotes_runtime(0);
  $data = fread($fp,filesize($img));
  set_magic_quotes_runtime($tmp);
  
  fclose($fp);

  $this->addJpegImage_common($data,$x,$y,$w,$h,$imageWidth,$imageHeight,$channels);
}

/**
* add an image into the document, from a GD object
* this function is not all that reliable, and I would probably encourage people to use 
* the file based functions
*/
function addImage(&$img,$x,$y,$w=0,$h=0,$quality=75){
  // add a new image into the current location, as an external object
  // add the image at $x,$y, and with width and height as defined by $w & $h
  
  // note that this will only work with full colour images and makes them jpg images for display
  // later versions could present lossless image formats if there is interest.
  
  // there seems to be some problem here in that images that have quality set above 75 do not appear
  // not too sure why this is, but in the meantime I have restricted this to 75.  
  if ($quality>75){
    $quality=75;
  }

  // if the width or height are set to zero, then set the other one based on keeping the image
  // height/width ratio the same, if they are both zero, then give up :)
  $imageWidth=imagesx($img);
  $imageHeight=imagesy($img);
  
  if ($w<=0 && $h<=0){
    return;
  }
  if ($w==0){
    $w=$h/$imageHeight*$imageWidth;
  }
  if ($h==0){
    $h=$w*$imageHeight/$imageWidth;
  }
  
  // gotta get the data out of the img..

  // so I write to a temp file, and then read it back.. soo ugly, my apologies.
  $tmpDir='/tmp';
  $tmpName=tempnam($tmpDir,'img');
  imagejpeg($img,$tmpName,$quality);
  $fp=fopen($tmpName,'rb');

  $tmp = get_magic_quotes_runtime();
  set_magic_quotes_runtime(0);
  $fp = @fopen($tmpName,'rb');
  if ($fp){
    $data='';
    while(!feof($fp)){
      $data .= fread($fp,1024);
    }
    fclose($fp);
  } else {
    $error = 1;
    $errormsg = 'trouble opening file';
  }
//  $data = fread($fp,filesize($tmpName));
  set_magic_quotes_runtime($tmp);
//  fclose($fp);
  unlink($tmpName);
  $this->addJpegImage_common($data,$x,$y,$w,$h,$imageWidth,$imageHeight);
}

/**
* common code used by the two JPEG adding functions
*
* @access private
*/
function addJpegImage_common(&$data,$x,$y,$w=0,$h=0,$imageWidth,$imageHeight,$channels=3){
  // note that this function is not to be called externally
  // it is just the common code between the GD and the file options
  $this->numImages++;
  $im=$this->numImages;
  $label='I'.$im;
  $this->numObj++;
  $this->o_image($this->numObj,'new',array('label'=>$label,'data'=>$data,'iw'=>$imageWidth,'ih'=>$imageHeight,'channels'=>$channels));

  $this->objects[$this->currentContents]['c'].="\nq";
  $this->objects[$this->currentContents]['c'].="\n".sprintf('%.3f',$w)." 0 0 ".sprintf('%.3f',$h)." ".sprintf('%.3f',$x)." ".sprintf('%.3f',$y)." cm";
  $this->objects[$this->currentContents]['c'].="\n/".$label.' Do';
  $this->objects[$this->currentContents]['c'].="\nQ";
}

/**
* specify where the document should open when it first starts
*/
function openHere($style,$a=0,$b=0,$c=0){
  // this function will open the document at a specified page, in a specified style
  // the values for style, and the required paramters are:
  // 'XYZ'  left, top, zoom
  // 'Fit'
  // 'FitH' top
  // 'FitV' left
  // 'FitR' left,bottom,right
  // 'FitB'
  // 'FitBH' top
  // 'FitBV' left
  $this->numObj++;
  $this->o_destination($this->numObj,'new',array('page'=>$this->currentPage,'type'=>$style,'p1'=>$a,'p2'=>$b,'p3'=>$c));
  $id = $this->catalogId;
  $this->o_catalog($id,'openHere',$this->numObj);
}

/**
* create a labelled destination within the document
*/
function addDestination($label,$style,$a=0,$b=0,$c=0){
  // associates the given label with the destination, it is done this way so that a destination can be specified after
  // it has been linked to
  // styles are the same as the 'openHere' function
  $this->numObj++;
  $this->o_destination($this->numObj,'new',array('page'=>$this->currentPage,'type'=>$style,'p1'=>$a,'p2'=>$b,'p3'=>$c));
  $id = $this->numObj;
  // store the label->idf relationship, note that this means that labels can be used only once
  $this->destinations["$label"]=$id;
}

/**
* define font families, this is used to initialize the font families for the default fonts
* and for the user to add new ones for their fonts. The default bahavious can be overridden should
* that be desired.
*/
function setFontFamily($family,$options=''){
  if (!is_array($options)){
    if ($family=='init'){
      // set the known family groups
      // these font families will be used to enable bold and italic markers to be included
      // within text streams. html forms will be used... <b></b> <i></i>
      $this->fontFamilies['Helvetica.afm']=array(
         'b'=>'Helvetica-Bold.afm'
        ,'i'=>'Helvetica-Oblique.afm'
        ,'bi'=>'Helvetica-BoldOblique.afm'
        ,'ib'=>'Helvetica-BoldOblique.afm'
      );
      $this->fontFamilies['Courier.afm']=array(
         'b'=>'Courier-Bold.afm'
        ,'i'=>'Courier-Oblique.afm'
        ,'bi'=>'Courier-BoldOblique.afm'
        ,'ib'=>'Courier-BoldOblique.afm'
      );
      $this->fontFamilies['Times-Roman.afm']=array(
         'b'=>'Times-Bold.afm'
        ,'i'=>'Times-Italic.afm'
        ,'bi'=>'Times-BoldItalic.afm'
        ,'ib'=>'Times-BoldItalic.afm'
      );
    }
  } else {
    // the user is trying to set a font family
    // note that this can also be used to set the base ones to something else
    if (strlen($family)){
      $this->fontFamilies[$family] = $options;
    }
  }
}

/**
* used to add messages for use in debugging
*/
function addMessage($message){
  $this->messages.=$message."\n";
}

/**
* a few functions which should allow the document to be treated transactionally.
*/
function transaction($action){
  switch ($action){
    case 'start':
      // store all the data away into the checkpoint variable
      $data = get_object_vars($this);
      $this->checkpoint = $data;
      unset($data);
      break;
    case 'commit':
      if (is_array($this->checkpoint) && isset($this->checkpoint['checkpoint'])){
        $tmp = $this->checkpoint['checkpoint'];
        $this->checkpoint = $tmp;
        unset($tmp);
      } else {
        $this->checkpoint='';
      }
      break;
    case 'rewind':
      // do not destroy the current checkpoint, but move us back to the state then, so that we can try again
      if (is_array($this->checkpoint)){
        // can only abort if were inside a checkpoint
        $tmp = $this->checkpoint;
        foreach ($tmp as $k=>$v){
          if ($k != 'checkpoint'){
            $this->$k=$v;
          }
        }
        unset($tmp);
      }
      break;
    case 'abort':
      if (is_array($this->checkpoint)){
        // can only abort if were inside a checkpoint
        $tmp = $this->checkpoint;
        foreach ($tmp as $k=>$v){
          $this->$k=$v;
        }
        unset($tmp);
      }
      break;
  }

}

} // end of class

?>