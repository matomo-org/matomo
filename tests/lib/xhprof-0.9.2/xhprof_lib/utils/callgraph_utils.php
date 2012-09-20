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

/*
 * This file contains callgraph image generation related XHProf utility
 * functions
 *
 */

// Supported ouput format
$xhprof_legal_image_types = array(
    "jpg" => 1,
    "gif" => 1,
    "png" => 1,
    "ps"  => 1,
    );

/**
 * Send an HTTP header with the response. You MUST use this function instead
 * of header() so that we can debug header issues because they're virtually
 * impossible to debug otherwise. If you try to commit header(), SVN will
 * reject your commit.
 *
 * @param string  HTTP header name, like 'Location'
 * @param string  HTTP header value, like 'http://www.example.com/'
 *
 */
function xhprof_http_header($name, $value) {

  if (!$name) {
    xhprof_error('http_header usage');
    return null;
  }

  if (!is_string($value)) {
    xhprof_error('http_header value not a string');
  }

  header($name.': '.$value, true);
}

/**
 * Genearte and send MIME header for the output image to client browser.
 *
 * @author cjiang
 */
function xhprof_generate_mime_header($type, $length) {
  switch ($type) {
    case 'jpg':
      $mime = 'image/jpeg';
      break;
    case 'gif':
      $mime = 'image/gif';
      break;
    case 'png':
      $mime = 'image/png';
      break;
    case 'ps':
      $mime = 'application/postscript';
    default:
      $mime = false;
  }

  if ($mime) {
    xhprof_http_header('Content-type', $mime);
    xhprof_http_header('Content-length', (string)$length);
  }
}

/**
 * Generate image according to DOT script. This function will spawn a process
 * with "dot" command and pipe the "dot_script" to it and pipe out the
 * generated image content.
 *
 * @param dot_script, string, the script for DOT to generate the image.
 * @param type, one of the supported image types, see
 * $xhprof_legal_image_types.
 * @returns, binary content of the generated image on success. empty string on
 *           failure.
 *
 * @author cjiang
 */
function xhprof_generate_image_by_dot($dot_script, $type) {
  $descriptorspec = array(
       // stdin is a pipe that the child will read from
       0 => array("pipe", "r"),
       // stdout is a pipe that the child will write to
       1 => array("pipe", "w"),
       // stderr is a file to write to
       2 => array("file", "/dev/null", "a")
       );

  $cmd = " dot -T".$type;

  $process = proc_open($cmd, $descriptorspec, $pipes, "/tmp", array());

  if (is_resource($process)) {
    fwrite($pipes[0], $dot_script);
    fclose($pipes[0]);

   $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    proc_close($process);
    return $output;
  }
  print "failed to shell execute cmd=\"$cmd\"\n";
  exit();
}

/*
 * Get the children list of all nodes.
 */
function xhprof_get_children_table($raw_data) {
  $children_table = array();
  foreach ($raw_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);
    if (!isset($children_table[$parent])) {
      $children_table[$parent] = array($child);
    } else {
      $children_table[$parent][] = $child;
    }
  }
  return $children_table;
}

/**
 * Generate DOT script from the given raw phprof data.
 *
 * @param raw_data, phprof profile data.
 * @param threshold, float, the threshold value [0,1). The functions in the
 *                   raw_data whose exclusive wall times ratio are below the
 *                   threshold will be filtered out and won't apprear in the
 *                   generated image.
 * @param page, string(optional), the root node name. This can be used to
 *              replace the 'main()' as the root node.
 * @param func, string, the focus function.
 * @param critical_path, bool, whether or not to display critical path with
 *                             bold lines.
 * @returns, string, the DOT script to generate image.
 *
 * @author cjiang
 */
