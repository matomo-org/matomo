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

//
// XHProf: A Hierarchical Profiler for PHP
//
// XHProf has two components:
//
//  * This module is the UI/reporting component, used
//    for viewing results of XHProf runs from a browser.
//
//  * Data collection component: This is implemented
//    as a PHP extension (XHProf).
//
// @author Kannan Muthukkaruppan
//

if (!isset($GLOBALS['XHPROF_LIB_ROOT'])) {
  // by default, the parent directory is XHPROF lib root
  $GLOBALS['XHPROF_LIB_ROOT'] = realpath(dirname(__FILE__) . '/..');
}

include_once $GLOBALS['XHPROF_LIB_ROOT'].'/utils/xhprof_lib.php';
include_once $GLOBALS['XHPROF_LIB_ROOT'].'/utils/callgraph_utils.php';
include_once $GLOBALS['XHPROF_LIB_ROOT'].'/utils/xhprof_runs.php';


/**
 * Our coding convention disallows relative paths in hrefs.
 * Get the base URL path from the SCRIPT_NAME.
 */
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/");


/**
 * Generate references to required stylesheets & javascript.
 *
 * If the calling script (such as index.php) resides in
 * a different location that than 'xhprof_html' directory the
 * caller must provide the URL path to 'xhprof_html' directory
 * so that the correct location of the style sheets/javascript
 * can be specified in the generated HTML.
 *
 */
function xhprof_include_js_css($ui_dir_url_path = null) {

  if (empty($ui_dir_url_path)) {
    $ui_dir_url_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/");
  }

  // style sheets
  echo "<link href='$ui_dir_url_path/css/xhprof.css' rel='stylesheet' ".
    " type='text/css'></link>";
  echo "<link href='$ui_dir_url_path/jquery/jquery.tooltip.css' ".
    " rel='stylesheet' type='text/css'></link>";
  echo "<link href='$ui_dir_url_path/jquery/jquery.autocomplete.css' ".
    " rel='stylesheet' type='text/css'></link>";

  // javascript
  echo "<script src='$ui_dir_url_path/jquery/jquery-1.2.6.js'>".
       "</script>";
  echo "<script src='$ui_dir_url_path/jquery/jquery.tooltip.js'>".
       "</script>";
  echo "<script src='$ui_dir_url_path/jquery/jquery.autocomplete.js'>"
       ."</script>";
  echo "<script src='$ui_dir_url_path/js/xhprof_report.js'></script>";
}


/*
 * Formats call counts for XHProf reports.
 *
 * Description:
 * Call counts in single-run reports are integer values.
 * However, call counts for aggregated reports can be
 * fractional. This function will print integer values
 * without decimal point, but with commas etc.
 *
 *   4000 ==> 4,000
 *
 * It'll round fractional values to decimal precision of 3
 *   4000.1212 ==> 4,000.121
 *   4000.0001 ==> 4,000
 *
 */
function xhprof_count_format($num) {
  $num = round($num, 3);
  if (round($num) == $num) {
    return number_format($num);
  } else {
    return number_format($num, 3);
  }
}

function xhprof_percent_format($s, $precision = 1) {
  return sprintf('%.'.$precision.'f%%', 100*$s);
}

/**
 * Implodes the text for a bunch of actions (such as links, forms,
 * into a HTML list and returns the text.
 */
function xhprof_render_actions($actions) {
  $out = array( );

  if (count($actions)) {
    $out[] = '<ul class="xhprof_actions">';
    foreach ($actions as $action) {
      $out[] = '<li>'.$action.'</li>';
    }
    $out[] = '</ul>';
  }

  return implode('', $out);
}


/**
 * @param html-str $content  the text/image/innerhtml/whatever for the link
 * @param raw-str  $href
 * @param raw-str  $class
 * @param raw-str  $id
 * @param raw-str  $title
 * @param raw-str  $target
 * @param raw-str  $onclick
 * @param raw-str  $style
 * @param raw-str  $access
 * @param raw-str  $onmouseover
 * @param raw-str  $onmouseout
 * @param raw-str  $onmousedown
 * @param raw-str  $dir
 * @param raw-str  $rel
 */
function xhprof_render_link($content, $href, $class='', $id='', $title='',
                            $target='',
                            $onclick='', $style='', $access='', $onmouseover='',
                            $onmouseout='', $onmousedown='') {

  if (!$content) {
    return '';
  }

  if ($href) {
    $link = '<a href="' . ($href) . '"';
  } else {
    $link = '<span';
  }

  if ($class) {
    $link .= ' class="' . ($class) . '"';
  }
  if ($id) {
    $link .= ' id="' . ($id) . '"';
  }
  if ($title) {
    $link .= ' title="' . ($title) . '"';
  }
  if ($target) {
    $link .= ' target="' . ($target) . '"';
  }
  if ($onclick && $href) {
    $link .= ' onclick="' . ($onclick) . '"';
  }
  if ($style && $href) {
    $link .= ' style="' . ($style) . '"';
  }
  if ($access && $href) {
    $link .= ' accesskey="' . ($access) . '"';
  }
  if ($onmouseover) {
    $link .= ' onmouseover="' . ($onmouseover) . '"';
  }
  if ($onmouseout) {
    $link .= ' onmouseout="' . ($onmouseout) . '"';
  }
  if ($onmousedown) {
    $link .= ' onmousedown="' . ($onmousedown) . '"';
  }

  $link .= '>';
  $link .= $content;
  if ($href) {
    $link .= '</a>';
  } else {
    $link .= '</span>';
  }

  return $link;
}


// default column to sort on -- wall time
$sort_col = "wt";

// default is "single run" report
$diff_mode = false;

// call count data present?
$display_calls = true;

