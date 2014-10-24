<?php
include 'xhprof-kit/XhprofIntegration.php';
include 'xhprof-kit/UprofilerIntegration.php';

use xhprof_kit\UprofilerIntegration;
use xhprof_kit\XhprofIntegration;

$run1   = $_SERVER['argv'][1];
$run2   = $_SERVER['argv'][2];
$extra  = isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:'';
$source = isset($_SERVER['argv'][4])?$_SERVER['argv'][4]:'drupal-perf';

if (XhprofIntegration::exists()) {
  $result = XhprofIntegration::compareRunsMetrics($run1, $run2, $source, $description_1, $description_2);
}
elseif (uprofilerIntegration::exists()) {
  $result = uprofilerIntegration::compareRunsMetrics($run1, $run2, $source, $description_1, $description_2);
}

list($totals_1, $totals_2, $metrics) = $result;

function print_pct($numer, $denom) {
  if ($denom == 0) {
    $pct = "N/A%";
  } else {
    $pct = sprintf('%.1f%%', 100 * $numer / abs($denom));
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

global $format_cbk;

foreach ($metrics as $metric) {
      $m = $metric;
      $fmt = $format_cbk[$m];
      print str_pad($m,4) . ': ' . print_num($totals_1[$m], $fmt) . '|' . print_num($totals_2[$m], $fmt) . '|' . print_num($totals_2[$m] - $totals_1[$m], $fmt) . '|' . print_pct(($totals_2[$m] - $totals_1[$m]), $totals_1[$m]) . "\n";
}
