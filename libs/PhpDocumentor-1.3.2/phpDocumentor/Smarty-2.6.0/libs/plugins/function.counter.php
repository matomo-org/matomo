<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {counter} function plugin
 *
 * Type:     function<br>
 * Name:     counter<br>
 * Purpose:  print out a counter value
 * @link http://smarty.php.net/manual/en/language.function.counter.php {counter}
 *       (Smarty online manual)
 * @param array parameters
 * @param Smarty
 * @return string|null
 */
function smarty_function_counter($params, &$smarty)
{
    static $counters = array();

    extract($params);

    if (!isset($name)) {
		if(isset($id)) {
			$name = $id;
		} else {		
        	$name = "default";
		}
	}

    if (!isset($counters[$name])) {
        $counters[$name] = array(
            'start'=>1,
            'skip'=>1,
            'direction'=>'up',
            'count'=>1
            );
    }
    $counter =& $counters[$name];

    if (isset($start)) {
        $counter['start'] = $counter['count'] = $start;
    }

    if (!empty($assign)) {
        $counter['assign'] = $assign;
    }

    if (isset($counter['assign'])) {
        $smarty->assign($counter['assign'], $counter['count']);
    }
    
    if (isset($print)) {
        $print = (bool)$print;
    } else {
        $print = empty($counter['assign']);
    }

    if ($print) {
        $retval = $counter['count'];
	} else {
		$retval = null;
	}

    if (isset($skip)) {
        $counter['skip'] = $skip;
    }
    
    if (isset($direction)) {
        $counter['direction'] = $direction;
    }

    if ($counter['direction'] == "down")
        $counter['count'] -= $counter['skip'];
    else
        $counter['count'] += $counter['skip'];
	
	return $retval;
	
}

/* vim: set expandtab: */

?>
