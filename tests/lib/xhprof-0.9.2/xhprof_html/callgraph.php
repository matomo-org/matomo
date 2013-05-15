<?php
//  Copyright (c) 2009 Facebook
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

/**
 *
 * A callgraph generator for XHProf.
 *
 * * This file is part of the UI/reporting component,
 *   used for viewing results of XHProf runs from a
 *   browser.
 *
 * Modification History:
 *  02/15/2008 - cjiang  - The first version of callgraph visualizer
 *                         based on Graphviz's DOT tool.
 *
 * @author Changhao Jiang (cjiang@facebook.com)
 */

// by default assume that xhprof_html & xhprof_lib directories
// are at the same level.
$GLOBALS['XHPROF_LIB_ROOT'] = dirname(__FILE__) . '/../xhprof_lib';

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/xhprof.php';

ini_set('max_execution_time', 100);

$params = array(// run id param
                'run' => array(XHPROF_STRING_PARAM, ''),

                // source/namespace/type of run
                'source' => array(XHPROF_STRING_PARAM, 'xhprof'),

                // the focus function, if it is set, only directly
                // parents/children functions of it will be shown.
                'func' => array(XHPROF_STRING_PARAM, ''),

                // image type, can be 'jpg', 'gif', 'ps', 'png'
                'type' => array(XHPROF_STRING_PARAM, 'png'),

                // only functions whose exclusive time over the total time
                // is larger than this threshold will be shown.
                // default is 0.01.
                'threshold' => array(XHPROF_FLOAT_PARAM, 0.01),

                // whether to show critical_path
                'critical' => array(XHPROF_BOOL_PARAM, true),

                // first run in diff mode.
                'run1' => array(XHPROF_STRING_PARAM, ''),

                // second run in diff mode.
                'run2' => array(XHPROF_STRING_PARAM, '')
                );

// pull values of these params, and create named globals for each param
xhprof_param_init($params);

// if invalid value specified for threshold, then use the default
if ($threshold < 0 || $threshold > 1) {
  $threshold = $params['threshold'][1];
}

// if invalid value specified for type, use the default
if (!array_key_exists($type, $xhprof_legal_image_types)) {
  $type = $params['type'][1]; // default image type.
}

$xhprof_runs_impl = new XHProfRuns_Default();

if (!empty($run)) {
  // single run call graph image generation
  xhprof_render_image($xhprof_runs_impl, $run, $type,
                      $threshold, $func, $source, $critical);
} else {
  // diff report call graph image generation
  xhprof_render_diff_image($xhprof_runs_impl, $run1, $run2,
                           $type, $threshold, $source);
}
