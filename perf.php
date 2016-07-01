<?php

namespace XhprofKit;

define('XHPROF_KIT_PROFILER_DIR', __DIR__ . '/xhprof');

# To use just include somewhere in the code.

// enable xhprof by default
$enable_xhprof = extension_loaded('xhprof');

class XhprofKitRun {

  public $namespace = 'drupal-perf';

  public function __construct($namespace = 'drupal-perf') {
    $this->enableXhprof = extension_loaded('xhprof');
    $this->timeStart = microtime( true );
    $this->namespace = $namespace;
    if ($this->enableXhprof) {
      include_once XHPROF_KIT_PROFILER_DIR . '/xhprof_lib/utils/xhprof_lib.php';
      include_once XHPROF_KIT_PROFILER_DIR . '/xhprof_lib/utils/xhprof_runs.php';
      xhprof_enable(XHPROF_FLAGS_MEMORY);
    }
  }

  static function create($namespace = 'drupal-perf') {
    return new static($namespace);
  }

  public function __destruct() {
    $time_end = ( microtime( true ) - $this->timeStart );
    $output = sprintf( "loop time: |%fs|",
        $time_end
    );

    if ($this->enableXhprof) {
      $xhprof_data = xhprof_disable();
      $xhprof_runs = new \XHProfRuns_Default();
      $run_id = $xhprof_runs->save_run($xhprof_data, $this->namespace);
      $base_url = '';

      // Get path for profiler url.
      $profiler_url = sprintf($base_url . '/xhprof-kit.php/?source=%s&url=%s&run=%s', $this->namespace, 'cli', $run_id);
      $output .= $run_id . '|' . $this->namespace . '|' . $profiler_url . "\n";
    }
    error_log($output, 3, '/tmp/perf.log');
  }
}
