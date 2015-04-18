## About

Note: Using this on a production system is unsupported and potentially dangerous.

## Setup

0. Install XHProf PHP Extension
   * This guide assumes you have the PHP XHProf extension installed already.
1. Install XHProf-Kit in the Drupal directory you want to benchmark
   * $ `/where/is/xhprof-kit/setup.sh`
   * This creates a xhprof-kit symlink in the directory.
   * This will also initialize and update the xhprof submodules.
   * Make sure symlinks are allowed in Apache configuration.
2. Install drush registry rebuild project in drush folder. (optional, only needed for Drupal 7)
   * $ `cd ~/.drush`
   * $ `drush dl registry_rebuild`

## Assumptions

Currently XHProf-Kit assumes that Drupal is installed at 127.0.0.1. If you use
a virtual host, you will need to change the URL in find-min-web.sh.

## Benchmarking

#### Create a scenario

* Install Drupal and setup a page you want to benchmark.
* Use devel and devel_generate to create a suitable page.
* For example: 50 nodes on frontpage

Now lets assume the patch you want to benchmark is in:

* core--issueno

and the vanilla core is in 8.0.x branch.

#### Find a base line

First you will need to find a base line, which is what you compare things to.

Use:

````
./xhprof-kit/benchmark-branch.sh 8.0.x
````

The output will be something like:

````
loop time: |0.345083s|5173f49dd982a|drupal-perf|8.0.x|<a href="http://127.0.0.1/xhprof-kit/xhprof/xhprof_html/index.php?run=5173f49dd982a&source=drupal-perf&extra=8.0.x&url=%2F" target="_blank">Profiler output</a>
````

The fields delimited by '|' are:

1. "loop time" - Just a string
2. loop time - The actual time Drupal needed to load the page.
3. xhprof-identifier - An identifier for the run that can be used to see the run via xhprof_html/index.php utility.
4. xhprof-source - A suffix used to distinguish runs - currently set to "drupal-perf".
5. extra identifier - An identifier used internally by xhprof-kit; usually the branch name.
6. Run URL - The url, where the XHProf output can be seen.

What you need as your baseline is the xhprof identifier: '5173f49dd982a'.

Note that as scenarios and core changes, so will your baseline, so be sure to
always re-calculate it when starting a different benchmarking scenario.

#### Compare Branches

To now benchmark your baseline against 8.0.x again (to verify it is accurate)
and against your new branch with the patch to test, use the following command:

````
./xhprof-kit/benchmark-branches.sh <your baseline identifier> baseline-8.0.x 8.0.x core--issueno
````

The output of this will be something like:

````
=== baseline-8.0.x..8.0.x compared (5173f49dd982a..5172adc3e447f):

ct  : 47,077|47,077|0|0.0%
wt  : 396,923|397,915|992|0.2%
cpu : 370,881|370,295|-586|-0.2%
mu  : 30,347,736|30,349,184|1,448|0.0%
pmu : 30,472,168|30,475,144|2,976|0.0%

=== baseline-8.0.x..core--issueno compared (5172ad6a9a6b6..5172ae167ba6d):

ct  : 47,077|47,207|130|0.3%
wt  : 396,923|399,899|2,976|0.7%
cpu : 370,881|373,186|2,305|0.6%
mu  : 30,347,736|30,440,776|93,040|0.3%
pmu : 30,472,168|30,564,656|92,488|0.3%

---
ct = function calls, wt = wall time, cpu = cpu time used, mu = memory usage, pmu = peak memory usage
````

### Hints

* Update 100 to 1000 in benchmark-branch.sh to have more accurate runs.
  * 1000 takes a while, but is very accurate. It depends on the speed of your machine and how dedicated it can be, if 100 runs are enough to find the minimum.
* 'baseline-8.0.x' is just a name, its a good idea to point out what the benchmark is here, like 'core-stark-10-nodes'.

Benchmarks are saved in xhprof-kit/data/[your baseline identifier]-[baseline name].data files.

* To later show a benchmark again use:
  * ./xhprof-kit/show-bench.sh [your baseline identifier] baseline-8.0.x

## Uploading

As XHProf runs are really useful when published and others can take a look, the easiest is to upload them to a hosted installation of XHProf.

````
./xhprof-kit/upload-bench.sh <your API key> <your xhprof source ID> <your baseline identifier> baseline-8.0.x
````

You can get an API key and source ID by contacting me via my drupal.org contact form (http://drupal.org/user/693738) or Fabianx on FreeNode.

Due to security reasons I only give out API keys to people I know.

## Hosting yourself

However you can also host runs yourself by changing just some little files:

* Download xhprof to / of your server.
* Install the xhprof submodule like usual.
* Change all lionsad.de domains to your server
* Add a hosted/api.php file like:

````
<?php

$api_keys = array(
  '8da2a3349ea4711eb59f57faff1eb05a' => 'drupal-perf-cottser',
);
````

The format is: API KEY => source identifier, which is used as xhprof source argument.

* Apply the following diff to xhprof:

````
diff --git a/xhprof_lib/utils/xhprof_runs.php b/xhprof_lib/utils/xhprof_runs.php
index cde5ff5..561d410 100644
--- a/xhprof_lib/utils/xhprof_runs.php
+++ b/xhprof_lib/utils/xhprof_runs.php
@@ -90,6 +90,9 @@ class XHProfRuns_Default implements iXHProfRuns {
     // we use the xhprof.output_dir ini setting
     // if specified, else we default to the directory
     // in which the error_log file resides.
+    if (empty($dir)) {
+      $dir = '../../data/stored-runs/';
+    }
 
     if (empty($dir)) {
       $dir = ini_get("xhprof.output_dir");
````
