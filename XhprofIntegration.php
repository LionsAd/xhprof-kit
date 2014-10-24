<?php

/**
 * @file
 * Contains \xhprof_kit\XhprofIntegration.
 */

namespace xhprof_kit;

class XhprofIntegration {

  public static function exists() {
    return extension_loaded('xhprof');
  }

  public static function before() {
    $profiler_dir = 'xhprof-kit/xhprof';

    include_once $profiler_dir . '/xhprof_lib/utils/xhprof_lib.php';
    include_once $profiler_dir . '/xhprof_lib/utils/xhprof_runs.php';
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
  }

  public static function after($benchmark_url) {
    global $base_url;
    $profiler_namespace = (!isset($_GET['namespace']))?'drupal-perf':$_GET['namespace'];
    $profiler_extra = (!isset($_GET['extra']))?'':$_GET['extra'];

    $profiler_dir = 'xhprof-kit/xhprof';

    include_once $profiler_dir . '/xhprof_lib/utils/xhprof_lib.php';
    include_once $profiler_dir . '/xhprof_lib/utils/xhprof_runs.php';

    $xhprof_data = xhprof_disable();
    $xhprof_runs = new \XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

    // url to the XHProf UI libraries (change the host name and path)
    $profiler_url = sprintf($base_url . '/xhprof-kit/xhprof/xhprof_html/index.php?source=%s&url=%s&run=%s&extra=%s', $profiler_namespace, urlencode($benchmark_url), $run_id, $profiler_extra);
    echo $run_id . '|' . $profiler_namespace . '|' . $profiler_extra . '|' . '<a href="'. $profiler_url .'" target="_blank">Profiler output</a>' . "\n";
  }

  public static function getRunData($run, $source, &$description) {
    // Retrieve run data
    include_once dirname(__FILE__) . '/xhprof/xhprof_lib/utils/xhprof_lib.php';
    include_once dirname(__FILE__) . '/xhprof/xhprof_lib/utils/xhprof_runs.php';
    include_once dirname(__FILE__) . '/xhprof/xhprof_lib/display/xhprof.php';

    $xhprof_runs_impl = new \XHProfRuns_Default();

    return $xhprof_runs_impl->get_run($run, $source, $description);
  }

  public static function compareRunsMetrics($run1, $run2, $source, &$description_1, &$description_2) {
    include_once dirname(__FILE__) . '/xhprof/xhprof_lib/utils/xhprof_lib.php';
    include_once dirname(__FILE__) . '/xhprof/xhprof_lib/utils/xhprof_runs.php';
    include_once dirname(__FILE__) . '/xhprof/xhprof_lib/display/xhprof.php';

    $xhprof_runs_impl = new \XHProfRuns_Default();

    $run1_data = $xhprof_runs_impl->get_run($run1, $source, $description_1);
    $run2_data = $xhprof_runs_impl->get_run($run2, $source, $description_2);

    $GLOBALS['display_calls'] = TRUE;
    $run_delta = xhprof_compute_diff($run1_data, $run2_data);
    $symbol_tab  = xhprof_compute_flat_info($run_delta, $totals);
    $symbol_tab1 = xhprof_compute_flat_info($run1_data, $totals_1);
    $symbol_tab2 = xhprof_compute_flat_info($run2_data, $totals_2);

    $metrics = xhprof_get_metrics($run_delta);
    return array($totals_1, $totals_2, $metrics);
  }

}