function xhprof_generate_dot_script($raw_data, $threshold, $source, $page,
                                    $func, $critical_path, $right=null,
                                    $left=null) {

  $max_width = 5;
  $max_height = 3.5;
  $max_fontsize = 35;
  $max_sizing_ratio = 20;

  $totals;

  if ($left === null) {
    // init_metrics($raw_data, null, null);
  }
  $sym_table = xhprof_compute_flat_info($raw_data, $totals);

  if ($critical_path) {
    $children_table = xhprof_get_children_table($raw_data);
    $node = "main()";
    $path = array();
    $path_edges = array();
    $visited = array();
    while ($node) {
      $visited[$node] = true;
      if (isset($children_table[$node])) {
        $max_child = null;
        foreach ($children_table[$node] as $child) {

          if (isset($visited[$child])) {
            continue;
          }
          if ($max_child === null ||
            abs($raw_data[xhprof_build_parent_child_key($node,
                                                        $child)]["wt"]) >
            abs($raw_data[xhprof_build_parent_child_key($node,
                                                        $max_child)]["wt"])) {
            $max_child = $child;
          }
        }
        if ($max_child !== null) {
          $path[$max_child] = true;
          $path_edges[xhprof_build_parent_child_key($node, $max_child)] = true;
        }
        $node = $max_child;
      } else {
        $node = null;
      }
    }
  }

  // if it is a benchmark callgraph, we make the benchmarked function the root.
 if ($source == "bm" && array_key_exists("main()", $sym_table)) {
    $total_times = $sym_table["main()"]["ct"];
    $remove_funcs = array("main()",
                          "hotprofiler_disable",
                          "call_user_func_array",
                          "xhprof_disable");

    foreach ($remove_funcs as $cur_del_func) {
      if (array_key_exists($cur_del_func, $sym_table) &&
          $sym_table[$cur_del_func]["ct"] == $total_times) {
        unset($sym_table[$cur_del_func]);
      }
    }
  }

  // use the function to filter out irrelevant functions.
  if (!empty($func)) {
    $interested_funcs = array();
    foreach ($raw_data as $parent_child => $info) {
      list($parent, $child) = xhprof_parse_parent_child($parent_child);
      if ($parent == $func || $child == $func) {
        $interested_funcs[$parent] = 1;
        $interested_funcs[$child] = 1;
      }
    }
    foreach ($sym_table as $symbol => $info) {
      if (!array_key_exists($symbol, $interested_funcs)) {
        unset($sym_table[$symbol]);
      }
    }
  }

  $result = "digraph call_graph {\n";

  // Filter out functions whose exclusive time ratio is below threshold, and
  // also assign a unique integer id for each function to be generated. In the
  // meantime, find the function with the most exclusive time (potentially the
  // performance bottleneck).
  $cur_id = 0; $max_wt = 0;
  foreach ($sym_table as $symbol => $info) {
    if (empty($func) && abs($info["wt"] / $totals["wt"]) < $threshold) {
      unset($sym_table[$symbol]);
      continue;
    }
    if ($max_wt == 0 || $max_wt < abs($info["excl_wt"])) {
      $max_wt = abs($info["excl_wt"]);
    }
    $sym_table[$symbol]["id"] = $cur_id;
    $cur_id ++;
  }

  // Generate all nodes' information.
  foreach ($sym_table as $symbol => $info) {
    if ($info["excl_wt"] == 0) {
      $sizing_factor = $max_sizing_ratio;
    } else {
      $sizing_factor = $max_wt / abs($info["excl_wt"]) ;
      if ($sizing_factor > $max_sizing_ratio) {
        $sizing_factor = $max_sizing_ratio;
      }
    }
    $fillcolor = (($sizing_factor < 1.5) ?
                  ", style=filled, fillcolor=red" : "");

    if ($critical_path) {
      // highlight nodes along critical path.
      if (!$fillcolor && array_key_exists($symbol, $path)) {
        $fillcolor = ", style=filled, fillcolor=yellow";
      }
    }

    $fontsize =", fontsize="
               .(int)($max_fontsize / (($sizing_factor - 1) / 10 + 1));

    $width = ", width=".sprintf("%.1f", $max_width / $sizing_factor);
    $height = ", height=".sprintf("%.1f", $max_height / $sizing_factor);

    if ($symbol == "main()") {
      $shape = "octagon";
      $name ="Total: ".($totals["wt"]/1000.0)." ms\\n";
      $name .= addslashes(isset($page) ? $page : $symbol);
    } else {
      $shape = "box";
      $name = addslashes($symbol)."\\nInc: ". sprintf("%.3f",$info["wt"]/1000) .
              " ms (" . sprintf("%.1f%%", 100 * $info["wt"]/$totals["wt"]).")";
    }
    if ($left === null) {
      $label = ", label=\"".$name."\\nExcl: "
               .(sprintf("%.3f",$info["excl_wt"]/1000.0))." ms ("
               .sprintf("%.1f%%", 100 * $info["excl_wt"]/$totals["wt"])
               . ")\\n".$info["ct"]." total calls\"";
    } else {
      if (isset($left[$symbol]) && isset($right[$symbol])) {
         $label = ", label=\"".addslashes($symbol).
                  "\\nInc: ".(sprintf("%.3f",$left[$symbol]["wt"]/1000.0))
                  ." ms - "
                  .(sprintf("%.3f",$right[$symbol]["wt"]/1000.0))." ms = "
                  .(sprintf("%.3f",$info["wt"]/1000.0))." ms".
                  "\\nExcl: "
                  .(sprintf("%.3f",$left[$symbol]["excl_wt"]/1000.0))
                  ." ms - ".(sprintf("%.3f",$right[$symbol]["excl_wt"]/1000.0))
                   ." ms = ".(sprintf("%.3f",$info["excl_wt"]/1000.0))." ms".
                  "\\nCalls: ".(sprintf("%.3f",$left[$symbol]["ct"]))." - "
                   .(sprintf("%.3f",$right[$symbol]["ct"]))." = "
                   .(sprintf("%.3f",$info["ct"]))."\"";
      } else if (isset($left[$symbol])) {
        $label = ", label=\"".addslashes($symbol).
                  "\\nInc: ".(sprintf("%.3f",$left[$symbol]["wt"]/1000.0))
                   ." ms - 0 ms = ".(sprintf("%.3f",$info["wt"]/1000.0))
                   ." ms"."\\nExcl: "
                   .(sprintf("%.3f",$left[$symbol]["excl_wt"]/1000.0))
                   ." ms - 0 ms = "
                   .(sprintf("%.3f",$info["excl_wt"]/1000.0))." ms".
                  "\\nCalls: ".(sprintf("%.3f",$left[$symbol]["ct"]))." - 0 = "
                  .(sprintf("%.3f",$info["ct"]))."\"";
      } else {
        $label = ", label=\"".addslashes($symbol).
                  "\\nInc: 0 ms - "
                  .(sprintf("%.3f",$right[$symbol]["wt"]/1000.0))
                  ." ms = ".(sprintf("%.3f",$info["wt"]/1000.0))." ms".
                  "\\nExcl: 0 ms - "
                  .(sprintf("%.3f",$right[$symbol]["excl_wt"]/1000.0))
                  ." ms = ".(sprintf("%.3f",$info["excl_wt"]/1000.0))." ms".
                  "\\nCalls: 0 - ".(sprintf("%.3f",$right[$symbol]["ct"]))
                  ." = ".(sprintf("%.3f",$info["ct"]))."\"";
      }
    }
    $result .= "N" . $sym_table[$symbol]["id"];
    $result .= "[shape=$shape ".$label.$width
               .$height.$fontsize.$fillcolor."];\n";
  }

  // Generate all the edges' information.
  foreach ($raw_data as $parent_child => $info) {
    list($parent, $child) = xhprof_parse_parent_child($parent_child);

    if (isset($sym_table[$parent]) && isset($sym_table[$child]) &&
        (empty($func) ||
         (!empty($func) && ($parent == $func || $child == $func)) )) {

      $label = $info["ct"] == 1 ? $info["ct"]." call" : $info["ct"]." calls";

      $headlabel = $sym_table[$child]["wt"] > 0 ?
                  sprintf("%.1f%%", 100 * $info["wt"]
                                    / $sym_table[$child]["wt"])
                  : "0.0%";

      $taillabel = ($sym_table[$parent]["wt"] > 0) ?
        sprintf("%.1f%%",
                100 * $info["wt"] /
                ($sym_table[$parent]["wt"] - $sym_table["$parent"]["excl_wt"]))
        : "0.0%";

      $linewidth= 1;
      $arrow_size = 1;

      if ($critical_path &&
          isset($path_edges[xhprof_build_parent_child_key($parent, $child)])) {
        $linewidth = 10; $arrow_size=2;
      }

      $result .= "N" . $sym_table[$parent]["id"] . " -> N"
                 . $sym_table[$child]["id"];
      $result .= "[arrowsize=$arrow_size, style=\"setlinewidth($linewidth)\","
                 ." label=\""
                 .$label."\", headlabel=\"".$headlabel
                 ."\", taillabel=\"".$taillabel."\" ]";
      $result .= ";\n";

    }
  }
  $result = $result . "\n}";

  return $result;
}

