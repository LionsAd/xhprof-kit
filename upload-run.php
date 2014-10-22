<?php

include 'XhprofIntegration.php';
include 'UprofilerIntegration.php';

use xhprof_kit\UprofilerIntegration;
use xhprof_kit\XhprofIntegration;

// Setup parameters
$key = $_SERVER['argv'][1];
$run   = $_SERVER['argv'][2];
$source = isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:'drupal-perf';

if (XhprofIntegration::exists()) {
  $run_data = XhprofIntegration::getRunData($run, $source, $description = '');
}
elseif (UprofilerIntegration::exists()) {
  $run_data = UprofilerIntegration::getRunData($run, $source, $description = '');
}

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
