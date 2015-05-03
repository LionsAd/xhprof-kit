<?php

// set profiler namespace 
$profiler_namespace = (!isset($_GET['namespace']))?'drupal-perf':$_GET['namespace'];
$profiler_extra = (!isset($_GET['extra']))?'':$_GET['extra'];
$profiler_dir = 'xhprof-kit/xhprof';

if (extension_loaded('xhprof')) {
    include_once $profiler_dir . '/xhprof_lib/utils/xhprof_lib.php';
    include_once $profiler_dir . '/xhprof_lib/utils/xhprof_runs.php';
    xhprof_enable(XHPROF_FLAGS_MEMORY);
}

// Parse URL
$benchmark_url = '/';
if (isset($_GET['url'])) {
  $benchmark_url = $_GET['url'];
}

// define default settings
$cmd = 'index.php';

$path = parse_url($benchmark_url);

if (isset($path['query'])) {
  $_SERVER['QUERY_STRING'] = $path['query'];
  parse_str($path['query'], $_GET);
  $_REQUEST = $_GET;
}

// set file to execute or Drupal path (clean URLs enabled)
if (isset($path['path'])) {
  $_SERVER['SCRIPT_NAME'] = '/' . $cmd;
  $_SERVER['REQUEST_URI'] = $path['path'];
}

// Benchmark loop
$time_start = microtime( true );

register_shutdown_function(function() use ($time_start, $profiler_namespace, $benchmark_url, $profiler_extra) {
  // Get docroot because adding it manually to the href is annoying.
  $docroot = '';
  if (isset($_GET['docroot'])) {
    $docroot = 'http://' . $_GET['docroot'];
  }

  $time_end = ( microtime( true ) - $time_start );
  printf( "loop time: |%fs|",
      $time_end
  );

  if (extension_loaded('xhprof')) {
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

    // url to the XHProf UI libraries (change the host name and path)
    $profiler_url = sprintf($base_url . '/xhprof-kit/xhprof/xhprof_html/index.php?source=%s&url=%s&run=%s&extra=%s', $profiler_namespace, urlencode($benchmark_url), $run_id, $profiler_extra);
    echo $run_id . '|' . $profiler_namespace . '|' . $profiler_extra . '|' . $docroot . $profiler_url . "\n";
  }

});


include $cmd;

exit();
