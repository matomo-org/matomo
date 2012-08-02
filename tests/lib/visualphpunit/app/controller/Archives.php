<?php

namespace app\controller;

class Archives extends \app\core\Controller {

    // GET
    public function index($request) {
        $snapshot_directory = \app\lib\Library::retrieve('snapshot_directory');
        if ( !$request->is('ajax') ) {
            $snapshots = array();
            $handler = @opendir($snapshot_directory);
            if ( !$handler ) {
                return compact('snapshots');
            }
            while ( $file = readdir($handler) ) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ( strpos($file, '.') === 0 || $ext != 'html' ) {
                    continue;
                }
                $snapshots[] = $file;
            }
            closedir($handler);
            rsort($snapshots);

            return compact('snapshots');
        }

        if ( !isset($request->query['snapshot']) ) {
            return '';
        }

        $file = realpath($snapshot_directory)
            . "/{$request->query['snapshot']}";
        return ( file_exists($file) ) ? file_get_contents($file) : '';
    }

}

?>
