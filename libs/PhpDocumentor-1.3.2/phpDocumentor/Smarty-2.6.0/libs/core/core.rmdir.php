<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * delete a dir recursively (level=0 -> keep root)
 * WARNING: no tests, it will try to remove what you tell it!
 *
 * @param string $dirname
 * @param integer $level
 * @param integer $exp_time
 * @return boolean
 */

//  $dirname, $level = 1, $exp_time = null

function smarty_core_rmdir($params, &$smarty)
{
   if(!isset($params['level'])) { $params['level'] = 1; }
   if(!isset($params['exp_time'])) { $params['exp_time'] = null; }

   if($_handle = @opendir($params['dirname'])) {

        while (false !== ($_entry = readdir($_handle))) {
            if ($_entry != '.' && $_entry != '..') {
                if (@is_dir($params['dirname'] . DIRECTORY_SEPARATOR . $_entry)) {
                    $_params = array(
                        'dirname' => $params['dirname'] . DIRECTORY_SEPARATOR . $_entry,
                        'level' => $params['level'] + 1,
                        'exp_time' => $params['exp_time']
                    );
                    require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.rmdir.php');
                    smarty_core_rmdir($_params, $smarty);
                }
                else {
                    $smarty->_unlink($params['dirname'] . DIRECTORY_SEPARATOR . $_entry, $params['exp_time']);
                }
            }
        }
        closedir($_handle);
   }

   if ($params['level']) {
       return @rmdir($params['dirname']);
   }
   return (bool)$_handle;

}

/* vim: set expandtab: */

?>