// The following column headers are sortable
$sortable_columns = array("fn" => 1,
                          "ct" => 1,
                          "wt" => 1,
                          "excl_wt" => 1,
                          "ut" => 1,
                          "excl_ut" => 1,
                          "st" => 1,
                          "excl_st" => 1,
                          "mu" => 1,
                          "excl_mu" => 1,
                          "pmu" => 1,
                          "excl_pmu" => 1,
                          "cpu" => 1,
                          "excl_cpu" => 1,
                          "samples" => 1,
                          "excl_samples" => 1
                          );

// Textual descriptions for column headers in "single run" mode
$descriptions = array(
                      "fn" => "Function Name",
                      "ct" =>  "Calls",
                      "Calls%" => "Calls%",

                      "wt" => "Incl. Wall Time<br>(microsec)",
                      "IWall%" => "IWall%",
                      "excl_wt" => "Excl. Wall Time<br>(microsec)",
                      "EWall%" => "EWall%",

                      "ut" => "Incl. User<br>(microsecs)",
                      "IUser%" => "IUser%",
                      "excl_ut" => "Excl. User<br>(microsec)",
                      "EUser%" => "EUser%",

                      "st" => "Incl. Sys <br>(microsec)",
                      "ISys%" => "ISys%",
                      "excl_st" => "Excl. Sys <br>(microsec)",
                      "ESys%" => "ESys%",

                      "cpu" => "Incl. CPU<br>(microsecs)",
                      "ICpu%" => "ICpu%",
                      "excl_cpu" => "Excl. CPU<br>(microsec)",
                      "ECpu%" => "ECPU%",

                      "mu" => "Incl.<br>MemUse<br>(bytes)",
                      "IMUse%" => "IMemUse%",
                      "excl_mu" => "Excl.<br>MemUse<br>(bytes)",
                      "EMUse%" => "EMemUse%",

                      "pmu" => "Incl.<br> PeakMemUse<br>(bytes)",
                      "IPMUse%" => "IPeakMemUse%",
                      "excl_pmu" => "Excl.<br>PeakMemUse<br>(bytes)",
                      "EPMUse%" => "EPeakMemUse%",

                      "samples" => "Incl. Samples",
                      "ISamples%" => "ISamples%",
                      "excl_samples" => "Excl. Samples",
                      "ESamples%" => "ESamples%",
                      );

// Formatting Callback Functions...
$format_cbk = array(
                      "fn" => "",
                      "ct" => "xhprof_count_format",
                      "Calls%" => "xhprof_percent_format",

                      "wt" => "number_format",
                      "IWall%" => "xhprof_percent_format",
                      "excl_wt" => "number_format",
                      "EWall%" => "xhprof_percent_format",

                      "ut" => "number_format",
                      "IUser%" => "xhprof_percent_format",
                      "excl_ut" => "number_format",
                      "EUser%" => "xhprof_percent_format",

                      "st" => "number_format",
                      "ISys%" => "xhprof_percent_format",
                      "excl_st" => "number_format",
                      "ESys%" => "xhprof_percent_format",

                      "cpu" => "number_format",
                      "ICpu%" => "xhprof_percent_format",
                      "excl_cpu" => "number_format",
                      "ECpu%" => "xhprof_percent_format",

                      "mu" => "number_format",
                      "IMUse%" => "xhprof_percent_format",
                      "excl_mu" => "number_format",
                      "EMUse%" => "xhprof_percent_format",

                      "pmu" => "number_format",
                      "IPMUse%" => "xhprof_percent_format",
                      "excl_pmu" => "number_format",
                      "EPMUse%" => "xhprof_percent_format",

                      "samples" => "number_format",
                      "ISamples%" => "xhprof_percent_format",
                      "excl_samples" => "number_format",
                      "ESamples%" => "xhprof_percent_format",
                      );


// Textual descriptions for column headers in "diff" mode
$diff_descriptions = array(
                      "fn" => "Function Name",
                      "ct" =>  "Calls Diff",
                      "Calls%" => "Calls<br>Diff%",

                      "wt" => "Incl. Wall<br>Diff<br>(microsec)",
                      "IWall%" => "IWall<br> Diff%",
                      "excl_wt" => "Excl. Wall<br>Diff<br>(microsec)",
                      "EWall%" => "EWall<br>Diff%",

                      "ut" => "Incl. User Diff<br>(microsec)",
                      "IUser%" => "IUser<br>Diff%",
                      "excl_ut" => "Excl. User<br>Diff<br>(microsec)",
                      "EUser%" => "EUser<br>Diff%",

                      "cpu" => "Incl. CPU Diff<br>(microsec)",
                      "ICpu%" => "ICpu<br>Diff%",
                      "excl_cpu" => "Excl. CPU<br>Diff<br>(microsec)",
                      "ECpu%" => "ECpu<br>Diff%",

                      "st" => "Incl. Sys Diff<br>(microsec)",
                      "ISys%" => "ISys<br>Diff%",
                      "excl_st" => "Excl. Sys Diff<br>(microsec)",
                      "ESys%" => "ESys<br>Diff%",

                      "mu" => "Incl.<br>MemUse<br>Diff<br>(bytes)",
                      "IMUse%" => "IMemUse<br>Diff%",
                      "excl_mu" => "Excl.<br>MemUse<br>Diff<br>(bytes)",
                      "EMUse%" => "EMemUse<br>Diff%",

                      "pmu" => "Incl.<br> PeakMemUse<br>Diff<br>(bytes)",
                      "IPMUse%" => "IPeakMemUse<br>Diff%",
                      "excl_pmu" => "Excl.<br>PeakMemUse<br>Diff<br>(bytes)",
                      "EPMUse%" => "EPeakMemUse<br>Diff%",

                      "samples" => "Incl. Samples Diff",
                      "ISamples%" => "ISamples Diff%",
                      "excl_samples" => "Excl. Samples Diff",
                      "ESamples%" => "ESamples Diff%",
                      );

// columns that'll be displayed in a top-level report
$stats = array();

// columns that'll be displayed in a function's parent/child report
$pc_stats = array();

