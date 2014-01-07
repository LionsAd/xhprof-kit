<?php
$run1   = $_SERVER['argv'][1];
$run2   = $_SERVER['argv'][2];
$extra  = isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:'';
$source = isset($_SERVER['argv'][4])?$_SERVER['argv'][4]:'drupal-perf';

include_once dirname(__FILE__) . '/xhprof/xhprof_lib/utils/xhprof_lib.php';
include_once dirname(__FILE__) . '/xhprof/xhprof_lib/utils/xhprof_runs.php';
include_once dirname(__FILE__) . '/xhprof/xhprof_lib/display/xhprof.php';

$xhprof_runs_impl = new XHProfRuns_Default();

$run1_data = $xhprof_runs_impl->get_run($run1, $source, $description1);
$run2_data = $xhprof_runs_impl->get_run($run2, $source, $description2);

$run_delta = xhprof_compute_diff($run1_data, $run2_data);
$symbol_tab  = xhprof_compute_flat_info($run_delta, $totals);
$symbol_tab1 = xhprof_compute_flat_info($run1_data, $totals_1);
$symbol_tab2 = xhprof_compute_flat_info($run2_data, $totals_2);

$metrics = xhprof_get_metrics($run_delta);

function print_pct($numer, $denom) {
  if ($denom == 0) {
    $pct = "N/A%";
  } else {
    $pct = xhprof_percent_format($numer / abs($denom));
  }

  return $pct;
}  

function print_num($num, $fmt_func = null) {
  if (!empty($fmt_func)) {
    $num = call_user_func($fmt_func, $num);
  }
  return $num;
}

print "=== $extra compared ($run1..$run2):\n\n";
array_unshift($metrics, 'ct');

foreach ($metrics as $metric) {
      $m = $metric;
      $fmt = $format_cbk[$m];
      print str_pad($m,4) . ': ' . print_num($totals_1[$m], $fmt) . '|' . print_num($totals_2[$m], $fmt) . '|' . print_num($totals_2[$m] - $totals_1[$m], $fmt) . '|' . print_pct(($totals_2[$m] - $totals_1[$m]), $totals_1[$m]) . "\n";
}
