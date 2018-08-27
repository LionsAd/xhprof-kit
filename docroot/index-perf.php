<?php

// set profiler namespace 
$profiler_namespace = (!isset($_GET['namespace']))?'drupal-perf':$_GET['namespace'];
$profiler_extra = (!isset($_GET['extra']))?'':$_GET['extra'];
$profiler_dir = __DIR__ . '/../xhprof';

// enable xhprof by default
$enable_xhprof = extension_loaded('xhprof');

$xhprof_kit_cwd = getcwd();

function xhprof_kit_http_build_query(array $query, $parent = '') {
  $params = array();
  foreach ($query as $key => $value) {
    $key = $parent ? $parent . '[' . $key . ']' : $key;

    // Recurse into children.
    if (is_array($value)) {
      $params[] = xhprof_kit_http_build_query($value, $key);
    }
    elseif (!isset($value)) {
      $params[] = $key;
    }
    else {

      // For better readability of paths in query strings, we decode slashes.
      $params[] = $key . '=' . $value;
    }
  }
  return implode('&', $params);
}

// Parse URL
$benchmark_url = '/';

$_SERVER['QUERY_STRING'] = '';
$xhprof_kit_parameters = [];

if (isset($_GET['disable_opcache'])) {
  ini_set('opcache.enable', 0);
  unset($_GET['disable_opcache']);
  unset($_REQUEST['disable_opcache']);
  $xhprof_kit_parameters[] = 'disable_opcache=1';
}

if (isset($_GET['xhprof_kit_post'])) {
  $_SERVER['REQUEST_METHOD'] = 'POST';
  unset($_GET['xhprof_kit_post']);
  unset($_REQUEST['xhprof_kit_post']);
  $xhprof_kit_parameters[] = 'xhprof_kit_post=1';
}

if (isset($_GET['disable_xhprof'])) {
  $enable_xhprof = FALSE;
  unset($_GET['disable_xhprof']);
  unset($_REQUEST['disable_xhprof']);
  $xhprof_kit_parameters[] = 'disable_xhprof=1';
}

if (isset($_GET['url'])) {
  $benchmark_url = $_GET['url'];
  unset($_GET['url']);
  unset($_REQUEST['url']);
}

// Always turn off compression as we cannot append to the page, else.
unset($_SERVER['HTTP_ACCEPT_ENCODING']);

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
else {
  $_SERVER['QUERY_STRING'] = xhprof_kit_http_build_query($_GET);
}

// set file to execute or Drupal path (clean URLs enabled)
if (isset($path['path'])) {
  $path['path'] = '/' . $path['path']; 
  $_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), $cmd, $_SERVER['SCRIPT_FILENAME']);
  $_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), $cmd, $_SERVER['SCRIPT_NAME']);
  $_SERVER['PHP_SELF'] = str_replace(basename(__FILE__), $cmd, $_SERVER['PHP_SELF']);
  $_SERVER['REQUEST_URI'] = $path['path'];
  if (!empty($_SERVER['QUERY_STRING'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING'];
  }
}

// Benchmark loop
$time_start = microtime( true );

register_shutdown_function(function() use ($time_start, $profiler_namespace, $benchmark_url, $profiler_extra, $enable_xhprof, $xhprof_kit_cwd, $xhprof_kit_parameters) {
  $time_end = ( microtime( true ) - $time_start );
  printf("loop time: %.0f ms |", $time_end * 1000);
  $text = sprintf("%.0f", $time_end * 1000) . ' ms | XHProf disabled';

  if ($enable_xhprof) {
    global $base_url;

    $xhprof_data = xhprof_disable();

    // Change back to original cwd().
    chdir($xhprof_kit_cwd);

    $path_to_store = 'sites/default/files/xhprof-runs/';
    if (!file_exists($path_to_store)) {
      @mkdir($path_to_store);
    }

    $xhprof_runs = new XHProfRuns_Default($path_to_store);
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
    error_log('xhprof-kit|' . sprintf("%.0f", $time_end * 1000) . ' ms |' . $run_id . '|' . $profiler_namespace . '|' . $profiler_extra . '|' . '<a id="xhprof-profiler-output" href="'. $profiler_url .'" target="_blank">Profiler output</a>' . "\n");

    $text = sprintf("%.0f", $time_end * 1000) . ' ms | <a id="xhprof-profiler-output" href="'. $profiler_url .'" target="_blank">Profiler output</a>';
  }

print <<<EOF
<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery('<div style="height: 40px; background: white; font-size: 20px; padding-left: 20px; padding-top: 10px; position:relative; z-index: 1;" class="xhprof-kit-output">$text</div>').prependTo('body');
  });
</script>
EOF;

  $parameters = implode('&', $xhprof_kit_parameters);
  if (!empty($parameters)) {
    $parameters = '&' . $parameters;
  }

  print <<<EOF
<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery('a').once('xhprof-perf').each(function() {
      if (this.hostname == document.domain) {
        this.href = '/index-perf.php?url=' + encodeURI(this.pathname) + '$parameters' + this.search.replace('?', '&');
      }
    });
  });
</script>
EOF;

});

if ($enable_xhprof) {
  include_once $profiler_dir . '/xhprof_lib/utils/xhprof_lib.php';
  include_once $profiler_dir . '/xhprof_lib/utils/xhprof_runs.php';
  xhprof_enable(XHPROF_FLAGS_MEMORY);
}

include $cmd;
exit();