function  xhprof_render_diff_image($xhprof_runs_impl, $run1, $run2,
                                   $type, $threshold, $source) {
  $total1;
  $total2;

  $raw_data1 = $xhprof_runs_impl->get_run($run1, $source, $desc_unused);
  $raw_data2 = $xhprof_runs_impl->get_run($run2, $source, $desc_unused);

  // init_metrics($raw_data1, null, null);
  $children_table1 = xhprof_get_children_table($raw_data1);
  $children_table2 = xhprof_get_children_table($raw_data2);
  $symbol_tab1 = xhprof_compute_flat_info($raw_data1, $total1);
  $symbol_tab2 = xhprof_compute_flat_info($raw_data2, $total2);
  $run_delta = xhprof_compute_diff($raw_data1, $raw_data2);
  $script = xhprof_generate_dot_script($run_delta, $threshold, $source,
                                       null, null, true,
                                       $symbol_tab1, $symbol_tab2);
  $content = xhprof_generate_image_by_dot($script, $type);

  xhprof_generate_mime_header($type, strlen($content));
  echo $content;
}

/**
 * Generate image content from phprof run id.
 *
 * @param object  $xhprof_runs_impl  An object that implements
 *                                   the iXHProfRuns interface
 * @param run_id, integer, the unique id for the phprof run, this is the
 *                primary key for phprof database table.
 * @param type, string, one of the supported image types. See also
 *              $xhprof_legal_image_types.
 * @param threshold, float, the threshold value [0,1). The functions in the
 *                   raw_data whose exclusive wall times ratio are below the
 *                   threshold will be filtered out and won't apprear in the
 *                   generated image.
 * @param func, string, the focus function.
 * @returns, string, the DOT script to generate image.
 *
 * @author cjiang
 */
