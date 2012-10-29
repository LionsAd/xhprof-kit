<?php

// set profiler namespace 
$profiler_namespace = (!isset($_GET['namespace']))?'drupal-perf':$_GET['namespace'];
$profiler_extra = (!isset($_GET['extra']))?'':$_GET['extra'];

// @todo: This still needs devel module with xhprof enabled

$time_start = microtime( true );

include 'index.php';

$time_end = ( microtime( true ) - $time_start );
printf( "loop time: |%fs|",
    $time_end
);

if (extension_loaded('xhprof')) {
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

    // url to the XHProf UI libraries (change the host name and path)
    $profiler_url = sprintf('http://' . $base_url . '/xhprof/xhprof_html/index.php?run=%s&source=%s&extra=%s', $run_id, $profiler_namespace, $profiler_extra);
    echo $run_id . '|' . $profiler_namespace . '|' . $profiler_extra . '|' . '<a href="'. $profiler_url .'" target="_blank">Profiler output</a>' . "\n";
}

exit();
