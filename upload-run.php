<?php

// Setup parameters
$key = $_SERVER['argv'][1];
$run   = $_SERVER['argv'][2];
$source = isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:'drupal-perf';

// Retrieve run data
include_once dirname(__FILE__) . '/xhprof/xhprof_lib/utils/xhprof_lib.php';
include_once dirname(__FILE__) . '/xhprof/xhprof_lib/utils/xhprof_runs.php';
include_once dirname(__FILE__) . '/xhprof/xhprof_lib/display/xhprof.php';

$xhprof_runs_impl = new XHProfRuns_Default();

$run_data = $xhprof_runs_impl->get_run($run, $source, $description); 

// Save run data ...
$data = serialize($run_data);
$tmp = tmpfile();
fwrite($tmp, $data);
fseek($tmp, 0);

// ... and upload.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.lionsad.de/xhprof-kit/hosted/upload.php?key=$key&run=$run&source=$source");
curl_setopt($ch, CURLOPT_PUT, true); 
curl_setopt($ch, CURLOPT_INFILE, $tmp); 
curl_setopt($ch, CURLOPT_INFILESIZE, strlen($data)); 
$result = curl_exec($ch);
curl_close($ch); 

// Then cleanup.
fclose($tmp); 
?>
