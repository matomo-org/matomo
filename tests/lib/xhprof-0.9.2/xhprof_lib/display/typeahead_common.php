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
 * AJAX endpoint for XHProf function name typeahead is implemented
 * as a thin wrapper around this file. The wrapper must set up
 * the global $xhprof_runs_impl to correspond to an object that
 * implements the iXHProfRuns interface.
 *
 * @author(s)  Kannan Muthukkaruppan
 *             Changhao Jiang
 */


require_once $GLOBALS['XHPROF_LIB_ROOT'].'/utils/xhprof_lib.php';

// param name, its type, and default value
$params = array('q'          => array(XHPROF_STRING_PARAM, ''),
                'run'        => array(XHPROF_STRING_PARAM, ''),
                'run1'       => array(XHPROF_STRING_PARAM, ''),
                'run2'       => array(XHPROF_STRING_PARAM, ''),
                'source'     => array(XHPROF_STRING_PARAM, 'xhprof'),
                );

// pull values of these params, and create named globals for each param
xhprof_param_init($params);

if (!empty($run)) {

  // single run mode
  $raw_data = $xhprof_runs_impl->get_run($run, $source, $desc_unused);
  $functions = xhprof_get_matching_functions($q, $raw_data);

} else if (!empty($run1) && !empty($run2)) {

  // diff mode
  $raw_data = $xhprof_runs_impl->get_run($run1, $source, $desc_unused);
  $functions1 = xhprof_get_matching_functions($q, $raw_data);

  $raw_data = $xhprof_runs_impl->get_run($run2, $source, $desc_unused);
  $functions2 = xhprof_get_matching_functions($q, $raw_data);


  $functions = array_unique(array_merge($functions1, $functions2));
  asort($functions);
} else {
  xhprof_error("no valid runs specified to typeahead endpoint");
  $functions = array();
}

// If exact match is present move it to the front
if (in_array($q, $functions)) {
  $old_functions = $functions;

  $functions = array($q);
  foreach ($old_functions as $f) {
    // exact match case has already been added to the front
    if ($f != $q) {
      $functions[] = $f;
    }
  }
}

foreach ($functions as $f) {
  echo $f."\n";
}

