<?php

$profiler_dir = __DIR__ . '/../xhprof';
$html_files_dir = $profiler_dir . '/xhprof_html';
$html_dir = __DIR__ . '/../xhprof-calls';
$GLOBALS['XHPROF_LIB_ROOT'] = $profiler_dir . '/xhprof_lib';

$script_name = basename(__FILE__);

$whitelist = array(
  '/css/xhprof.css' => 'text/css',
  '/jquery/jquery.autocomplete.css' => 'text/css',
  '/jquery/jquery.tooltip.css' => 'text/css',
  '/jquery/jquery-1.2.6.js' => 'application/javascript',
  '/jquery/jquery.autocomplete.js' => 'application/javascript',
  '/jquery/jquery.tooltip.js' => 'application/javascript',
  '/js/xhprof_report.js' => 'application/javascript',
);

// define default settings
$cmd = $script_name . '/' . $script_name;

// Set the REQUEST_URI var for all cases.
if (!isset($_SERVER['REQUEST_URI'])) {
  print "Error: REQUEST_URI missing.";
  exit(1);
}

// Strip the script name, we have urls like /xhprof-kit.php/.
$potential_file = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);

// Redirect a wrong url (no / after script name, to the right one).
if (strpos($potential_file, '?') === 0) {
  header('Location: ' . $_SERVER['SCRIPT_NAME'] . '/?' . $_SERVER['QUERY_STRING']);
  exit(0);
}

// Helper scripts.
if (strpos($potential_file, '/typeahead.php') === 0) {
  require_once $html_dir . '/typeahead.php';
  exit(0);
}

if (strpos($potential_file, '/callgraph.php') === 0) {
  require_once $html_dir . '/callgraph.php';
  exit(0);
}

// Whitelist for static files.
if (isset($whitelist[$potential_file])) {
  $name = $html_files_dir . $potential_file;

  header("Content-Type: " . $whitelist[$potential_file]);
  header("Content-Length: " . filesize($name));
  header("Cache-Control: public, max-age=600");

  $fp = fopen($name, 'rb');
  fpassthru($fp);

  exit(0);
}

// Ensure xhprof-kit/xhprof-kit.php is set.

$_SERVER['SCRIPT_FILENAME'] = str_replace($script_name, $cmd, $_SERVER['SCRIPT_FILENAME']);
$_SERVER['SCRIPT_NAME'] = str_replace($script_name, $cmd, $_SERVER['SCRIPT_NAME']);
$_SERVER['PHP_SELF'] = str_replace($script_name, $cmd, $_SERVER['PHP_SELF']);
$_SERVER['REQUEST_URI'] = str_replace($script_name, $cmd, $_SERVER['REQUEST_URI']);

require_once $html_dir . '/index.php';