// Various total counts
$totals = 0;
$totals_1 = 0;
$totals_2 = 0;

/*
 * The subset of $possible_metrics that is present in the raw profile data.
 */
$metrics = null;

/**
 * Callback comparison operator (passed to usort() for sorting array of
 * tuples) that compares array elements based on the sort column
 * specified in $sort_col (global parameter).
 *
 * @author Kannan
 */
function sort_cbk($a, $b)
{
  global $sort_col;
  global $diff_mode;

  if ($sort_col == "fn") {

    // case insensitive ascending sort for function names
    $left = strtoupper($a["fn"]);
    $right = strtoupper($b["fn"]);

    if ($left == $right)
      return 0;
    return ($left < $right) ? -1 : 1;

  } else {

    // descending sort for all others
    $left = $a[$sort_col];
    $right = $b[$sort_col];

    // if diff mode, sort by absolute value of regression/improvement
    if ($diff_mode) {
      $left = abs($left);
      $right = abs($right);
    }

    if ($left == $right)
      return 0;
    return ($left > $right) ? -1 : 1;
  }
}

/**
 * Initialize the metrics we'll display based on the information
 * in the raw data.
 *
 * @author Kannan
 */
function init_metrics($xhprof_data, $rep_symbol, $sort, $diff_report = false) {
  global $stats;
  global $pc_stats;
  global $metrics;
  global $diff_mode;
  global $sortable_columns;
  global $sort_col;
  global $display_calls;

  $diff_mode = $diff_report;

  if (!empty($sort)) {
    if (array_key_exists($sort, $sortable_columns)) {
      $sort_col = $sort;
    } else {
      print("Invalid Sort Key $sort specified in URL");
    }
  }

  // For C++ profiler runs, walltime attribute isn't present.
  // In that case, use "samples" as the default sort column.
  if (!isset($xhprof_data["main()"]["wt"])) {

    if ($sort_col == "wt") {
      $sort_col = "samples";
    }

    // C++ profiler data doesn't have call counts.
    // ideally we should check to see if "ct" metric
    // is present for "main()". But currently "ct"
    // metric is artificially set to 1. So, relying
    // on absence of "wt" metric instead.
    $display_calls = false;
  } else {
    $display_calls = true;
  }

  // parent/child report doesn't support exclusive times yet.
  // So, change sort hyperlinks to closest fit.
  if (!empty($rep_symbol)) {
    $sort_col = str_replace("excl_", "", $sort_col);
  }

  if ($display_calls) {
    $stats = array("fn", "ct", "Calls%");
  } else {
    $stats = array("fn");
  }

  $pc_stats = $stats;

  $possible_metrics = xhprof_get_possible_metrics($xhprof_data);
  foreach ($possible_metrics as $metric => $desc) {
    if (isset($xhprof_data["main()"][$metric])) {
      $metrics[] = $metric;
      // flat (top-level reports): we can compute
      // exclusive metrics reports as well.
      $stats[] = $metric;
      $stats[] = "I" . $desc[0] . "%";
      $stats[] = "excl_" . $metric;
      $stats[] = "E" . $desc[0] . "%";

      // parent/child report for a function: we can
      // only breakdown inclusive times correctly.
      $pc_stats[] = $metric;
      $pc_stats[] = "I" . $desc[0] . "%";
    }
  }
}

/**
 * Get the appropriate description for a statistic
 * (depending upon whether we are in diff report mode
 * or single run report mode).
 *
 * @author Kannan
 */
function stat_description($stat) {
  global $descriptions;
  global $diff_descriptions;
  global $diff_mode;

  if ($diff_mode) {
    return $diff_descriptions[$stat];
  } else {
    return $descriptions[$stat];
  }
}


/**
 * Analyze raw data & generate the profiler report
 * (common for both single run mode and diff mode).
 *
 * @author: Kannan
 */
