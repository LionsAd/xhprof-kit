<?php

/**
 * @file
 * Contains \xhprof_kit\UprofilerIntegration.
 */

namespace xhprof_kit;

class UprofilerIntegration {

  public static function exists() {
    return extension_loaded('uprofiler');
  }

  public static function before() {
    $profiler_dir = 'xhprof-kit/uprofiler';

    include_once $profiler_dir . '/uprofiler_lib/utils/uprofiler_lib.php';
    include_once $profiler_dir . '/uprofiler_lib/utils/uprofiler_runs.php';
    uprofiler_enable(UPROFILER_FLAGS_CPU + UPROFILER_FLAGS_MEMORY);
  }

  public static function after($benchmark_url) {
    global $base_url;
    $profiler_namespace = (!isset($_GET['namespace']))?'drupal-perf':$_GET['namespace'];
    $profiler_extra = (!isset($_GET['extra']))?'':$_GET['extra'];

    $profiler_dir = 'xhprof-kit/uprofiler';

    include_once $profiler_dir . '/uprofiler_lib/utils/uprofiler_lib.php';
    include_once $profiler_dir . '/uprofiler_lib/utils/uprofiler_runs.php';

    $xhprof_data = uprofiler_disable();
    $xhprof_runs = new \uprofilerRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

    // url to the XHProf UI libraries (change the host name and path)
    $profiler_url = sprintf($base_url . '/xhprof-kit/xhprof/xhprof_html/index.php?source=%s&url=%s&run=%s&extra=%s', $profiler_namespace, urlencode($benchmark_url), $run_id, $profiler_extra);
    echo $run_id . '|' . $profiler_namespace . '|' . $profiler_extra . '|' . '<a href="'. $profiler_url .'" target="_blank">Profiler output</a>' . "\n";
  }

  public static function getRunData($run, $source, &$description) {
    // Retrieve run data
    include_once dirname(__FILE__) . '/uprofiler/uprofiler_lib/utils/uprofiler_lib.php';
    include_once dirname(__FILE__) . '/uprofiler/uprofiler_lib/utils/uprofiler_runs.php';
    include_once dirname(__FILE__) . '/uprofiler/uprofiler_lib/display/uprofiler.php';

    $xhprof_runs_impl = new \uprofilerRuns_Default();

    return $xhprof_runs_impl->get_run($run, $source, $description);
  }

  public static function compareRunsMetrics($run1, $run2, $source, &$description_1, &$description_2) {
    include_once dirname(__FILE__) . '/uprofiler/uprofiler_lib/utils/uprofiler_lib.php';
    include_once dirname(__FILE__) . '/uprofiler/uprofiler_lib/utils/uprofiler_runs.php';
    include_once dirname(__FILE__) . '/uprofiler/uprofiler_lib/display/uprofiler.php';

    $xhprof_runs_impl = new \uprofilerRuns_Default();

    $run1_data = $xhprof_runs_impl->get_run($run1, $source, $description_1);
    $run2_data = $xhprof_runs_impl->get_run($run2, $source, $description_2);

    $run_delta = uprofiler_compute_diff($run1_data, $run2_data);
    $symbol_tab  = uprofiler_compute_flat_info($run_delta, $totals);
    $symbol_tab1 = uprofiler_compute_flat_info($run1_data, $totals_1);
    $symbol_tab2 = uprofiler_compute_flat_info($run2_data, $totals_2);

    $metrics = uprofiler_get_metrics($run_delta);
    return array($totals_1, $totals_2, $metrics);
  }

}
