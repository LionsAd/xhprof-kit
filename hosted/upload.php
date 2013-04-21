<?php

function get_key_source($key) {
  @require_once "api.php";

  if (!isset($api_keys[$key])) {
    return FALSE;
  }
  return $api_keys[$key];
}

$source = FALSE;
if (isset($_GET['key'])) {
  $source = get_key_source($_GET['key']);
}

if ($source === FALSE) {
  echo 'Access denied. Please supply a correct API key.' . "\n";
  exit(1);
}

$run_id = $_GET['run'];
$source = $_GET['source'] . '-' . $source;

$data = unserialize(file_get_contents('php://stdin'));

include_once dirname(__FILE__) . '/../xhprof/xhprof_lib/utils/xhprof_lib.php';
include_once dirname(__FILE__) . '/../xhprof/xhprof_lib/utils/xhprof_runs.php';
include_once dirname(__FILE__) . '/../xhprof/xhprof_lib/display/xhprof.php';

@mkdir('../data/stored-runs/', 0777, TRUE);
$xhprof_runs_impl = new XHProfRuns_Default('../data/stored-runs/');
$xhprof_runs_impl->save_run($data, $source, $run_id);

echo "Run uploaded successfully for $source.\n";