function profiler_report ($url_params,
                          $rep_symbol,
                          $sort,
                          $run1,
                          $run1_desc,
                          $run1_data,
                          $run2 = 0,
                          $run2_desc = "",
                          $run2_data = array()) {
  global $totals;
  global $totals_1;
  global $totals_2;
  global $stats;
  global $pc_stats;
  global $diff_mode;
  global $base_path;

  // if we are reporting on a specific function, we can trim down
  // the report(s) to just stuff that is relevant to this function.
  // That way compute_flat_info()/compute_diff() etc. do not have
  // to needlessly work hard on churning irrelevant data.
  if (!empty($rep_symbol)) {
    $run1_data = xhprof_trim_run($run1_data, array($rep_symbol));
    if ($diff_mode) {
      $run2_data = xhprof_trim_run($run2_data, array($rep_symbol));
    }
  }

  if ($diff_mode) {
    $run_delta = xhprof_compute_diff($run1_data, $run2_data);
    $symbol_tab  = xhprof_compute_flat_info($run_delta, $totals);
    $symbol_tab1 = xhprof_compute_flat_info($run1_data, $totals_1);
    $symbol_tab2 = xhprof_compute_flat_info($run2_data, $totals_2);
  } else {
    $symbol_tab = xhprof_compute_flat_info($run1_data, $totals);
  }

  $run1_txt = sprintf("<b>Run #%s:</b> %s",
                      $run1, $run1_desc);

  $base_url_params = xhprof_array_unset(xhprof_array_unset($url_params,
                                                           'symbol'),
                                        'all');

  $top_link_query_string = "$base_path/?" . http_build_query($base_url_params);

  if ($diff_mode) {
    $diff_text = "Diff";
    $base_url_params = xhprof_array_unset($base_url_params, 'run1');
    $base_url_params = xhprof_array_unset($base_url_params, 'run2');
    $run1_link = xhprof_render_link('View Run #' . $run1,
                           "$base_path/?" .
                           http_build_query(xhprof_array_set($base_url_params,
                                                      'run',
                                                      $run1)));
    $run2_txt = sprintf("<b>Run #%s:</b> %s",
                        $run2, $run2_desc);

    $run2_link = xhprof_render_link('View Run #' . $run2,
                                    "$base_path/?" .
                        http_build_query(xhprof_array_set($base_url_params,
                                                          'run',
                                                          $run2)));
  } else {
    $diff_text = "Run";
  }

  // set up the action links for operations that can be done on this report
  $links = array();
  $links []=  xhprof_render_link("View Top Level $diff_text Report",
                                 $top_link_query_string);

  if ($diff_mode) {
    $inverted_params = $url_params;
    $inverted_params['run1'] = $url_params['run2'];
    $inverted_params['run2'] = $url_params['run1'];

    // view the different runs or invert the current diff
    $links []= $run1_link;
    $links []= $run2_link;
    $links []= xhprof_render_link('Invert ' . $diff_text . ' Report',
                           "$base_path/?".
                           http_build_query($inverted_params));
  }

  // lookup function typeahead form
  $links [] = '<input class="function_typeahead" ' .
              ' type="input" size="40" maxlength="100" />';

  echo xhprof_render_actions($links);


  echo
    '<dl class=phprof_report_info>' .
    '  <dt>' . $diff_text . ' Report</dt>' .
    '  <dd>' . ($diff_mode ?
                $run1_txt . '<br><b>vs.</b><br>' . $run2_txt :
                $run1_txt) .
    '  </dd>' .
    '  <dt>Tip</dt>' .
    '  <dd>Click a function name below to drill down.</dd>' .
    '</dl>' .
    '<div style="clear: both; margin: 3em 0em;"></div>';

  // data tables
  if (!empty($rep_symbol)) {
    if (!isset($symbol_tab[$rep_symbol])) {
      echo "<hr>Symbol <b>$rep_symbol</b> not found in XHProf run</b><hr>";
      return;
    }

    /* single function report with parent/child information */
    if ($diff_mode) {
      $info1 = isset($symbol_tab1[$rep_symbol]) ?
                       $symbol_tab1[$rep_symbol] : null;
      $info2 = isset($symbol_tab2[$rep_symbol]) ?
                       $symbol_tab2[$rep_symbol] : null;
      symbol_report($url_params, $run_delta, $symbol_tab[$rep_symbol],
                    $sort, $rep_symbol,
                    $run1, $info1,
                    $run2, $info2);
    } else {
      symbol_report($url_params, $run1_data, $symbol_tab[$rep_symbol],
                    $sort, $rep_symbol, $run1);
    }
  } else {
    /* flat top-level report of all functions */
    full_report($url_params, $symbol_tab, $sort, $run1, $run2);
  }
}

/**
 * Computes percentage for a pair of values, and returns it
 * in string format.
 */
function pct($a, $b) {
  if ($b == 0) {
    return "N/A";
  } else {
    $res = (round(($a * 1000 / $b)) / 10);
    return $res;
  }
}

/**
 * Given a number, returns the td class to use for display.
 *
 * For instance, negative numbers in diff reports comparing two runs (run1 & run2)
 * represent improvement from run1 to run2. We use green to display those deltas,
 * and red for regression deltas.
 */
function get_print_class($num, $bold) {
  global $vbar;
  global $vbbar;
  global $vrbar;
  global $vgbar;
  global $diff_mode;

  if ($bold) {
    if ($diff_mode) {
      if ($num <= 0) {
        $class = $vgbar; // green (improvement)
      } else {
        $class = $vrbar; // red (regression)
      }
    } else {
      $class = $vbbar; // blue
    }
  }
  else {
    $class = $vbar;  // default (black)
  }

  return $class;
}

/**
 * Prints a <td> element with a numeric value.
 */
function print_td_num($num, $fmt_func, $bold=false, $attributes=null) {

  $class = get_print_class($num, $bold);

  if (!empty($fmt_func)) {
    $num = call_user_func($fmt_func, $num);
  }

  print("<td $attributes $class>$num</td>\n");
}

/**
 * Prints a <td> element with a pecentage.
 */
function print_td_pct($numer, $denom, $bold=false, $attributes=null) {
  global $vbar;
  global $vbbar;
  global $diff_mode;

  $class = get_print_class($numer, $bold);

  if ($denom == 0) {
    $pct = "N/A%";
  } else {
    $pct = xhprof_percent_format($numer / abs($denom));
  }

  print("<td $attributes $class>$pct</td>\n");
}

/**
 * Print "flat" data corresponding to one function.
 *
 * @author Kannan
 */
function print_function_info($url_params, $info, $sort, $run1, $run2) {
  static $odd_even = 0;

  global $totals;
  global $sort_col;
  global $metrics;
  global $format_cbk;
  global $display_calls;
  global $base_path;

  // Toggle $odd_or_even
  $odd_even = 1 - $odd_even;

  if ($odd_even) {
    print("<tr>");
  }
  else {
    print('<tr bgcolor="#e5e5e5">');
  }

  $href = "$base_path/?" .
           http_build_query(xhprof_array_set($url_params,
                                             'symbol', $info["fn"]));

  print('<td>');
  print(xhprof_render_link($info["fn"], $href));
  print("</td>\n");

  if ($display_calls) {
    // Call Count..
    print_td_num($info["ct"], $format_cbk["ct"], ($sort_col == "ct"));
    print_td_pct($info["ct"], $totals["ct"], ($sort_col == "ct"));
  }

  // Other metrics..
  foreach ($metrics as $metric) {
    // Inclusive metric
    print_td_num($info[$metric], $format_cbk[$metric],
                 ($sort_col == $metric));
    print_td_pct($info[$metric], $totals[$metric],
                 ($sort_col == $metric));

    // Exclusive Metric
    print_td_num($info["excl_" . $metric],
                 $format_cbk["excl_" . $metric],
                 ($sort_col == "excl_" . $metric));
    print_td_pct($info["excl_" . $metric],
                 $totals[$metric],
                 ($sort_col == "excl_" . $metric));
  }

  print("</tr>\n");
}

