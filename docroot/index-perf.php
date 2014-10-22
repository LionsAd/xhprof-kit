<?php

include 'xhprof-kit/XhprofIntegration.php';
include 'xhprof-kit/UprofilerIntegration.php';

use xhprof_kit\UprofilerIntegration;
use xhprof_kit\XhprofIntegration;

if (XhprofIntegration::exists()) {
  XhprofIntegration::before();
}
elseif (UprofilerIntegration::exists()) {
  UprofilerIntegration::before();
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

include $cmd;

$time_end = ( microtime( true ) - $time_start );
printf( "loop time: |%fs|",
    $time_end
);

if (XhprofIntegration::exists()) {
  XhprofIntegration::after($benchmark_url);
}
elseif (UprofilerIntegration::exists()) {
  UprofilerIntegration::after($benchmark_url);
}


exit();
