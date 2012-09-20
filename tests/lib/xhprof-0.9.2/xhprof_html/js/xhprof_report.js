/*  Copyright (c) 2009 Facebook
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Helper javascript functions for XHProf report tooltips.
 *
 * @author Kannan Muthukkaruppan
 */

// Take a string which is actually a number in comma separated format
// and return a string representing the absolute value of the number.
function stringAbs(x) {
  return x.replace("-", "");
}

// Takes a number in comma-separated string format, and
// returns a boolean to indicate if the number is negative
// or not.
function isNegative(x) {

  return (x.indexOf("-") == 0);

}

function addCommas(nStr)
{
  nStr += '';
  x = nStr.split('.');
  x1 = x[0];
  x2 = x.length > 1 ? '.' + x[1] : '';
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, '$1' + ',' + '$2');
  }
  return x1 + x2;
}

// Mouseover tips for parent rows in parent/child report..
function ParentRowToolTip(cell, metric)
{
  var metric_val;
  var parent_metric_val;
  var parent_metric_pct_val;
  var col_index;
  var diff_text;

  row = cell.parentNode;
  tds = row.getElementsByTagName("td");

  parent_func    = tds[0].innerHTML;  // name

  if (diff_mode) {
    diff_text = " diff ";
  } else {
    diff_text = "";
  }

  s = '<center>';

  if (metric == "ct") {
    parent_ct      = tds[1].innerHTML;  // calls
    parent_ct_pct  = tds[2].innerHTML;

    func_ct = addCommas(func_ct);

    if (diff_mode) {
      s += 'There are ' + stringAbs(parent_ct) +
        (isNegative(parent_ct) ? ' fewer ' : ' more ') +
        ' calls to ' + func_name + ' from ' + parent_func + '<br>';

      text = " of diff in calls ";
    }  else {
      text = " of calls ";
    }

    s += parent_ct_pct + text + '(' + parent_ct + '/' + func_ct + ') to '
      + func_name + ' are from ' + parent_func + '<br>';
  } else {

    // help for other metrics such as wall time, user cpu time, memory usage
    col_index = metrics_col[metric];
    parent_metric_val     = tds[col_index].innerHTML;
    parent_metric_pct_val = tds[col_index+1].innerHTML;

    metric_val = addCommas(func_metrics[metric]);

    s += parent_metric_pct_val + '(' + parent_metric_val + '/' + metric_val
      + ') of ' + metrics_desc[metric] +
      (diff_mode ? ((isNegative(parent_metric_val) ?
                    " decrease" : " increase")) : "") +
      ' in ' + func_name + ' is due to calls from ' + parent_func + '<br>';
  }

  s += '</center>';

  return s;
}

// Mouseover tips for child rows in parent/child report..
function ChildRowToolTip(cell, metric)
{
  var metric_val;
  var child_metric_val;
  var child_metric_pct_val;
  var col_index;
  var diff_text;

  row = cell.parentNode;
  tds = row.getElementsByTagName("td");

  child_func   = tds[0].innerHTML;  // name

  if (diff_mode) {
    diff_text = " diff ";
  } else {
    diff_text = "";
  }

  s = '<center>';

  if (metric == "ct") {

    child_ct     = tds[1].innerHTML;  // calls
    child_ct_pct = tds[2].innerHTML;

    s += func_name + ' called ' + child_func + ' ' + stringAbs(child_ct) +
      (diff_mode ? (isNegative(child_ct) ? " fewer" : " more") : "" )
        + ' times.<br>';
    s += 'This accounts for ' + child_ct_pct + ' (' + child_ct
        + '/' + total_child_ct
        + ') of function calls made by '  + func_name + '.';

  } else {

    // help for other metrics such as wall time, user cpu time, memory usage
    col_index = metrics_col[metric];
    child_metric_val     = tds[col_index].innerHTML;
    child_metric_pct_val = tds[col_index+1].innerHTML;

    metric_val = addCommas(func_metrics[metric]);

    if (child_func.indexOf("Exclusive Metrics") != -1) {
      s += 'The exclusive ' + metrics_desc[metric] + diff_text
        + ' for ' + func_name
        + ' is ' + child_metric_val + " <br>";

      s += "which is " + child_metric_pct_val + " of the inclusive "
        + metrics_desc[metric]
        + diff_text + " for " + func_name + " (" + metric_val + ").";

    } else {

      s += child_func + ' when called from ' + func_name
        + ' takes ' + stringAbs(child_metric_val)
        + (diff_mode ? (isNegative(child_metric_val) ? " less" : " more") : "")
        + " of " + metrics_desc[metric] + " <br>";

      s += "which is " + child_metric_pct_val + " of the inclusive "
        + metrics_desc[metric]
        + diff_text + " for " + func_name + " (" + metric_val + ").";
    }
  }

  s += '</center>';

  return s;
}

$(document).ready(function() {
  $('td[@metric]').tooltip(
    { bodyHandler: function() {
          var type = $(this).attr('type');
          var metric = $(this).attr('metric');
          if (type == 'Parent') {
             return ParentRowToolTip(this, metric);
          } else if (type == 'Child') {
             return ChildRowToolTip(this, metric);
          }
      },
      showURL : false
    });
  var cur_params = {} ;
  $.each(location.search.replace('?','').split('&'), function(i, x) {
    var y = x.split('='); cur_params[y[0]] = y[1];
  });
  $('input.function_typeahead')
    .autocomplete('typeahead.php', { extraParams : cur_params })
    .result(function(event, item) {
      cur_params['symbol'] = item;
      location.search = '?' + jQuery.param(cur_params);
    });
});