/**
 * Print non-hierarchical (flat-view) of profiler data.
 *
 * @author Kannan
 */
function print_flat_data($url_params, $title, $flat_data, $sort, $run1, $run2, $limit) {

  global $stats;
  global $sortable_columns;
  global $vwbar;
  global $base_path;

  $size  = count($flat_data);
  if (!$limit) {              // no limit
    $limit = $size;
    $display_link = "";
  } else {
    $display_link = xhprof_render_link(" [ <b class=bubble>display all </b>]",
                                       "$base_path/?" .
                                       http_build_query(xhprof_array_set($url_params,
                                                                         'all', 1)));
  }

  print("<h3 align=center>$title $display_link</h3><br>");

  print('<table border=1 cellpadding=2 cellspacing=1 width="90%" '
        .'rules=rows bordercolor="#bdc7d8" align=center>');
  print('<tr bgcolor="#bdc7d8" align=right>');

  foreach ($stats as $stat) {
    $desc = stat_description($stat);
    if (array_key_exists($stat, $sortable_columns)) {
      $href = "$base_path/?"
              . http_build_query(xhprof_array_set($url_params, 'sort', $stat));
      $header = xhprof_render_link($desc, $href);
    } else {
      $header = $desc;
    }

    if ($stat == "fn")
      print("<th align=left><nobr>$header</th>");
    else
      print("<th " . $vwbar . "><nobr>$header</th>");
  }
  print("</tr>\n");

  if ($limit >= 0) {
    $limit = min($size, $limit);
    for($i=0; $i < $limit; $i++) {
      print_function_info($url_params, $flat_data[$i], $sort, $run1, $run2);
    }
  } else {
    // if $limit is negative, print abs($limit) items starting from the end
    $limit = min($size, abs($limit));
    for($i=0; $i < $limit; $i++) {
      print_function_info($url_params, $flat_data[$size - $i - 1], $sort, $run1, $run2);
    }
  }
  print("</table>");

  // let's print the display all link at the bottom as well...
  if ($display_link) {
    echo '<div style="text-align: left; padding: 2em">' . $display_link . '</div>';
  }

}

/**
 * Generates a tabular report for all functions. This is the top-level report.
 *
 * @author Kannan
 */
function full_report($url_params, $symbol_tab, $sort, $run1, $run2) {
  global $vwbar;
  global $vbar;
  global $totals;
  global $totals_1;
  global $totals_2;
  global $metrics;
  global $diff_mode;
  global $descriptions;
  global $sort_col;
  global $format_cbk;
  global $display_calls;
  global $base_path;

  $possible_metrics = xhprof_get_possible_metrics();

  if ($diff_mode) {

    $base_url_params = xhprof_array_unset(xhprof_array_unset($url_params,
                                                             'run1'),
                                          'run2');
    $href1 = "$base_path/?" .
      http_build_query(xhprof_array_set($base_url_params,
                                        'run', $run1));
    $href2 = "$base_path/?" .
      http_build_query(xhprof_array_set($base_url_params,
                                        'run', $run2));

    print("<h3><center>Overall Diff Summary</center></h3>");
    print('<table border=1 cellpadding=2 cellspacing=1 width="30%" '
          .'rules=rows bordercolor="#bdc7d8" align=center>' . "\n");
    print('<tr bgcolor="#bdc7d8" align=right>');
    print("<th></th>");
    print("<th $vwbar>" . xhprof_render_link("Run #$run1", $href1) . "</th>");
    print("<th $vwbar>" . xhprof_render_link("Run #$run2", $href2) . "</th>");
    print("<th $vwbar>Diff</th>");
    print("<th $vwbar>Diff%</th>");
    print('</tr>');

    if ($display_calls) {
      print('<tr>');
      print("<td>Number of Function Calls</td>");
      print_td_num($totals_1["ct"], $format_cbk["ct"]);
      print_td_num($totals_2["ct"], $format_cbk["ct"]);
      print_td_num($totals_2["ct"] - $totals_1["ct"], $format_cbk["ct"], true);
      print_td_pct($totals_2["ct"] - $totals_1["ct"], $totals_1["ct"], true);
      print('</tr>');
    }

    foreach ($metrics as $metric) {
      $m = $metric;
      print('<tr>');
      print("<td>" . str_replace("<br>", " ", $descriptions[$m]) . "</td>");
      print_td_num($totals_1[$m], $format_cbk[$m]);
      print_td_num($totals_2[$m], $format_cbk[$m]);
      print_td_num($totals_2[$m] - $totals_1[$m], $format_cbk[$m], true);
      print_td_pct($totals_2[$m] - $totals_1[$m], $totals_1[$m], true);
      print('<tr>');
    }
    print('</table>');

    $callgraph_report_title = '[View Regressions/Improvements using Callgraph Diff]';

  } else {
    print("<p><center>\n");

    print('<table cellpadding=2 cellspacing=1 width="30%" '
          .'bgcolor="#bdc7d8" align=center>' . "\n");
    echo "<tr>";
    echo "<th style='text-align:right'>Overall Summary</th>";
    echo "<th'></th>";
    echo "</tr>";

    foreach ($metrics as $metric) {
      echo "<tr>";
      echo "<td style='text-align:right; font-weight:bold'>Total "
            . str_replace("<br>", " ", stat_description($metric)) . ":</td>";
      echo "<td>" . number_format($totals[$metric]) .  " "
           . $possible_metrics[$metric][1] . "</td>";
      echo "</tr>";
    }

    if ($display_calls) {
      echo "<tr>";
      echo "<td style='text-align:right; font-weight:bold'>Number of Function Calls:</td>";
      echo "<td>" . number_format($totals['ct']) . "</td>";
      echo "</tr>";
    }

    echo "</table>";
    print("</center></p>\n");

    $callgraph_report_title = '[View Full Callgraph]';
  }

  print("<center><br><h3>" .
        xhprof_render_link($callgraph_report_title,
                    "$base_path/callgraph.php" . "?" . http_build_query($url_params))
        . "</h3></center>");


  $flat_data = array();
  foreach ($symbol_tab as $symbol => $info) {
    $tmp = $info;
    $tmp["fn"] = $symbol;
    $flat_data[] = $tmp;
  }
  usort($flat_data, 'sort_cbk');

  print("<br>");

  if (!empty($url_params['all'])) {
    $all = true;
    $limit = 0;    // display all rows
  } else {
    $all = false;
    $limit = 100;  // display only limited number of rows
  }

  $desc = str_replace("<br>", " ", $descriptions[$sort_col]);

  if ($diff_mode) {
    if ($all) {
      $title = "Total Diff Report: '
               .'Sorted by absolute value of regression/improvement in $desc";
    } else {
      $title = "Top 100 <i style='color:red'>Regressions</i>/"
               . "<i style='color:green'>Improvements</i>: "
               . "Sorted by $desc Diff";
    }
  } else {
    if ($all) {
      $title = "Sorted by $desc";
    } else {
      $title = "Displaying top $limit functions: Sorted by $desc";
    }
  }
  print_flat_data($url_params, $title, $flat_data, $sort, $run1, $run2, $limit);
}


