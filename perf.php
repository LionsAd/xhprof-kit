<?php

# To use just include somewhere in the code.

$profiler_namespace = (!isset($_GET['namespace']))?'drupal-perf':$_GET['namespace'];
$profiler_extra = (!isset($_GET['extra']))?'':$_GET['extra'];
$profiler_dir = __DIR__ . '/xhprof';

// enable xhprof by default
$enable_xhprof = extension_loaded('xhprof');

// Benchmark loop
$time_start = microtime( true );

register_shutdown_function(function() use ($time_start, $profiler_namespace, $benchmark_url, $profiler_extra, $enable_xhprof) {
  $time_end = ( microtime( true ) - $time_start );
  $output = sprintf( "loop time: |%fs|",
      $time_end
  );

  if ($enable_xhprof) {
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
    $base_url = '';

    // Get path for profiler url.
    $profiler_url = sprintf($base_url . '/xhprof-kit.php/?source=%s&url=%s&run=%s&extra=%s', $profiler_namespace, urlencode($benchmark_url), $run_id, $profiler_extra);
    $output .= $run_id . '|' . $profiler_namespace . '|' . $profiler_extra . '|' . '<a id="xhprof-profiler-output" href="'. $profiler_url .'" target="_blank">Profiler output</a>' . "\n";
    error_log($output, 3, '/tmp/perf.log');
  }
});

if ($enable_xhprof) {
  include_once $profiler_dir . '/xhprof_lib/utils/xhprof_lib.php';
  include_once $profiler_dir . '/xhprof_lib/utils/xhprof_runs.php';
  xhprof_enable(XHPROF_FLAGS_MEMORY);
}