function xhprof_get_content_by_run($xhprof_runs_impl, $run_id, $type,
                                   $threshold, $func, $source,
                                   $critical_path) {
  if (!$run_id)
    return "";

  $raw_data = $xhprof_runs_impl->get_run($run_id, $source, $description);
  if (!$raw_data) {
    xhprof_error("Raw data is empty");
    return "";
  }

  $script = xhprof_generate_dot_script($raw_data, $threshold, $source,
                                       $description, $func, $critical_path);

  $content = xhprof_generate_image_by_dot($script, $type);
  return $content;
}

/**
 * Generate image from phprof run id and send it to client.
 *
 * @param object  $xhprof_runs_impl  An object that implements
 *                                   the iXHProfRuns interface
 * @param run_id, integer, the unique id for the phprof run, this is the
 *                primary key for phprof database table.
 * @param type, string, one of the supported image types. See also
 *              $xhprof_legal_image_types.
 * @param threshold, float, the threshold value [0,1). The functions in the
 *                   raw_data whose exclusive wall times ratio are below the
 *                   threshold will be filtered out and won't apprear in the
 *                   generated image.
 * @param func, string, the focus function.
 * @param bool, does this run correspond to a PHProfLive run or a dev run?
 * @author cjiang
 */
function xhprof_render_image($xhprof_runs_impl, $run_id, $type, $threshold,
                             $func, $source, $critical_path) {

  $content = xhprof_get_content_by_run($xhprof_runs_impl, $run_id, $type,
                                       $threshold,
                                       $func, $source, $critical_path);
  if (!$content) {
    print "Error: either we can not find profile data for run_id ".$run_id
          ." or the threshold ".$threshold." is too small or you do not"
          ." have 'dot' image generation utility installed.";
    exit();
  }

  xhprof_generate_mime_header($type, strlen($content));
  echo $content;
}