/**
 * Return attribute names and values to be used by javascript tooltip.
 */
function get_tooltip_attributes($type, $metric) {
  return "type='$type' metric='$metric'";
}

/**
 * Print info for a parent or child function in the
 * parent & children report.
 *
 * @author Kannan
 */
function pc_info($info, $base_ct, $base_info, $parent) {
  global $sort_col;
  global $metrics;
  global $format_cbk;
  global $display_calls;

  if ($parent)
    $type = "Parent";
  else
    $type = "Child";

  if ($display_calls) {
    $mouseoverct = get_tooltip_attributes($type, "ct");
    /* call count */
    print_td_num($info["ct"], $format_cbk["ct"], ($sort_col == "ct"), $mouseoverct);
    print_td_pct($info["ct"], $base_ct, ($sort_col == "ct"), $mouseoverct);
  }

  /* Inclusive metric values  */
  foreach ($metrics as $metric) {
    print_td_num($info[$metric], $format_cbk[$metric],
                 ($sort_col == $metric),
                 get_tooltip_attributes($type, $metric));
    print_td_pct($info[$metric], $base_info[$metric], ($sort_col == $metric),
                 get_tooltip_attributes($type, $metric));
  }
}

function print_pc_array($url_params, $results, $base_ct, $base_info, $parent,
                        $run1, $run2) {
  global $base_path;

  // Construct section title
  if ($parent) {
    $title = 'Parent function';
  }
  else {
    $title = 'Child function';
  }
  if (count($results) > 1) {
    $title .= 's';
  }

  print("<tr bgcolor='#e0e0ff'><td>");
  print("<b><i><center>" . $title . "</center></i></b>");
  print("</td></tr>");

  $odd_even = 0;
  foreach ($results as $info) {
    $href = "$base_path/?" .
      http_build_query(xhprof_array_set($url_params,
                                        'symbol', $info["fn"]));
    $odd_even = 1 - $odd_even;

    if ($odd_even) {
      print('<tr>');
    }
    else {
      print('<tr bgcolor="#e5e5e5">');
    }

    print("<td>" . xhprof_render_link($info["fn"], $href) . "</td>");
    pc_info($info, $base_ct, $base_info, $parent);
    print("</tr>");
  }
}


function print_symbol_summary($symbol_info, $stat, $base) {

  $val = $symbol_info[$stat];
  $desc = str_replace("<br>", " ", stat_description($stat));

  print("$desc: </td>");
  print(number_format($val));
  print(" (" . pct($val, $base) . "% of overall)");
  if (substr($stat, 0, 4) == "excl") {
    $func_base = $symbol_info[str_replace("excl_", "", $stat)];
    print(" (" . pct($val, $func_base) . "% of this function)");
  }
  print("<br>");
}

/**
 * Generates a report for a single function/symbol.
 *
 * @author Kannan
 */
