<?php
/**
 *	base include file for eclipse plugin  
 *	@package	SimpleTest
 *	@subpackage	Eclipse
 *	@version	$Id: eclipse.php,v 1.11 2006/12/10 00:38:30 stevenbalthazor Exp $
 */
/**#@+
 * simpletest include files
 */
include_once 'unit_tester.php';
include_once 'test_case.php';
include_once 'invoker.php';
include_once 'socket.php';
include_once 'mock_objects.php';
/**#@-*/

/**
 *	base reported class for eclipse plugin  
 *	@package	SimpleTest
 *	@subpackage	Eclipse
 */
class EclipseReporter extends SimpleScorer {
	function EclipseReporter(&$listener,$cc=false){
		$this->_listener = &$listener;
		$this->SimpleScorer();
		$this->_case = "";
		$this->_group = "";
		$this->_method = "";
		$this->_cc = $cc;
		$this->_error = false;
		$this->_fail = false;
	}
	
	function getDumper() {
		return new SimpleDumper();
	}
	
	function &createListener($port,$host="127.0.0.1"){
		$tmplistener = & new SimpleSocket($host,$port,5);
		return $tmplistener;
	}
	
	function &createInvoker(&$invoker){
		$eclinvoker = & new EclipseInvoker( $invoker, $this->_listener);
		return $eclinvoker;
	}
	
	function escapeVal($val){
		$needle = array("\\","\"","/","\b","\f","\n","\r","\t");
		$replace = array('\\\\','\"','\/','\b','\f','\n','\r','\t');
		return str_replace($needle,$replace,$val);
	}
	
	function paintPass($message){
        //get the first passing item -- so that clicking the test item goes to first pass
		if (!$this->_pass){
			$this->_message = $this->escapeVal($message);
		}
		$this->_pass = true;
	}
	
	function paintFail($message){
        //only get the first failure or error
        if (!$this->_fail && !$this->_error){
    		$this->_fail = true;
    		$this->_message = $this->escapeVal($message);
    		$this->_listener->write('{status:"fail",message:"'.$this->_message.'",group:"'.$this->_group.'",case:"'.$this->_case.'",method:"'.$this->_method.'"}');
        }
    }
	
	function paintError($message){
        //only get the first failure or error
        if (!$this->_fail && !$this->_error){
    		$this->_error = true;
    		$this->_message = $this->escapeVal($message);
    		$this->_listener->write('{status:"error",message:"'.$this->_message.'",group:"'.$this->_group.'",case:"'.$this->_case.'",method:"'.$this->_method.'"}');
        }
	}
	
	function paintHeader($method){
	}
	
	function paintFooter($method){
	}
	
	function paintMethodStart($method) {
		$this->_pass = false;
		$this->_fail = false;
		$this->_error = false;
		$this->_method = $this->escapeVal($method);
	}
		
	function paintMethodEnd($method){	
		if ($this->_fail || $this->_error || !$this->_pass){
			//do nothing
		}else{
			//this ensures we only get one message per method that passes
			$this->_listener->write('{status:"pass",message:"'.$this->_message.'",group:"'.$this->_group.'",case:"'.$this->_case.'",method:"'.$this->_method.'"}');
		}
	}
	
	function paintCaseStart($case){
		$this->_case = $this->escapeVal($case);
	}
	
	function paintCaseEnd($case){
		$this->_case = "";
	}
	function paintGroupStart($group,$size){
		$this->_group = $this->escapeVal($group);
		if ($this->_cc){
			if (extension_loaded('xdebug')){
				xdebug_start_code_coverage(XDEBUG_CC_UNUSED| XDEBUG_CC_DEAD_CODE); 
			}
		}
	}
	function paintGroupEnd($group){
		$this->_group = "";
		$cc = "";
		if ($this->_cc){
			if (extension_loaded('xdebug')){
				$arrfiles = xdebug_get_code_coverage();
				xdebug_stop_code_coverage();
				$thisdir = dirname(__FILE__);
				$thisdirlen = strlen($thisdir);
				foreach ($arrfiles as $index=>$file){
					if (substr($index,0,$thisdirlen)===$thisdir){
						continue;
					}
					$lcnt = 0;
					$ccnt = 0;
					foreach ($file as $line){
						if ($line == -2){
							continue;
						}
						$lcnt++;
						if ($line==1){
							$ccnt++;
						}
					}
					if ($lcnt > 0){
						$cc.=round(($ccnt/$lcnt)*100,2).'%';
					}else{
						$cc.="0.00%";
					}
					$cc.= "\t".$index."\n";
				}
			}
		}
		$this->_listener->write('{status:"coverage",message:"'.EclipseReporter::escapeVal($cc).'"}');
	}
}

/**
 *	base invoker class for eclipse plugin  
 *	@package	SimpleTest
 *	@subpackage	Eclipse
 */
class EclipseInvoker extends SimpleInvokerDecorator{
	function EclipseInvoker(&$invoker,&$listener) {
		$this->_listener = &$listener;
		$this->SimpleInvokerDecorator($invoker);
	}
	
	function before($method){
		ob_start();
		$this->_invoker->before($method);
	}

	function after($method) {
		$this->_invoker->after($method);
		$output = ob_get_contents();
		ob_end_clean();
		if ($output!==""){
			$result = $this->_listener->write('{status:"info",message:"'.EclipseReporter::escapeVal($output).'"}');
		}
	}
	
	
}

?>