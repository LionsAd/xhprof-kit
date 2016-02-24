<?php

// set profiler namespace 
$profiler_namespace = (!isset($_GET['namespace']))?'drupal-perf':$_GET['namespace'];
$profiler_extra = (!isset($_GET['extra']))?'':$_GET['extra'];
$profiler_dir = __DIR__ . '/../xhprof';

// enable xhprof by default
$enable_xhprof = extension_loaded('xhprof');

// Parse URL
$benchmark_url = '/';

$_SERVER['QUERY_STRING'] = '';

if (isset($_GET['disable_opcache'])) {
  ini_set('opcache.enable', 0);
  unset($_GET['disable_opcache']);
  unset($_REQUEST['disable_opcache']);
}

if (isset($_GET['disable_xhprof'])) {
  $enable_xhprof = FALSE;
  unset($_GET['disable_xhprof']);
  unset($_REQUEST['disable_xhprof']);
}


if (isset($_GET['url'])) {
  $benchmark_url = $_GET['url'];
  unset($_GET['url']);
  unset($_REQUEST['url']);
}

// define default settings
$cmd = 'index.php';

$benchmark_url = ltrim($benchmark_url, '/');
$path = parse_url($benchmark_url);

if (isset($path['query'])) {
  $path['path'] = $path['host'];
  $_SERVER['QUERY_STRING'] = $path['query'];
  parse_str($path['query'], $_GET);
  $_REQUEST = $_GET;
}

// set file to execute or Drupal path (clean URLs enabled)
if (isset($path['path'])) {
  $path['path'] = '/' . $path['path']; 
  $_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), $cmd, $_SERVER['SCRIPT_FILENAME']);
  $_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), $cmd, $_SERVER['SCRIPT_NAME']);
  $_SERVER['PHP_SELF'] = str_replace(basename(__FILE__), $cmd, $_SERVER['PHP_SELF']);
  $_SERVER['REQUEST_URI'] = $path['path'];
}

// Benchmark loop
$time_start = microtime( true );

register_shutdown_function(function() use ($time_start, $profiler_namespace, $benchmark_url, $profiler_extra, $enable_xhprof) {
  $time_end = ( microtime( true ) - $time_start );
  printf( "loop time: |%fs|",
      $time_end
  );

  if ($enable_xhprof) {
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

    if (!isset($base_url)) {
      $base_url = '';

      // D8 compatibility code.
      if (class_exists('\Symfony\Component\HttpFoundation\Request', FALSE)) {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $base_url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
      }
    }

    // Get path for profiler url.
    $profiler_url = sprintf($base_url . '/xhprof-kit.php/?source=%s&url=%s&run=%s&extra=%s', $profiler_namespace, urlencode($benchmark_url), $run_id, $profiler_extra);
    echo $run_id . '|' . $profiler_namespace . '|' . $profiler_extra . '|' . '<a id="xhprof-profiler-output" href="'. $profiler_url .'" target="_blank">Profiler output</a>' . "\n";
  }

});

if ($enable_xhprof) {
  include_once $profiler_dir . '/xhprof_lib/utils/xhprof_lib.php';
  include_once $profiler_dir . '/xhprof_lib/utils/xhprof_runs.php';
  xhprof_enable(XHPROF_FLAGS_MEMORY);
}

include $cmd;

exit();