function symbol_report($url_params,
                       $run_data, $symbol_info, $sort, $rep_symbol,
                       $run1,
                       $symbol_info1 = null,
                       $run2 = 0,
                       $symbol_info2 = null) {
  global $vwbar;
  global $vbar;
  global $totals;
  global $pc_stats;
  global $sortable_columns;
  global $metrics;
  global $diff_mode;
  global $descriptions;
  global $format_cbk;
  global $sort_col;
  global $display_calls;
  global $base_path;

  $possible_metrics = xhprof_get_possible_metrics();

  if ($diff_mode) {
    $diff_text = "<b>Diff</b>";
    $regr_impr = "<i style='color:red'>Regression</i>/<i style='color:green'>Improvement</i>";
  } else {
    $diff_text = "";
    $regr_impr = "";
  }

  if ($diff_mode) {

    $base_url_params = xhprof_array_unset(xhprof_array_unset($url_params,
                                                             'run1'),
                                          'run2');
    $href1 = "$base_path?"
      . http_build_query(xhprof_array_set($base_url_params, 'run', $run1));
    $href2 = "$base_path?"
      . http_build_query(xhprof_array_set($base_url_params, 'run', $run2));

    print("<h3 align=center>$regr_impr summary for $rep_symbol<br><br></h3>");
    print('<table border=1 cellpadding=2 cellspacing=1 width="30%" '
          .'rules=rows bordercolor="#bdc7d8" align=center>' . "\n");
    print('<tr bgcolor="#bdc7d8" align=right>');
    print("<th align=left>$rep_symbol</th>");
    print("<th $vwbar><a href=" . $href1 . ">Run #$run1</a></th>");
    print("<th $vwbar><a href=" . $href2 . ">Run #$run2</a></th>");
    print("<th $vwbar>Diff</th>");
    print("<th $vwbar>Diff%</th>");
    print('</tr>');
    print('<tr>');

    if ($display_calls) {
      print("<td>Number of Function Calls</td>");
      print_td_num($symbol_info1["ct"], $format_cbk["ct"]);
      print_td_num($symbol_info2["ct"], $format_cbk["ct"]);
      print_td_num($symbol_info2["ct"] - $symbol_info1["ct"],
                   $format_cbk["ct"], true);
      print_td_pct($symbol_info2["ct"] - $symbol_info1["ct"],
                   $symbol_info1["ct"], true);
      print('</tr>');
    }


    foreach ($metrics as $metric) {
      $m = $metric;

      // Inclusive stat for metric
      print('<tr>');
      print("<td>" . str_replace("<br>", " ", $descriptions[$m]) . "</td>");
      print_td_num($symbol_info1[$m], $format_cbk[$m]);
      print_td_num($symbol_info2[$m], $format_cbk[$m]);
      print_td_num($symbol_info2[$m] - $symbol_info1[$m], $format_cbk[$m], true);
      print_td_pct($symbol_info2[$m] - $symbol_info1[$m], $symbol_info1[$m], true);
      print('</tr>');

      // AVG (per call) Inclusive stat for metric
      print('<tr>');
      print("<td>" . str_replace("<br>", " ", $descriptions[$m]) . " per call </td>");
      $avg_info1 = 'N/A';
      $avg_info2 = 'N/A';
      if ($symbol_info1['ct'] > 0) {
        $avg_info1 = ($symbol_info1[$m]/$symbol_info1['ct']);
      }
      if ($symbol_info2['ct'] > 0) {
        $avg_info2 = ($symbol_info2[$m]/$symbol_info2['ct']);
      }
      print_td_num($avg_info1, $format_cbk[$m]);
      print_td_num($avg_info2, $format_cbk[$m]);
      print_td_num($avg_info2 - $avg_info1, $format_cbk[$m], true);
      print_td_pct($avg_info2 - $avg_info1, $avg_info1, true);
      print('</tr>');

      // Exclusive stat for metric
      $m = "excl_" . $metric;
      print('<tr style="border-bottom: 1px solid black;">');
      print("<td>" . str_replace("<br>", " ", $descriptions[$m]) . "</td>");
      print_td_num($symbol_info1[$m], $format_cbk[$m]);
      print_td_num($symbol_info2[$m], $format_cbk[$m]);
      print_td_num($symbol_info2[$m] - $symbol_info1[$m], $format_cbk[$m], true);
      print_td_pct($symbol_info2[$m] - $symbol_info1[$m], $symbol_info1[$m], true);
      print('</tr>');
    }

    print('</table>');
  }

  print("<br><h4><center>");
  print("Parent/Child $regr_impr report for <b>$rep_symbol</b>");

  $callgraph_href = "$base_path/callgraph.php?"
    . http_build_query(xhprof_array_set($url_params, 'func', $rep_symbol));

  print(" <a href='$callgraph_href'>[View Callgraph $diff_text]</a><br>");

  print("</center></h4><br>");

  print('<table border=1 cellpadding=2 cellspacing=1 width="90%" '
        .'rules=rows bordercolor="#bdc7d8" align=center>' . "\n");
  print('<tr bgcolor="#bdc7d8" align=right>');

  foreach ($pc_stats as $stat) {
    $desc = stat_description($stat);
    if (array_key_exists($stat, $sortable_columns)) {

      $href = "$base_path/?" .
        http_build_query(xhprof_array_set($url_params,
                                          'sort', $stat));
      $header = xhprof_render_link($desc, $href);
    } else {
      $header = $desc;
    }

    if ($stat == "fn")
      print("<th align=left><nobr>$header</th>");
    else
      print("<th " . $vwbar . "><nobr>$header</th>");
  }
  print("</tr>");

  print("<tr bgcolor='#e0e0ff'><td>");
  print("<b><i><center>Current Function</center></i></b>");
  print("</td></tr>");

  print("<tr>");
  // make this a self-reference to facilitate copy-pasting snippets to e-mails
  print("<td><a href=''>$rep_symbol</a></td>");

  if ($display_calls) {
    // Call Count
    print_td_num($symbol_info["ct"], $format_cbk["ct"]);
    print_td_pct($symbol_info["ct"], $totals["ct"]);
  }

  // Inclusive Metrics for current function
  foreach ($metrics as $metric) {
    print_td_num($symbol_info[$metric], $format_cbk[$metric], ($sort_col == $metric));
    print_td_pct($symbol_info[$metric], $totals[$metric], ($sort_col == $metric));
  }
  print("</tr>");

  print("<tr bgcolor='#ffffff'>");
  print("<td style='text-align:right;color:blue'>"
        ."Exclusive Metrics $diff_text for Current Function</td>");

  if ($display_calls) {
    // Call Count
    print("<td $vbar></td>");
    print("<td $vbar></td>");
  }

  // Exclusive Metrics for current function
  foreach ($metrics as $metric) {
    print_td_num($symbol_info["excl_" . $metric], $format_cbk["excl_" . $metric],
                 ($sort_col == $metric),
                 get_tooltip_attributes("Child", $metric));
    print_td_pct($symbol_info["excl_" . $metric], $symbol_info[$metric],
                 ($sort_col == $metric),
                 get_tooltip_attributes("Child", $metric));
  }
  print("</tr>");

  // list of callers/parent functions
  $results = array();
  if ($display_calls) {
    $base_ct = $symbol_info["ct"];
  } else {
    $base_ct = 0;
  }
  foreach ($metrics as $metric) {
    $base_info[$metric] = $symbol_info[$metric];
  }
  foreach ($run_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);
    if (($child == $rep_symbol) && ($parent)) {
      $info_tmp = $info;
      $info_tmp["fn"] = $parent;
      $results[] = $info_tmp;
    }
  }
  usort($results, 'sort_cbk');

  if (count($results) > 0) {
    print_pc_array($url_params, $results, $base_ct, $base_info, true,
                   $run1, $run2);
  }

  // list of callees/child functions
  $results = array();
  $base_ct = 0;
  foreach ($run_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);
    if ($parent == $rep_symbol) {
      $info_tmp = $info;
      $info_tmp["fn"] = $child;
      $results[] = $info_tmp;
      if ($display_calls) {
        $base_ct += $info["ct"];
      }
    }
  }
  usort($results, 'sort_cbk');

  if (count($results)) {
    print_pc_array($url_params, $results, $base_ct, $base_info, false,
                   $run1, $run2);
  }

  print("</table>");

  // These will be used for pop-up tips/help.
  // Related javascript code is in: xhprof_report.js
  print("\n");
  print('<script language="javascript">' . "\n");
  print("var func_name = '\"" . $rep_symbol . "\"';\n");
  print("var total_child_ct  = " . $base_ct . ";\n");
  if ($display_calls) {
    print("var func_ct   = " . $symbol_info["ct"] . ";\n" );
  }
  print("var func_metrics = new Array();\n");
  print("var metrics_col  = new Array();\n");
  print("var metrics_desc  = new Array();\n");
  if ($diff_mode) {
    print("var diff_mode = true;\n");
  } else {
    print("var diff_mode = false;\n");
  }
  $column_index = 3; // First three columns are Func Name, Calls, Calls%
  foreach ($metrics as $metric) {
    print("func_metrics[\"" . $metric . "\"] = " . round($symbol_info[$metric]) . ";\n" );
    print("metrics_col[\"". $metric . "\"] = " . $column_index . ";\n");
    print("metrics_desc[\"". $metric . "\"] = \"" . $possible_metrics[$metric][2] . "\";\n");

    // each metric has two columns..
    $column_index += 2;
  }
  print('</script>');
  print("\n");

}

