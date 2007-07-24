<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {mailto} function plugin
 *
 * Type:     function<br>
 * Name:     mailto<br>
 * Date:     May 21, 2002
 * Purpose:  automate mailto address link creation, and optionally
 *           encode them.<br>
 * Input:<br>
 *         - address = e-mail address
 *         - text = (optional) text to display, default is address
 *         - encode = (optional) can be one of:
 *                * none : no encoding (default)
 *                * javascript : encode with javascript
 *                * hex : encode with hexidecimal (no javascript)
 *         - cc = (optional) address(es) to carbon copy
 *         - bcc = (optional) address(es) to blind carbon copy
 *         - subject = (optional) e-mail subject
 *         - newsgroups = (optional) newsgroup(s) to post to
 *         - followupto = (optional) address(es) to follow up to
 *         - extra = (optional) extra tags for the href link
 * 
 * Examples:
 * <pre>
 * {mailto address="me@domain.com"}
 * {mailto address="me@domain.com" encode="javascript"}
 * {mailto address="me@domain.com" encode="hex"}
 * {mailto address="me@domain.com" subject="Hello to you!"}
 * {mailto address="me@domain.com" cc="you@domain.com,they@domain.com"}
 * {mailto address="me@domain.com" extra='class="mailto"'}
 * </pre>
 * @link http://smarty.php.net/manual/en/language.function.mailto.php {mailto}
 *          (Smarty online manual)
 * @version  1.2
 * @author	 Monte Ohrt <monte@ispi.net>
 * @author credits to Jason Sweat (added cc, bcc and subject functionality)
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_mailto($params, &$smarty)
{
    $extra = '';
    extract($params);

    if (empty($address)) {
        $smarty->trigger_error("mailto: missing 'address' parameter");
        return;
    }
	
    if (empty($text)) {
		$text = $address;
    }
	
	// netscape and mozilla do not decode %40 (@) in BCC field (bug?)
	// so, don't encode it.
	
	$mail_parms = array();
	if (!empty($cc)) {
		$mail_parms[] = 'cc='.str_replace('%40','@',rawurlencode($cc));
	}
	
	if (!empty($bcc)) {
		$mail_parms[] = 'bcc='.str_replace('%40','@',rawurlencode($bcc));
	}
	
	if (!empty($subject)) {
		$mail_parms[] = 'subject='.rawurlencode($subject);
	}

	if (!empty($newsgroups)) {
		$mail_parms[] = 'newsgroups='.rawurlencode($newsgroups);
	}

	if (!empty($followupto)) {
		$mail_parms[] = 'followupto='.str_replace('%40','@',rawurlencode($followupto));
	}
	
    $mail_parm_vals = '';
	for ($i=0; $i<count($mail_parms); $i++) {
		$mail_parm_vals .= (0==$i) ? '?' : '&';
		$mail_parm_vals .= $mail_parms[$i];
	}
	$address .= $mail_parm_vals;
	
	if (empty($encode)) {
		$encode = 'none';
    } elseif (!in_array($encode,array('javascript','hex','none')) ) {
        $smarty->trigger_error("mailto: 'encode' parameter must be none, javascript or hex");
        return;		
	}
	
	if ($encode == 'javascript' ) {
		$string = 'document.write(\'<a href="mailto:'.$address.'" '.$extra.'>'.$text.'</a>\');';
		
		for ($x=0; $x < strlen($string); $x++) {
			$js_encode .= '%' . bin2hex($string[$x]);
		}
	
		return '<script type="text/javascript" language="javascript">eval(unescape(\''.$js_encode.'\'))</script>';
		
	} elseif ($encode == 'hex') {

		preg_match('!^(.*)(\?.*)$!',$address,$match);
		if(!empty($match[2])) {
        	$smarty->trigger_error("mailto: hex encoding does not work with extra attributes. Try javascript.");
        	return;						
		}  
		for ($x=0; $x < strlen($address); $x++) {
			if(preg_match('!\w!',$address[$x])) {
				$address_encode .= '%' . bin2hex($address[$x]);
			} else {
				$address_encode .= $address[$x];				
			}
		}
		for ($x=0; $x < strlen($text); $x++) {
			$text_encode .= '&#x' . bin2hex($text[$x]).';';
		}
		
		return '<a href="mailto:'.$address_encode.'" '.$extra.'>'.$text_encode.'</a>';
		
	} else {
		// no encoding		
		return '<a href="mailto:'.$address.'" '.$extra.'>'.$text.'</a>';

	}
	
}

/* vim: set expandtab: */

?>
