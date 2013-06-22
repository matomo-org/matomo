<?php

namespace app\controller;

class Home extends \app\core\Controller {

    protected function _create_snapshot($view_data) {
        $filename = realpath(
            \app\lib\Library::retrieve('snapshot_directory')
        ) . '/' . date('Y-m-d_G-i') . '.html';

        $contents = $this->render_html('partial/test_results', $view_data);

        $handle = @fopen($filename, 'a');
        if ( !$handle ) {
            return array(
                'type'    => 'failed',
                'title'   => 'Error Creating Snapshot',
                'message' => 'Please ensure that the '
                    . '<code>snapshot_directory</code> in '
                    . '<code>app/config/bootstrap.php</code> exists and '
                    . 'has the proper permissions.'
            );
        }

        fwrite($handle, $contents);
        fclose($handle);
        return array(
            'type'    => 'succeeded',
            'title'   => 'Snapshot Created',
            'message' => "Snapshot can be found at <code>{$filename}</code>."
        );
    }

    // GET
    public function help($request) {
        return array();
    }

    // GET/POST
    public function index($request) {
        if ( $request->is('get') ) {
            $test_directory = str_replace(
                '\\', '/', realpath(\app\lib\Library::retrieve('test_directory'))
            );
            $suites = array();
            $stats = array();
            $store_statistics = \app\lib\Library::retrieve('store_statistics');
            $create_snapshots = \app\lib\Library::retrieve('create_snapshots');
            $sandbox_errors = \app\lib\Library::retrieve('sandbox_errors');
            $use_xml = \app\lib\Library::retrieve('xml_configuration_file');
            $xhprof_installed = \app\lib\Library::isXHProfInstalled();
            
            $tests_dir = dirname(dirname(dirname(dirname(__DIR__))));
            $benchmark_fixtures_dir = $tests_dir.'/PHPUnit/Benchmarks/Fixtures';
            $benchmark_fixtures = array_map('basename', glob($benchmark_fixtures_dir.'/*.php'));
            
            return compact(
                'create_snapshots',
                'sandbox_errors',
                'stats',
                'store_statistics',
                'suites',
                'test_directory',
                'use_xml',
                'xhprof_installed',
                'benchmark_fixtures'
            );
        }

        $data = array();
        if (!empty($request->data['data_keys']) && !empty($request->data['data_values']))
        {
	          $data = array_combine($request->data['data_keys'], $request->data['data_values']);
        }
        
        $tests = explode('|', $request->data['test_files']);
        $vpu = new \app\lib\VPU();

        if ( $request->data['sandbox_errors'] ) {
            error_reporting(\app\lib\Library::retrieve('error_reporting'));
            set_error_handler(array($vpu, 'handle_errors'));
        }

        $xml_config = false;

        $notifications = array();
        if ( $request->data['use_xml'] ) {
            $xml_config = \app\lib\Library::retrieve('xml_configuration_file');
            if ( !$xml_config || !$xml_config = realpath($xml_config) ) {
                $notifications[] = array(
                    'type'    => 'failed',
                    'title'   => 'No Valid XML Configuration File Found',
                    'message' => 'Please ensure that the '
                    . '<code>xml_configuration_file</code> in '
                    . '<code>app/config/bootstrap.php</code> exists and '
                    . 'has the proper permissions.'
                );
            }
        }
        
        $use_xhprof = $request->data['use_xhprof'];
        
        // set benchmarking globals
        if (count($tests) == 1
            && basename(dirname(reset($tests))) == 'Benchmarks') {
            
            if (!empty($request->data['benchmark_fixture'])) {
                $parts = explode('.', $request->data['benchmark_fixture']);
                $data['PIWIK_BENCHMARK_FIXTURE'] = $parts[0];
            }
            
            if (!empty($request->data['fixture_db_name'])) {
                $data['PIWIK_BENCHMARK_DATABASE'] = $request->data['fixture_db_name'];
            }
        }

        list($results, $memory_stats, $xhprof_run_id) = ( $xml_config )
            ? $vpu->run_with_xml($xml_config, $use_xhprof)
            : $vpu->run_tests($tests, $data, $use_xhprof);
        $results = $vpu->compile_suites($results, 'web');
        
        $xhprof_url = false;
        if ($xhprof_run_id !== false) {
            $xhprof_url_root = \app\lib\Library::retrieve('xhprof_root').'/xhprof_html/';
            $xhprof_ns = \app\lib\Library::retrieve('xhprof_namespace');
            $xhprof_url = $xhprof_url_root.'?source='.urlencode($xhprof_ns).'&run='.urlencode($xhprof_run_id);
        }

        if ( $request->data['sandbox_errors'] ) {
            restore_error_handler();
        }
        
        $suites = $results['suites'];
        $stats = $results['stats'];
        $errors = $vpu->get_errors();
        $to_view = compact('suites', 'stats', 'errors', 'memory_stats', 'xhprof_url');

        if ( $request->data['create_snapshots'] ) {
            $notifications[] = $this->_create_snapshot($to_view);
        }
        if ( $request->data['store_statistics'] ) {
            $notifications[] = $this->_store_statistics($stats);
        }

        return $to_view + compact('notifications');
    }
    
    // POST
    public function copy_processed($request) {
    	// piwik-specific HACK!
    	$processed_file_location = $request->data['processed_file'];
    	$expected_file_location =
    		dirname(dirname($processed_file_location)).'/expected/'.basename($processed_file_location);
    	
    	if (!file_exists($processed_file_location))
    	{
    		return array('error' => "Cannot find processed file at '$processed_file_location'.");
    	}
    	
    	$processed = fopen($processed_file_location, 'r');
    	$expected = fopen($expected_file_location, 'w');
    	fwrite($expected, fread($processed, filesize($processed_file_location)));
    	fclose($processed);
    	fclose($expected);
    	
    	return array();
    }

    protected function _store_statistics($stats) {
        $db_options = \app\lib\Library::retrieve('db');
        $db = new $db_options['plugin']();
        if ( !$db->connect($db_options) ) {
            return array(
                'type'    => 'failed',
                'title'   => 'Error Connecting to Database',
                'message' => implode(' ', $db->get_errors())
            );
        }

        $now = date('Y-m-d H:i:s');
        foreach ( $stats as $key => $stat ) {
            $data = array(
                'run_date'   => $now,
                'failed'     => $stat['failed'],
                'incomplete' => $stat['incomplete'],
                'skipped'    => $stat['skipped'],
                'succeeded'  => $stat['succeeded']
            );
            $table = ucfirst(rtrim($key, 's')) . 'Result';
            if ( !$db->insert($table, $data) ) {
                return array(
                    'type'    => 'failed',
                    'title'   => 'Error Inserting Record',
                    'message' => implode(' ', $db->get_errors())
                );
            }
        }

        return array(
            'type'    => 'succeeded',
            'title'   => 'Statistics Stored',
            'message' => 'The statistics generated during this test run were '
                . 'successfully stored.'
        );

    }

}

?>