/**
 * Generate the profiler report for a single run.
 *
 * @author Kannan
 */
function profiler_single_run_report ($url_params,
                                     $xhprof_data,
                                     $run_desc,
                                     $rep_symbol,
                                     $sort,
                                     $run) {

  init_metrics($xhprof_data, $rep_symbol, $sort, false);

  profiler_report($url_params, $rep_symbol, $sort, $run, $run_desc,
                  $xhprof_data);
}



/**
 * Generate the profiler report for diff mode (delta between two runs).
 *
 * @author Kannan
 */
function profiler_diff_report($url_params,
                              $xhprof_data1,
                              $run1_desc,
                              $xhprof_data2,
                              $run2_desc,
                              $rep_symbol,
                              $sort,
                              $run1,
                              $run2) {


  // Initialize what metrics we'll display based on data in Run2
  init_metrics($xhprof_data2, $rep_symbol, $sort, true);

  profiler_report($url_params,
                  $rep_symbol,
                  $sort,
                  $run1,
                  $run1_desc,
                  $xhprof_data1,
                  $run2,
                  $run2_desc,
                  $xhprof_data2);
}


/**
 * Generate a XHProf Display View given the various URL parameters
 * as arguments. The first argument is an object that implements
 * the iXHProfRuns interface.
 *
 * @param object  $xhprof_runs_impl  An object that implements
 *                                   the iXHProfRuns interface
 *.
 * @param array   $url_params   Array of non-default URL params.
 *
 * @param string  $source       Category/type of the run. The source in
 *                              combination with the run id uniquely
 *                              determines a profiler run.
 *
 * @param string  $run          run id, or comma separated sequence of
 *                              run ids. The latter is used if an aggregate
 *                              report of the runs is desired.
 *
 * @param string  $wts          Comma separate list of integers.
 *                              Represents the weighted ratio in
 *                              which which a set of runs will be
 *                              aggregated. [Used only for aggregate
 *                              reports.]
 *
 * @param string  $symbol       Function symbol. If non-empty then the
 *                              parent/child view of this function is
 *                              displayed. If empty, a flat-profile view
 *                              of the functions is displayed.
 *
 * @param string  $run1         Base run id (for diff reports)
 *
 * @param string  $run2         New run id (for diff reports)
 *
 */
function displayXHProfReport($xhprof_runs_impl, $url_params, $source,
                             $run, $wts, $symbol, $sort, $run1, $run2) {

  if ($run) {                              // specific run to display?

    // run may be a single run or a comma separate list of runs
    // that'll be aggregated. If "wts" (a comma separated list
    // of integral weights is specified), the runs will be
    // aggregated in that ratio.
    //
    $runs_array = explode(",", $run);

    if (count($runs_array) == 1) {
      $xhprof_data = $xhprof_runs_impl->get_run($runs_array[0],
                                                $source,
                                                $description);
    } else {
      if (!empty($wts)) {
        $wts_array  = explode(",", $wts);
      } else {
        $wts_array = null;
      }
      $data = xhprof_aggregate_runs($xhprof_runs_impl,
                                    $runs_array, $wts_array, $source, false);
      $xhprof_data = $data['raw'];
      $description = $data['description'];
    }


    profiler_single_run_report($url_params,
                               $xhprof_data,
                               $description,
                               $symbol,
                               $sort,
                               $run);

  } else if ($run1 && $run2) {                  // diff report for two runs

    $xhprof_data1 = $xhprof_runs_impl->get_run($run1, $source, $description1);
    $xhprof_data2 = $xhprof_runs_impl->get_run($run2, $source, $description2);

    profiler_diff_report($url_params,
                         $xhprof_data1,
                         $description1,
                         $xhprof_data2,
                         $description2,
                         $symbol,
                         $sort,
                         $run1,
                         $run2);

  } else {
    echo "No XHProf runs specified in the URL.";
  }
}
